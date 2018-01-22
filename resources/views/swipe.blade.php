@extends('layouts.navbar_product')
@section('css')
<style type="text/css">
.view-all {
  float: right;
  margin-top: -30px;
}
.row{
  margin-left: -15px;
  margin-right: -15px;
}

.thumbnail{
  margin-left: 0px;
  margin-right: 0px;
}
.thumbnail.caption{
  min-height:80px;
  margin-bottom: 10px;
}
</style>
@endsection
@section('content')
    <link href='https://fonts.googleapis.com/css?family=Lato:300,400,700,400italic,300italic' rel='stylesheet' type='text/css'>


    <!-- Owl Stylesheets -->
    <link rel="stylesheet" href="assets/owlcarousel/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="assets/owlcarousel/assets/owl.theme.default.min.css">


    <div class="container">
      @include('shop.carausel')
    </div>
    <!-- body -->
    @foreach($product_flexfields as $flexfield)
    @php ($price=0)
    @php ($disc=0)
    <div class="container">
      <br>
      <h4><strong>{{$flexfield->description}}</strong></h4>
      @if($flexfield->products->count()>10)
      <p class="view-all"><a href="{{route('product.category',$flexfield->flex_value)}}">Lihat Semua &nbsp;</a><i class="fa fa-angle-right" aria-hidden="true"></i></p>
      @endif
      <legend></legend>
      <div class="row">
        <div class="large-12 columns">
          <div class="owl-carousel owl-theme owl-loaded owl-drag">
            @foreach($flexfield->products->take(10) as $product)
            <div class="item">
              <div class="thumbnail">
                @if($product->imagePath)
                  <img data-src="holder.js/100%x180" alt="No Image"  class="img product" style="height:80px;" src="{{ asset('img/'.$product->imagePath) }}" data-holder-rendered="true">
                @else
                  <img data-src="holder.js/100%x180" alt="No Image" class="img product" style="height:80px;" src="" data-holder-rendered="true">
                @endif
                <legend></legend>
                <div class="caption">
                  <h4 style="text-align: center; margin-top: -2px; margin-bottom: -5px;">
                    <a href="{{ route('product.detail',['id'=>$product->id])}}">{{$product->title}}</a>
                  </h4>
                </div>
                <div class="boxprice">
                @if(Auth::check())
                  <div class="input-group input-group-sm" style="width: 100%; margin-left: 8px;">
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
                  <div  id="lblhrg-{{$product->id}}" class="clearfix price">
                    @if($product->item=="43")
                      @php ($currency="$ ")
                    @else
                      @php ($currency="Rp. ")
                    @endif
                    @if(Auth::user()->hasRole('Distributor'))
                      {{$currency.number_format($product->price_diskon,2)}}/{{$product->satuan_secondary}}
                    @else
                      {{$currency.number_format($product->price_diskon,2)}}/{{$product->satuan_primary}}
                    @endif
                  </div>
                  <div class="price coret" id="hrgcoret-{{$product->id}}">
                      @if($product->harga!=$product->price_diskon)
                        {{$currency.number_format($product->harga,2)}}
                      @endif
                  </div>
                @endif
                </div>
                  @if(Auth::check())
                  <input type="hidden" id="hrg-{{$product->id}}" value="{{$product->harga}}">
                  <input type="hidden" id="disc-{{$product->id}}" value="{{$product->price_diskon}}">
                  <div class ="clearfix" id="addCart-{{$product->id}}">
                    <a onclick="addCart('{{$product->id}}');return false;" href="#" class="btn btn-success btn-block"  role="button">@lang('shop.AddToCart')</a>
                  </div>
                  <div class="info-product" id="info-product-{{$product->id}}">
          					@if(Auth::user()->hasRole('Distributor'))
          						@php ($uom= $product->satuan_secondary)
          						1{{$product->satuan_secondary." = ".(float)$product->rate."/".$product->satuan_primary}}<br>
          					@endif
          				</div>
                  <div class="info-product" id="info-product2-{{$product->id}}" style="color:blue;font-weight:bold;text-align:center">
                    @if($product->promo)
                    @if($product->promo->product_attr_value==$product->promo->item_id)
                    {{"Buy ".$product->promo->pricing_attr_value_from."+".$product->promo->benefit_qty." ".$product->promo->benefit_uom_code}}
                    @endif
                    @endif
                  </div>
                  @endif
              </div>
            </div>
            @endforeach

          </div>
        </div>
      </div>

    </div>
    @endforeach
@endsection
@section('js')
<script src="assets/vendors/jquery.min.js"></script>
<script src="assets/owlcarousel/owl.carousel.js"></script>
<script src="{{ asset('js/myproduct.js') }}"></script>
    <script>
      var owl = $('.owl-carousel');
      owl.owlCarousel({
        margin: 10,
        loop: false,
        nav: false,
        responsive: {
          0: {
            items: 1
          },
          320:{
            items:2
          },
          480: {
            items: 3
          },
          720: {
            items: 4
          },
          1000: {
            items: 5
          }
        }
      })
    </script>
@endsection
