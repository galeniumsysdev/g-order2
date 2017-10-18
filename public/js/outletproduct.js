$(document).ready(function() {
	if($('#product-list').length)
    	$('#product-list').DataTable();
    if($('#import-product').length)
    	$('#import-product').DataTable();
	if($('#import-stock').length)
    	$('#import-stock').DataTable();

    $('#trx-in-date, #trx-out-date').datetimepicker({
        format: "DD MMMM YYYY",
        locale: "en"
    });

    $.get('/outlet/product/getList/',
        function (data) {
            $('#product-name-in').typeahead({
                name: 'product-in-list-'+data.length,
                source: data,
                items: 'all',
                showHintOnFocus: 'all',
                displayText: function (item) {
                    return item.title;
                },
                afterSelect: function (item) {
                    $('#product-code-in').val(item.op_id);
                    $('#unit-sell-in').text(item.unit);
                }
            });

            $('#product-name-out').typeahead({
                name: 'product-out-list-'+data.length,
                source: data,
                items: 'all',
                showHintOnFocus: 'all',
                displayText: function (item) {
                    return item.title;
                },
                afterSelect: function (item) {
                    $('#product-code-out').val(item.op_id);
                    $('#unit-sell-out').text(item.unit);
                }
            });
    }, 'json');

    $('#tabs').tabs();
});