<?php

namespace App\Services;

use Exception;
use App\Http\Traits\RandomizeTrait;
use App\Models\SalesOrderItemBreakdown;
use Barryvdh\DomPDF\Facade\Pdf;
use Str;
use Storage;
use File;
use App\Models\DeliveryOrder;

class GeneratePDFDeliveryOrder{

    private $deliveryOrder;
    private $path;
    private $directory;

    public function __construct(DeliveryOrder $deliveryOrder){
        $this->deliveryOrder = $deliveryOrder;
        $this->directory = 'pdf/delivery-order/'.$deliveryOrder->salesOrder->salesorder_no.'/';
        $this->path = $this->directory.'SURAT-JALAN-[SO]'.$deliveryOrder->salesOrder->salesorder_no.'.pdf';
    }

    public function generate(){
        $deliveryOrder = $this->deliveryOrder;

        $logo = base64_encode(File::get(public_path('images/logo.jpeg')));
        $customPaper = array(0,0,684,792); //ukuran kertas custom dalam satuan point (1 inch = 72 point)
        $pdf = Pdf::loadView('pdfs.deliveryOrder', compact('deliveryOrder','logo'))
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
