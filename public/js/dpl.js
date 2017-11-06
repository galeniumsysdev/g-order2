$(document).ready(function(){
	$('#generate-sugg-no-form').submit(function (e){
		if($('#outlet').val() == 0){
			alert('Silakan pilih outlet.');
			e.preventDefault();
		}
	})
})