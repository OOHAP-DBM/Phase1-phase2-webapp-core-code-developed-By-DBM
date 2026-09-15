<?php

namespace Modules\Import\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class InventoryImportBatch extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_import_batches';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vendor_id',
        'media_type',
        'status',
        'total_rows',
        'valid_rows',
        'invalid_rows',
        'file_path',
        'ppt_path',
        'uploaded_by_user_id'
    ];


    protected $casts = [
        'total_rows' => 'integer',
        'valid_rows' => 'integer',
        'invalid_rows' => 'integer',
    ];


    public function stagingRecords(): HasMany
    {
        return $this->hasMany(InventoryImportStaging::class, 'batch_id');
    }

    public function vendor()
    {
        return $this->belongsTo(\App\Models\User::class, 'vendor_id');
    }


    public function scopeUploaded(Builder $query): Builder
    {
        return $query->where('status', 'uploaded');
    }

    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', 'processing');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by_user_id');
    }

    public function scopeProcessed(Builder $query): Builder
    {
        return $query->where('status', 'processed');
    }


    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }


    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }


    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }


    public function scopeByMediaType(Builder $query, string $mediaType): Builder
    {
        return $query->where('media_type', $mediaType);
    }


    public function scopeByVendor(Builder $query, int $vendorId): Builder
    {
        return $query->where('vendor_id', $vendorId);
    }


    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }


    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }


    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }


    public function getErrorRatePercentage(): float
    {
        if ($this->total_rows === 0) {
            return 0;
        }

        return round(($this->invalid_rows / $this->total_rows) * 100, 2);
    }


    public function getSuccessRatePercentage(): float
    {
        if ($this->total_rows === 0) {
            return 0;
        }

        return round(($this->valid_rows / $this->total_rows) * 100, 2);
    }


    public function updateStatus(string $status): void
    {
        $this->update(['status' => $status]);
    }


    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
        ]);
    }

    public function markAsProcessed(): void
    {
        $this->update([
            'status' => 'processed',
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
        ]);
    }

    public function markAsFailed(string $errorMessage = ''): void
    {
        $this->update([
            'status' => 'failed',
        ]);
    }

    public function updateRowCounts(int $totalRows, int $validRows, int $invalidRows): void
    {
        $this->update([
            'total_rows' => $totalRows,
            'valid_rows' => $validRows,
            'invalid_rows' => $invalidRows,
        ]);
    }
}
