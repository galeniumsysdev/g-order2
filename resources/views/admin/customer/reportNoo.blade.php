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
                      <input type="text" data-provide="typeahead" autocomplete="off"  class="form-control mb-8 mr-sm-8 mb-sm-4" name="name" id="name" value="{{$request->name}}" >
                    </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-sm-2" for="role" style="margin-left:15px;"><strong>Area :</strong></label>
                    <div class="col-sm-8" style="margin-left:15px; margin-right:15px; margin-top:10px;">
                      <select class="form-control" name="role" id="role">
                        <option value="">--</option>
                        @foreach($regencies as $city)
                            <option value="{{$city->id}}">{{$city->name}}</option>
                        @endforeach-->
                      </select>
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
<script src="{{ asset('js/moment-with-locales.js') }}"></script>
<script src="{{ asset('js/bootstrap3-typeahead.min.js') }}"></script>
<script src="{{ asset('js/ui/1.12.1/jquery-ui.js') }}"></script>
<script src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
@endsection
