<?php

namespace Modules\Hoardings\Services;

use App\Models\Hoarding;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\DOOH\Models\DOOHPackage;
use Modules\DOOH\Models\DOOHScreen;
use Modules\DOOH\Models\DOOHScreenBrandLogo;
use Modules\DOOH\Models\DOOHScreenMedia;
use Modules\DOOH\Models\DOOHSlot;
use Modules\Hoardings\Models\HoardingBrandLogo;
use Modules\Hoardings\Models\HoardingMedia;
use Modules\Hoardings\Models\HoardingPackage;
use Modules\Hoardings\Models\OOHHoarding;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class HoardingTransferService
{
    private const SCHEMA_VERSION = 2;
    private const MAX_WORKBOOK_BYTES = 50 * 1024 * 1024;
    private const MAX_UNCOMPRESSED_BYTES = 250 * 1024 * 1024;
    private const MAX_MEDIA_BYTES = 25 * 1024 * 1024;
    private const MAX_MEDIA_ROWS = 20000;
    private const MAX_RELATED_RECORDS = 1000;
    private const MAX_MANIFEST_BYTES = 5 * 1024 * 1024;
    private const WORKBOOK_CHUNK_LENGTH = 30000;
    private const MEDIA_FIELDS = ['file_path', 'path_100', 'path_300', 'path_600', 'path_1000', 'path_1500'];
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4', 'webm', 'mov'];
    private const MEDIA_LIBRARY_COLLECTIONS = ['hero_image', 'night_image', 'gallery', 'size_overlay'];
    private const CSV_TRANSFER_HEADERS = [
        'OOHAPP Transfer Format',
        'Schema Version',
        'Vendor Email',
        'Vendor GSTIN',
        'Source Instance',
        'Title',
        'Type',
        'Locality',
        'City',
        'State',
        'Latitude',
        'Longitude',
        'Monthly Price',
        'Hoarding Fields (JSON)',
        'OOH Specifications (JSON)',
        'DOOH Screen (JSON)',
        'OOH Packages (JSON)',
        'DOOH Packages (JSON)',
        'DOOH Slots (JSON)',
        'Geofence (JSON)',
        'Media Records (JSON)',
        'Transfer Record (JSON)',
    ];

    public function export(User $vendor, array $hoardingIds): array
    {
        $this->assertVendorIdentityAvailable($vendor);

        $hoardings = Hoarding::query()
            ->where('vendor_id', $vendor->id)
            ->whereIn('id', $hoardingIds)
            ->with([
                'doohScreen.packages',
                'doohScreen.slots',
                'doohScreen.media',
                'doohScreen.brandLogos',
                'oohMedia',
                'oohPackages',
                'geo',
                'media',
            ])
            ->orderBy('id')
            ->get();

        if ($hoardings->count() !== count(array_unique($hoardingIds))) {
            throw new \InvalidArgumentException('One or more selected hoardings do not belong to your vendor account.');
        }
        if ($hoardings->isEmpty()) {
            throw new \InvalidArgumentException('Select at least one hoarding to export.');
        }

        $workbookPath = tempnam(sys_get_temp_dir(), 'ooh-hoardings-');
        if ($workbookPath === false) {
            throw new \RuntimeException('Could not create the transfer workbook.');
        }
        @unlink($workbookPath);
        $workbookPath .= '.xlsx';
        $spreadsheet = new Spreadsheet();
        $transferSheet = $spreadsheet->getActiveSheet();
        $transferSheet->setTitle('Transfer');
        $mediaSheet = $spreadsheet->createSheet();
        $mediaSheet->setTitle('Media');
        $listingsSheet = $spreadsheet->createSheet();
        $listingsSheet->setTitle('Hoardings');
        $spreadsheet->setActiveSheetIndexByName('Hoardings');
        $mediaFiles = [];
        $skippedHoardings = [];
        try {
            $records = [];
            foreach ($hoardings as $hoarding) {
                $mediaFilesBeforeHoarding = $mediaFiles;
                try {
                    $records[] = $this->exportHoarding($hoarding, $vendor, $mediaFiles);
                } catch (HoardingTransferMediaMissingException $exception) {
                    $mediaFiles = $mediaFilesBeforeHoarding;
                    $skippedHoardings[] = [
                        'id' => $hoarding->id,
                        'title' => $hoarding->title,
                        'reason' => $exception->getMessage(),
                    ];
                }
            }

            if ($records === []) {
                $details = implode("\n", array_column($skippedHoardings, 'reason'));
                throw new \InvalidArgumentException("No hoardings were exported because all selected listings have missing media.\n{$details}");
            }

            $manifest = [
                'format' => 'oohapp-hoarding-transfer',
                'schema_version' => self::SCHEMA_VERSION,
                'created_at' => now()->toIso8601String(),
                'source_instance' => $this->sourceInstanceReference(),
                'vendor' => $this->vendorIdentity($vendor),
                'hoardings' => $records,
            ];
            $manifestJson = json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            if (strlen($manifestJson) > self::MAX_MANIFEST_BYTES) {
                throw new \InvalidArgumentException('The hoarding data is too large for one transfer workbook.');
            }

            $chunks = str_split($manifestJson, self::WORKBOOK_CHUNK_LENGTH);
            $this->setCellString($transferSheet, 'A1', 'OOHAPP_HOARDING_TRANSFER_XLSX');
            $this->setCellString($transferSheet, 'A2', (string) self::SCHEMA_VERSION);
            $this->setCellString($transferSheet, 'A3', (string) count($chunks));
            foreach ($chunks as $index => $chunk) {
                $this->setCellString($transferSheet, 'A' . ($index + 4), $chunk);
            }
            $transferSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

            foreach (['Reference', 'File name', 'MIME type', 'Chunk', 'Base64 data'] as $index => $heading) {
                $this->setCellString($mediaSheet, chr(65 + $index) . '1', $heading);
            }
            $mediaSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
            $this->writeReadableListings($listingsSheet, $records);
            $row = 2;
            $totalMediaBytes = 0;
            foreach ($mediaFiles as $reference => $file) {
                $size = filesize($file['path']);
                if ($size === false || $size < 1 || $size > self::MAX_MEDIA_BYTES || $totalMediaBytes + $size > self::MAX_UNCOMPRESSED_BYTES) {
                    throw new \InvalidArgumentException('A media file is empty, too large, or the total media exceeds the 250 MB safety limit.');
                }
                $totalMediaBytes += $size;
                $contents = file_get_contents($file['path']);
                if (!is_string($contents)) {
                    throw new \RuntimeException('A media file could not be read for the transfer workbook.');
                }
                $chunks = str_split(base64_encode($contents), self::WORKBOOK_CHUNK_LENGTH);
                foreach ($chunks as $chunkIndex => $chunk) {
                    if ($row > self::MAX_MEDIA_ROWS + 1) {
                        throw new \InvalidArgumentException('The workbook contains too many media parts. Export fewer hoardings at a time.');
                    }
                    $this->setCellString($mediaSheet, 'A' . $row, $reference);
                    $this->setCellString($mediaSheet, 'B' . $row, $file['name']);
                    $this->setCellString($mediaSheet, 'C' . $row, $file['mime_type']);
                    $this->setCellString($mediaSheet, 'D' . $row, (string) $chunkIndex);
                    $this->setCellString($mediaSheet, 'E' . $row, $chunk);
                    $row++;
                }
            }

            (new Xlsx($spreadsheet))->save($workbookPath);
        } catch (Throwable $exception) {
            @unlink($workbookPath);
            throw $exception;
        } finally {
            $spreadsheet->disconnectWorksheets();
        }

        $workbookSize = filesize($workbookPath);
        if ($workbookSize === false) {
            @unlink($workbookPath);
            throw new \RuntimeException('Could not verify the generated transfer workbook.');
        }
        if ($workbookSize > self::MAX_WORKBOOK_BYTES) {
            @unlink($workbookPath);
            throw new \InvalidArgumentException('The generated Excel workbook exceeds the 50 MB transfer limit. Export fewer hoardings at a time.');
        }

        return [
            'path' => $workbookPath,
            'filename' => 'oohapp-hoardings-' . now()->format('Ymd-His') . '.xlsx',
            'skipped_hoardings' => $skippedHoardings,
        ];
    }

    public function exportCsvTransfer(User $vendor, array $hoardingIds): array
    {
        $workbook = $this->export($vendor, $hoardingIds);
        $bundlePath = tempnam(sys_get_temp_dir(), 'ooh-hoardings-csv-');
        if ($bundlePath === false) {
            @unlink($workbook['path']);
            throw new \RuntimeException('Could not create the CSV transfer bundle.');
        }

        try {
            [$manifest, $mediaFiles, $temporaryDirectory] = $this->readWorkbook($workbook['path']);
        } catch (Throwable $exception) {
            @unlink($workbook['path']);
            @unlink($bundlePath);
            throw $exception;
        }
        $csvPath = tempnam(sys_get_temp_dir(), 'ooh-hoardings-csv-data-');
        if ($csvPath === false) {
            @unlink($workbook['path']);
            @unlink($bundlePath);
            $this->removeTemporaryDirectory($temporaryDirectory);
            throw new \RuntimeException('Could not create the transfer CSV.');
        }

        try {
            $stream = fopen($csvPath, 'wb');
            if ($stream === false) {
                throw new \RuntimeException('Could not write the transfer CSV.');
            }

            try {
                if (fputcsv($stream, self::CSV_TRANSFER_HEADERS, ',', '"', '') === false) {
                    throw new \RuntimeException('Could not write the transfer CSV headings.');
                }
                foreach ($manifest['hoardings'] as $record) {
                    $attributes = $record['hoarding'];
                    $values = [
                        'OOHAPP_HOARDING_TRANSFER_CSV',
                        (string) self::SCHEMA_VERSION,
                        (string) ($manifest['vendor']['email'] ?? ''),
                        (string) ($manifest['vendor']['gstin'] ?? ''),
                        (string) ($manifest['source_instance'] ?? ''),
                        (string) ($attributes['title'] ?? ''),
                        strtoupper($record['type']),
                        (string) ($attributes['locality'] ?? ''),
                        (string) ($attributes['city'] ?? ''),
                        (string) ($attributes['state'] ?? ''),
                        (string) ($attributes['latitude'] ?? ''),
                        (string) ($attributes['longitude'] ?? ''),
                        (string) ($attributes['monthly_price'] ?? ''),
                        $this->readableJson($attributes),
                        $this->readableJson($record['ooh']),
                        $this->readableJson($record['dooh']),
                        $this->readableJson($record['ooh_packages']),
                        $this->readableJson($record['dooh_packages']),
                        $this->readableJson($record['slots']),
                        $this->readableJson($record['geo']),
                        $this->readableJson([
                            'ooh_media' => $record['media'],
                            'ooh_brand_logos' => $record['brand_logos'],
                            'dooh_media' => $record['screen_media'],
                            'dooh_brand_logos' => $record['screen_brand_logos'],
                            'media_library' => $record['library_media'],
                        ]),
                        json_encode($record, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                    ];
                    for ($index = 5; $index <= 12; $index++) {
                        $values[$index] = $this->safeCsvDisplayValue($values[$index]);
                    }
                    if (fputcsv($stream, $values, ',', '"', '') === false) {
                        throw new \RuntimeException('Could not write a transfer CSV row.');
                    }
                }
            } finally {
                fclose($stream);
            }

            $bundle = new \ZipArchive();
            if ($bundle->open($bundlePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Could not open the CSV transfer bundle for writing.');
            }
            try {
                if (!$bundle->addFile($csvPath, 'hoardings.csv')) {
                    throw new \RuntimeException('The transfer CSV could not be added to the package.');
                }
                foreach ($mediaFiles as $reference => $file) {
                    if (!$bundle->addFile($file['path'], $reference)) {
                        throw new \RuntimeException('A media file could not be added to the CSV transfer bundle.');
                    }
                }
            } finally {
                $bundle->close();
            }
            $bundleSize = filesize($bundlePath);
            if ($bundleSize === false || $bundleSize > self::MAX_UNCOMPRESSED_BYTES) {
                throw new \InvalidArgumentException('The CSV transfer bundle exceeds the 250 MB package limit.');
            }

            return [
                'path' => $bundlePath,
                'filename' => 'oohapp-hoardings-transfer-' . now()->format('Ymd-His') . '.zip',
            ];
        } catch (Throwable $exception) {
            @unlink($bundlePath);
            throw $exception;
        } finally {
            @unlink($workbook['path']);
            @unlink($csvPath);
            $this->removeTemporaryDirectory($temporaryDirectory);
        }
    }

    private function writeReadableListings($sheet, array $records): void
    {
        $headings = [
            'Title',
            'Type',
            'Hoarding fields (JSON)',
            'Locality',
            'City',
            'State',
            'Latitude',
            'Longitude',
            'Monthly price',
            'OOH specifications (JSON)',
            'DOOH screen (JSON)',
            'OOH packages (JSON)',
            'DOOH packages (JSON)',
            'DOOH slots (JSON)',
            'Geofence (JSON)',
            'Media records (JSON)',
        ];
        foreach ($headings as $index => $heading) {
            $this->setCellString($sheet, chr(65 + $index) . '1', $heading);
        }

        foreach ($records as $index => $record) {
            $row = $index + 2;
            $attributes = $record['hoarding'];
            $values = [
                $attributes['title'] ?? '',
                strtoupper($record['type']),
                $this->readableJson($attributes),
                $attributes['locality'] ?? '',
                $attributes['city'] ?? '',
                $attributes['state'] ?? '',
                $attributes['latitude'] ?? '',
                $attributes['longitude'] ?? '',
                $attributes['monthly_price'] ?? '',
                $this->readableJson($record['ooh']),
                $this->readableJson($record['dooh']),
                $this->readableJson($record['ooh_packages']),
                $this->readableJson($record['dooh_packages']),
                $this->readableJson($record['slots']),
                $this->readableJson($record['geo']),
                $this->readableJson([
                    'ooh_media' => $record['media'],
                    'ooh_brand_logos' => $record['brand_logos'],
                    'dooh_media' => $record['screen_media'],
                    'dooh_brand_logos' => $record['screen_brand_logos'],
                    'media_library' => $record['library_media'],
                ]),
            ];
            foreach ($values as $column => $value) {
                $this->setCellString($sheet, chr(65 + $column) . $row, (string) $value);
            }
        }
    }

    public function inspect(string $workbookPath, User $vendor): array
    {
        $this->assertVendorIdentityAvailable($vendor);
        [$manifest, $mediaFiles, $temporaryDirectory] = $this->readWorkbook($workbookPath);
        try {
            $this->assertVendorMatches($manifest['vendor'] ?? null, $vendor);
            $records = $manifest['hoardings'];
            $this->validateRecords($mediaFiles, $records);
            return $this->buildPreview($records, $vendor, $manifest['source_instance'] ?? null);
        } finally {
            $this->removeTemporaryDirectory($temporaryDirectory);
        }
    }

    public function inspectCsv(string $csvPath, User $vendor, ?string $mediaArchivePath = null): array
    {
        return $this->inspectCsvInput($csvPath, $vendor, $mediaArchivePath, false);
    }

    public function inspectCsvBundle(string $bundlePath, User $vendor): array
    {
        return $this->inspectCsvInput($bundlePath, $vendor, null, true);
    }

    public function import(string $workbookPath, User $vendor): array
    {
        $this->inspect($workbookPath, $vendor);
        [$manifest, $mediaFiles, $temporaryDirectory] = $this->readWorkbook($workbookPath);
        try {
            $this->assertVendorMatches($manifest['vendor'] ?? null, $vendor);
            $this->validateRecords($mediaFiles, $manifest['hoardings']);
            return $this->importRecords($manifest, $mediaFiles, $vendor);
        } finally {
            $this->removeTemporaryDirectory($temporaryDirectory);
        }
    }

    public function importCsv(string $csvPath, User $vendor, ?string $mediaArchivePath = null): array
    {
        $this->inspectCsv($csvPath, $vendor, $mediaArchivePath);
        [$manifest, $mediaFiles, $temporaryDirectory] = $this->readCsvInput($csvPath, $mediaArchivePath, false);
        try {
            $this->assertVendorMatches($manifest['vendor'] ?? null, $vendor);
            $this->validateCsvRecords($manifest['hoardings'], $mediaFiles, $manifest['record_lines'] ?? []);
            return $this->importRecords($manifest, $mediaFiles, $vendor);
        } finally {
            $this->removeTemporaryDirectory($temporaryDirectory);
        }
    }

    public function importCsvBundle(string $bundlePath, User $vendor): array
    {
        $this->inspectCsvBundle($bundlePath, $vendor);
        [$manifest, $mediaFiles, $temporaryDirectory] = $this->readCsvInput($bundlePath, null, true);
        try {
            $this->assertVendorMatches($manifest['vendor'] ?? null, $vendor);
            $this->validateCsvRecords($manifest['hoardings'], $mediaFiles, $manifest['record_lines'] ?? []);
            return $this->importRecords($manifest, $mediaFiles, $vendor);
        } finally {
            $this->removeTemporaryDirectory($temporaryDirectory);
        }
    }

    private function inspectCsvInput(string $path, User $vendor, ?string $mediaArchivePath, bool $isBundle): array
    {
        $this->assertVendorIdentityAvailable($vendor);
        [$manifest, $mediaFiles, $temporaryDirectory] = $this->readCsvInput($path, $mediaArchivePath, $isBundle);
        try {
            $this->assertVendorMatches($manifest['vendor'] ?? null, $vendor);
            $this->validateCsvRecords($manifest['hoardings'], $mediaFiles, $manifest['record_lines'] ?? []);
            return $this->buildPreview($manifest['hoardings'], $vendor, $manifest['source_instance'] ?? null);
        } finally {
            $this->removeTemporaryDirectory($temporaryDirectory);
        }
    }

    private function importRecords(array $manifest, array $mediaFiles, User $vendor): array
    {
        $createdPaths = [];
        $result = ['created' => 0, 'skipped' => 0, 'hoardings' => []];
        $legacySourceReferences = empty($manifest['source_instance'])
            ? $this->existingSourceReferences($vendor)
            : [];

        try {
            DB::transaction(function () use ($manifest, $mediaFiles, $vendor, &$createdPaths, &$result, $legacySourceReferences) {
                foreach ($manifest['hoardings'] as $record) {
                    $existing = DB::table('hoarding_transfer_imports')
                        ->where('vendor_id', $vendor->id)
                        ->where('source_reference', $record['source_reference'])
                        ->lockForUpdate()
                        ->first();

                    $existingHoardingIsActive = $existing
                        && $existing->imported_hoarding_id
                        && Hoarding::withTrashed()
                            ->whereKey($existing->imported_hoarding_id)
                            ->whereNull('deleted_at')
                            ->exists();

                    if ($existingHoardingIsActive) {
                        $result['skipped']++;
                        $result['hoardings'][] = [
                            'title' => $record['hoarding']['title'],
                            'status' => 'skipped',
                            'id' => $existing->imported_hoarding_id,
                        ];
                        continue;
                    }

                    if ($this->isExistingSourceListing($manifest, $record, $vendor, $legacySourceReferences)) {
                        $result['skipped']++;
                        $result['hoardings'][] = [
                            'title' => $record['hoarding']['title'],
                            'status' => 'skipped — original listing still exists in this database',
                            'id' => $record['source_id'] ?? $legacySourceReferences[$record['source_reference']] ?? null,
                        ];
                        continue;
                    }

                    $hoarding = $this->createHoarding($record, $vendor);
                    $this->restoreMedia($mediaFiles, $record, $hoarding, $createdPaths);
                    $this->restoreRelatedRecords($mediaFiles, $record, $hoarding, $createdPaths);
                    $importRecord = [
                        'source_title' => $record['hoarding']['title'],
                        'hoarding_type' => $record['type'],
                        'imported_hoarding_id' => $hoarding->id,
                        'updated_at' => now(),
                    ];
                    if ($existing) {
                        DB::table('hoarding_transfer_imports')
                            ->where('id', $existing->id)
                            ->update($importRecord);
                    } else {
                        DB::table('hoarding_transfer_imports')->insert($importRecord + [
                            'vendor_id' => $vendor->id,
                            'source_reference' => $record['source_reference'],
                            'created_at' => now(),
                        ]);
                    }
                    $result['created']++;
                    $result['hoardings'][] = [
                        'title' => $hoarding->title,
                        'status' => 'created as draft',
                        'id' => $hoarding->id,
                    ];
                }
            });
        } catch (Throwable $exception) {
            foreach ($createdPaths as $path) {
                if (is_array($path)) {
                    Storage::disk($path['disk'])->deleteDirectory($path['directory']);
                    continue;
                }
                Storage::disk('public')->delete($path);
            }
            throw $exception;
        }

        return $result;
    }

    private function exportHoarding(Hoarding $hoarding, User $vendor, array &$mediaFiles): array
    {
        $identity = $this->vendorIdentity($vendor);
        $reference = $this->sourceReference(
            $identity,
            (int) $hoarding->getKey(),
            optional($hoarding->created_at)->toISOString() ?? ''
        );

        $record = [
            'source_reference' => $reference,
            'source_id' => (int) $hoarding->getKey(),
            'source_created_at' => $hoarding->getRawOriginal('created_at'),
            'type' => strtolower((string) $hoarding->hoarding_type),
            'hoarding' => $this->safeAttributes($hoarding, ['id', 'vendor_id', 'slug', 'status', 'current_step', 'is_featured', 'is_recommended', 'view_count', 'bookings_count', 'last_booked_at', 'submitted_at', 'verified_at', 'approved_at', 'rejected_at', 'verified_by', 'approved_by', 'rejected_by', 'rejection_reason', 'admin_notes', 'commission_percent', 'deleted_at']),
            'geo' => $hoarding->geo
                ? $this->safeAttributes($hoarding->geo, ['id', 'hoarding_id', 'created_at', 'updated_at'])
                : null,
            'ooh' => null,
            'dooh' => null,
            'ooh_packages' => [],
            'dooh_packages' => [],
            'slots' => [],
            'media' => [],
            'brand_logos' => [],
            'screen_media' => [],
            'screen_brand_logos' => [],
            'library_media' => [],
        ];

        if ($hoarding->hoarding_type === Hoarding::TYPE_OOH && $hoarding->oohHoarding) {
            $record['ooh'] = $this->safeAttributes($hoarding->oohHoarding, ['id', 'hoarding_id', 'created_at', 'updated_at', 'deleted_at']);
            $record['ooh_packages'] = $hoarding->oohPackages
                ->map(fn (HoardingPackage $package) => $this->safeAttributes($package, ['id', 'hoarding_id', 'vendor_id', 'package_code', 'created_at', 'updated_at', 'deleted_at']))
                ->all();
            $record['brand_logos'] = HoardingBrandLogo::query()->where('hoarding_id', $hoarding->id)
                ->orderBy('sort_order')
                ->get()
                ->map(function (HoardingBrandLogo $logo) use ($reference, &$mediaFiles, $hoarding) {
                    return $this->exportFileRecord(
                        $reference,
                        'ooh-logo',
                        $logo->getAttributes(),
                        ['id', 'hoarding_id', 'created_at', 'updated_at', 'deleted_at'],
                        $mediaFiles,
                        $this->hoardingMediaContext($hoarding, 'brand logo')
                    );
                })
                ->filter()
                ->values()
                ->all();
        } elseif ($hoarding->hoarding_type === Hoarding::TYPE_DOOH && $hoarding->doohScreen) {
            $screen = $hoarding->doohScreen;
            $record['dooh'] = $this->safeAttributes($screen, ['id', 'hoarding_id', 'vendor_id', 'external_screen_id', 'sync_status', 'last_synced_at', 'sync_metadata', 'status', 'commission_percent', 'created_at', 'updated_at', 'deleted_at']);
            $record['dooh_packages'] = $screen->packages
                ->map(fn (DOOHPackage $package) => $this->safeAttributes($package, ['id', 'dooh_screen_id', 'created_at', 'updated_at', 'deleted_at']))
                ->all();
            $record['slots'] = $screen->slots
                ->filter(fn (DOOHSlot $slot) => !$slot->booking_id)
                ->map(fn (DOOHSlot $slot) => $this->safeAttributes($slot, ['id', 'dooh_screen_id', 'booking_id', 'start_date', 'end_date', 'total_booking_days', 'total_booking_cost', 'created_at', 'updated_at', 'deleted_at']))
                ->all();
            $record['screen_media'] = $screen->media
                ->map(function (DOOHScreenMedia $media) use ($reference, &$mediaFiles, $hoarding) {
                    return $this->exportMediaRecord(
                        $reference,
                        'dooh-media',
                        $media,
                        $mediaFiles,
                        $this->hoardingMediaContext($hoarding, 'screen media')
                    );
                })
                ->filter()
                ->values()
                ->all();
            $record['screen_brand_logos'] = $screen->brandLogos
                ->map(function (DOOHScreenBrandLogo $logo) use ($reference, &$mediaFiles, $hoarding) {
                    return $this->exportFileRecord(
                        $reference,
                        'dooh-logo',
                        $logo->getAttributes(),
                        ['id', 'dooh_screen_id', 'created_at', 'updated_at', 'deleted_at'],
                        $mediaFiles,
                        $this->hoardingMediaContext($hoarding, 'screen brand logo')
                    );
                })
                ->filter()
                ->values()
                ->all();
        } else {
            throw new \InvalidArgumentException('Hoarding #' . $hoarding->getKey() . ' is missing its matching OOH or DOOH detail record.');
        }

        $record['media'] = $hoarding->oohMedia
            ->map(function (HoardingMedia $media) use ($reference, &$mediaFiles, $hoarding) {
                return $this->exportMediaRecord(
                    $reference,
                    'ooh-media',
                    $media,
                    $mediaFiles,
                    $this->hoardingMediaContext($hoarding, 'media')
                );
            })
            ->filter()
            ->values()
            ->all();

        $record['library_media'] = $hoarding->getMedia()
            ->map(function (Media $media, int $index) use ($reference, &$mediaFiles) {
                $file = $media->getPath();
                if (!is_file($file)) {
                    throw new HoardingTransferMediaMissingException('A hoarding media file is missing from storage and cannot be included in the transfer.');
                }
                $mediaReference = "media/{$reference}/library-{$index}-" . $this->safeFileName($media->file_name);
                $this->registerMediaFile($mediaFiles, $mediaReference, $file, $media->file_name, (string) ($media->mime_type ?: 'application/octet-stream'));
                return [
                    'collection' => $media->collection_name,
                    'name' => $media->name,
                    'file_name' => $this->safeFileName($media->file_name),
                    'mime_type' => $media->mime_type,
                    'custom_properties' => $media->custom_properties,
                    'file_reference' => $mediaReference,
                ];
            })
            ->filter()
            ->values()
            ->all();

        return $record;
    }

    private function exportMediaRecord(
        string $reference,
        string $prefix,
        $media,
        array &$mediaFiles,
        string $context
    ): ?array
    {
        return $this->exportFileRecord(
            $reference,
            $prefix,
            $media->getAttributes(),
            ['id', 'hoarding_id', 'dooh_screen_id', 'created_at', 'updated_at', 'deleted_at'],
            $mediaFiles,
            $context
        );
    }

    private function exportFileRecord(
        string $reference,
        string $prefix,
        array $attributes,
        array $excluded,
        array &$mediaFiles,
        string $context
    ): ?array
    {
        $files = [];
        $fileAttributes = [];
        foreach (self::MEDIA_FIELDS as $field) {
            $path = $attributes[$field] ?? null;
            if (!$path) {
                continue;
            }
            $fileAttributes[] = $field;
            $fullPath = Storage::disk('public')->path($path);
            if (!is_file($fullPath)) {
                continue;
            }
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                throw new \InvalidArgumentException('A hoarding media file has an unsupported extension.');
            }
            $mediaReference = "media/{$reference}/{$prefix}-" . Str::uuid() . "-{$field}.{$extension}";
            $mimeType = (string) ($attributes['mime_type'] ?? '');
            $this->registerMediaFile(
                $mediaFiles,
                $mediaReference,
                $fullPath,
                basename($path),
                $mimeType !== '' ? $mimeType : (mime_content_type($fullPath) ?: 'application/octet-stream')
            );
            $files[$field] = $mediaReference;
        }

        if (!$files) {
            $recordId = isset($attributes['id']) ? ' #' . $attributes['id'] : '';
            throw new HoardingTransferMediaMissingException(
                "A hoarding media record has no available file to include in the transfer. {$context}, media record{$recordId}: the database references a file that is missing from public storage. Restore the original media files or replace/remove the broken media entry before exporting."
            );
        }

        if (!isset($files['file_path']) && $files) {
            $fallbackField = isset($files['path_1500']) ? 'path_1500' : array_key_first($files);
            $files['file_path'] = $files[$fallbackField];
        }

        return [
            'attributes' => array_diff_key($attributes, array_flip(array_merge($excluded, $fileAttributes))),
            'files' => $files,
        ];
    }

    private function hoardingMediaContext(Hoarding $hoarding, string $mediaType): string
    {
        return sprintf(
            '%s for hoarding "%s" (#%s)',
            ucfirst($mediaType),
            $hoarding->title,
            $hoarding->getKey()
        );
    }

    private function registerMediaFile(array &$mediaFiles, string $reference, string $path, string $name, string $mimeType): void
    {
        $size = filesize($path);
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (
            $size === false
            || $size < 1
            || $size > self::MAX_MEDIA_BYTES
            || !in_array($extension, self::ALLOWED_EXTENSIONS, true)
            || !$this->isValidWorkbookMediaReference($reference)
        ) {
            throw new \InvalidArgumentException('A media file is missing, too large, or has an unsupported format.');
        }
        $mediaFiles[$reference] = [
            'path' => $path,
            'name' => $this->safeFileName($name),
            'mime_type' => $mimeType,
        ];
    }

    private function buildPreview(array $records, User $vendor, mixed $sourceInstance): array
    {
        $references = array_column($records, 'source_reference');
        if (count($references) !== count(array_unique($references))) {
            throw new \InvalidArgumentException('The workbook contains duplicate source hoarding references.');
        }

        $knownImports = DB::table('hoarding_transfer_imports')
            ->where('vendor_id', $vendor->id)
            ->whereIn('source_reference', $references)
            ->get()
            ->keyBy('source_reference');

        $legacySourceReferences = empty($sourceInstance)
            ? $this->existingSourceReferences($vendor)
            : [];
        $items = [];
        foreach ($records as $record) {
            $title = trim((string) ($record['hoarding']['title'] ?? ''));
            if ($title === '' || mb_strlen($title) > 255) {
                throw new \InvalidArgumentException('Every hoarding must have a title of 255 characters or fewer.');
            }

            $existingImport = $knownImports->get($record['source_reference']);
            $alreadyImported = $existingImport
                && $existingImport->imported_hoarding_id
                && Hoarding::withTrashed()
                    ->whereKey($existingImport->imported_hoarding_id)
                    ->whereNull('deleted_at')
                    ->exists();
            $sourceExists = !$alreadyImported
                && $this->isExistingSourceListing(
                    ['source_instance' => $sourceInstance],
                    $record,
                    $vendor,
                    $legacySourceReferences
                );
            $items[] = [
                'source_reference' => $record['source_reference'],
                'title' => $title,
                'type' => strtoupper($record['type']),
                'location' => $this->locationLabel($record['hoarding']),
                'media_count' => count($record['media'] ?? []) + count($record['brand_logos'] ?? []) + count($record['screen_media'] ?? []) + count($record['screen_brand_logos'] ?? []) + count($record['library_media'] ?? []),
                'status' => $alreadyImported ? 'duplicate' : ($sourceExists ? 'source_exists' : 'ready'),
                'existing_hoarding_id' => $alreadyImported ? $existingImport->imported_hoarding_id : null,
            ];
        }

        return [
            'vendor_name' => $vendor->name,
            'items' => $items,
            'ready_count' => collect($items)->where('status', 'ready')->count(),
            'duplicate_count' => collect($items)->where('status', 'duplicate')->count(),
            'source_exists_count' => collect($items)->where('status', 'source_exists')->count(),
        ];
    }

    private function sourceInstanceReference(): string
    {
        return hash('sha256', implode('|', [
            app()->environment(),
            strtolower(trim((string) config('app.url'))),
            (string) DB::connection()->getDatabaseName(),
        ]));
    }

    private function sourceReference(array $identity, int $sourceId, string $createdAt): string
    {
        return hash('sha256', implode('|', [
            $identity['email'] ?? $identity['gstin'],
            (string) $sourceId,
            $createdAt,
        ]));
    }

    private function existingSourceReferences(User $vendor): array
    {
        $identity = $this->vendorIdentity($vendor);
        $references = [];
        foreach (Hoarding::query()->where('vendor_id', $vendor->id)->get() as $hoarding) {
            $reference = $this->sourceReference(
                $identity,
                (int) $hoarding->getKey(),
                optional($hoarding->created_at)->toISOString() ?? ''
            );
            $references[$reference] = (int) $hoarding->getKey();
        }
        return $references;
    }

    private function isExistingSourceListing(array $manifest, array $record, User $vendor, array $legacySourceReferences = []): bool
    {
        if (isset($legacySourceReferences[$record['source_reference'] ?? ''])) {
            return true;
        }
        if (
            !isset($manifest['source_instance'], $record['source_id'], $record['source_created_at'])
            || !is_string($manifest['source_instance'])
            || !hash_equals($this->sourceInstanceReference(), $manifest['source_instance'])
            || !is_numeric($record['source_id'])
            || !is_string($record['source_created_at'])
        ) {
            return false;
        }

        return Hoarding::query()
            ->whereKey((int) $record['source_id'])
            ->where('vendor_id', $vendor->id)
            ->where('created_at', $record['source_created_at'])
            ->exists();
    }

    private function readWorkbook(string $workbookPath): array
    {
        if (!is_file($workbookPath) || filesize($workbookPath) > self::MAX_WORKBOOK_BYTES) {
            throw new \InvalidArgumentException('The Excel workbook is missing or exceeds the 50 MB upload limit.');
        }

        $temporaryDirectory = $this->createTemporaryDirectory();

        try {
            try {
                $spreadsheet = IOFactory::load($workbookPath);
            } catch (Throwable $exception) {
                throw new \InvalidArgumentException('The uploaded file is not a valid OOHAPP Excel transfer workbook.', 0, $exception);
            }

            try {
                $transferSheet = $spreadsheet->getSheetByName('Transfer');
                $mediaSheet = $spreadsheet->getSheetByName('Media');
                if (!$transferSheet || !$mediaSheet) {
                    throw new \InvalidArgumentException('This Excel file is not an OOHAPP transfer workbook. Re-export it from the vendor panel using Export > Excel, then upload the downloaded .xlsx without resaving it.');
                }
                if ($transferSheet->getCell('A1')->getValue() !== 'OOHAPP_HOARDING_TRANSFER_XLSX') {
                    throw new \InvalidArgumentException('This is a regular Excel report, not a transfer workbook. Re-export the hoardings using Export > Excel and upload that downloaded .xlsx file.');
                }

                $workbookVersion = filter_var($transferSheet->getCell('A2')->getValue(), FILTER_VALIDATE_INT);
                if (!in_array($workbookVersion, [1, self::SCHEMA_VERSION], true)) {
                    throw new \InvalidArgumentException('This transfer workbook version is not supported. Re-export it from the current vendor panel and upload the new .xlsx file.');
                }

                $chunkCount = filter_var($transferSheet->getCell('A3')->getValue(), FILTER_VALIDATE_INT);
                if ($chunkCount === false || $chunkCount < 1 || $chunkCount > 200) {
                    throw new \InvalidArgumentException('The workbook manifest has an invalid size.');
                }
                $manifestJson = '';
                for ($row = 4; $row < 4 + $chunkCount; $row++) {
                    $chunk = $transferSheet->getCell('A' . $row)->getValue();
                    if (!is_string($chunk) || strlen($chunk) > self::WORKBOOK_CHUNK_LENGTH) {
                        throw new \InvalidArgumentException('The workbook manifest contains an invalid data chunk.');
                    }
                    $manifestJson .= $chunk;
                }
                if (strlen($manifestJson) > self::MAX_MANIFEST_BYTES) {
                    throw new \InvalidArgumentException('The workbook manifest exceeds the supported size.');
                }
                try {
                    $manifest = json_decode($manifestJson, true, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException $exception) {
                    throw new \InvalidArgumentException('The workbook manifest is not valid JSON.', 0, $exception);
                }
                if (
                    !is_array($manifest)
                    || ($manifest['format'] ?? null) !== 'oohapp-hoarding-transfer'
                    || (int) ($manifest['schema_version'] ?? 0) !== $workbookVersion
                    || !is_array($manifest['hoardings'] ?? null)
                    || count($manifest['hoardings']) < 1
                ) {
                    throw new \InvalidArgumentException('The workbook has an unsupported format or invalid number of hoardings.');
                }
                if ($workbookVersion === 1) {
                    $manifest = $this->upgradeLegacyManifest($manifest);
                }

                $mediaFiles = $this->readWorkbookMedia($mediaSheet, $temporaryDirectory);
            } finally {
                $spreadsheet->disconnectWorksheets();
            }

            return [$manifest, $mediaFiles, $temporaryDirectory];
        } catch (Throwable $exception) {
            $this->removeTemporaryDirectory($temporaryDirectory);
            throw $exception;
        }
    }

    private function readCsvInput(string $path, ?string $mediaArchivePath, bool $isBundle): array
    {
        $temporaryDirectory = $this->createTemporaryDirectory();
        try {
            if ($isBundle) {
                if (!is_file($path) || filesize($path) > self::MAX_UNCOMPRESSED_BYTES) {
                    throw new \InvalidArgumentException('The CSV transfer bundle is missing or too large.');
                }
                $archive = new \ZipArchive();
                if ($archive->open($path) !== true) {
                    throw new \InvalidArgumentException('The CSV transfer bundle is not a valid ZIP file.');
                }
                try {
                    $csvEntries = 0;
                    for ($index = 0; $index < $archive->numFiles; $index++) {
                        if ($archive->getNameIndex($index) === 'hoardings.csv') {
                            $csvEntries++;
                        }
                    }
                    if ($csvEntries !== 1) {
                        throw new \InvalidArgumentException('The CSV transfer bundle must contain exactly one hoardings.csv file.');
                    }
                    $csvContents = $archive->getFromName('hoardings.csv');
                } finally {
                    $archive->close();
                }
                if (!is_string($csvContents) || strlen($csvContents) > self::MAX_WORKBOOK_BYTES) {
                    throw new \InvalidArgumentException('The transfer bundle is missing hoardings.csv or the CSV is too large.');
                }
                $csvPath = $temporaryDirectory . DIRECTORY_SEPARATOR . 'hoardings.csv';
                if (file_put_contents($csvPath, $csvContents) !== strlen($csvContents)) {
                    throw new \RuntimeException('Could not stage the transfer CSV for import.');
                }
                $mediaArchivePath = $path;
            } else {
                if (!is_file($path) || filesize($path) > self::MAX_WORKBOOK_BYTES) {
                    throw new \InvalidArgumentException('The transfer CSV is missing or exceeds the 50 MB upload limit.');
                }
                $csvPath = $path;
            }

            $mediaFiles = $mediaArchivePath
                ? $this->readCsvMediaArchive($mediaArchivePath, $temporaryDirectory)
                : [];
            $manifest = $this->parseTransferCsv($csvPath);
            return [$manifest, $mediaFiles, $temporaryDirectory];
        } catch (Throwable $exception) {
            $this->removeTemporaryDirectory($temporaryDirectory);
            throw $exception;
        }
    }

    private function parseTransferCsv(string $csvPath): array
    {
        $stream = fopen($csvPath, 'rb');
        if ($stream === false) {
            throw new \InvalidArgumentException('The transfer CSV could not be opened.');
        }

        try {
            $headers = fgetcsv($stream, null, ',', '"', '');
            if (!is_array($headers)) {
                throw new \InvalidArgumentException('The transfer CSV is empty.');
            }
            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
            if (
                count($headers) !== count(self::CSV_TRANSFER_HEADERS)
                || array_values($headers) !== self::CSV_TRANSFER_HEADERS
            ) {
                if (array_values($headers) === ['ID', 'Title', 'Type', 'Location', 'Bookings', 'Status']) {
                    throw new \InvalidArgumentException(
                        'This is a hoarding report CSV, not a transfer file. To import, choose Export > CSV transfer bundle (.zip with media) and upload the ZIP without extracting it, or upload the .xlsx transfer workbook. CSV/PDF reports remain report-only.'
                    );
                }
                throw new \InvalidArgumentException(
                    'The CSV columns do not match the OOHAPP transfer format. Upload the complete CSV transfer bundle (.zip with media) without extracting it, or upload the .xlsx transfer workbook. CSV/PDF reports cannot be imported.'
                );
            }

            $records = [];
            $rowErrors = [];
            $metadata = null;
            $transferBytes = 0;
            $line = 1;
            while (($row = fgetcsv($stream, null, ',', '"', '')) !== false) {
                $line++;
                if ($row === [null] || $row === []) {
                    continue;
                }
                if (count($row) !== count($headers)) {
                    $rowErrors[] = "Row {$line}: the number of CSV values does not match the header.";
                    continue;
                }

                $values = array_combine($headers, $row);
                if (
                    ($values['OOHAPP Transfer Format'] ?? null) !== 'OOHAPP_HOARDING_TRANSFER_CSV'
                    || (int) ($values['Schema Version'] ?? 0) !== self::SCHEMA_VERSION
                ) {
                    $rowErrors[] = "Row {$line}: unsupported or missing OOHAPP transfer CSV format/version.";
                    continue;
                }

                $rowMetadata = [
                    'email' => trim((string) $values['Vendor Email']),
                    'gstin' => trim((string) $values['Vendor GSTIN']),
                    'source_instance' => trim((string) $values['Source Instance']),
                ];
                if ($metadata !== null && $metadata !== $rowMetadata) {
                    $rowErrors[] = "Row {$line}: vendor or source metadata differs from other rows.";
                    continue;
                }
                $metadata = $rowMetadata;

                try {
                    $transferJson = (string) $values['Transfer Record (JSON)'];
                    $transferBytes += strlen($transferJson);
                    if ($transferBytes > self::MAX_MANIFEST_BYTES) {
                        $rowErrors[] = "Row {$line}: transfer data exceeds the supported 5 MB limit.";
                        continue;
                    }
                    $record = json_decode($transferJson, true, 512, JSON_THROW_ON_ERROR);
                    if (!is_array($record)) {
                        throw new \JsonException('Transfer record must be a JSON object.');
                    }
                    $records[] = ['line' => $line, 'record' => $record];
                } catch (\JsonException $exception) {
                    $rowErrors[] = "Row {$line}: transfer record JSON is invalid.";
                }
            }

            if ($records === [] && $rowErrors === []) {
                throw new \InvalidArgumentException('The transfer CSV does not contain any hoarding rows.');
            }
            if ($rowErrors) {
                throw new \InvalidArgumentException(implode("\n", $rowErrors));
            }

            return [
                'format' => 'oohapp-hoarding-transfer',
                'schema_version' => self::SCHEMA_VERSION,
                'source_instance' => $metadata['source_instance'] ?? null,
                'vendor' => [
                    'email' => $metadata['email'] ?? null,
                    'gstin' => $metadata['gstin'] ?? null,
                ],
                'hoardings' => array_column($records, 'record'),
                'record_lines' => array_column($records, 'line'),
            ];
        } finally {
            fclose($stream);
        }
    }

    private function readCsvMediaArchive(string $archivePath, string $temporaryDirectory): array
    {
        if (!is_file($archivePath) || filesize($archivePath) > self::MAX_UNCOMPRESSED_BYTES) {
            throw new \InvalidArgumentException('The media package is missing or too large.');
        }
        $archive = new \ZipArchive();
        if ($archive->open($archivePath) !== true) {
            throw new \InvalidArgumentException('The media package must be a valid ZIP file.');
        }

        $mediaFiles = [];
        $totalBytes = 0;
        try {
            if ($archive->numFiles > self::MAX_MEDIA_ROWS + 1) {
                throw new \InvalidArgumentException('The media package contains too many files.');
            }
            for ($index = 0; $index < $archive->numFiles; $index++) {
                $reference = $archive->getNameIndex($index);
                if (!is_string($reference) || $reference === 'hoardings.csv' || str_ends_with($reference, '/')) {
                    continue;
                }
                if (!$this->isValidWorkbookMediaReference($reference)) {
                    throw new \InvalidArgumentException('The media package contains an invalid media reference.');
                }
                if (isset($mediaFiles[$reference])) {
                    throw new \InvalidArgumentException('The media package contains duplicate media references.');
                }

                $stat = $archive->statIndex($index);
                $size = (int) ($stat['size'] ?? 0);
                $name = basename($reference);
                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $totalBytes += $size;
                if (
                    $size < 1
                    || $size > self::MAX_MEDIA_BYTES
                    || $totalBytes > self::MAX_UNCOMPRESSED_BYTES
                    || !in_array($extension, self::ALLOWED_EXTENSIONS, true)
                ) {
                    throw new \InvalidArgumentException("Media file {$name} is empty, too large, or has an unsupported format.");
                }

                $input = $archive->getStream($reference);
                $targetPath = $temporaryDirectory . DIRECTORY_SEPARATOR . Str::uuid()->toString();
                $output = fopen($targetPath, 'wb');
                if ($input === false || $output === false) {
                    if (is_resource($input)) {
                        fclose($input);
                    }
                    if (is_resource($output)) {
                        fclose($output);
                    }
                    throw new \RuntimeException("Could not stage media file {$name}.");
                }
                try {
                    $copiedBytes = stream_copy_to_stream($input, $output);
                } finally {
                    fclose($input);
                    fclose($output);
                }
                if ($copiedBytes !== $size) {
                    throw new \InvalidArgumentException("Media file {$name} is incomplete.");
                }

                $mediaFiles[$reference] = [
                    'path' => $targetPath,
                    'name' => $name,
                    'mime_type' => mime_content_type($targetPath) ?: 'application/octet-stream',
                ];
            }
        } finally {
            $archive->close();
        }

        return $mediaFiles;
    }

    private function validateCsvRecords(array $rows, array $mediaFiles, array $recordLines = []): void
    {
        $errors = [];
        $seenReferences = [];
        $referencedFiles = [];
        $rowsWithMissingMedia = [];

        foreach ($rows as $index => $record) {
            $line = $recordLines[$index] ?? ($index + 2);
            $references = $this->recordMediaReferences($record);
            foreach ($references as $reference) {
                if (is_string($reference)) {
                    $referencedFiles[$reference] = true;
                }
            }

            $sourceReference = is_array($record) ? ($record['source_reference'] ?? null) : null;
            if (is_string($sourceReference) && isset($seenReferences[$sourceReference])) {
                $errors[] = "Row {$line}: duplicate source reference (also used on row {$seenReferences[$sourceReference]}).";
                continue;
            }
            if (is_string($sourceReference)) {
                $seenReferences[$sourceReference] = $line;
            }

            $recordFiles = array_intersect_key($mediaFiles, array_flip(array_filter($references, 'is_string')));
            try {
                $this->validateRecords($recordFiles, [$record]);
                $title = trim((string) ($record['hoarding']['title'] ?? ''));
                if ($title === '' || mb_strlen($title) > 255) {
                    throw new \InvalidArgumentException('title is required and must be 255 characters or fewer.');
                }
                $this->validateCsvNumericFields($record);
            } catch (\InvalidArgumentException $exception) {
                if (str_contains($exception->getMessage(), 'references a missing or invalid media file')) {
                    $rowsWithMissingMedia[] = $line;
                    continue;
                }
                $errors[] = "Row {$line}: " . $exception->getMessage();
            }
        }

        if ($rowsWithMissingMedia) {
            $errors[] = 'Rows ' . implode(', ', $rowsWithMissingMedia)
                . ' reference media files that are not in the uploaded CSV ZIP. Upload the original CSV transfer bundle ZIP downloaded from the same Export action without extracting, editing, or recompressing it. If using a separate media ZIP, it must be the matching ZIP from that exact export.';
        }

        $unreferencedFiles = array_diff_key($mediaFiles, $referencedFiles);
        if ($unreferencedFiles) {
            $errors[] = 'Media package contains unreferenced files: ' . implode(', ', array_map('basename', array_keys($unreferencedFiles))) . '.';
        }
        if ($errors) {
            throw new \InvalidArgumentException(implode("\n", $errors));
        }
    }

    private function validateCsvNumericFields(array $record): void
    {
        $fieldsByGroup = [
            'hoarding' => [
                'latitude', 'longitude', 'expected_footfall', 'expected_eyeball',
                'base_monthly_price', 'monthly_price', 'weekly_price_1', 'weekly_price_2',
                'weekly_price_3', 'graphics_charge', 'survey_charge', 'grace_period_days',
                'min_booking_duration', 'max_booking_months',
            ],
            'ooh' => [
                'width', 'height', 'printing_charge', 'mounting_charge',
                'remounting_charge', 'lighting_charge',
            ],
            'dooh' => [
                'resolution_width', 'resolution_height', 'slot_duration_seconds',
                'loop_duration_seconds', 'slots_per_loop', 'total_slots_per_day',
                'available_slots_per_day', 'price_per_slot', 'display_price_per_30s',
                'max_file_size_mb', 'video_length',
            ],
        ];

        foreach ($fieldsByGroup as $group => $fields) {
            $attributes = $record[$group] ?? null;
            if (!is_array($attributes)) {
                continue;
            }
            $this->assertCsvNumericAttributes($attributes, $fields, $group);
        }

        foreach (['ooh_packages', 'dooh_packages', 'slots'] as $group) {
            $position = 0;
            foreach (is_array($record[$group] ?? null) ? $record[$group] : [] as $attributes) {
                $position++;
                if (!is_array($attributes)) {
                    continue;
                }
                $fields = match ($group) {
                    'ooh_packages' => ['discount_percent', 'discount_value', 'min_booking_duration'],
                    'dooh_packages' => ['slots_per_day', 'slots_per_month', 'loop_interval_minutes', 'min_booking_duration', 'discount_percent'],
                    default => [
                        'duration_seconds', 'frequency_per_hour', 'loop_position',
                        'total_daily_displays', 'total_hourly_displays', 'interval_seconds',
                        'price_per_display', 'hourly_cost', 'daily_cost', 'monthly_cost',
                    ],
                };
                $this->assertCsvNumericAttributes($attributes, $fields, $group . ' record ' . $position);
            }
        }
    }

    private function assertCsvNumericAttributes(array $attributes, array $fields, string $group): void
    {
        foreach ($fields as $field) {
            $value = $attributes[$field] ?? null;
            if ($value !== null && $value !== '' && !is_numeric($value)) {
                throw new \InvalidArgumentException("{$group}.{$field} must be numeric.");
            }
        }
    }

    private function recordMediaReferences(mixed $record): array
    {
        if (!is_array($record)) {
            return [];
        }
        $references = [];
        foreach (['media', 'brand_logos', 'screen_media', 'screen_brand_logos'] as $group) {
            foreach (is_array($record[$group] ?? null) ? $record[$group] : [] as $media) {
                if (is_array($media) && is_array($media['files'] ?? null)) {
                    foreach ($media['files'] as $reference) {
                        $references[] = $reference;
                    }
                }
            }
        }
        foreach (is_array($record['library_media'] ?? null) ? $record['library_media'] : [] as $media) {
            if (is_array($media)) {
                $references[] = $media['file_reference'] ?? null;
            }
        }
        return $references;
    }

    private function createTemporaryDirectory(): string
    {
        $temporaryDirectory = tempnam(sys_get_temp_dir(), 'ooh-transfer-');
        if ($temporaryDirectory === false || !@unlink($temporaryDirectory) || !@mkdir($temporaryDirectory, 0700)) {
            throw new \RuntimeException('Could not create temporary storage for transfer media.');
        }
        return $temporaryDirectory;
    }

    private function upgradeLegacyManifest(array $manifest): array
    {
        foreach ($manifest['hoardings'] as &$record) {
            if (isset($record['library_media']) && is_array($record['library_media'])) {
                foreach ($record['library_media'] as &$media) {
                    if (isset($media['archive_path']) && !isset($media['file_reference'])) {
                        $media['file_reference'] = $media['archive_path'];
                        unset($media['archive_path']);
                    }
                }
                unset($media);
            }
        }
        unset($record);
        $manifest['schema_version'] = self::SCHEMA_VERSION;
        return $manifest;
    }

    private function readWorkbookMedia($sheet, string $temporaryDirectory): array
    {
        $expectedHeadings = ['Reference', 'File name', 'MIME type', 'Chunk', 'Base64 data'];
        foreach ($expectedHeadings as $index => $heading) {
            if ($sheet->getCell(chr(65 + $index) . '1')->getValue() !== $heading) {
                throw new \InvalidArgumentException('The workbook media sheet has invalid headings.');
            }
        }

        $highestRow = $sheet->getHighestDataRow();
        if ($highestRow > self::MAX_MEDIA_ROWS + 1) {
            throw new \InvalidArgumentException('The workbook contains too many media chunks.');
        }
        $files = [];
        $streams = [];
        $totalBytes = 0;
        try {
            for ($row = 2; $row <= $highestRow; $row++) {
                $reference = $sheet->getCell('A' . $row)->getValue();
                $name = $sheet->getCell('B' . $row)->getValue();
                $mimeType = $sheet->getCell('C' . $row)->getValue();
                $chunkIndex = filter_var($sheet->getCell('D' . $row)->getValue(), FILTER_VALIDATE_INT);
                $encodedChunk = $sheet->getCell('E' . $row)->getValue();
                if (
                    !is_string($reference)
                    || !$this->isValidWorkbookMediaReference($reference)
                    || !is_string($name)
                    || $name !== $this->safeFileName($name)
                    || !in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), self::ALLOWED_EXTENSIONS, true)
                    || !is_string($mimeType)
                    || strlen($mimeType) > 255
                    || $chunkIndex === false
                    || $chunkIndex < 0
                    || !is_string($encodedChunk)
                    || $encodedChunk === ''
                    || strlen($encodedChunk) > self::WORKBOOK_CHUNK_LENGTH
                    || !preg_match('/^[A-Za-z0-9+\\/=]+$/', $encodedChunk)
                ) {
                    throw new \InvalidArgumentException('The workbook contains an invalid or incomplete media row.');
                }
                if (!isset($files[$reference])) {
                    if ($chunkIndex !== 0) {
                        throw new \InvalidArgumentException('A workbook media file is missing its first chunk.');
                    }
                    $path = tempnam($temporaryDirectory, 'media-');
                    $stream = $path === false ? false : fopen($path, 'wb');
                    if ($path === false || $stream === false) {
                        throw new \RuntimeException('Could not stage workbook media for import.');
                    }
                    $files[$reference] = [
                        'path' => $path,
                        'name' => $name,
                        'mime_type' => $mimeType,
                        'chunks' => 0,
                        'size' => 0,
                    ];
                    $streams[$reference] = $stream;
                }
                if (
                    $files[$reference]['name'] !== $name
                    || $files[$reference]['mime_type'] !== $mimeType
                    || $files[$reference]['chunks'] !== $chunkIndex
                ) {
                    throw new \InvalidArgumentException('Workbook media chunks are duplicated, out of order, or inconsistent.');
                }
                $decoded = base64_decode($encodedChunk, true);
                if (!is_string($decoded) || $decoded === '') {
                    throw new \InvalidArgumentException('A workbook media chunk is not valid base64 data.');
                }
                $files[$reference]['size'] += strlen($decoded);
                $totalBytes += strlen($decoded);
                if ($files[$reference]['size'] > self::MAX_MEDIA_BYTES || $totalBytes > self::MAX_UNCOMPRESSED_BYTES) {
                    throw new \InvalidArgumentException('Workbook media exceeds the supported file or total size limit.');
                }
                if (fwrite($streams[$reference], $decoded) !== strlen($decoded)) {
                    throw new \RuntimeException('Could not write staged workbook media.');
                }
                $files[$reference]['chunks']++;
            }
        } finally {
            foreach ($streams as $stream) {
                fclose($stream);
            }
        }

        return $files;
    }

    private function validateRecords(array $mediaFiles, array $records): void
    {
        $referencedFiles = [];
        foreach ($records as $record) {
            if (
                !is_array($record)
                || !preg_match('/^[a-f0-9]{64}$/', (string) ($record['source_reference'] ?? ''))
                || !in_array($record['type'] ?? null, ['ooh', 'dooh'], true)
                || !is_array($record['hoarding'] ?? null)
                || !is_array($record['media'] ?? null)
                || !is_array($record['brand_logos'] ?? null)
                || !is_array($record['library_media'] ?? [])
                || !is_array($record['ooh_packages'] ?? [])
                || !is_array($record['dooh_packages'] ?? [])
                || !is_array($record['slots'] ?? [])
                || !is_array($record['screen_media'] ?? [])
                || !is_array($record['screen_brand_logos'] ?? [])
            ) {
                throw new \InvalidArgumentException('The workbook contains an incomplete or invalid hoarding record.');
            }
            if ($record['type'] === 'ooh' && !is_array($record['ooh'] ?? null)) {
                throw new \InvalidArgumentException('An OOH listing is missing its specification data.');
            }
            if ($record['type'] === 'dooh' && !is_array($record['dooh'] ?? null)) {
                throw new \InvalidArgumentException('A DOOH listing is missing its screen data.');
            }
            if ($record['type'] === 'ooh' && $record['ooh'] === []) {
                throw new \InvalidArgumentException('An OOH listing has incomplete specification data.');
            }
            if ($record['type'] === 'dooh' && $record['dooh'] === []) {
                throw new \InvalidArgumentException('A DOOH listing has incomplete screen data.');
            }
            $relatedCount = count($record['media'])
                + count($record['brand_logos'])
                + count($record['library_media'])
                + count($record['ooh_packages'])
                + count($record['dooh_packages'])
                + count($record['slots'])
                + count($record['screen_media'])
                + count($record['screen_brand_logos']);
            if ($relatedCount > self::MAX_RELATED_RECORDS) {
                throw new \InvalidArgumentException('A hoarding contains too many related records for safe import.');
            }

            foreach (['media', 'brand_logos', 'screen_media', 'screen_brand_logos'] as $group) {
                foreach ($record[$group] ?? [] as $media) {
                    if (!is_array($media) || !is_array($media['files'] ?? null) || !is_array($media['attributes'] ?? null)) {
                        throw new \InvalidArgumentException('The workbook contains an invalid media record.');
                    }
                    if ($media['files'] === []) {
                        throw new \InvalidArgumentException('A media record has no referenced media file.');
                    }
                    $allowedFields = $group === 'brand_logos' || $group === 'screen_brand_logos'
                        ? ['file_path']
                        : self::MEDIA_FIELDS;
                    if (array_diff(array_keys($media['files']), $allowedFields)) {
                        throw new \InvalidArgumentException('The workbook media record contains unsupported file fields.');
                    }
                    $fileReferences = $group === 'brand_logos' || $group === 'screen_brand_logos'
                        ? ['file_path' => $media['files']['file_path'] ?? null]
                        : $media['files'];
                    foreach ($fileReferences as $reference) {
                        $this->assertMediaReferenceExists($reference, $mediaFiles);
                        $referencedFiles[$reference] = true;
                    }
                }
            }
            foreach ($record['library_media'] ?? [] as $media) {
                if (
                    !is_array($media)
                    || !is_string($media['file_name'] ?? null)
                    || !is_array($media['custom_properties'] ?? [])
                    || !in_array($media['collection'] ?? null, self::MEDIA_LIBRARY_COLLECTIONS, true)
                ) {
                    throw new \InvalidArgumentException('The workbook contains an invalid media library record.');
                }
                $reference = $media['file_reference'] ?? null;
                $this->assertMediaReferenceExists($reference, $mediaFiles);
                $referencedFiles[$reference] = true;
            }
        }

        if (count($referencedFiles) !== count($mediaFiles)) {
            throw new \InvalidArgumentException('The workbook contains missing or unreferenced media files.');
        }
    }

    private function assertMediaReferenceExists(mixed $reference, array $mediaFiles): void
    {
        if (!is_string($reference) || !$this->isValidWorkbookMediaReference($reference) || !isset($mediaFiles[$reference])) {
            throw new \InvalidArgumentException('The workbook references a missing or invalid media file.');
        }
    }

    private function isValidWorkbookMediaReference(string $reference): bool
    {
        return (bool) preg_match('#^media/[a-f0-9]{64}/[a-zA-Z0-9_.-]+$#', $reference);
    }

    private function setCellString($sheet, string $cell, string $value): void
    {
        $sheet->setCellValueExplicit($cell, $value, DataType::TYPE_STRING);
    }

    private function readableJson(mixed $value): string
    {
        $json = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        if (!is_string($json)) {
            return 'null';
        }
        if (strlen($json) > 32000) {
            return '{"note":"Complete value is preserved in the workbook transfer data."}';
        }
        return $json;
    }

    private function safeCsvDisplayValue(string $value): string
    {
        return preg_match('/^[\s\x00-\x1F]*[=+\-@]/', $value) ? "'" . $value : $value;
    }

    private function removeTemporaryDirectory(string $directory): void
    {
        foreach (glob($directory . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
        @rmdir($directory);
    }

    private function assertVendorIdentityAvailable(User $vendor): void
    {
        if (blank($vendor->email_verified_at) && blank($vendor->vendorProfile?->gstin) && blank($vendor->gstin)) {
            throw new \InvalidArgumentException('Verify your account email or add a business GSTIN before using hoarding transfer.');
        }
    }

    private function vendorIdentity(User $vendor): array
    {
        return [
            'email' => $vendor->email_verified_at ? mb_strtolower(trim((string) $vendor->email)) : null,
            'gstin' => strtoupper(trim((string) ($vendor->vendorProfile?->gstin ?? $vendor->gstin ?? ''))) ?: null,
        ];
    }

    private function assertVendorMatches(mixed $sourceIdentity, User $vendor): void
    {
        if (!is_array($sourceIdentity)) {
            throw new \InvalidArgumentException('The package does not include a vendor identity.');
        }
        $sourceEmail = mb_strtolower(trim((string) ($sourceIdentity['email'] ?? '')));
        $sourceGstin = strtoupper(trim((string) ($sourceIdentity['gstin'] ?? '')));
        $target = $this->vendorIdentity($vendor);
        $emailMatches = $sourceEmail !== '' && $target['email'] !== null && hash_equals($sourceEmail, $target['email']);
        $gstinMatches = $sourceGstin !== '' && $target['gstin'] !== null && hash_equals($sourceGstin, $target['gstin']);
        if (!$emailMatches && !$gstinMatches) {
            throw new \InvalidArgumentException('This package belongs to a different vendor identity. It was not imported.');
        }
    }

    private function safeAttributes($model, array $excluded): array
    {
        $attributes = [];
        foreach (array_keys($model->getAttributes()) as $key) {
            if (!in_array($key, $excluded, true)) {
                $attributes[$key] = $model->getAttribute($key);
            }
        }

        return $attributes;
    }

    private function createHoarding(array $record, User $vendor): Hoarding
    {
        $attributes = $record['hoarding'];
        $attributes['vendor_id'] = $vendor->id;
        $attributes['hoarding_type'] = $record['type'];
        $attributes['status'] = Hoarding::STATUS_DRAFT;
        $attributes['current_step'] = 3;
        $attributes['is_featured'] = false;
        $attributes['is_recommended'] = false;
        $attributes['view_count'] = 0;
        $attributes['bookings_count'] = 0;
        $attributes['last_booked_at'] = null;
        $attributes['slug'] = null;
        $attributes['submitted_at'] = null;
        $attributes['verified_at'] = null;
        $attributes['approved_at'] = null;
        $attributes['rejected_at'] = null;
        $attributes['verified_by'] = null;
        $attributes['approved_by'] = null;
        $attributes['rejected_by'] = null;
        $attributes['rejection_reason'] = null;
        $attributes['admin_notes'] = null;

        $hoarding = Hoarding::withoutEvents(fn () => Hoarding::create($attributes));

        if ($record['type'] === Hoarding::TYPE_OOH) {
            $child = $record['ooh'];
            unset($child['id'], $child['hoarding_id'], $child['created_at'], $child['updated_at'], $child['deleted_at']);
            $hoarding->oohHoarding()->create($child);
        } else {
            $child = $record['dooh'];
            unset($child['id'], $child['hoarding_id'], $child['vendor_id'], $child['created_at'], $child['updated_at'], $child['deleted_at']);
            $child['external_screen_id'] = null;
            $child['sync_status'] = DOOHScreen::SYNC_STATUS_PENDING;
            $child['last_synced_at'] = null;
            $child['sync_metadata'] = null;
            $hoarding->doohScreen()->create($child);
        }

        if (is_array($record['geo'] ?? null)) {
            $geo = $record['geo'];
            unset($geo['id'], $geo['hoarding_id'], $geo['created_at'], $geo['updated_at']);
            $hoarding->geo()->create($geo);
        }

        $hoarding->updateQuietly([
            'slug' => $hoarding->generateSlugWithId(),
            'title' => $record['hoarding']['title'],
        ]);
        return $hoarding->fresh();
    }

    private function restoreRelatedRecords(array $mediaFiles, array $record, Hoarding $hoarding, array &$createdPaths): void
    {
        if ($record['type'] === Hoarding::TYPE_OOH) {
            foreach ($record['ooh_packages'] ?? [] as $package) {
                unset($package['id'], $package['hoarding_id'], $package['vendor_id'], $package['package_code'], $package['created_at'], $package['updated_at'], $package['deleted_at']);
                $hoarding->oohPackages()->create($package + ['vendor_id' => $hoarding->vendor_id]);
            }
            foreach ($record['brand_logos'] ?? [] as $logo) {
                $attributes = $logo['attributes'];
                unset($attributes['id'], $attributes['hoarding_id'], $attributes['created_at'], $attributes['updated_at'], $attributes['deleted_at']);
                $attributes['file_path'] = $this->copyWorkbookFileFromRecord($mediaFiles, $logo, 'file_path', 'oohHoardings/brand_logos/' . $hoarding->id, $createdPaths);
                HoardingBrandLogo::create($attributes + ['hoarding_id' => $hoarding->id]);
            }
        } else {
            $screen = $hoarding->doohScreen;
            foreach ($record['dooh_packages'] ?? [] as $package) {
                unset($package['id'], $package['dooh_screen_id'], $package['created_at'], $package['updated_at'], $package['deleted_at']);
                $screen->packages()->create($package);
            }
            foreach ($record['slots'] ?? [] as $slot) {
                unset($slot['id'], $slot['dooh_screen_id'], $slot['booking_id'], $slot['start_date'], $slot['end_date'], $slot['total_booking_days'], $slot['total_booking_cost'], $slot['created_at'], $slot['updated_at'], $slot['deleted_at']);
                $slot['status'] = in_array($slot['status'] ?? null, ['available', 'blocked', 'maintenance'], true)
                    ? $slot['status']
                    : 'available';
                $screen->slots()->create($slot + ['booking_id' => null]);
            }
            foreach ($record['screen_brand_logos'] ?? [] as $logo) {
                $attributes = $logo['attributes'];
                unset($attributes['id'], $attributes['dooh_screen_id'], $attributes['created_at'], $attributes['updated_at'], $attributes['deleted_at']);
                $attributes['file_path'] = $this->copyWorkbookFileFromRecord($mediaFiles, $logo, 'file_path', 'dooh/screens/brand_logos/' . $screen->id, $createdPaths);
                DOOHScreenBrandLogo::create($attributes + ['dooh_screen_id' => $screen->id]);
            }
        }
    }

    private function restoreMedia(array $mediaFiles, array $record, Hoarding $hoarding, array &$createdPaths): void
    {
        foreach ($record['media'] ?? [] as $media) {
            $attributes = $media['attributes'];
            unset($attributes['id'], $attributes['hoarding_id'], $attributes['dooh_screen_id'], $attributes['created_at'], $attributes['updated_at'], $attributes['deleted_at']);
            $attributes['hoarding_id'] = $hoarding->id;
            $attributes = $this->restoreMediaFiles($mediaFiles, $attributes, $media['files'] ?? [], 'hoardings/media', $hoarding->id, $createdPaths);
            HoardingMedia::create($attributes);
        }

        if ($record['type'] === Hoarding::TYPE_DOOH) {
            foreach ($record['screen_media'] ?? [] as $media) {
                $attributes = $media['attributes'];
                unset($attributes['id'], $attributes['hoarding_id'], $attributes['dooh_screen_id'], $attributes['created_at'], $attributes['updated_at'], $attributes['deleted_at']);
                $attributes['dooh_screen_id'] = $hoarding->doohScreen->id;
                $attributes = $this->restoreMediaFiles($mediaFiles, $attributes, $media['files'] ?? [], 'dooh/screens', $hoarding->doohScreen->id, $createdPaths);
                DOOHScreenMedia::create($attributes);
            }
        }

        foreach ($record['library_media'] ?? [] as $media) {
            $mediaFile = $mediaFiles[$media['file_reference']] ?? null;
            if (!$mediaFile || !is_file($mediaFile['path'])) {
                throw new \InvalidArgumentException('A media file could not be read from the package.');
            }
            $storedMedia = $hoarding->addMedia($mediaFile['path'])
                ->usingFileName($this->safeFileName($media['file_name']))
                ->withCustomProperties(is_array($media['custom_properties'] ?? null) ? $media['custom_properties'] : [])
                    ->toMediaCollection(in_array($media['collection'], self::MEDIA_LIBRARY_COLLECTIONS, true)
                        ? $media['collection']
                        : throw new \InvalidArgumentException('The workbook references an unsupported media collection.'));
            $createdPaths[] = [
                'disk' => $storedMedia->disk,
                'directory' => dirname($storedMedia->getPathRelativeToRoot()),
            ];
        }
    }

    private function restoreMediaFiles(array $mediaFiles, array $attributes, array $files, string $base, int $recordId, array &$createdPaths): array
    {
        $type = strtolower((string) ($attributes['media_type'] ?? 'image'));
        $folder = $type === 'video' ? '/videos' : '';
        $name = Str::uuid()->toString();
        $restoredReferences = [];
        foreach ($files as $field => $mediaReference) {
            if (isset($restoredReferences[$mediaReference])) {
                $attributes[$field] = $restoredReferences[$mediaReference];
                continue;
            }
            $extension = strtolower(pathinfo($mediaReference, PATHINFO_EXTENSION));
            $sizeDirectory = $field === 'file_path'
                ? ($type === 'video' ? '' : '/1500')
                : '/' . substr($field, 5);
            $path = trim($base, '/') . '/' . now()->format('Y/m') . '/' . $recordId . $folder . $sizeDirectory . '/' . $name . '.' . $extension;
            $mediaFile = $mediaFiles[$mediaReference] ?? null;
            $contents = $mediaFile && is_file($mediaFile['path']) ? file_get_contents($mediaFile['path']) : false;
            if (!is_string($contents) || !Storage::disk('public')->put($path, $contents)) {
                throw new \RuntimeException('A media file could not be restored to public storage.');
            }
            $createdPaths[] = $path;
            $restoredReferences[$mediaReference] = $path;
            $attributes[$field] = $path;
        }
        return $attributes;
    }

    private function copyWorkbookFileFromRecord(array $mediaFiles, array $record, string $field, string $directory, array &$createdPaths): string
    {
        $path = $record['files'][$field] ?? null;
        if (!$path) {
            throw new \InvalidArgumentException('A logo file is missing from the package.');
        }
        $mediaFile = $mediaFiles[$path] ?? null;
        $contents = $mediaFile && is_file($mediaFile['path']) ? file_get_contents($mediaFile['path']) : false;
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $target = trim($directory, '/') . '/' . Str::uuid() . '.' . $extension;
        if (!is_string($contents) || !Storage::disk('public')->put($target, $contents)) {
            throw new \RuntimeException('A logo file could not be restored to public storage.');
        }
        $createdPaths[] = $target;
        return $target;
    }

    private function locationLabel(array $attributes): string
    {
        return implode(', ', array_filter([
            $attributes['locality'] ?? null,
            $attributes['city'] ?? null,
            $attributes['state'] ?? null,
        ]));
    }

    private function safeFileName(string $fileName): string
    {
        $name = basename(str_replace('\\', '/', $fileName));
        return preg_replace('/[^a-zA-Z0-9_.-]/', '_', $name) ?: 'media-file';
    }
}
