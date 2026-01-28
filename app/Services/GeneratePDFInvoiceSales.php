<?php

namespace App\Services;

use Exception;
use App\Http\Traits\RandomizeTrait;
use App\Models\SalesOrderItemBreakdown;
use Barryvdh\DomPDF\Facade\Pdf;
use Str;
use Storage;
use File;
use \Carbon\Carbon;
use App\Models\SalesOrder;

class GeneratePDFInvoiceSales{

    private $salesOrder;
    private $path;
    private $directory;

    public function __construct(SalesOrder $salesOrder){
        $this->salesOrder = $salesOrder;
        $this->directory = 'pdf/invoice/sales/';
        $this->path = $this->directory.'INV-'.$salesOrder->salesorder_no.'.pdf';
    }

    public function generate($changeDate = false, $invoiceDate = null){
        $salesOrder = $this->salesOrder;

        $logo = base64_encode(File::get(public_path('images/logo.jpeg')));
        if(is_null($salesOrder->invoice_no) || $changeDate){
            if(is_null($invoiceDate)){
                $dateNumber = date('ym');
                $dateNumberInvoice = date('ym');
            }
            else{
                $invoiceDate = Carbon::parse($invoiceDate);
                $dateNumber = $invoiceDate->format('y');
                $dateNumberInvoice = $invoiceDate->format('ym');
            }

            $number = get_counter('KS-R'.$dateNumber,'KS-R'.$dateNumberInvoice,6);

            $salesOrder->invoice_no = $number;

            if(is_null($invoiceDate)){
                $salesOrder->invoice_date = now();
                $salesOrder->term_of_payment = now()->addDays(60)->endOfMonth();
            }
            else{
                $salesOrder->invoice_date = $invoiceDate;
                $salesOrder->term_of_payment = $invoiceDate->addDays(60)->endOfMonth();
            }
        }
        if($salesOrder->discount_sales > $salesOrder->discount_company)
            $salesOrder->total_omset = $salesOrder->items->sum('master_total_price') * (100-$salesOrder->discount_sales)/100;
        else
            $salesOrder->total_omset = $salesOrder->items->sum('total_price') * (100-$salesOrder->discount_company)/100;
        $salesOrder->save();
        $customPaper = array(0,0,684,792);
        $pdf = Pdf::loadView('pdfs.invoiceSales', compact('salesOrder','logo'))
                    ->setPaper($customPaper, 'Portrait');
        $content = $pdf->download()->getOriginalContent();
        Storage::disk('minio')->put($this->path,$content);
    }

    public function getPDF(){
        try{
            if(!Storage::disk('minio')->files($this->directory)){
                Storage::makeDirectory($this->directory);
            }

            if(Storage::disk('minio')->missing($this->path)){{
                $this->generate();
            }}
        }
        catch(Exception $e){
            $this->generate();
        }

        return Storage::disk(name: 'minio')->url($this->path);
    }

}
