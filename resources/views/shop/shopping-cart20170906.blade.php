@extends('layouts.navbar_product')
@section('content')
  @if(Session::has('cart'))
    <div class="row">
      <div class="col-sm-6 col-md-6 col-md-offset-3 col-sm-offset-3">
        <ul class="list-group">
          @foreach($products as $product)
            <li class="list-group-item">
              <span class="badge"><a href="{{route('product.removeItem',['id'=>$product['item']['id']."-".$product['uom']]) }}" title="Remove this product" style="color:#fff"><i class="glyphicon glyphicon-remove"></i></a></span>
              <strong>{{ $product['item']['title'] }}</strong>
              <span class="label label-success">{{ $product['qty']." ".$product['uom']}}</span>
              <span class="label label-success">{{ number_format($product['amount'],2) }}</span>

              <!--<div class="btn-group">
                <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown">Action
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                  <li><a href="{{route('product.reduceByOne',['id'=>$product['item']['id']]) }}">Reduce by 1</a></li>
                  <li><a href="{{route('product.removeItem',['id'=>$product['item']['id']]) }}">Reduce All</a></li>
                </ul>
              </div>-->
            </li>
          @endforeach
        </ul>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-6 col-md-6 col-md-offset-3 col-sm-offset-3">
        <strong>Total: {{$totalPrice}}</strong>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-6 col-md-6 col-md-offset-3 col-sm-offset-3">
        <form action="#" method="post">
          <div class="form-group">
            <label for="no_order">Purchase Order No.</label>
            <input type="text" id="no_order" name="no_order" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="Alamat">Alamat Pengiriman</label>
            <textarea id="alamat" name="alamat" class="form-control" required></textarea>
          </div>

        <button type="submit" class="btn btn-success">Order</button>
          {{ csrf_field() }}
      </div>
    </div>
  @else
    <div class="row">
      <div class="col-sm-6 col-md-6 col-md-offset-3 col-sm-offset-3">
        <h2>No Items in Cart</h2>
      </div>
    </div>
  @endif
@endsection
