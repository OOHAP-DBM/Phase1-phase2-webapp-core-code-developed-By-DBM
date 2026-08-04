<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Import\Entities\InventoryImportBatch;


class InventoryImportedMail extends Mailable
{
    use Queueable, SerializesModels;

    public InventoryImportBatch $batch;
    public string $adminName;

    public function __construct(InventoryImportBatch $batch, string $adminName)
    {
        $this->batch = $batch;
        $this->adminName = $adminName;
    }

    public function build()
    {
        return $this
            ->subject('Inventory Imported Successfully')
            ->view('emails.inventory-imported');
    }
}