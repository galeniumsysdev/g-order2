var baseurl = window.Laravel.url;
function getvaluemapping(){
  var tipe=$("#mapping-type").val();
  if(tipe=="regencies")
  {
    $('#mapping-value').empty();
    $.get(baseurl+'/ajax/getCity',function(data){
        $.each(data,function(index,subcatObj){
            $('#mapping-value').append('<option value="'+subcatObj.id+'">'+subcatObj.name+'</option>');
        });
    });
  }else if(tipe=="category_outlets"){
    $('#mapping-value').empty();
    $.get(baseurl+'/ajax/getCatOutlet?id='+$('#customer_id').val(),function(data){
        //console.log(data);
          $('#mapping-value').empty();
        $.each(data,function(index,subcatObj){
            $('#mapping-value').append('<option value="'+subcatObj.id+'">'+subcatObj.name+'</option>');
        });
    });

  }else{
    $('#mapping-value').empty();
  }
}
$(document).ready(function() {

  var table=  $('#mapping-table').DataTable({
          "processing": true,
          //"serverSide": true,
          "ajax": baseurl+"/ajax/getMappingType/"+$("#customer_id").val(),
          "columns":[
              { "data": "id" , "orderable":false, "searchable":false, "name":"ID" },
              { "data": "data" },
              { "data": "name" }
          ],
          "columnDefs": [
            { "width": "15", "targets": 0 ,}
          ]
       });

$('#check-all').click(function() {
    if ($(this).prop('checked')) {
        $('.chk-mapping').prop('checked', true);
    } else {
        $('.chk-mapping').prop('checked', false);
    }
});




$('#frm-addmapping').on('submit', function(event){
        event.preventDefault();
        $('#button_action').val('add');
        var form_data = $(this).serialize();
        console.log("data"+form_data);
        $.ajax({
            url:baseurl+"/ajax/addMappingType",
            method:"POST",
            data:form_data,
            dataType:"json",
            success:function(data)
            {
                if(data.error.length > 0)
                {
                    var error_html = '';
                    for(var count = 0; count < data.error.length; count++)
                    {
                        error_html += '<div class="alert alert-danger">'+data.error[count]+'</div>';
                    }
                    $('#form_output').html(error_html);
                }
                else
                {
                    $('#form_output').html(data.success);
                    $('#frm-addmapping')[0].reset();
                    $('#mapping-value').empty();
                    $('#mapping-table').DataTable().ajax.reload();
                }
            }
        })
    });

});
