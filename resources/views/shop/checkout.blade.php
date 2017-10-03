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
  <form class="form-horizontal" enctype="multipart/form-data" action="{{route('product.postOrder')}}" method="post">
  {{ csrf_field() }}
  <div class="row">
        <div class="col-sm-8 col-md-8 col-md-offset-2 col-sm-offset-2">
          <div class="form-group{{ $errors->has('dist') ? ' has-error' : '' }}">
            <label class="control-label col-sm-3" for="no_order">Distributor :</label>
            <div class="col-sm-8">
              <input type="text" id="distributor" name="dist" class="form-control" placeholder="Distributor" required readonly="readonly" value="{{$distributor['customer_name']}}">
            </div>
          </div>
            <div class="form-group{{ $errors->has('no_order') ? ' has-error' : '' }}">
              <label class="control-label col-sm-3" for="no_order">@lang('shop.Po_num') :</label>
              <div class="col-sm-8">
                <input type="text" id="no_order" name="no_order" class="form-control" placeholder="@lang('shop.Po_num')" value="{{old('no_order')}}" required>
                @if ($errors->has('no_order'))
                    <span class="help-block with-errors">
                        <strong>{{ $errors->first('no_order') }}</strong>
                    </span>
                @endif
              </div>
            </div>
            <div class="form-group{{ $errors->has('alamat') ? ' has-error' : '' }}">
              <label class="control-label col-sm-3" for="Alamat">@lang('shop.ShipTo') :</label>
              <div class="col-sm-8">
                    <select name="alamat" id="alamat" class="form-control">
                      @foreach($addresses as $address)
                      <option value="{{$address->id}}" {{old('alamat')==$address->id?"selected=selected":""}}>{{$address->address1}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('alamat'))
                        <span class="help-block with-errors">
                            <strong>{{ $errors->first('alamat') }}</strong>
                        </span>
                    @endif

              </div>
            </div>
            @if(!is_null($billto))
            <div class="form-group {{ $errors->has('billto') ? ' has-error' : '' }}">
              <label class="control-label col-sm-3" for="Alamat">@lang('shop.BillTo') :</label>
              <div class="col-sm-8">
                    <select name="billto" id="billto" class="form-control">
                      @foreach($billto as $bt)
                      <option value="{{$bt->id}}" {{old('billto')==$bt->id ?"selected=selected":""}}>{{$bt->address1}}</option>
                      @endforeach
                    </select>
                    @if ($errors->has('billto'))
      									<span class="help-block with-errors">
      											<strong>{{ $errors->first('billto') }}</strong>
      									</span>
      							@endif
              </div>
            </div>
            @endif
            <div class="form-group {{ $errors->has('filepo') ? ' has-error' : '' }}">
              <label class="control-label col-sm-3" for="filepo">@lang('shop.documentPO') :</label>
              <div class="col-sm-8">
                  <input type="file" accept="application/pdf" name="filepo" id="filepo" >
                  @if ($errors->has('filepo'))
    									<span class="help-block with-errors">
    											<strong>{{ $errors->first('filepo') }}</strong>
    									</span>
    							@endif
              </div>
            </div>

        </div>
      </div>
  <div class="row">
  <div class="col-sm-8 col-md-8 col-md-offset-2 col-sm-offset-2">
    <table id="cart" class="table table-hover table-condensed">
				<thead>
				<tr>
					<th style="width:50%" class="text-center">@lang('shop.Product')</th>
					<th style="width:10%" class="text-center">@lang('shop.Price')</th>
					<th style="width:20%" class="text-center">@lang('shop.Quantity')</th>
					<th style="width:20%" class="text-center">@lang('shop.SubTotal')</th>
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
					<td data-th="@lang('shop.Price')" id="hrg-{{$id}}" class="text-center xs-only-text-left">{{ number_format($product['price'],2) }}</td>
					<td data-th="@lang('shop.Quantity')" class="text-center xs-only-text-left">
              {{ $product['qty']." ".$product['item']['satuan_secondary'] }}
					</td>
					<td data-th="@lang('shop.SubTotal')" class="text-center xs-only-text-left" id="subtot-{{$id}}">{{  number_format($product['amount'],2) }}</td>
				</tr>
          @endforeach
			</tbody>
			<tfoot>
				<tr class="visible-xs">
					<td class="text-center" ><strong class="totprice" id="totprice1">Total {{number_format($totalPrice,2)}}</strong></td>
				</tr>
				<tr>
					<td><a href="{{route('product.shoppingCart') }}" class="btn btn-warning" style="min-width:200px;"><i class="fa fa-angle-left"></i> @lang('shop.back')</a></td>
					<td colspan="1" class="hidden-xs"></td>
					<td class="hidden-xs text-center"><strong id="totprice2">Total {{ number_format($totalPrice,2) }}</strong></td>
					<td><button type="submit" class="btn btn-success btn-block">@lang('shop.CreateOrder') <i class="fa fa-angle-right"></i></button></td>
				</tr>
        <tr>
          <td class="xs text-center"colspan"*"><small>*@lang('pesan.notfixedprice')</small></td>
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
