<?php

namespace App\Services;

use App\Http\Traits\RandomizeTrait;
use App\Models\SalesOrderItemBreakdown;
use Barryvdh\DomPDF\Facade\Pdf;
use Str;
use Storage;
use File;
use App\Models\SalesOrder;

class GeneratePDFBreakdownItem{

    private $salesOrder;
    private $path;

    public function __construct(SalesOrder $salesOrder){
        $this->salesOrder = $salesOrder;
        $this->path = 'pdf/breakdown/salesOrder/Breakdown['.$salesOrder->salesorder_no.'].pdf';
    }

    public function generate(){
        $salesOrder = $this->salesOrder;

        // $breakdowns = $salesOrder->breakdowns()
        //                         ->select('sales_order_item_breakdowns.sales_order_item_id','sales_order_item_breakdowns.product_id','sales_order_item_breakdowns.uom')
        //                         ->join('sales_order_items','sales_order_item_breakdowns.sales_order_item_id','sales_order_items.id')
        //                         ->selectRaw('SUM(sales_order_item_breakdowns.qty * sales_order_items.qty) as total_qty')
        //                         ->groupBy('sales_order_item_breakdowns.product_id','sales_order_item_breakdowns.uom')
        //                         ->get();
        //$logo = base64_encode(File::get(public_path('logo/logo-02.svg')));
        // $customPaper = array(0,0,684,396);
        // $pdf = Pdf::loadView('pdfs.breakdown', compact('salesOrder','breakdowns'))
        $pdf = Pdf::loadView('pdfs.breakdown', compact('salesOrder'))
                    ->setPaper('a4', 'portrait');
        $content = $pdf->download()->getOriginalContent();
        Storage::disk('minio')->put($this->path,$content);
    }

    public function getPDF(){
        // generate tiap kali dipanggil

        // try{
        //     if(!Storage::disk('minio')->files($this->directory)){
        //         Storage::makeDirectory($this->directory);
        //     }

        //     if(Storage::disk('minio')->missing($this->path)){{
        //         $this->generate();
        //     }}
        // }
        // catch(\Exception $e){
            $this->generate();
        // }

        return Storage::disk(name: 'minio')->url($this->path);
    }

}
