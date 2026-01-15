<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\GeneratePDFReceipt;

Schedule::call(function () {
    $receipt = new GeneratePDFReceipt;
    $receipt->fetchReceipt();
})->monthly();
