@extends('layouts.tempAdminSB')
@section('content')
@if($status= Session::get('message'))
  <div class="alert alert-info">
      {{$status}}
  </div>
@endif
<h3>Product</h3>
  <form action="{{route('product.update',$product->id)}}" enctype="multipart/form-data" method="post" role="form">
       {{csrf_field()}}

     <div class="form-group">
       <label for="name">Kode</label>
       <input type="text" class="form-control" name="kode" id="" value="{{ $product->inventory_item_id }}">
     </div>
     <div class="form-group">
       <label for="display_name">Nama</label>
       <input type="text" class="form-control" name="nama" id="" value="{{$product->title}}">
     </div>
     <div class="form-group">
       <label for="description">Price</label>
       <input type="text" class="form-control" name="harga" size="5" id="" value="{{$product->price}}">
     </div>
     <div class="form-group">
       <label for="description">Satuan</label>
       <input type="text" class="form-control" name="satuan" size="5" id="" value="{{$product->satuan_primary}}" readonly="readonly">
     </div>
     <div class="form-group">
       <label for="description">Description (English)</label>
       <textarea class="form-control" id="en_descr" name="en_descr">{{$product->description_en}}</textarea>
     </div>
     <div class="form-group">
       <label for="messageArea">Description (Indonesia)</label>
       <textarea class="form-control" id="id_descr" name="id_descr">{{$product->description}}</textarea>
     </div>
     <div class="form-group">
         <label for="imageInput">File input</label>
         <input data-preview="#preview" accept="image/*" name="input_img" type="file" id="imageInput">
         @if ($errors->has('input_img'))
             <span class="help-block">
                 <strong>{{ $errors->first('input_img') }}</strong>
             </span>
         @endif
    </div>
           <div class="thumbnail">
           <img id="profile-img-tag" data-src="holder.js/100%x180" alt="100%x180" style="height: 180px; width: 180px; display: block;" src="{{ asset($product->imagePath) }}" data-holder-rendered="true">
           </div>

     <input type="hidden" name="itemid" value="">
     <button type="submit" class="btn btn-primary">Save Product</button>
   </form>
@endsection
@section('js')
   <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
   <script src="{{ asset('vendor/unisharp/laravel-ckeditor/ckeditor.js') }}"></script>
	<script>
	    CKEDITOR.replace( 'id_descr' );
      CKEDITOR.replace( 'en_descr' );
      function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#profile-img-tag').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
      }

    $("#imageInput").change(function(){
        readURL(this);
    });
	</script>
@endsection
