@extends('layouts.navbar_product')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/docs.theme.min.css') }}">

<!-- Owl Stylesheets -->
<link rel="stylesheet" href="{{ asset('assets/owlcarousel/assets/owl.carousel.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/owlcarousel/assets/owl.theme.default.min.css') }}">
<div class="container">
  @include('shop.carausel')
</div>

<div class="row">
	<div class="large-12 columns">
		<br>
		<p>Produk Categori 1</p>
		<p class="view-produk">Lihat Semua</p>
		<hr>

		<!--Categori 1-->
		<div class="owl-carousel owl-theme">
			<div class="carousel">
			<div class="item">
			<img src="http://www.g-store.id/data/script/backend/upload/upload/produk/glumunos.jpg">
			<p class="title-produk">Glimunos Syr 30 ml</p>
				<div class="item-price">
				<p class="price"><strong>Harga Rp. 50.200</strong></p>
				</div>
			</div>
				<div class="input-price" class="col-xs-2">
				<input type="text" placeholder="1">
				<select class="list">
					<option>Box</option>
					<option>Pcs</option>
				</select>
				</div>
				<button type="button" class="btn btn-primary" style="width:100%; height:30px; padding-top:5px; border-radius:5px;">Buy</button>
			</div>

			<div class="carousel">
			<div class="item">
			<img src="http://www.g-store.id/data/script/backend/upload/upload/produk/glumunos_30ml.png">
			<p class="title-produk">Glimunos Syr 60 ml</p>
				<div class="item-price">
				<p class="price"><strong>Harga Rp. 27.800</strong></p>
				</div>
			</div>
				<div class="input-price" class="col-xs-2">
				<input type="text" placeholder="1">
				<select class="list">
					<option>Box</option>
					<option>Pcs</option>
				</select>
				</div>
				<button type="button" class="btn btn-primary" style="width:100%; height:30px; padding-top:5px; border-radius:5px;">Buy</button>
			</div>

			<div class="carousel">
			<div class="item">
			<img src="http://www.g-store.id/data/script/backend/upload/upload/produk/glumunos_kaplet.png">
			<p class="title-produk">Glimunos Syr 60 ml</p>
				<div class="item-price">
				<p class="price"><strong>Harga Rp. 27.800</strong></p>
				</div>
			</div>
				<div class="input-price" class="col-xs-2">
				<input type="text" placeholder="1">
				<select class="list">
					<option>Box</option>
					<option>Pcs</option>
				</select>
				</div>
				<button type="button" class="btn btn-primary" style="width:100%; height:30px; padding-top:5px; border-radius:5px;">Buy</button>
			</div>

			<div class="carousel">
			<div class="item">
			<img src="http://www.g-store.id/data/script/backend/upload/upload/produk/haemogal.png">
			<p class="title-produk">Glimunos Syr 60 ml</p>
				<div class="item-price">
				<p class="price"><strong>Harga Rp. 27.800</strong></p>
				</div>
			</div>
				<div class="input-price" class="col-xs-2">
				<input type="text" placeholder="1">
				<select class="list">
					<option>Box</option>
					<option>Pcs</option>
				</select>
				</div>
				<button type="button" class="btn btn-primary" style="width:100%; height:30px; padding-top:5px; border-radius:5px;">Buy</button>
			</div>

			<div class="carousel">
			<div class="item">
			<img src="http://g-store.id/data/script/backend/upload/upload/produk/jovial.png">
			<p class="title-produk">V-mina Feminine Hygiene Cleansing Mousse</p>
				<div class="item-price">
				<p class="price"><strong>Harga Rp. 27.800</strong></p>
				</div>
			</div>
				<div class="input-price" class="col-xs-2">
				<input type="text" placeholder="1">
				<select class="list">
					<option>Box</option>
					<option>Pcs</option>
				</select>
				</div>
				<button type="button" class="btn btn-primary" style="width:100%; height:30px; padding-top:5px; border-radius:5px;">Buy</button>
			</div>
		</div>
	</div>
</div>

@endsection
@section('js')
<script>
$(document).ready(function() {
$('.owl-carousel').owlCarousel({
loop: true,
margin: 10,
responsiveClass: true,
responsive: {
0: {
items: 2,
nav: true,
loop: false,
},
600: {
items: 3,
nav: false,
loop: false,
},
1000: {
items: 5,
nav: true,
loop: false,
margin: 20
}
}
})
})
</script>
	<!-- vendors -->
	<script src="{{ asset('assets/vendors/highlight.js') }}"></script>
	<script src="{{ asset('assets/js/app.js') }}"></script>
	<script src="{{ asset('assets/vendors/jquery.min.js') }}"></script>
	<script src="{{ asset('assets/owlcarousel/owl.carousel.js') }}"></script>
@endsection
