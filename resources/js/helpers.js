window.bcmulJS = function (num1, num2, scale = 0) {
    // Simple example for demonstration; consider using a dedicated JS library
    // for arbitrary precision math if needed, as native JS math has limits.
    const result = parseFloat(num1) * parseFloat(num2);
    return result.toFixed(scale);
};

window.clean_numericJS = function (value) {
    if (value === null) {
        return 0;
    }
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
