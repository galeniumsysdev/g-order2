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
          <div class="panel-heading"><strong>Transaction List</strong></div>
          <div class="panel-body" style="overflow-x:auto;">
            <table id="trx-list" class="table table-hover table-center-header">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Product Name</th>
                  <th>Type</th>
                  <th>Qty</th>
                  <th>Tgl. Trx</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($data as $key=>$trx)
                    <tr class="{{ $trx['class'] }}">
                      <td>{{ $key+1 }}</td>
                      <td>{{ $trx['title'] }}</td>
                      <td align="center">{!! $trx['event'] !!}</td>
                      <td align="center">{!! $trx['qty'] !!}</td>
                      <td align="center">{{ $trx['trx_date'] }}</td>
                    </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
@section('js')

<script src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<script
    src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
<link rel="stylesheet"
    href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css">
<script src="{{ asset('js/outletproduct.js') }}"></script>

@endsection
