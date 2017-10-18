<!-- 
/**
* created by WK Productions
*/ 
-->
@extends('layouts.navbar_product')
@section('content')
  <link href="{{ asset('css/table.css') }}" rel="stylesheet">
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
          <div class="panel-heading"><strong>Product List</strong></div>
          <div class="panel-body" style="overflow-x:auto;">
            <div id="tabs">
              <ul>
                <li><a href="#trx-in">Transaction In</a></li>
                <li><a href="#trx-out">Transaction Out</a></li>
              </ul>

              <div id="trx-in"> 
              {!! Form::open(['url' => '/outlet/transaction/in/process']) !!}
                <table>
                  <tr>
                    <td>Tgl. Transaksi</td>
                    <td>:</td>
                    <td>{{ Form::text('trx_in_date', date('d M Y'), array('class'=>'form-control','autocomplete'=>'off', 'id'=>'trx-in-date')) }}</td>
                  </tr>
                  <tr>
                    <td>Product</td>
                    <td>:</td>
                    <td id="product-container">
                      {{ Form::text('product_name_in', '', array('class'=>'form-control','autocomplete'=>'off', 'data-provide'=>'typeahead', 'id'=>'product-name-in')) }}
                      {{ Form::hidden('product_code_in', '', array('id'=>'product-code-in')) }}
                    </td>
                  </tr>
                  <tr>
                    <td>Qty</td>
                    <td>:</td>
                    <td id="product-container">
                      <div class="input-prepend input-group">
                        {{ Form::number('qty_in', '', array('class'=>'form-control','autocomplete'=>'off', 'id'=>'qty-in', 'min'=>1)) }}
                        <span class="add-on input-group-addon" id="unit-sell-in"></span>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="3">{{ Form::submit('Submit', array('class'=>'btn btn-primary')) }}</td>
                  </tr>
                </table>
              {{ Form::close() }}
              </div>

              <div id="trx-out"> 
              {!! Form::open(['url' => '/outlet/transaction/out/process']) !!}
                <table>
                  <tr>
                    <td>Tgl. Transaksi</td>
                    <td>:</td>
                    <td>{{ Form::text('trx_out_date', date('d M Y'), array('class'=>'form-control','autocomplete'=>'off', 'id'=>'trx-out-date')) }}</td>
                  </tr>
                  <tr>
                    <td>Product</td>
                    <td>:</td>
                    <td id="product-container">
                      {{ Form::text('product_name_out', '', array('class'=>'form-control','autocomplete'=>'off', 'data-provide'=>'typeahead', 'id'=>'product-name-out')) }}
                      {{ Form::hidden('product_code_out', '', array('id'=>'product-code-out')) }}
                    </td>
                  </tr>
                  <tr>
                    <td>Qty</td>
                    <td>:</td>
                    <td id="product-container">
                      <div class="input-prepend input-group">
                        {{ Form::number('qty_out', '', array('class'=>'form-control','autocomplete'=>'off', 'id'=>'qty-out', 'min'=>1)) }}
                        <span class="add-on input-group-addon" id="unit-sell-out"></span>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="3">{{ Form::submit('Submit', array('class'=>'btn btn-primary')) }}</td>
                  </tr>
                </table>
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
