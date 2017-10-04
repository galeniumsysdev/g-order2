$(document).ready(function(){
	$('#outlet').change(function(){
		var outlet_id = $(this).val();
		$.get('/dpl/distlist/'+outlet_id,function (result){
			$('#distributor').empty();
			if(result.length != 0){
				$.each(result, function(key,value){
					$('#distributor')
							.append($('<option></option>')
									.attr('value',value.distributor_id)
									.text(value.customer_name))
				})
			}
			else{
				$('#distributor')
							.append($('<option></option>')
									.attr('value',0)
									.text('---Silakan Pilih Outlet---'))
			}
		},'json');
	});

	$('#generate-sugg-no-form').submit(function (e){
		if($('#outlet').val() == 0 || $('#distributor').val() == 0){
			alert('Silakan pilih outlet dan distributor');
			e.preventDefault();
		}
	})
})