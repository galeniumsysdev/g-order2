var baseurl = window.Laravel.url; //$('#baseurl').val() ;
function addCart(id)
{
  if(window.Laravel.auth.user)
  {
    $.ajax({
             type: 'post',
             url: baseurl+'/add-to-cart/'+id,
             data: {
                 '_token': $('input[name=_token]').val(),
                 'id': id,
                 'satuan': $('#satuan-'+id).val(),
                 'qty': $('#qty-'+id).val(),
                 'hrg':$('#hrg-'+id).val()
             },
             success: function(data) {
               $('#shopcart').html(data.totline) ;
             }
         });
  }else{
      window.location.href =  baseurl+'/login';
  }

  return false;
}

function changeProduct(id){
  if(window.Laravel.auth.user)
  {
    var id = id.substr(4,40);

    var v_id = id.substr(0,36);
    var v_uom = id.substr(-3,3);
    var url=$('#rmv-'+id).attr("href").substr(0,40);

    $.ajax({
             type: 'post',
             url: baseurl+'/edit-item-cart/'+id,
             data: {
                 '_token': $('input[name=_token]').val(),
                 'product': v_id,
                 'satuan': $('#stn-'+id).val(),
                 'qty': $('#qty-'+id).val(),
                 'hrg': 0
             },
             success: function(data) {
               var v_id_new = v_id+"-"+$('#stn-'+id).val();
               //alert(v_id_new);
               $('#totprice2').html("Total: "+data.total) ;
               $('#totprice1').html("Total: "+data.total) ;
               $('#subtot-'+id).html(data.amount) ;
               $('#hrg-'+id).html(data.price) ;
               if(v_uom!=$('#stn-'+id).val())
               {
                  $('#'+id).attr('id',v_id_new);
                  $('#subtot-'+id).attr('id',"subtot-"+v_id_new) ;
                  $('#hrg-'+id).attr('id',"hrg-"+v_id_new) ;
                  $('#qty-'+id).attr('id',"qty-"+v_id_new) ;
                  $('#stn-'+id).attr('id',"stn-"+v_id_new) ;
                  $('#rfs-'+id).attr('id',"rfs-"+v_id_new) ;
                  $('#rmv-'+id).attr("href", url+v_id_new);
                  $('#rmv-'+id).attr("id", "rmv-"+v_id_new);
                //  alert($('#qty-'+v_id_new).attr('id'));
               }

             },error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert("Status: " + textStatus); alert("Error: " + errorThrown);
                }
         });
  }else{
      window.location.href =  baseurl+'/login';
  }

  return false;
}

function getPrice(id){
  var v_uom = $('#satuan-'+id).val();
  $.get(baseurl+'/getPrice?product='+id+'&uom='+v_uom,function(data){
        $('#lblhrg-'+id).html('Rp. '+rupiah(data.price)+'/'+v_uom);
        $('#hrg-'+id).val(data.price);
      });
}

function rupiah(nStr) {
   nStr += '';
   x = nStr.split('.');
   x1 = x[0];
   x2 = x.length > 1 ? '.' + x[1] : '';
   var rgx = /(\d+)(\d{3})/;
   while (rgx.test(x1))
   {
      x1 = x1.replace(rgx, '$1' + ',' + '$2');
   }
   return  x1 + x2;
}

function validateNumber(event) {
    var key = window.event ? event.keyCode : event.which;
    if (event.keyCode === 8 || event.keyCode === 46) {
        return true;
    } else if ( key < 48 || key > 57 ) {
        return false;
    } else {
        return true;
    }
};

function isNumberKey(evt)
{
  var charCode = (evt.which) ? evt.which : evt.keyCode;
  if (charCode != 46 && charCode > 31
    && (charCode < 48 || charCode > 57))
     return false;

  return true;
}

function isNumberCheck(field) {
    var regExpr = new RegExp("^\d*\.?\d*$");
    //var regExpr = new RegExp("^\d+(\.\d*)?$");
    if (!regExpr.test(field.value)) {
      // Case of error
      field.value = "";
    }
}
