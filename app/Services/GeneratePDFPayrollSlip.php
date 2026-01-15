<?php

namespace App\Services;

use Exception;
use App\Http\Traits\RandomizeTrait;
use App\Models\SalesOrderItemBreakdown;
use Barryvdh\DomPDF\Facade\Pdf;
use Str;
use Storage;
use File;
use App\Models\EmployeePayroll;

class GeneratePDFPayrollSlip{

    private $employeePayroll;
    private $path;
    private $directory;

    public function __construct(EmployeePayroll $employeePayroll){
        $this->employeePayroll = $employeePayroll;
        $this->directory = 'pdf/payroll-slip/'.$employeePayroll->employee->name.'['.$employeePayroll->employee->id.']';
        $this->path = $this->directory.'PAYROLL-SLIP-'.$employeePayroll->employee->name.'-'.$employeePayroll->id.'.pdf';
    }

    public function generate(){
        $employeePayroll = $this->employeePayroll;

        $logo = base64_encode(File::get(public_path('images/logo.jpeg')));
        $customPaper = array(0,0,684,396);
        $pdf = Pdf::loadView('pdfs.payrollSlip', compact('employeePayroll','logo'))
                    ->setPaper($customPaper, 'portrait');
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
