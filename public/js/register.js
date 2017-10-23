var baseurl = window.Laravel.url;

function getListCity(id,old){
  $.get(baseurl+'/ajax/getCity?id='+id,function(data){
      //console.log(data);
        $('#city').empty();
        $('#city').append('<option value="" selected=selected>--</option>');
        $('#district').empty();
        $('#district').append('<option value="" selected=selected>--</option>');
        $('#subdistricts').empty();
        $('#subdistricts').append('<option value="" selected=selected>--</option>');
      $.each(data,function(index,subcatObj){
        if (subcatObj.id==old)
        {
          $('#city').append('<option value="'+subcatObj.id+'" selected=selected>'+subcatObj.name+'</option>');
        }else{
          $('#city').append('<option value="'+subcatObj.id+'">'+subcatObj.name+'</option>');
        }
      });

  });
}

function getListDistrict(id,old){

  $.get(baseurl+'/ajax/getDistrict?id='+id,function(data){
      //console.log(data);
        $('#district').empty();
        $('#district').append('<option value="" selected=selected>--</option>');
        $('#subdistricts').empty();
        $('#subdistricts').append('<option value="" selected=selected>--</option>');
      $.each(data,function(index,subcatObj){
        if (subcatObj.id==old)
        {
          $('#district').append('<option value="'+subcatObj.id+'" selected=selected>'+subcatObj.name+'</option>');
        }else{
          $('#district').append('<option value="'+subcatObj.id+'">'+subcatObj.name+'</option>');
        }
      });

  });
}

function getListSubdistrict(id,old){

  $.get(baseurl+'/ajax/getSubdistrict?id='+id,function(data){
      //console.log(data);
        $('#subdistricts').empty();
        $('#subdistricts').append('<option value="" selected=selected>--</option>');
      $.each(data,function(index,subcatObj){
        if (subcatObj.id==old)
        {
          $('#subdistricts').append('<option value="'+subcatObj.id+'" selected=selected>'+subcatObj.name+'</option>');
        }else{
          $('#subdistricts').append('<option value="'+subcatObj.id+'">'+subcatObj.name+'</option>');
        }
      });

  });
}
