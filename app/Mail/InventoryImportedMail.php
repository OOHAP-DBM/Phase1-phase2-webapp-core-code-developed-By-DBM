<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Modules\Import\Entities\InventoryImportBatch;

class InventoryImportedMail extends Mailable
{
    use Queueable, SerializesModels;

    public InventoryImportBatch $batch;
    public string $adminName;
    public Collection $inventories;

    /**
     * Create a new message instance.
     */
    public function __construct(
        InventoryImportBatch $batch,
        string $adminName,
        Collection $inventories
    ) {
        $this->batch = $batch;
        $this->adminName = $adminName;
        $this->inventories = $inventories;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this
            ->subject('Inventory Imported Successfully')
            ->view('emails.inventory-imported');
    }
}