@extends('layouts.tempAdminSB')
@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Product</h2>
        </div>
    </div>
</div>
@if ($message = Session::get('success'))
  <div class="alert alert-success">
    <p>{{ $message }}</p>
  </div>
@endif
<table class="table" id="table">
  <thead>
  <tr>
    <th>Code</th>
    <th>Name</th>
    <th>Satuan</th>
    <th>Price</th>
    <th>Action</th>
  </tr>
</thead>
<tbody>
  @forelse($products as $product)
  <tr>
      <td>{{$product->inventory_item_id}}</td>
      <td>{{$product->title}}</td>
      <td>{{$product->satuan_primary}}</td>
      <td>{{$product->price}}</td>
      <td><a class="btn btn-info btn-sm" href="{{route('product.master',$product->id)}}">Edit</a></td>
  </tr>
  @empty
  <tr><td colspan="4">No Product</td></tr>
  @endforelse
</tbody>
</table>

@endsection
@section('js')
<!--<script src="//code.jquery.com/jquery-1.12.3.js"></script>-->
<script src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<script
    src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
<!--<link rel="stylesheet"
    href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">-->
<link rel="stylesheet"
    href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css">
    <script>
      $(document).ready(function() {
        $('#table').DataTable({
          'columnDefs' : [     // see https://datatables.net/reference/option/columns.searchable
                            {
                               'searchable'    : true,
                               'targets'       : [0,1,2,3]
                            },
                        ]
        });
    } );
     </script>
@endsection
