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
        {{$status}}
    </div>
  @endif

  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading"><strong>Transaction</strong></div>
          <div class="panel-body" style="overflow-x:auto;">
            <div id="tabs" class="simple-tabs">
              <ul>
                <li><a href="#trx-in">Transaction In</a></li>
                <li><a href="#trx-out">Transaction Out</a></li>
              </ul>
              <!-- Transaction In -->
              <div id="trx-in"> 
                <div class="form-wrapper">
                  {!! Form::open(['url' => '/outlet/transaction/in/process']) !!}
                    <div class="form-group">
                      <div class="container-fluid">
                        <div class="row">
                          <div class="col-md-2">
                            <div class="form-label">
                              <label for="trx-in-date">Transaction Date</label>
                            </div>
                          </div>
                          <div class="col-md-4">
                            {{ Form::text('trx_in_date', date('d M Y'), array('class'=>'form-control','autocomplete'=>'off', 'id'=>'trx-in-date')) }}
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="container-fluid">
                        <div class="row">
                          <div class="col-md-2">
                            <div class="form-label">
                              <label for="product-name-in">Product</label>
                            </div>
                          </div>
                          <div class="col-md-4">
                            {{ Form::text('product_name_in', '', array('class'=>'form-control','autocomplete'=>'off', 'data-provide'=>'typeahead', 'id'=>'product-name-in')) }}
                            {{ Form::hidden('product_code_in', '', array('id'=>'product-code-in')) }}
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="container-fluid">
                        <div class="row">
                          <div class="col-md-2">
                            <div class="form-label">
                              <label for="qty-in">Quantity</label>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="input-prepend input-group">
                              {{ Form::number('qty_in', '', array('class'=>'form-control','autocomplete'=>'off', 'id'=>'qty-in', 'min'=>1)) }}
                              <span class="add-on input-group-addon" id="unit-sell-in"></span>
                            </div>
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
                          </div>
                        </div>
                      </div>
                    </div>
                  {{ Form::close() }}
                </div>
              </div>
              <!-- Transaction Out -->
              <div id="trx-out"> 
                <div class="form-wrapper">
                  {!! Form::open(['url' => '/outlet/transaction/out/process']) !!}
                    <div class="form-group">
                      <div class="container-fluid">
                        <div class="row">
                          <div class="col-md-2">
                            <div class="form-label">
                              <label for="trx-out-date">Transaction Date</label>
                            </div>
                          </div>
                          <div class="col-md-4">
                            {{ Form::text('trx_out_date', date('d M Y'), array('class'=>'form-control','autocomplete'=>'off', 'id'=>'trx-out-date')) }}
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="container-fluid">
                        <div class="row">
                          <div class="col-md-2">
                            <div class="form-label">
                              <label for="product-name-out">Product</label>
                            </div>
                          </div>
                          <div class="col-md-4">
                            {{ Form::text('product_name_out', '', array('class'=>'form-control','autocomplete'=>'off', 'data-provide'=>'typeahead', 'id'=>'product-name-out')) }}
                            {{ Form::hidden('product_code_out', '', array('id'=>'product-code-out')) }}
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="container-fluid">
                        <div class="row">
                          <div class="col-md-2">
                            <div class="form-label">
                              <label for="qty-out">Quantity</label>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="input-prepend input-group">
                              {{ Form::number('qty_out', '', array('class'=>'form-control','autocomplete'=>'off', 'id'=>'qty-out', 'min'=>1)) }}
                              <span class="add-on input-group-addon" id="unit-sell-out"></span>
                            </div>
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
  </div>
@endsection
@section('js')

<script src="{{ asset('js/moment-with-locales.js') }}"></script>
<script src="{{ asset('js/bootstrap-datetimepicker.min.js') }}"></script>
<script src="{{ asset('js/bootstrap3-typeahead.min.js') }}"></script>
<script src="{{ asset('js/ui/1.12.1/jquery-ui.js') }}"></script>
<script src="{{ asset('js/outletproduct.js') }}"></script>

@endsection
