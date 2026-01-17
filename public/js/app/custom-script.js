window.bcmulJS = function (num1, num2, scale = 0) {
    // Simple example for demonstration; consider using a dedicated JS library
    // for arbitrary precision math if needed, as native JS math has limits.
    const result = parseFloat(num1) * parseFloat(num2);
    return result.toFixed(scale);
};

window.clean_numericJS = function (value) {
    if (value === null || value === "") {
        return 0;
    }

    value = value.toString();
    // 1. Hapus semua titik pemisah ribuan
    value = value.replaceAll('.', '');

    // 2. Ganti koma menjadi titik (desimal)
    value = value.replaceAll(',', '.');

    // 3. Jika ada karakter lain selain angka dan titik, hapus
    // value = preg_replace('/[^0-9.]/', '', $numeric);

    return value;
}

window.numberFormatJS = function(value, decimal_digit = 0){
    const specificFormatter = new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: decimal_digit,
        maximumFractionDigits: decimal_digit,
        useGrouping: true
    });

    value = (value === null) ? 0 : value;
    return specificFormatter.format(value);
}
