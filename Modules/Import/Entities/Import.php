<?php

namespace Modules\Import\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Import extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'file_path',
        'file_name',
        'file_type',
        'imported_type',
        'status',
        'total_rows',
        'processed_rows',
        'failed_rows',
        'error_message',
        'started_at',
        'completed_at',
    ];


    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

     
    public function importable(): MorphTo
    {
        return $this->morphTo();
    }

        public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

     
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

     
    public function isPending(): bool
    {
        return $this->status === 'pending';
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


    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

     
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
    }

     
    public function updateProgress(int $processedRows, int $failedRows = 0): void
    {
        $this->update([
            'processed_rows' => $processedRows,
            'failed_rows' => $failedRows,
        ]);
    }
}
