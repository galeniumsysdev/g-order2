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
            <a href="{{ route('outlet.formProduct') }}" class="btn btn-default">Add Product</a>
            <a href="{{ route('outlet.importProduct') }}" class="btn btn-default">Import Product</a>
            <a href="{{ route('outlet.importProductStock') }}" class="btn btn-info pull-right">Import Stock</a>
            <br/><br/>
            <div class="table-responsive">
              <table id="product-list" class="display responsive nowrap" width="100%">
                <thead>
                  <tr>
                    <th>Title</th>
                    <th>Stok</th>
                    <th>ID</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($stock as $cell)
                    <tr>
                      <td>{{ $cell['title'] }}</td>
                      <td>{!! $cell['stock'] !!}</td>
                      <td>{{ $cell['op_id'] }}</td>
                      <td align="center">
                        @if ($cell['flag'] == 'outlet')
                        <a href="{{ route('outlet.formProduct',$cell['op_id']) }}" class="btn btn-primary">Edit</a>
                        <a href="{{ route('outlet.deleteProduct',$cell['op_id']) }}" class="btn btn-danger" onclick="if(!confirm('Are you sure want to delete \'{{ $cell['title'] }}\' and its histories?')){return false;}">Delete</a>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
@section('js')

<script src="//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
<script src="//cdn.datatables.net/responsive/2.2.0/js/dataTables.responsive.min.js"></script>
<link rel="stylesheet" href="//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="//cdn.datatables.net/responsive/2.2.0/css/responsive.dataTables.min.css">
<script src="{{ asset('js/outletproduct.js') }}"></script>

@endsection
