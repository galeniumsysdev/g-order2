@extends('layouts.navbar_product')
@section('content')
<link href="{{ asset('css/detail.css') }}" rel="stylesheet">
<input type="hidden" id="baseurl" value="{{url('/')}}">
<div class="container">
		<div class="card">
			<div class="container-fliud">
				<div class="wrapper row">
					<div class="preview col-md-6">
						<div class="preview-pic tab-content">
						  @if(isset($product->imagePath))
								<img src="{{ asset('img//'.$product->imagePath) }}" />
							@endif
						</div>
					</div>
					<div class="details col-md-6">
						<h3 class="product-title">{{$product->title}}</h3>
						<!--<div class="rating">
							<div class="stars">
								<span class="fa fa-star checked"></span>
								<span class="fa fa-star checked"></span>
								<span class="fa fa-star checked"></span>
								<span class="fa fa-star"></span>
								<span class="fa fa-star"></span>
							</div>
							<span class="review-no">41 reviews</span>
						</div>-->
						<p class="product-description">
							@if(config('app.locale')=="en")
								{!!html_entity_decode($product->description_en)!!}
							@else
								{!!html_entity_decode($product->description)!!}
							@endif
						</p>
						<h4 class="price">Price: <span id="lblhrg-{{$product->id}}">Rp. {{ number_format($product->harga,2) }}/{{$product->satuan_secondary}}</span></h4>
						@if (!Auth::guest())
						<input type="hidden" id="hrg-{{$product->id}}" value="{{$product->harga}}">
						<div class="input-group" style="width:100%">
							<span class="input-group-addon" id="basic-addon1">Qty</span>
							<input type="text" name="qty" id="qty-{{$product->id}}" value="1" size="2" class="form-control" style="text-align:right;width:40%" />
							<select name="satuan" id="satuan-{{$product->id}}" class="form-control" style="width:40%" onChange="getPrice('{{$product->id}}')">
								@foreach($product->uom as $satuan)
									<option value="{{$satuan->uom_code}}" {{$product->satuan_secondary==$satuan->uom_code?'selected':''}}>{{$satuan->uom_code}}</option>
								@endforeach
								<!--<option id="satuan1">{{$product->satuan_secondary}}</option>
								<option id="satuan2">{{$product->satuan_primary}}</option>-->
							</select>
						</div>
            <div class ="clearfix">
              <a href="" onclick="addCart('{{$product->id}}');return false;" class="btn btn-success pull-right" role="button">Add to cart</a>
            </div>
						@endif
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
@section('js')
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script src="//codeorigin.jquery.com/ui/1.10.2/jquery-ui.min.js"></script>
<script src="{{ asset('js/myproduct.js') }}"></script>
<script type="text/javascript">
$(function()
{
	 $( "#alamat" ).autocomplete({
	  source: baseurl+"/ajax/shiptoaddr",
	  minLength: 1,
	  select: function(event, ui) {
	  	$('#alamat').val(ui.item.address1);
	  }
	});
});
</script>
@endsection
