$(document).ready(function() {
	if($('#product-list').length)
    	$('#product-list').DataTable();
    if($('#import-product').length)
    	$('#import-product').DataTable();
	if($('#import-stock').length)
    	$('#import-stock').DataTable();
 
    $('.change-product').hide();

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
                    $('#product-name-in').attr('readonly','readonly');
                    $('#change-product-in').show();
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
                    $('#product-name-out').attr('readonly','readonly');
                    $('#change-product-out').show();
                    $('#product-code-out').val(item.op_id);
                    $('#unit-sell-out').text(item.unit);
                }
            });
    }, 'json');

	$('.product-container').on('click','li',function(){
		$(this).closest('.form-trx').find('.qty').focus();
	})

    $('.change-product').click(function(){
        $(this).hide();
        var product_container = $(this).closest('.product-container');
        product_container.find('.product-name').removeAttr('readonly').val('');
        product_container.find('.product-code').val('');
        console.log($(this).closest('.form-trx').find('.unit-sell').text());
        $(this).closest('.form-trx').find('.unit-sell').text('');
    })

    $('#tabs').tabs();
});