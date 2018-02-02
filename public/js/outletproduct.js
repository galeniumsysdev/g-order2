$(document).ready(function() {
    // Datatable
    var trx_list_datatable;

	if($('#product-list').length){
    	$('#product-list').DataTable();
        window.setTimeout(function(){
            $(window).resize();
        },2000);
    }

    if($('#import-product').length){
    	$('#import-product').DataTable({
            'order': [],
        });
        window.setTimeout(function(){
            $(window).resize();
        },2000);
    }

	if($('#import-stock').length){
    	$('#import-stock').DataTable({
            'order': [],
        });
        window.setTimeout(function(){
            $(window).resize();
        },2000);
    }
    if($('#trx-list').length){
        trx_list_datatable = $('#trx-list').DataTable({
            'scrollX':        true,
            'scrollCollapse': true,
            'order': [],
            columnDefs: [
                { responsivePriority: 1, targets: 0 },
                { responsivePriority: 2, targets: 1 },
                { responsivePriority: 3, targets: 4 },
                { responsivePriority: 3, targets: 5 },
            ]
        });
        window.setTimeout(function(){
            $(window).resize();
        },2000);
    }

    if($('#detail-list').length){
        $('#detail-list').DataTable({
            'ordering': false,
        });
        window.setTimeout(function(){
            $(window).resize();
        },2000);
    }

    if($('#report-list').length){
        $('#report-list').DataTable({
            'order': [],
        });
        window.setTimeout(function(){
            $(window).resize();
        },2000);
    }
    // ===============

    $('.change-product').hide();

    if($('#trx-in-date, #trx-out-date').length){
        $('#trx-in-date, #trx-out-date').datetimepicker({
            format: "DD MMMM YYYY",
            locale: "en"
        });
    }

    if($('.date-range').length){
        $('.date-range').datetimepicker({
            format: "DD MMMM YYYY",
            locale: "en"
        });
    }

    if($('#product-name-in, #product-name-out').length){
        $.get(window.Laravel.url+'/outlet/product/getList',
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
    }

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

    if($('#tabs').length)
        $('#tabs').tabs();

    $('#form-product').submit(function(e){
        if($('.duplicate').length){
            e.preventDefault();
            alert('Please fix the duplicate item(s).');
        }
    })

    if($('#outlet-name').length){
        $.get(window.Laravel.url+'/ajax/typeaheadOutlet/',
            function(data){
                $('#outlet-name').typeahead({
                    name: 'outlet',
                    source: data,
                    items: 'all',
                    showHintOnFocus: 'all',
                    displayText: function (item) {
                        return item.customer_name;
                    }
                });
            }, 'json');
    }

    if($('#province').length){
        $.get(window.Laravel.url+'/ajax/typeaheadProvince/',
            function(data){
                $('#province').typeahead({
                    name: 'province',
                    source: data,
                    items: 'all',
                    showHintOnFocus: 'all',
                    displayText: function (item) {
                        return item.name;
                    },
                    afterSelect: function (item) {
                        $('#area').val('');
                        $('#area').attr('disabled');
                        $.get(window.Laravel.url+'/ajax/getCity',{
                            id: item.id
                        }).done(
                            function(data){
                                $('#area').removeAttr('disabled');
                                $('#area').typeahead('destroy');
                                $('#area').typeahead({
                                    name: 'area',
                                    source: data,
                                    items: 'all',
                                    showHintOnFocus: 'all',
                                    displayText: function (item) {
                                        return item.name;
                                    }
                                });
                            }, 'json');
                    }
                });
            }, 'json');
    }

    // Trx List
    $('#btn-filter').click(function(){
        window.location.href = window.Laravel.url+'/outlet/transaction/list?start_date='+$('#start-date-trx').val()+'&end_date='+$('#end-date-trx').val()+'&product_name='+$('#product-name').val()+'&generic='+$('#generic').val();
    })
    // ==============
});
