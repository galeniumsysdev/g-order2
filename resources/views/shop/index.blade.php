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
                <div class="input-group input-group-sm" style="width:100%">
                    <input type="text" class="form-control" id="qty-{{$product->id}}" name="qty" value=1 size="2" style="text-align:right;width:50%">
                    <select name="satuan" class="form-control" style="width:40% " id="satuan-{{$product->id}}" onChange="getPrice('{{$product->id}}')">
                      @foreach($product->uom as $satuan)
                        @if(Auth::user()->hasRole('Distributor'))
                          <option value="{{$satuan->uom_code}}" {{$product->satuan_secondary==$satuan->uom_code?'selected':''}}>{{$satuan->uom_code}}</option>
                        @else
                          <option value="{{$satuan->uom_code}}" {{$product->satuan_primary==$satuan->uom_code?'selected':''}}>{{$satuan->uom_code}}</option>
                        @endif
                      @endforeach
                    </select>
                </div>
                <div class="clearfix price"  id="lblhrg-{{$product->id}}">
                  @if($product->item=="43")
                    $  {{number_format($product->price_diskon,2)}}/{{$product->satuan_secondary}}
                  @else
                    Rp. {{number_format($product->price_diskon,2)}}/{{$product->satuan_secondary}}
                  @endif
                  @if((float)$product->rate!=1)
                  ({{(float)$product->rate." ".$product->satuan_primary}})
                  @endif
                </div>
                @if($product->harga!=$product->price_diskon)
                <div class="price coret" id="hrgcoret-{{$product->id}}">
                  @if($product->item=="43")
                    $  {{number_format($product->harga,2)}}
                  @else
                    Rp  {{number_format($product->harga,2)}}
                  @endif
                </div>
                @endif
                <input type="hidden" id="hrg-{{$product->id}}" value="{{$product->harga}}">
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
