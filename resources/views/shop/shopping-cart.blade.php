@extends('layouts.navbar_product')
@section('content')
  <link href="{{ asset('css/table.css') }}" rel="stylesheet">
  @if($status= Session::get('msg'))
    <div class="alert alert-info">
        {{$status}}
    </div>
  @endif
<!--<link rel="stylesheet"href="//codeorigin.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" />-->
  @if(Session::has('cart'))
  <div class="row">
        <div class="col-sm-8 col-md-8 col-md-offset-2 col-sm-offset-2">
          <div class="form-group">
            <label class="control-label col-sm-3" for="no_order">Distributor :</label>
            <div class="col-sm-8">
              <input type="text" id="distributor" name="dist" class="form-control" placeholder="Distributor" required readonly="readonly" value="{{$distributor['customer_name']}}">
            </div>
          </div>
        </div>
  </div>
  <div class="row">
    <div class="col-sm-8 col-md-8 col-md-offset-2 col-sm-offset-2">
      <table id="cart" class="table table-hover table-condensed">
  				<thead>
  				<tr>
  					<th style="width:40%" class="text-center">@lang('shop.Product')</th>
  					<th style="width:10%" class="text-center">@lang('shop.Price')</th>
  					<th style="width:20%" class="text-center">@lang('shop.Quantity')</th>
  					<th style="width:20%" class="text-center">@lang('shop.SubTotal')</th>
  					<th style="width:10%"></th>
  				</tr>
  			</thead>
  			<tbody>
          @foreach($products as $product)
          @php ($id  = $product['item']['id']."-".$product['uom'])
  				<tr>
  					<td data-th="@lang('shop.Product')">
  						<div class="row">
  							<div class="col-sm-2 hidden-xs"><img src="{{ asset('img//'.$product['item']['imagePath']) }}" alt="..." class="img-responsive"/></div>
  							<div class="col-sm-10">
                  <input type="hidden" value="{{$product['item']['id']}}" id="id-{{$product['item']['id']}}">
  								<h4 >{{ $product['item']['title'] }}</h4>
  							</div>
  						</div>
  					</td>
  					<td data-th="@lang('shop.Price')" id="hrg-{{$id}}">{{ number_format($product['price'],2) }}</td>
  					<td data-th="@lang('shop.Quantity')">
              <div class="input-group">
                <input type="number" name="qty-{{$id}}" id="qty-{{$id}}" class="form-control text-center" value="{{ $product['qty'] }}" style="min-width:80px;">

                <span class="input-group-btn">
                  <select class="form-control" name="stn-{{$id}}" id="stn-{{$id}}" style="width:80px;">
                     <!--<option {{$product['uom']==$product['item']['satuan_secondary']?'selected=selected':''}}>{{$product['item']['satuan_secondary']}}</option>
                     <option {{$product['uom']==$product['item']['satuan_primary']?'selected=selected':''}}>{{$product['item']['satuan_primary']}}</option>-->
                     @foreach($product['item']->uom as $satuan)
     									<option value="{{$satuan->uom_code}}" {{$product['uom']==$satuan->uom_code?'selected':''}}>{{$satuan->uom_code}}</option>
     								 @endforeach
                   </select>
                </span>
              </div>

  					</td>
  					<td data-th="@lang('shop.SubTotal')" class="text-center" id="subtot-{{$id}}">{{  number_format($product['amount'],2) }}</td>
  					<td class="actions" data-th="">
  						<button class="btn btn-info btn-sm" id="rfs-{{$id}}" onclick="return changeProduct(this.id);" ><i class="fa fa-refresh"></i></button>
  						<a href="{{route('product.removeItem',['id'=>$id]) }}" id="rmv-{{$id}}"><button class="btn btn-danger btn-sm"><i class="fa fa-trash-o"></i></button></a>
  					</td>
  				</tr>
            @endforeach
  			</tbody>
  			<tfoot>
  				<tr class="visible-xs">
  					<td class="text-center" ><strong class="totprice" id="totprice1">Total {{number_format($totalPrice,2)}}</strong></td>
  				</tr>
  				<tr>
  					<td><a href="{{route('product.index') }}" class="btn btn-warning"><i class="fa fa-angle-left"></i> @lang('shop.ContinueShopping')</a></td>
  					<td colspan="2" class="hidden-xs"></td>
  					<td class="hidden-xs text-center"><strong id="totprice2">Total {{ number_format($totalPrice,2) }}</strong></td>
  					<td><a href="{{route('product.checkOut')}}" class="btn btn-success btn-block" >@lang('shop.CheckOut') <i class="fa fa-angle-right"></i></a></td>
  				</tr>
  			</tfoot>
  		</table>
    </div>
</div>
</form>
  @else
    <div class="row">
      <div class="col-sm-6 col-md-6 col-md-offset-3 col-sm-offset-3">
        <h2>@lang('shop.NoItem')</h2>
      </div>
    </div>
  @endif
@endsection
@section('js')
<!--<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script src="//codeorigin.jquery.com/ui/1.10.2/jquery-ui.min.js"></script>
<script type="text/javascript">
$(function()
{
	 $("#alamat").autocomplete({
	  source: baseurl+"/ajax/shiptoaddr",
	  minLength: 1,
	  select: function(event, ui) {
	  	$('#alamat').val(ui.item.address1);
	  }
	});
});
</script>-->

<script src="{{ asset('js/myproduct.js') }}"></script>

@endsection
