<?php

namespace App\Services;

use Exception;
use App\Http\Traits\RandomizeTrait;
use App\Models\SalesOrderItemBreakdown;
use Barryvdh\DomPDF\Facade\Pdf;
use Str;
use Storage;
use File;
use DB;
use Log;
use \Carbon\Carbon;
use App\Models\SalesOrder;
use App\Models\ReturnSalesOrder;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Jobs\GenerateReceipt;

class GeneratePDFReceipt{

    private $path;
    private $directory;

    public function setup(Receipt $receipt){
        $this->directory = 'pdf/receipt/'.str_replace(" ","-",$receipt->customer->name).'/';
        $this->path = $this->directory.'TANDA-TERIMA-'.$receipt->receipt_date->format('Y-m-d').'.pdf';
    }

    /**
     * Assume it will be run on the first date each month
     */
    public function fetchReceipt(){
        $startPrevMonth = Carbon::now()->subMonth()->startOfMonth();
        $endPrevMonth = Carbon::now()->subMonth()->endOfMonth();

        // Invoice from Sales Order
        $salesOrders = SalesOrder::whereBetween('invoice_date',[$startPrevMonth, $endPrevMonth])
                        ->whereNotNull('invoice_no')
                        ->orderBy('invoice_date')
                        ->get()
                        ->groupBy('customer_id');

        DB::beginTransaction();
        foreach($salesOrders as $customer_id => $invoices){
            $receipt = new Receipt;
            $receipt->customer_id = $customer_id;
            $receipt->receipt_date = date('Y-m-d');
            $receipt->save();

            foreach($invoices as $invoice){
                $receiptItem = new ReceiptItem;
                $receiptItem->receipt_id = $receipt->id;
                $receiptItem->reference = 'Sales Order';
                $receiptItem->reference_id = $invoice->id; // sales order ID
                $receiptItem->reference_date = $invoice->invoice_date;
                $receiptItem->save();

                // cek apakah punya retur atau tidak
                $returns = ReturnSalesOrder::where('sales_order_id',$invoice->id)
                                ->whereNotNull('processed_at')
                                ->get();

                if(count($returns)){
                    foreach($returns as $return){
                        $receiptItem = new ReceiptItem;
                        $receiptItem->receipt_id = $receipt->id;
                        $receiptItem->reference = 'Retur';
                        $receiptItem->reference_id = $return->id; // sales order ID
                        $receiptItem->reference_date = $return->return_date;
                        $receiptItem->save();
                    }
                }
            }
        }

        DB::commit();

        $this->dispatchReceipt();
    }

    public function dispatchReceipt(){
        $receipts = Receipt::whereDate('receipt_date',date('Y-m-d'))->get();
        $count = 0;
        foreach($receipts as $receipt){
            GenerateReceipt::dispatch($receipt);
            $count++;
        }
        Log::info($count.' receipt is dispatched to generate.');
    }

    public function generate(Receipt $receipt){
        $this->setup($receipt);

        $logo = base64_encode(File::get(public_path('images/logo.jpeg')));
        $customPaper = array(0,0,684,396);
        $pdf = Pdf::loadView('pdfs.receipt', compact('receipt','logo'))
                    ->setPaper($customPaper, 'landscape');
        $content = $pdf->download()->getOriginalContent();
        Storage::disk('minio')->put($this->path,$content);
    }

    public function getPDF(Receipt $receipt){
        $this->setup($receipt);

        try{
            if(!Storage::disk('minio')->files($this->directory)){
                Storage::makeDirectory($this->directory);
            }

            if(Storage::disk('minio')->missing($this->path)){{
                $this->generate($receipt);
            }}
        }
        catch(Exception $e){
            $this->generate($receipt);
        }

        return Storage::disk(name: 'minio')->url($this->path);
    }

}
