<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Receipt;
use App\Services\GeneratePDFReceipt;

class GenerateReceipt implements ShouldQueue
{
    use Queueable;

    public $receipt;
    /**
     * Create a new job instance.
     */
    public function __construct(Receipt $receipt)
    {
        $this->receipt = $receipt;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $pdf = new GeneratePDFReceipt;
        $pdf->generate($this->receipt);
    }
}
