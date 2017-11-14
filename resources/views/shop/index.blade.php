@extends('layouts.navbar_product')

@section('content')
<link rel="stylesheet" href="{{ URL::to('css/bootstrap_ms.css') }}">
<div class="container">
@include('shop.carausel')
</div>
<div class="container">
     {{csrf_field()}}
     <input type="hidden" id="baseurl" value="{{url('/')}}">
     @if(isset($nama_kategori))
     <div><h2>Category Product: {{$nama_kategori}}</h2></div>
     @endif
  @forelse($products->chunk(6) as $productChunk)
    <div class="row display-flex">
      @foreach($productChunk as $product)
        <div class="col-xs-6 col-ms-4 col-sm-4 col-md-2" >
          <div class="thumbnail">
            <a href="{{ route('product.detail',['id'=>$product->id])}}" title="{{ $product->title }}">
            @if($product->imagePath)
              <img data-src="holder.js/100%x180" alt="100%x180"  class="img product" src="{{ asset('img/'.$product->imagePath) }}" data-holder-rendered="true">
            @else
              <img data-src="holder.js/100%x180" alt="100%x180" class="img product" src="" data-holder-rendered="true">
            @endif
            </a>
            <div class="caption">
              <h4><a href="{{ route('product.detail',['id'=>$product->id])}}">{{ $product->title }}</a></h4>
              @if(Auth::check())
                <div class="input-group" style="width:100%">
                    <input type="text" class="form-control " id="qty-{{$product->id}}" name="qty" value=1 size="2" style="text-align:right;width:50%">
                    <select name="satuan" class="form-control" style="width:40% " id="satuan-{{$product->id}}" onChange="getPrice('{{$product->id}}')">
                      <option value="Box">Box</option>
                      <option value="Pcs">Pcs</option>
                    </select>
                </div>
                <div class="clearfix price" >Rp. 0</div>
                <div class ="clearfix" id="addCart-{{$product->id}}">
                  <a onclick="addCart('{{$product->id}}');return false;" href="#" class="btn btn-success pull-right" role="button">Add to cart</a>
                </div>
              @endif
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @empty
      <div class="row"><h2 align="center">No Product</h2></div>
  @endforelse
</div>
<div>


</div>
@endsection
@section('js')
<script src="{{ asset('js/myproduct.js') }}"></script>
@endsection
