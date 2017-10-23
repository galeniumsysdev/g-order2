<!-- 
/**
* created by WK Productions
*/ 
-->
@extends('layouts.navbar_product')
@section('content')
  <link href="{{ asset('css/table.css') }}" rel="stylesheet">
  <link href="{{ asset('css/dpl.css') }}" rel="stylesheet">
  @if($status= Session::get('msg'))
    <div class="alert alert-info">
        {{$status}}
    </div>
  @endif

  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading"><strong>Stock Trx Detail</strong></div>
          <div class="panel-body" style="overflow-x:auto;">
            <h4>
              Product Name : {{ $title }}<br/>
              Stock : {{ $last_stock }}
            </h4>
            <br/>
            <table id="detail-list" class="table table-hover table-center-header">
              <thead>
                <tr>
                  <th>Type</th>
                  <th>Qty</th>
                  <th>Trx. Date</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($stock as $cell)
                  <tr class="{{ $cell['class'] }}">
                    <td>{!! $cell['event'] !!}</td>
                    <td align="center">{!! $cell['qty'] !!}</td>
                    <td align="center">{{ $cell['trx_date'] }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
            <a href="/outlet/product/list" class="btn btn-default">Back</a>
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
