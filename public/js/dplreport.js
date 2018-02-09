$(document).ready(function() {

    if($('#trx-in-date, #trx-out-date').length){
        $('#trx-in-date, #trx-out-date').datetimepicker({
            format: "MMMM YYYY",
            locale: "en"
        });
    }

    if($('.date-range').length){
        $('.date-range').datetimepicker({
            format: "MMMM YYYY",
            locale: "en"
        });
    }
    // ==============
});
