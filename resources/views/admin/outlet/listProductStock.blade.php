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
          <div class="panel-heading"><strong>Product List</strong></div>
          <div class="panel-body" style="overflow-x:auto;">
            <table id="product-list" class="table table-striped table-hover table-center-header">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Title</th>
                  <th>Stok</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($stock as $cell)
                  <tr>
                    <td>{{ $cell['op_id'] }}</td>
                    <td>{{ $cell['title'] }}</td>
                    <td>{{ number_format($cell['product_qty'],0,',','.') }}</td>
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
