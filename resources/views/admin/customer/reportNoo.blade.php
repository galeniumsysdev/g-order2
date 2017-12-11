@extends('layouts.navbar_product')

@section('content')
<link rel="stylesheet"
href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css">
<div class="container">
  <div class="row">
    <div class="col-sm-12">
      <div class="panel panel-default">
        <div class="panel-heading"><strong>REPORT NOO</strong></div>
          <div class="panel-body">
            <div id="frmsearch" class="panel panel-default">
              <br>
              <form action="{{route('ExportClients')}}" class="form-horizontal" method="post" role="form">
                {{csrf_field()}}
                <div class="form-group">
                  <label class="control-label col-sm-2" for="email" style="margin-left:15px;"><strong>@lang('label.distributor') :</strong></label>
                    <div class="col-sm-8" style="margin-left:15px; margin-right:15px; margin-top:9px;">
                      <div class="input-group col-sm-12">
                        <input type="text" data-provide="typeahead" autocomplete="off"  class="form-control mb-8 mr-sm-8 mb-sm-4" name="distributor" id="distributor" value="" >
                        <span class="input-group-addon" id="change-dist">
                          <i class="fa fa-times" aria-hidden="true"></i>
                        </span>
                        <input type="hidden" name="dist_id" id="dist_id">
                    </div>
                    </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-sm-2" for="role" style="margin-left:15px;"><strong>Area :</strong></label>
                    <div class="col-sm-8" style="margin-left:15px; margin-right:15px; margin-top:10px;">
                      <!--<select class="form-control" name="city" id="city">
                        <option value="">--</option>
                        @foreach($regencies as $city)
                            <option value="{{$city->id}}">{{$city->name}}</option>
                        @endforeach
                      </select>-->
                      <input type="text" data-provide="typeahead" autocomplete="off"  class="form-control mb-8 mr-sm-8 mb-sm-4" name="city" id="city" value="" >
                      <span class="input-group-addon" id="change-city">
                        <i class="fa fa-times" aria-hidden="true"></i>
                      </span>
                      <input type="hidden" name="city_id" id="city_id">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="needproduct" style="margin-left: 15px;"><strong>Divisi :</strong></label>
                    <div class="col-sm-6" style="margin-left:15px; margin-right:15px; margin-top:8px;">
                      <input type="checkbox" class="form-check-input" name="psc_flag" id="psc_flag" value="1" {{$request->psc_flag=="1"?"checked":""}}> PSC &nbsp;
                      <input type="checkbox" class="form-check-input" name="pharma_flag" id="pharma_flag" value="1" {{$request->pharma_flag=="1"?"checked":""}}> Pharma (Non PSC)
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="subcategorydc" style="margin-left:15px;"><strong>@lang('label.channel') :</strong></label>
                    <div class="col-sm-8" style="margin-left:15px; margin-right:15px; margin-top:11px;">
                        <select multiple class="form-control" name="subgroupdc[]" >
                          @foreach($subgroupdc as $sub)
                          @if($request->subgroupdc)
                            <option value="{{$sub->id}}" {{in_array($sub->id,$request->subgroupdc)?"selected":""}}>{{$sub->group."-".$sub->subgroup}}</option>
                          @else
                            <option value="{{$sub->id}}">{{$sub->group."-".$sub->subgroup}}</option>
                          @endif
                          @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-9 col-sm-offset-2">
                        <button type="submit" id="btn-search" class="btn btn-primary">
                          Download Excel
                        </button>
                    </div>
                </div>
              </form>
            </div>
          </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('js')
@section('js')
<script src="{{ asset('js/moment-with-locales.js') }}"></script>
<script src="{{ asset('js/bootstrap3-typeahead.min.js') }}"></script>
<script src="{{ asset('js/ui/1.12.1/jquery-ui.js') }}"></script>
<script>
  $(document).ready(function() {
    $('#change-dist').hide();
    $('#change-city').hide();
    /*typeahead distributor*/
    var path2 = "{{ route('customer.searchDistributor') }}";
    $.get(path2,
        function (data) {
            $('#distributor').typeahead({
                source: data,
                items: 10,
                showHintOnFocus: 'all',
                displayText: function (item) {
                    return item.customer_name;
                },
                afterSelect: function (item) {
                  $('#distributor').val(item.customer_name);
                  $('#dist_id').val(item.id);
                  $('#change-dist').show();
                  $('#distributor').attr('readonly','readonly');
                }
            });
          }, 'json');
      $('#change-dist').click(function(){
          $(this).hide();
          $('#distributor').removeAttr('readonly').val('');
          $('#dist_id').val('');
      });
      var path = window.Laravel.url+"/ajax/typeaheadCity";
      $.get(path,
          function (data) {
              $('#city').typeahead({
                  source: data,
                  items: 10,
                  showHintOnFocus: 'all',
                  displayText: function (item) {
                      return item.name;
                  },
                  afterSelect: function (item) {
                    $('#city').val(item.name);
                    $('#city_id').val(item.id);
                    $('#change-city').show();
                    $('#city').attr('readonly','readonly');
                  }
              });
            }, 'json');
            $('#change-city').click(function(){
                $(this).hide();
                $('#city').removeAttr('readonly').val('');
                $('#city_id').val('');
            });
  });
</script>
@endsection
