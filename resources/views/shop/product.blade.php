@extends('layouts.navbar_product')

@section('content')
<link rel="stylesheet" href="{{ URL::to('css/bootstrap_ms.css') }}">
<link rel="stylesheet" href="{{ URL::to('css/displayproduct.css') }}">
<div class="container">
  @include('shop.carausel')
</div>
@foreach($product_flexfields as $flexfield)
<div class="container-product">
	<div class="row">
    <br>
		<h4><strong>{{$flexfield->description}}</strong></h4>
		<p class="font-product"><a href="#">Lihat Semua &nbsp;</a><i class="fa fa-angle-right" aria-hidden="true"></i></p>
		<legend></legend>
	</div>
  <div id="carousel-container">
		<div class="profile-rotater">
      @foreach($flexfield->products->take(6) as $product)
      <div class="col-md-2 col-ms-6 column productbox">
        <!--<div class="thumbnail">-->
          @if($product->imagePath)
              <img data-src="holder.js/100%x180" alt="100%x180"  class="img product" src="{{ asset('img/'.$product->imagePath) }}" data-holder-rendered="true">
            @else
              <img data-src="holder.js/100%x180" alt="100%x180" class="img product" src="" data-holder-rendered="true">
            @endif
          <div class="producttitle"><a href="{{ route('product.detail',['id'=>$product->id])}}">{{$product->title}}</a></div>
          @if (!Auth::guest())
              <div class="input-group   input-group-sm" style="width:100%">
                  <input type="text" class="form-control input-sm" name="qty" id="qty-{{$product->id}}" value=1 style="text-align:right;width:40%"  onkeypress="return isNumberKey(event)" >
                  <select name="satuan" class="form-control input-sm" style="width:50%;" id="satuan-{{$product->id}}" onChange="getPrice('{{$product->id}}')">
                    @foreach($product->uom as $satuan)
                       @php ($vrate =1)
                      <option value="{{$satuan->uom_code}}" {{$product->satuan_secondary==$satuan->uom_code?'selected':''}}>{{$satuan->uom_code}}</option>
                      @if($product->satuan_secondary==$satuan->uom_code)
                        @php ($vrate = $satuan->rate)
                      @endif
                    @endforeach

                  </select>
              </div>
              @endif
          <div class="productprice">
            <div class="pull-right"></div>
            <div class="pricetext" id="lblhrg-{{$product->id}}">
              @if(substr($product->itemcode,1,2)=="43")
                $
              @else
                Rp.
              @endif
              @if(Auth::user()->hasRole('Distributor'))
                {{number_format($product->getPrice(Auth()->user()->id,$product->satuan_secondary),2)}}/{{$product->satuan_secondary}}
              @else
                {{number_format($product->getPrice(Auth()->user()->id,$product->satuan_primary),2)}}/{{$product->satuan_primary}}
              @endif
            </div>
            <div class="price coret" id="hrgcoret-{{$product->id}}">
              @if(substr($product->itemcode,1,2)=="43")
                $
              @else
                Rp.
              @endif
              @if(Auth::user()->hasRole('Distributor'))
                {{number_format($product->getRealPrice(Auth()->user()->id,$product->satuan_secondary),2)}}/{{$product->satuan_secondary}}
              @else
                {{number_format($product->getRealPrice(Auth()->user()->id,$product->satuan_primary),2)}}/{{$product->satuan_primary}}
              @endif
            </div>
            <input type="hidden" id="hrg-{{$product->id}}" value="{{$product->harga}}">
          </div>
          <div class ="clearfix" id="addCart-{{$product->id}}">
            <!--<a href="{{ route('product.addToCart',['id'=>$product->id])}}" class="btn btn-sm btn-success center-block" role="button">Add to cart</a>-->
            <a onclick="addCart('{{$product->id}}');return false;" href="#"  class="btn btn-sm btn-success center-block" role="button">Add to cart</a>
          </div>
          <div class="info-product">
            1{{$product->satuan_secondary." = ".(float)$vrate."/".$product->satuan_primary}}<br>

          </div>
        <!--</div>-->
      </div>
      @endforeach
    </div>
  </div>
</div>
@endforeach


@endsection
@section('js')
<script src="{{ asset('js/myproduct.js') }}"></script>
@endsection
