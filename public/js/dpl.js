$(document).ready(function(){
	$('#outlet').change(function(){
		var outlet_id = $(this).val();
		$.get('/dpl/distlist/'+outlet_id,function (result){
			$('#distributor').empty();
			$.each(result, function(key,value){
				$('#distributor')
						.append($('<option></option>')
								.attr('value',value.distributor_id)
								.text(value.customer_name))
				//console.log(value.distributor_id);
			})
		},'json');
	})
})