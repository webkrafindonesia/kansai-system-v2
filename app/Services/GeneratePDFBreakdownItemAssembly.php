<?php

namespace App\Services;

use App\Http\Traits\RandomizeTrait;
use App\Models\SalesOrderItemBreakdown;
use Barryvdh\DomPDF\Facade\Pdf;
use Str;
use Storage;
use File;
use App\Models\Assembly;

class GeneratePDFBreakdownItemAssembly{

    private $assembly;
    private $path;

    public function __construct(Assembly $assembly){
        $this->assembly = $assembly;
        $this->path = 'pdf/breakdown/assembly/Breakdown['.$assembly->code.'].pdf';
    }

    public function generate(){
        $assembly = $this->assembly;

        $breakdowns = $assembly->breakdowns()
                                ->select('assembly_item_breakdowns.product_id','assembly_item_breakdowns.uom')
                                ->join('assembly_items','assembly_item_breakdowns.assembly_item_id','assembly_items.id')
                                ->selectRaw('SUM(assembly_item_breakdowns.qty * assembly_items.qty) as total_qty')
                                ->groupBy('assembly_item_breakdowns.product_id','assembly_item_breakdowns.uom')
                                ->get();
        //$logo = base64_encode(File::get(public_path('logo/logo-02.svg')));
        // $customPaper = array(0,0,684,396);
        $pdf = Pdf::loadView('pdfs.breakdownAssembly', compact('assembly','breakdowns'))
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
