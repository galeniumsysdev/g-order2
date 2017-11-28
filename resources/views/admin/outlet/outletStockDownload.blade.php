<!-- 
/**
* created by WK Productions
*/ 
-->
@extends('layouts.navbar_product')
@section('content')
  <link href="{{ asset('css/table.css') }}" rel="stylesheet">
  <link href="{{ asset('css/dpl.css') }}" rel="stylesheet">
  <link href="{{ asset('css/outletproduct.css') }}" rel="stylesheet">
  <link href="{{ asset('css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
  <link href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
  @if($status= Session::get('msg'))
    <div class="alert alert-info">
        {!!$status!!}
    </div>
  @endif

  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading"><strong>Download Report Stock</strong></div>
          <div class="panel-body" style="overflow-x:auto;">
            <div id="tabs" class="simple-tabs">
              <div class="form-wrapper">
                {!! Form::open(['url' => route('outlet.downloadStockProcess')]) !!}
                  <div class="form-group">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-md-2">
                          <div class="form-label">
                            <label for="trx-in-date">Tanggal</label>
                          </div>
                        </div>
                        <div class="col-md-4">
                          {{ Form::text('start_date', date('d M Y', strtotime('-1 month')), array('class'=>'form-control date-range','autocomplete'=>'off', 'id'=>'start-date', 'required'=>'required')) }}
                          {{ Form::text('end_date', date('d M Y'), array('class'=>'form-control date-range','autocomplete'=>'off', 'id'=>'end-date', 'required'=>'required')) }}
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-md-2">
                          <div class="form-label">
                            <label for="product-name-in">Nama Outlet</label>
                          </div>
                        </div>
                        <div class="col-md-4 outlet-container">
                          {{ Form::text('outlet_name', '', array('class'=>'form-control','autocomplete'=>'off', 'id'=>'outlet-name')) }}
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-md-2">
                          <div class="form-label">
                            <label for="qty-in">Provinsi</label>
                          </div>
                        </div>
                        <div class="col-md-4 province-container">
                          {{ Form::text('province', '', array('class'=>'form-control','autocomplete'=>'off', 'id'=>'province')) }}
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-md-2">
                          <div class="form-label">
                            <label for="qty-in">Area</label>
                          </div>
                        </div>
                        <div class="col-md-4 area-container">
                          {{ Form::text('area', '', array('class'=>'form-control','autocomplete'=>'off', 'id'=>'area', 'disabled'=>'disabled')) }}
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-md-2">
                          &nbsp;
                        </div>
                        <div class="col-md-4">
                          {{ Form::submit('Submit', array('class'=>'btn btn-primary')) }}
                          <a href="{{ route('outlet.listProductStock') }}" class="btn btn-default">Back</a>
                        </div>
                      </div>
                    </div>
                  </div>
                {{ Form::close() }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
@section('js')

<script src="{{ asset('js/moment-with-locales.js') }}"></script>
<script src="{{ asset('js/bootstrap-datetimepicker.min.js') }}"></script>
<script src="{{ asset('js/bootstrap3-typeahead.min.js') }}"></script>
<script src="{{ asset('js/ui/1.12.1/jquery-ui.js') }}"></script>
<script src="{{ asset('js/outletproduct.js') }}"></script>

@endsection
