$(document).ready(function(){
	if($('#dpl-list').length){
    	$('#dpl-list').DataTable({
    		'order': [],
    		columnDefs: [
	            { responsivePriority: 1, targets: 0 },
	            { responsivePriority: 2, targets: 1 },
	            { responsivePriority: 3, targets: 8 }
	        ]
    	});
        window.setTimeout(function(){
            $(window).resize();
        },2000);
    }

    if($('#dpl-history').length){
    	$('#dpl-history').DataTable({
    		'order': [],
    		columnDefs: [
	            { responsivePriority: 1, targets: 0 },
	            { responsivePriority: 2, targets: 3 }
	        ]
    	});
        window.setTimeout(function(){
            $(window).resize();
        },2000);
    }

	$('#generate-sugg-no-form').submit(function (e){
		if($('#outlet-id').val() == 0){
			alert('Silakan masukkan outlet yang sesuai.');
			e.preventDefault();
		}
	})

	$('.discount-form').submit(function (e){
		e.preventDefault();
		document.getElementById("loader").style.display = "block";
		document.getElementById("myDiv").style.display = "none";
		$.post($(this).attr('action'),{
			action: $(this).find('#action').val(),
			suggest_no: $(this).find('#suggest-no').val(),
			reason_reject: ($(this).find('#reason-reject')) ? $(this).find('#reason-reject').val() : '',
			note: $('#note').val()
		}).done(function (result){
			window.location.href = window.Laravel.url + '/dpl/list';
		})
	})

	$.get(window.Laravel.url+'/dpl/list/outlet',
		function (data){
			$('#outlet').typeahead('destroy');
			$('#outlet').typeahead({
				source: data,
				items: 'all',
				showHintOnFocus: 'all',
				displayText: function (item){
					return item.customer_name;
				},
				afterSelect: function (item){
					$('#outlet-id').val(item.id);
				}
			})
		}
	)
})
