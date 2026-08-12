<?php

namespace Modules\Import\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Modules\Import\Entities\InventoryImportBatch;
use Modules\Import\Services\PythonImportService;
use Modules\Import\Exceptions\ImportApiException;
use Exception;

use Illuminate\Support\Facades\Mail;
use App\Mail\InventoryImportedMail;
use ZipArchive;


class ProcessInventoryImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected const CHUNK_SIZE = 500;
    protected InventoryImportBatch $batch;

    protected string $excelPath;
    protected string $pptPath;
    public $tries = 3;
    public $timeout = 900;  

    public $uniqueFor = 3600;


    public function __construct(InventoryImportBatch $batch, string $excelPath, string $pptPath)
    {
        $this->batch = $batch;
        $this->excelPath = $excelPath;
        $this->pptPath = $pptPath;
        $this->queue = config('import.batch.queue', 'default');
    }


    public function handle()
    {
        try {
            $this->batch->markAsProcessing();

            \Log::info('Starting inventory import processing', [
                'batch_id' => $this->batch->id,
                'vendor_id' => $this->batch->vendor_id,
                'media_type' => $this->batch->media_type,
                'excel_file' => [
                    'path' => $this->excelPath,
                    'name' => basename($this->excelPath),
                    'size_bytes' => @filesize($this->excelPath) ?: null,
                ],
                'ppt_file' => [
                    'path' => $this->pptPath,
                    'name' => basename($this->pptPath),
                    'size_bytes' => @filesize($this->pptPath) ?: null,
                ],
            ]);


            $pythonService = app(PythonImportService::class);
            $apiResponse = $pythonService->processImport(
                $this->excelPath,
                $this->pptPath,
                $this->batch->vendor_id,
                $this->batch->media_type
            );

            if (!$apiResponse['success']) {
                throw ImportApiException::apiError(
                    $apiResponse['message'] ?? 'Import processing failed'
                );
            }

            $apiLogPayload = $apiResponse;
            unset($apiLogPayload['images_zip_base64']);

            \Log::info('Inventory import API data received', [
                'batch_id' => $this->batch->id,
                'api_response_without_base64_zip' => $apiLogPayload,
                'received_data' => $apiResponse['data'] ?? [],
            ]);


            try {
                $this->ingestImageArchive($apiResponse);
            } catch (Exception $e) {
                \Log::warning('Image archive ingestion skipped', [
                    'batch_id' => $this->batch->id,
                    'reason' => $e->getMessage(),
                ]);
            }

            // Process rows with bulk insert
            $this->processApiRows(
                $apiResponse['data'] ?? [],
                $apiResponse['total_rows'] ?? 0
            );

            $this->batch->markAsCompleted();
            \Log::info('MAIL TEST START');

            $this->batch->load('vendor');

            \Log::info('Vendor Debug', [
                'vendor_id' => $this->batch->vendor_id,
                'vendor_found' => $this->batch->vendor ? true : false,
                'vendor_email' => optional($this->batch->vendor)->email,
            ]);

            if ($this->batch->vendor && !empty($this->batch->vendor->email)) {

                \Log::info('Sending inventory imported mail');

                Mail::to($this->batch->vendor->email)
                    ->send(new InventoryImportedMail(
                        $this->batch,
                        auth()->user()->name ?? 'Admin'
                    ));

                \Log::info('Inventory imported mail sent successfully');

            } else {

                \Log::warning('Vendor email not found');

            }


            \Log::info('Inventory import processing completed', [
                'batch_id' => $this->batch->id,
                'total_rows' => $this->batch->total_rows,
                'valid_rows' => $this->batch->valid_rows,
                'invalid_rows' => $this->batch->invalid_rows,
            ]);
        } catch (ImportApiException $e) {
            $this->batch->markAsFailed($e->getMessage());
            \Log::error('Import API error', [
                'batch_id' => $this->batch->id,
                'api_code' => $e->getApiCode(),
                'error' => $e->getMessage(),
            ]);

            $apiCode = (string) $e->getApiCode();
            $isClientError = str_starts_with($apiCode, 'API_ERROR_401')
                || str_starts_with($apiCode, 'API_ERROR_403')
                || str_starts_with($apiCode, 'API_ERROR_404')
                || str_starts_with($apiCode, 'API_ERROR_422');

            if ($isClientError) {
                return;
            }

            throw $e;
        } catch (Exception $e) {
            $this->batch->markAsFailed($e->getMessage());
            \Log::error('Inventory import processing failed', [
                'batch_id' => $this->batch->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    protected function processApiRows(array $apiRows, int $totalRows): void
    {
        $validCount = 0;
        $invalidCount = 0;


        $stagingRows = [];

        foreach ($apiRows as $index => $row) {

            $transformed = $this->transformRow($row);

            $duplicate = \App\Models\Hoarding::query()
                ->where('vendor_id', $this->batch->vendor_id)
                ->where('city', $transformed['city'] ?? null)
                ->where('base_monthly_price', $transformed['base_monthly_price'] ?? 0)
                ->first();

            if ($duplicate) {

                \Log::warning('Duplicate Found', [
                    'vendor' => $this->batch->vendor_id,
                    'city' => $transformed['city'],
                    'price' => $transformed['base_monthly_price'],
                ]);

                $transformed['status'] = 'invalid';
                $transformed['error_message'] = 'Duplicate hoarding already exists';
            }

            \Log::info('Transform Result', [
                'row' => $index,
                'python_status' => $row['status'] ?? null,
                'transformed_status' => $transformed['status'],
                'error' => $transformed['error_message'] ?? null,
            ]);

            if ($transformed['status'] === 'valid') {
                $validCount++;
            } else {
                $invalidCount++;
            }

            $stagingRows[] = $transformed;
        }

        \Log::info('Final Counter', [
            'valid' => $validCount,
            'invalid' => $invalidCount,
        ]);


        if (!empty($stagingRows)) {
            $this->bulkInsertChunk($stagingRows);
        }


        $this->batch->updateRowCounts($totalRows, $validCount, $invalidCount);
    }

    protected function transformRow(array $row): array
    {
        try {


            if (empty($row['code']) && empty($row['media_id']) && empty($row['Media ID'])) {
                $row['code'] = 'DBM-' . uniqid();
            }


            if (
                isset($row['status']) &&
                strtolower((string) $row['status']) === 'invalid'
            ) {
                throw new Exception(
                    $row['error_message']
                    ?? implode(', ', (array) ($row['errors'] ?? []))
                    ?? 'Invalid row'
                );
            }


            $this->validateRowFields($row);

            $resolvedImageName = $this->toNullableString(
                $this->rowValue($row, ['image_name', 'image_path'])
            );

            if ($resolvedImageName) {
                $resolvedImageName = basename($resolvedImageName);
                $this->persistRowImageForBatch($row, $resolvedImageName);
            }

            return [

                'batch_id' => $this->batch->id,
                'vendor_id' => $this->batch->vendor_id,
                'media_type' => $this->batch->media_type,

                'code' => $this->rowValue($row, ['code', 'media_id', 'Media ID']),
                'city' => $this->rowValue($row, ['city', 'City']),
                'category' => $this->rowValue($row, ['category', 'media_type_name', 'Media Type']),

                'address' => $this->rowValue($row, ['address', 'full_address', 'Full Address']),
                'locality' => $this->rowValue($row, ['locality', 'Locality']),
                'landmark' => $this->rowValue($row, ['landmark', 'Landmark']),
                'state' => $this->rowValue($row, ['state', 'State']),
                'pincode' => $this->rowValue($row, ['pincode', 'Pincode']),

                'latitude' => $this->toNullableDecimal($this->rowValue($row, ['latitude', 'Latitude']), 7),
                'longitude' => $this->toNullableDecimal($this->rowValue($row, ['longitude', 'Longitude']), 7),

                'width' => $this->toNullableDecimal($this->rowValue($row, ['width', 'Width']), 2),
                'height' => $this->toNullableDecimal($this->rowValue($row, ['height', 'Height']), 2),

                'measurement_unit' => $this->rowValue($row, ['measurement_unit', 'unit', 'Unit']),
                'lighting_type' => $this->rowValue($row, ['lighting_type', 'illumination', 'Illumination']),
                'screen_type' => $this->rowValue($row, ['screen_type']),

                'image_name' => $resolvedImageName,

                'base_monthly_price' => $this->toNullableDecimal(
                    $this->rowValue($row, ['display_monthly_price', 'base_monthly_price', 'd_c_p_m', 'dcpm_or_price', 'DCPM / Price', 'price']),
                    2
                ),

                'monthly_price' => $this->toNullableDecimal(
                    $this->rowValue($row, ['sale_price', 'monthly_price', 'monthly_sale_price', 'Monthly Sale Price']),
                    2
                ),

                'availability' => $this->rowValue($row, ['availability', 'Availability']),
                'discount_type' => $this->rowValue($row, ['discount_type', 'Discount Type']),
                'discount_value' => $this->toNullableDecimal($this->rowValue($row, ['discount_value', 'Discount Value']), 2),

                'extra_attributes' => json_encode(
                    $this->extractExtraAttributes($row),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                ),

                'status' => 'valid',
                'error_message' => null,

                'created_at' => now(),
                'updated_at' => now(),
            ];

        } catch (\Throwable $e) {

            return [

                'batch_id' => $this->batch->id,
                'vendor_id' => $this->batch->vendor_id,
                'media_type' => $this->batch->media_type,

                'code' => $row['code'] ?? ('DBM-' . uniqid()),
                'city' => $row['city'] ?? null,
                'category' => $row['category'] ?? null,

                'address' => $row['address'] ?? null,
                'locality' => $row['locality'] ?? null,
                'landmark' => $row['landmark'] ?? null,
                'state' => $row['state'] ?? null,
                'pincode' => $row['pincode'] ?? null,

                'width' => $this->toNullableDecimal($row['width'] ?? null),
                'height' => $this->toNullableDecimal($row['height'] ?? null),

                'image_name' => $row['image_name'] ?? null,

                'status' => 'invalid',
                'error_message' => $e->getMessage(),

                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
    }


    protected function validateRowFields(array &$row): void
    {

        if (empty($row['code']) && empty($row['media_id']) && empty($row['Media ID'])) {
            $row['code'] = 'DBM-' . uniqid();
        }

        if (
            isset($row['width']) &&
            $row['width'] !== '' &&
            !is_numeric($row['width'])
        ) {
            throw new Exception('Width must be numeric');
        }


        if (
            isset($row['height']) &&
            $row['height'] !== '' &&
            !is_numeric($row['height'])
        ) {
            throw new Exception('Height must be numeric');
        }
    }


    protected function extractExtraAttributes(array $row): ?string
    {
        \Log::info('Extracting extra attributes from row', ['row' => $row]);
        $standardFields = [
            'code',
            'media_id',
            'Media ID',
            'city',
            'City',
            'category',
            'media_type_name',
            'Media Type',
            'address',
            'full_address',
            'Full Address',
            'locality',
            'Locality',
            'landmark',
            'Landmark',
            'state',
            'State',
            'pincode',
            'Pincode',
            'latitude',
            'Latitude',
            'longitude',
            'Longitude',
            'width',
            'Width',
            'height',
            'Height',
            'measurement_unit',
            'unit',
            'Unit',
            'lighting_type',
            'illumination',
            'Illumination',
            'screen_type',
            'Screen Type',
            'image_name',
            'base_monthly_price',
            'dcpm_or_price',
            'DCPM / Price',
            'monthly_price',
            'monthly_sale_price',
            'Monthly Sale Price',
            'weekly_price_1',
            'weekly_price_2',
            'weekly_price_3',
            'price_per_slot',
            'price_per_spot',
            'Price Per Spot (₹)',
            'slot_duration_seconds',
            'ad_duration_sec',
            'Ad Duration (Sec)',
            'screen_run_time',
            'daily_play_hours',
            'Daily Play Hours',
            'total_slots_per_day',
            'spots_per_day',
            'Spots Per Day',
            'total_slots_per_day',
            'min_booking_duration',
            'minimum_duration_days',
            'Minimum Duration (Days)',
            'graphics_charge',
            'designing_charge',
            'Designing Charge',
            'survey_charge',
            'printing_charge',
            'Printing Charge',
            'mounting_charge',
            'Mounting Charge',
            'remounting_charge',
            'lighting_charge',
            'discount_type',
            'Discount Type',
            'discount_value',
            'Discount Value',
            'availability',
            'Availability',
            'currency',
            'available_from',
            'available_to',
            'status',
            'errors',
            'error_message',
        ];

        $extra = [];

        foreach ($row as $key => $value) {
            if (!in_array($key, $standardFields, true) && $value !== null && $value !== '') {
                $extra[$key] = $value;
            }
        }

        if (empty($extra)) {
            return null;
        }

        return json_encode($extra, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }


    protected function toNullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value) || is_object($value)) {
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $encoded = $encoded === false ? null : trim($encoded);
            return $encoded === '' ? null : $encoded;
        }

        $stringValue = trim((string) $value);
        return $stringValue === '' ? null : $stringValue;
    }


    protected function rowValue(array $row, array $keys, $default = null)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }

        return $default;
    }


    protected function toNullableDecimal($value, int $scale = 2): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        return number_format((float) $value, $scale, '.', '');
    }

    protected function toNullableInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }


    protected function toNullableDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return \Carbon\Carbon::parse((string) $value)->toDateString();
        } catch (Exception $e) {
            return null;
        }
    }


    protected function bulkInsertChunk(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $allColumns = [];

        foreach ($rows as $row) {
            $allColumns = array_unique(array_merge($allColumns, array_keys($row)));
        }


        $normalizedRows = [];

        foreach ($rows as $index => $row) {

            $normalizedRow = [];

            foreach ($allColumns as $column) {
                $normalizedRow[$column] = $row[$column] ?? null;
            }

            $normalizedRows[] = $normalizedRow;

            \Log::info("Row {$index}", [
                'column_count' => count($normalizedRow),
                'columns' => array_keys($normalizedRow),
            ]);
        }


        \Log::info('First Row', $normalizedRows[0]);

        DB::transaction(function () use ($normalizedRows) {

            \Log::info('First Row Keys', array_keys($normalizedRows[0]));
            \Log::info('First Row Data', $normalizedRows[0]);




            DB::table('inventory_import_staging')->insert($normalizedRows);

            \Log::info('Chunk inserted successfully', [
                'rows' => count($normalizedRows),
            ]);
        });
    }


    protected function persistRowImageForBatch(array $row, string $imageName): void
    {
        $imagePath = isset($row['image_path']) ? trim((string) $row['image_path']) : '';
        if ($imagePath === '') {
            return;
        }

        $normalizedPath = str_replace('/', DIRECTORY_SEPARATOR, $imagePath);
        $sourcePath = null;

        if (is_file($imagePath) && is_readable($imagePath)) {
            $sourcePath = $imagePath;
        } elseif (is_file($normalizedPath) && is_readable($normalizedPath)) {
            $sourcePath = $normalizedPath;
        }

        if ($sourcePath === null) {
            \Log::warning('Row image source path is missing or unreadable during import', [
                'batch_id' => $this->batch->id,
                'image_path_raw' => $imagePath,
                'image_path_normalized' => $normalizedPath,
                'image_name' => $imageName,
            ]);
            return;
        }

        $targetPath = "imports/{$this->batch->id}/images/{$imageName}";
        $localDisk = Storage::disk('local');

        if ($localDisk->exists($targetPath)) {
            return;
        }

        $contents = @file_get_contents($sourcePath);
        if ($contents === false) {
            \Log::warning('Failed reading source row image path during import', [
                'batch_id' => $this->batch->id,
                'image_path' => $sourcePath,
                'image_name' => $imageName,
            ]);
            return;
        }

        $saved = $localDisk->put($targetPath, $contents);

        if (!$saved) {
            \Log::warning('Failed storing row image into import batch directory', [
                'batch_id' => $this->batch->id,
                'image_path' => $imagePath,
                'target_path' => $targetPath,
            ]);
            return;
        }

        \Log::info('Persisted row image into import batch directory', [
            'batch_id' => $this->batch->id,
            'image_path' => $sourcePath,
            'target_path' => $targetPath,
        ]);
    }

    /**
     * Ingest images archive from Python API response (base64 or URL).
     *
     * @param array $apiResponse
     * @return void
     * @throws Exception
     */
    protected function ingestImageArchive(array $apiResponse): void
    {
        $zipBase64 = $apiResponse['images_zip_base64'] ?? null;
        $zipUrl = $apiResponse['images_zip_download_url']
            ?? $apiResponse['images_zip_url']
            ?? null;

        if (empty($zipBase64) && empty($zipUrl)) {
            return;
        }

        $disk = Storage::disk('local');
        $batchRoot = "imports/{$this->batch->id}";
        $imagesDir = "{$batchRoot}/images";
        $zipPath = "{$batchRoot}/images_bundle.zip";

        $disk->makeDirectory($imagesDir);

        $zipBinary = null;

        if (!empty($zipBase64)) {
            $payload = (string) $zipBase64;

            if (str_starts_with($payload, 'data:')) {
                $parts = explode(',', $payload, 2);
                $payload = $parts[1] ?? '';
            }

            $decoded = base64_decode($payload, true);
            if ($decoded === false) {
                throw new Exception('Invalid images ZIP base64 payload from Python API');
            }

            $zipBinary = $decoded;
        } elseif (!empty($zipUrl)) {
            $resolvedZipUrl = $this->resolvePythonDownloadUrl((string) $zipUrl);
            $http = Http::timeout((int) config('import.python_timeout', 300));

            $pythonToken = (string) config('import.python_token', '');
            if ($pythonToken !== '') {
                $http = $http->withToken($pythonToken);
            }

            \Log::info('Downloading import image ZIP from Python service', [
                'batch_id' => $this->batch->id,
                'zip_url_raw' => (string) $zipUrl,
                'zip_url_resolved' => $resolvedZipUrl,
                'auth_token_present' => $pythonToken !== '',
            ]);

            $response = $http->get($resolvedZipUrl);

            \Log::info('Import image ZIP download response received', [
                'batch_id' => $this->batch->id,
                'status' => $response->status(),
                'content_type' => $response->header('Content-Type'),
                'content_length' => $response->header('Content-Length'),
            ]);

            if ($response->failed()) {
                throw new Exception('Failed to download images ZIP from Python API URL');
            }

            $zipBinary = $response->body();
        }

        if ($zipBinary === null || $zipBinary === '') {
            throw new Exception('Empty images ZIP payload received from Python API');
        }

        $disk->put($zipPath, $zipBinary);

        $zipAbsolutePath = $disk->path($zipPath);
        $imagesAbsolutePath = $disk->path($imagesDir);

        if (!is_dir($imagesAbsolutePath)) {
            mkdir($imagesAbsolutePath, 0755, true);
        }

        if (!class_exists(ZipArchive::class)) {
            \Log::warning('ZipArchive extension is not available; storing ZIP without extraction', [
                'batch_id' => $this->batch->id,
                'zip_path' => $zipPath,
            ]);

            return;
        }

        $zip = new ZipArchive();
        $openResult = $zip->open($zipAbsolutePath);

        if ($openResult !== true) {
            throw new Exception('Unable to open images ZIP archive');
        }

        $zip->extractTo($imagesAbsolutePath);
        $zip->close();

        $allExtractedFiles = $disk->allFiles($imagesDir);

        \Log::info('Extracted image archive for import batch', [
            'batch_id' => $this->batch->id,
            'images_dir' => $imagesDir,
            'zip_path' => $zipPath,
            'extracted_files_count' => count($allExtractedFiles),
            'extracted_files' => $allExtractedFiles,
        ]);
    }

    /**
     * Resolve Python image ZIP URL (supports absolute and relative download URLs).
     */
    protected function resolvePythonDownloadUrl(string $url): string
    {
        $trimmedUrl = trim($url);

        if ($trimmedUrl === '') {
            return $trimmedUrl;
        }

        if (str_starts_with($trimmedUrl, 'http://') || str_starts_with($trimmedUrl, 'https://')) {
            return $trimmedUrl;
        }

        $baseUrl = (string) config('import.python_url', 'http://127.0.0.1:9000');
        return rtrim($baseUrl, '/') . '/' . ltrim($trimmedUrl, '/');
    }


    public function uniqueId(): string
    {
        return "inventory-import-batch-{$this->batch->id}";
    }

    public function __serialize(): array
    {
        return [
            'batch_id' => $this->batch->id,
            'excelPath' => $this->excelPath,
            'pptPath' => $this->pptPath,
        ];
    }


    public function __unserialize(array $data): void
    {
        $this->batch = InventoryImportBatch::find($data['batch_id']);
        $this->excelPath = $data['excelPath'];
        $this->pptPath = $data['pptPath'];
    }
}
