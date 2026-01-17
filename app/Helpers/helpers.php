<?php

use Money\Money;
use Money\Currency;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use App\Models\Counter;
use Carbon\Carbon;

function format_warehouse_type($type) {
    return match ($type) {
        'raw_material' => 'Gudang Bahan Baku',
        'finish_good' => 'Gudang Barang Jadi',
        'virtual' => 'Gudang Virtual',
        'others' => 'Lain-lain',
        default => 'Tidak Diketahui',
    };
}

function product_types() {
    return [
        'raw_material' => 'Bahan Baku',
        'assembled_good' => 'Barang Rakit',
        'finish_good' => 'Barang Jadi',
    ];
}

function product_type_match($type){
    return match ($type) {
        'raw_material' => 'Bahan Baku',
        'assembled_good' => 'Barang Rakit',
        'finish_good' => 'Barang Jadi',
        default => ''
    };
}

function return_types() {
    return [
        'skip' => 'Abaikan',
        'back_in_stock' => 'Masuk ke gudang barang jadi'
    ];
}

function return_descriptions() {
    return [
        'skip' => 'Tidak akan masuk ke stok',
        'back_in_stock' => 'Otomatis menjadi stok di gudang barang jadi'
    ];
}

function return_type_match($type){
    return match ($type) {
        'skip' => 'Abaikan',
        'back_in_stock' => 'Masuk ke gudang barang jadi'
    };
}

function clean_numeric($numeric){
    if ($numeric === null) return 0;

    $numeric = (string)$numeric;
    // 1. Hapus semua titik pemisah ribuan
    $numeric = str_replace('.', '', $numeric);

    // // 2. Ganti koma menjadi titik (desimal)
    $numeric = str_replace(',', '.', $numeric);

    // // 3. Jika ada karakter lain selain angka dan titik, hapus
    // $numeric = preg_replace('/[^0-9.]/', '', $numeric);

    return $numeric;
}

function moneyFormat($value): int{
    // $value = is_null($value) ? 0 : $value;
    // $money = new Money($value, new Currency('IDR'));
    // $raw = $money->getAmount();
    // $currencies = new ISOCurrencies();
    // $formatter = new DecimalMoneyFormatter($currencies);

    // return intval($formatter->format($money));
    return $value;
}

function numberFormat($value, $decimal_digit = 0){
    $value = is_null($value) ? 0 : $value;
    return number_format($value,$decimal_digit,',','.');
    // return $value;
}

function dateFormat($value, $format = 'M d, Y'){
    return Carbon::parse($value)->format($format);
}

function get_counter($prefix, $string = null, $pad_length = 4, $pad_string = "0", $pad_position = STR_PAD_LEFT){
    $string = is_null($string) ? $prefix : $string;
    $counter = Counter::firstOrCreate(['prefix'=>$prefix]);
    $counter->counter++;
    $counter->save();

    return $string.str_pad($counter->counter, $pad_length, $pad_string, $pad_position);
}
