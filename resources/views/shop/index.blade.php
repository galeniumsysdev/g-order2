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
            @if($product->imagePath)
              <img data-src="holder.js/100%x180" alt="100%x180"  class="img product" src="{{ asset('img/'.$product->imagePath) }}" data-holder-rendered="true">
            @else
              <img data-src="holder.js/100%x180" alt="100%x180" class="img product" src="" data-holder-rendered="true">
            @endif
            <div class="caption">
              <h4><a href="{{ route('product.detail',['id'=>$product->id])}}">{{ $product->title }}</a></h4>            
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
