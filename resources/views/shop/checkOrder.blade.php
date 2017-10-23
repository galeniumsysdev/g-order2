@extends('layouts.navbar_product')
@section('content')
<!--tidak ke oracle-->
  <link href="{{ asset('css/table.css') }}" rel="stylesheet">
  @if($status= Session::get('message'))
    <div class="alert alert-info">
        {{$status}}
    </div>
  @endif
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-default">
        <div class="panel-heading"><strong>Order Product</strong></div>
        <div class="panel-body" style="overflow-x:auto;">

            <div class="form-horizontal col-sm-6">

                  <br>
              <div class="form-group">
                <label for="subject" class="col-md-2 control-label">No.Trx</label>
                <div class="col-md-10 ">
                    <label class="form-class">{{$header->notrx}}&nbsp;
                      @if(isset($header->file_po))
                      <a href="{{url('/download/'.$header->file_po)}}" title="@lang('shop.download') file PO"><i class="glyphicon glyphicon-download-alt"></i></a>
                      @endif
                      @if($header->status==0)
                      &nbsp;
                      <a href="{{route('order.checkPO',$header->id)}}?print=yes" class="btn btn-primary">@lang('shop.print') PO</a>
                      @endif
                    </label>
                </div>
              </div>
              <div class="form-group">
                <label for="name" class="col-md-2 control-label">Distributor</label>
                  <div class="col-md-10" >
                    <input type="text" class="form-control" value="{{$header->distributor_name}}" readonly>
                  </div>
              </div>

              <div class="form-group">
                <label for="subject" class="col-md-2 control-label">@lang('shop.orderdate')</label>
                <div class="col-md-10">
                    <input type="text" class="form-control" value="{{date('d-M-Y',strtotime($header->tgl_order))}}" readonly>
                </div>
              </div>
              <div class="form-group">
                <label for="subject" class="col-md-2 control-label">Status</label>
                <div class="col-md-10">
                    <input type="text" class="form-control" value="{{$header->status_name}}" readonly>
                </div>
              </div>

            </div>
            <div class="form-horizontal col-sm-6">
              <div class="form-group"><br class="hidden-xs">
                <label for="name" class="col-md-2 control-label">@lang('shop.Po_num')</label>
                  <div class="col-md-10" >
                    <input type="text" class="form-control" value="{{$header->customer_po}}" readonly>
                  </div>
              </div>
              <div class="form-group">
                <label for="name" class="col-md-2 control-label">Customer</label>
                  <div class="col-md-10" >
                    <input type="text" class="form-control" value="{{$header->customer_name}}" readonly>
                  </div>
              </div>
              <div class="form-group">
                <label for="name" class="col-md-2 control-label">@lang('shop.ShipTo')</label>
                  <div class="col-md-10" >
                    <textarea class="form-control">{{$header->ship_to_addr}}</textarea>
                  </div>
              </div>
            </div>
          <br>
        @if(Auth::user()->customer_id==$header->distributor_id)
          <form class="form-horizontal" action="{{route('order.approvalSO')}}" method="post">
        @elseif(Auth::user()->customer_id==$header->customer_id)
        <form class="form-horizontal" action="{{route('order.cancelPO')}}" method="post">
        @endif
          {{ csrf_field() }}
        <input type="hidden" name="header_id" value="{{$header->id}}">
        <table id="cart" class="table table-hover table-condensed">
    				<thead>
    				<tr>
    					<th style="width:45%" class="text-center">@lang('shop.Product')</th>
    					<th style="width:10%" class="text-center">@lang('shop.Price')</th>
              <th style="width:5%" class="text-center">@lang('shop.uom')</th>
    					<th class="text-center">@lang('shop.qtyorder')</th>
              @if(Auth::user()->customer_id==$header->distributor_id)
                @if($header->status==0)
                    <th style="width:10%" class="text-center">@lang('shop.qtyavailable')</th>
                @elseif($header->status>0 and $header->status<3)
                    <th style="width:10%" class="text-center">@lang('shop.qtyship')</th>
                @elseif($header->status==3)
                    <th style="width:10%" class="text-center">@lang('shop.qtyreceive')</th>
                @endif
              @elseif(Auth::user()->customer_id==$header->customer_id)
                @if($header->status>=0)
                    <th style="width:10%" class="text-center">@lang('shop.qtyreceive')</th>
                @endif
              @endif
    					<th style="width:20%" class="text-center">@lang('shop.SubTotal')</th>
    				</tr>
    			</thead>
    			<tbody>
            @php ($totamount = 0)
            @foreach($lines as $line)
              @php ($id  = $line->line_id)
    				<tr>
    					<td >
    						<div class="row">
    							<div class="col-sm-2 hidden-xs"><img src="{{ asset('img//'.$line->imagePath) }}" alt="..." class="img-responsive"/></div>
    							<div class="col-sm-10">
    								<h4 >{{ $line->title }}</h4>
    							</div>
    						</div>
    					</td>
    					<td data-th="@lang('shop.Price')" class="xs-only-text-left text-center" >{{ number_format($line->unit_price,2) }}</td>
              <td data-th="@lang('shop.uom')" class="xs-only-text-left text-center" >{{ $line->uom }}</td>
    					<td data-th="@lang('shop.qtyorder')" class="text-center xs-only-text-left">
                  {{ $line->qty_request }}
    					</td>
              @if(Auth::user()->customer_id==$header->distributor_id)
                @if($header->status==0)
                    <td data-th="@lang('shop.qtyavailable')" class="text-center xs-only-text-left">
                      <input type="number" name="qtyshipping[{{$id}}]" id="qty-{{$id}}" class="form-control text-center" value="{{ $line->qty_request }}" style="min-width:80px;">
                    </td>
                @elseif($header->status==1)
                        <td data-th="@lang('shop.qtyship')" class="text-center xs-only-text-left">
                          <input type="number" name="qtyshipping[{{$id}}]" id="qty-{{$id}}" class="form-control text-center" value="{{ $line->qty_confirm }}" style="min-width:80px;">
                        </td>
                @elseif($header->status>1 and $header->status<3)
                    <td data-th="@lang('shop.qtyship')" class="text-center xs-only-text-left">
                      <input type="number" name="qtyshipping[{{$id}}]" id="qty-{{$id}}" class="form-control text-center" value="{{ $line->qty_shipping }}" style="min-width:80px;">
                    </td>
                @elseif($header->status==3)
                    <td data-th="@lang('shop.qtyreceive')" class="text-center xs-only-text-left">
                      {{$line->qty_accept}}
                    </td>
                @endif
              @elseif(Auth::user()->customer_id==$header->customer_id)
                @if($header->status>=0)
                    <td data-th="@lang('shop.qtyreceive')" class="text-center xs-only-text-left">
                      @if($header->status==3)
                        {{$line->qty_accept}}
                      @elseif($header->status==2)
                          <input type="number" name="qtyreceive[{{$id}}]" id="qty-{{$id}}" class="form-control text-center" value="{{ $line->qty_shipping }}" style="min-width:80px;">
                      @elseif($header->status<=1)
                        @if(is_null($line->qty_confirm))
                          <input type="number" name="qtyreceive[{{$id}}]" id="qty-{{$id}}" class="form-control text-center" value="{{ $line->qty_request }}" style="min-width:80px;">
                        @else
                          <input type="number" name="qtyreceive[{{$id}}]" id="qty-{{$id}}" class="form-control text-center" value="{{ $line->qty_confirm }}" style="min-width:80px;">
                        @endif
                      @endif
                    </td>
                @endif
              @endif
    					<td data-th="@lang('shop.SubTotal')" class="xs-only-text-left text-right">
                @if($header->status<=0)
                  {{  number_format($line->amount,2) }}
                  @php ($amount  = $line->amount)
                @elseif($header->status==3)
                  @php ($amount  = $line->qty_accept*$line->unit_price)
                  {{ number_format($amount,2)}}
                @elseif($header->status==1)
                @php ($amount  = $line->qty_confirm*$line->unit_price)
                {{ number_format($amount,2)}}
                @elseif($header->status>0 and $header->status<3)
                  @php ($amount  = $line->qty_shipping*$line->unit_price)
                  {{ number_format($amount,2)}}
                @endif
                @php ($totamount  += $amount)
              </td>
    				</tr>
              @endforeach
    			</tbody>
          <tfoot>
    				<tr class="visible-xs">
    					<td class="text-center" ><strong class="totprice" id="totprice1">Total
              @if($header->currency=='IDR')
                Rp.
              @elseif($header->currency=='USD')
                $
              @endif{{number_format($totamount,2)}}</strong>
              </td>
    				</tr>
    				<tr class="hidden-xs">
              @if($header->status>=0)
              <td></td>
              @endif
    					<td colspan="4" class="hidden-xs text-right">SubTotal: </td>
    					<td class="hidden-xs text-right"><strong id="totprice2">
                @if($header->currency=='IDR')
                Rp.
                @elseif($header->currency=='USD')
                $
                @endif
                {{ number_format($totamount,2) }}</strong></td>
    				</tr>
            <tr class="hidden-xs">
              @if($header->status>=0)
              <td></td>
              @endif
    					<td colspan="4" class="hidden-xs text-right">Tax: </td>
    					<td class="hidden-xs text-right"><strong id="totprice2">
                @if($header->currency=='IDR')
                Rp.
                @elseif($header->currency=='USD')
                $
                @endif
                {{ number_format(0,2) }}</strong></td>
    				</tr>
            <tr class="hidden-xs">
              @if($header->status>=0)
              <td></td>
              @endif
    					<td colspan="4" class="hidden-xs text-right">Total: </td>
    					<td class="hidden-xs text-right"><strong id="totprice2">
                @if($header->currency=='IDR')
                Rp.
                @elseif($header->currency=='USD')
                $
                @endif
                {{ number_format($totamount,2) }}</strong></td>
    				</tr>
            @if($header->status==0 and Auth::user()->customer_id==$header->distributor_id)
              <tr>
      					<td><button type="submit" name="approve" value="reject" class="btn btn-warning text-left" style="min-width:200px;"><i class="fa fa-angle-left" style="color:#fff"></i>@lang('label.reject')

              </td>
      					<td colspan="4" class="hidden-xs"></td>
      					<td><button type="submit" name="approve" value="approve" class="btn btn-success btn-block text-right">@lang('label.approve')&nbsp; <i class="fa fa-angle-right" style="color:#fff"></i></button></td>
      				</tr>
            @else
            <tr style="border-top-style:hidden;">
              <td class="text-left">
                @if(Auth::user()->customer_id==$header->customer_id and $header->status==0)
                <button type="submit" name="batal" value="batal" class="btn btn-warning" style="min-width:200px;"><i class="fa fa-angle-left" style="color:#fff"></i> @lang('label.cancel')</button>
                @else
                <a href="{{URL::previous()}}" class="btn btn-warning" style="min-width:200px;"><i class="fa fa-angle-left" style="color:#fff"></i>@lang('label.back')</a>
                @endif
              </td>

              @if(Auth::user()->customer_id==$header->customer_id and $header->status<0)
                <td colspan="3" class="hidden-xs"></td>
              @else
                <td colspan="4" class="hidden-xs"></td>
              @endif

              @if($header->status>=0 )
              <td>

                @if($header->status>=0 and $header->status<3 and Auth::user()->customer_id==$header->customer_id)
                  <button type="submit" name="terima" value="terima" class="btn btn-success btn-block">@lang('shop.Receive')&nbsp; <i class="fa fa-angle-right" style="color:#fff"></i></button>
                @elseif($header->status==1 and Auth::user()->customer_id==$header->distributor_id and (Auth::user()->hasRole('Distributor') or Auth::user()->hasRole('Distributor Cabang')))
                  <button type="submit" name="kirim" value="kirim" class="btn btn-success btn-block">@lang('shop.Send')&nbsp; <i class="fa fa-angle-right" style="color:#fff"></i></button>
                @endif
              </td>
              @endif
            </tr>
            @endif
    			</tfoot>
    		</table>
      </form>
      </div>
      </div>
  </div>
</div>
</div>
@endsection
@section('js')

<script src="{{ asset('js/myproduct.js') }}"></script>

@endsection
