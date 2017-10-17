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
            <table class="table table-striped table-hover table-center-header">
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
                    <td>{{ $cell['product_qty'] }}</td>
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

<script src="{{ asset('js/dpl.js') }}"></script>

@endsection
