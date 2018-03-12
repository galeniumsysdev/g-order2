var baseurl = window.Laravel.url;
function getvaluemapping(){
  var tipe=$("#mapping-type").val();
  if(tipe=="regencies")
  {
    $('#province-div').show();
    $('#mapping-value').empty();
    if($('#province').length){
      $('#province-area').val($('#province').val());
      $.get(baseurl+'/ajax/getCity',{id:$('#province').val()},function(data){
          $.each(data,function(index,subcatObj){
              $('#mapping-value').append('<option value="'+subcatObj.id+'">'+subcatObj.name+'</option>');
          });
      });
    }else{
      $.get(baseurl+'/ajax/getCity',function(data){
          $.each(data,function(index,subcatObj){
              $('#mapping-value').append('<option value="'+subcatObj.id+'">'+subcatObj.name+'</option>');
          });
      });
    }
  }else if(tipe=="category_outlets"){
    $('#province-div').hide();
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
    $('#province-div').hide();
  }
}

function getvalueregencies(){
  var province=$("#province-area").val();
    $('#province-div').show();
    $('#mapping-value').empty();
    $.get(baseurl+'/ajax/getCity',{id:province},function(data){
        $.each(data,function(index,subcatObj){
            $('#mapping-value').append('<option value="'+subcatObj.id+'">'+subcatObj.name+'</option>');
        });
    });

}
$(document).ready(function() {
  $('#province-div').hide();
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

if($('#dist-table').length){
  $('#dist-table').DataTable();
    window.setTimeout(function(){
        $(window).resize();
    },2000);
}



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
                    $('#province-div').hide();
                    $('#mapping-table').DataTable().ajax.reload();
                }
            }
        })
    });

});
