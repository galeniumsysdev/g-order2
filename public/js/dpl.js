$(document).ready(function(){
	if($('#dpl-list').length){
    	$('#dpl-list').DataTable({
    		'order': []
    	});
        window.setTimeout(function(){
            $(window).resize();
        },2000);
    }

	$('#generate-sugg-no-form').submit(function (e){
		if($('#outlet').val() == 0){
			alert('Silakan pilih outlet.');
			e.preventDefault();
		}
	})
})