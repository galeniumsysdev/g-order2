@extends('layouts.navbar_product')
@section('content')
  <!--untuk ke oracle-->
  <link href="{{ asset('css/table.css') }}" rel="stylesheet">
  @if($status= Session::get('message'))
    <div class="alert alert-info">
        {{$status}}
    </div>
  @endif
<!--<link rel="stylesheet"href="//codeorigin.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" />-->
<div class="container">
  <div class="row">

    <div class="col-md-12">
      <div class="panel panel-default">
        <div class="panel-heading"><strong>Order Product</strong></div>


        <div class="panel-body">
          <div class="panel panel-default">
            <div class="form-horizontal col-sm-6">

                  <br>
              <div class="form-group">
                <label for="subject" class="col-md-2 control-label">No.Trx</label>
                <div class="col-md-10 ">
                    <label class="form-class">{{$header->notrx}}&nbsp;<a href="{{url('/download/'.$header->file_po)}}" title="@lang('shop.download') file PO"><i class="glyphicon glyphicon-download-alt"></i></a></label>
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
                  @if($header->status==0 and $header->approve==1 and Auth::user()->customer_id==$header->distributor_id)
                    <input type="text" class="form-control" value="Menunggu Booked Oracle" readonly>
                  @else

                    @if($header->status==-2 and !is_null($header->alasan_tolak))
                    <input type="text" class="form-control" value="{{$header->status_name. ' (alasan: '.$header->alasan_tolak.')'}}" readonly>
                    @else
                    <input type="text" class="form-control" value="{{$header->status_name}}" readonly>
                    @endif
                  @endif
                </div>
              </div>
              @if($header->status>=2)
              <div class="form-group">
                <label for="subject" class="col-md-2 control-label">@lang('shop.senddate')</label>
                <div class="col-md-10">
                    <input type="text" class="form-control" value="{{date('d-M-Y',strtotime($header->tgl_kirim))}}" readonly>
                </div>
              </div>
              @endif

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
              @if($header->status>=3)
              <div class="form-group">
                <label for="subject" class="col-md-2 control-label">@lang('shop.receivedate')</label>
                <div class="col-md-10">
                    <input type="text" class="form-control" value="{{date('d-M-Y',strtotime($header->tgl_terima))}}" readonly>
                </div>
              </div>
              @endif
            </div>
          </div>
          <div class="col-sm-12 table-responsive">
            @if($header->status==0 and Auth::user()->customer_id==$header->distributor_id)
              <form class="form-horizontal" action="{{route('order.approvalSO')}}" method="post">
            @elseif(Auth::user()->customer_id==$header->customer_id)
              <form class="form-horizontal" action="{{route('order.cancelPO')}}" method="post">
            @endif
              {{ csrf_field() }}
                <input type="hidden" name="header_id" value="{{$header->id}}">
                <table  class="table">
            				<thead>
            				<tr>
            					<th style="width:45%;" class="text-center">@lang('shop.Product')</th>
            					<th style="width:10%" class="text-center">@lang('shop.Price')</th>
                      <th style="width:5%" class="text-center">@lang('shop.uom')</th>
            					<th class="text-center">@lang('shop.qtyorder')</th>
                      @if($header->status>0 or ($header->status==0 and Auth::user()->customer_id==$header->distributor_id ))
                        <th class="text-center">@lang('shop.qtyavailable')</th>
                      @endif
                      @if($header->status>1)
                        <th class="text-center">@lang('shop.qtyship')</th>
                      @endif
                      @if(Auth::user()->customer_id==$header->customer_id and  $header->status==2)
                        <th class="text-center">@lang('shop.qtyreceive')</th>
                      @endif
                      @if($header->status>2)
                          <th class="text-center">@lang('shop.qtyreceive')</th>
                      @endif
            					<th style="width:20%" class="text-center">@lang('shop.SubTotal')</th>
            				</tr>
            			</thead>
            			<tbody>
                    @php ($totamount=0)
                    @php($taxtotal=0)
                    @foreach($lines as $line)
                      @php ($id  = $line->line_id)
            				<tr>
            					<td>
            						<div class="row">
            							<div class="col-sm-2 hidden-xs"><img src="{{ asset('img//'.$line->imagePath) }}" alt="..." class="img-responsive"/></div>
            							<div class="col-sm-10">
            								{{ $line->title }}
            							</div>
            						</div>
            					</td>
            					<td data-th="@lang('shop.Price')" class="xs-only-text-left text-center" >{{ number_format($line->unit_price,2) }}</td>
                      <td data-th="@lang('shop.uom')" class="xs-only-text-left text-center" >{{ $line->uom }}</td>
            					<td data-th="@lang('shop.qtyorder')" class="text-center xs-only-text-left">
                          {{ (float)$line->qty_request }}
            					</td>
                      @if($header->status>0 or (Auth::user()->customer_id==$header->distributor_id and $header->status==0))
                        <td data-th="@lang('shop.qtyavailable')" class="text-center xs-only-text-left">
                          @if(Auth::user()->customer_id==$header->distributor_id and $header->status==0)
                            <input type="number" name="qtyshipping[{{$id}}]" id="qty-{{$id}}" class="form-control text-center" value="{{ $line->qty_request }}" style="min-width:80px;">
                          @elseif($header->status>0)
                            {{(float)$line->qty_confirm}}
                          @endif
                        </td>
                      @endif
                      @if($header->status>1)
                        <td data-th="@lang('shop.qtyship')" class="text-center xs-only-text-left">
                          {{(float)$line->qty_shipping}}
                        </td>
                      @endif
                      @if(Auth::user()->customer_id==$header->customer_id and $header->status==2)
                        <td data-th="@lang('shop.qtyreceive')" class="text-center xs-only-text-left">
                          <input type="number" name="qtyreceive[{{$id}}]" id="qty-{{$id}}" class="form-control text-center" value="{{ $line->qty_shipping }}" style="min-width:80px;">
                        </td>
                      @endif
                      @if($header->status>2)
                          <td data-th="@lang('shop.qtyreceive')" class="text-center xs-only-text-left">
                            {{(float)$line->qty_accept}}
                          </td>
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
                        @php($taxtotal += $line->tax_amount)
                        @php ($totamount  += $amount)
                      </td>
            				</tr>
                      @endforeach
                      @php($total = $taxtotal+$totamount)
            			</tbody>
                  <tfoot>
            				<tr  style="text-align:right">
                      @if($header->status<0 or ($header->status==0 and Auth::user()->customer_id==$header->customer_id ))
            					     <td colspan="4">SubTotal: </td>
                      @elseif($header->status==1 or ($header->status==0 and Auth::user()->customer_id==$header->distributor_id) )
                           <td colspan="5" >SubTotal: </td>
                      @elseif($header->status==2 and  Auth::user()->customer_id==$header->distributor_id)
                            <td colspan="6">SubTotal: </td>
                      @else
                            <td colspan="7">SubTotal: </td>
                      @endif
            					<td><strong id="totprice2">
                        @if($header->currency=='IDR')
                        Rp.
                        @elseif($header->currency=='USD')
                        $
                        @endif
                        {{ number_format($totamount,2) }}</strong>
                      </td>
            				</tr>
                    <tr style="text-align:right">
                      @if($header->status<0 or ($header->status==0 and Auth::user()->customer_id==$header->customer_id ))
            					     <td colspan="4">Tax: </td>
                      @elseif($header->status==1 or ($header->status==0 and Auth::user()->customer_id==$header->distributor_id) )
                           <td colspan="5">Tax: </td>
                      @elseif($header->status==2 and  Auth::user()->customer_id==$header->distributor_id)
                            <td colspan="6">Tax: </td>
                      @else
                            <td colspan="7">Tax: </td>
                      @endif
            					<td><strong id="totprice2">
                        @if($header->currency=='IDR')
                        Rp.
                        @elseif($header->currency=='USD')
                        $
                        @endif
                        {{ number_format($taxtotal,2) }}</strong></td>
            				</tr>
                    <tr  style="text-align:right">
                      @if($header->status<0 or ($header->status==0 and Auth::user()->customer_id==$header->customer_id ))
            					     <td colspan="4">Total: </td>
                      @elseif($header->status==1 or ($header->status==0 and Auth::user()->customer_id==$header->distributor_id) )
                           <td colspan="5" >Total: </td>
                      @elseif($header->status==2 and  Auth::user()->customer_id==$header->distributor_id)
                            <td colspan="6">Total: </td>
                      @else
                            <td colspan="7">Total: </td>
                      @endif
            					<td><strong id="totprice2">
                        @if($header->currency=='IDR')
                        Rp.
                        @elseif($header->currency=='USD')
                        $
                        @endif
                        {{ number_format($total,2) }}</strong></td>
            				</tr>
                    @if($header->status==0 and $header->approve==0 and Auth::user()->customer_id==$header->distributor_id)
                      <tr>
              					<td><!--<a onclick="return false;" id="reject_PO" class="btn btn-warning" style="min-width:200px;">-->
                          <input type="hidden" id="alasanreject" value="" name="alasan">
                          <button type="submit" onclick="return inputreason();" name="approve" value="reject" class="btn btn-warning" style="min-width:200px;" >
                          <i class="fa fa-angle-left" style="color:#fff"></i>@lang('label.reject')
                          </button>
                          <!--</a>-->
                        </td>
              					<td colspan="4" class="hidden-xs"></td>
              					<td><button type="submit" name="approve" value="approve" class="btn btn-success btn-block text-right">@lang('label.approve')&nbsp; <i class="fa fa-angle-right" style="color:#fff"></i></button></td>
              				</tr>
                    @else
                      <tr style="border-top-style:hidden;">
                        <td>
                        <a href="{{URL::previous()}}" class="btn btn-warning" style="min-width:200px;"><i class="fa fa-angle-left" style="color:#fff"></i>&nbsp;@lang('label.back')</a>
                        </td>
                        @if($header->status==2 and Auth::user()->customer_id==$header->customer_id)
                          <td colspan="6"></td>
                          <td><button type="submit" name="terima" value="terima" class="btn btn-success btn-block">@lang('shop.Receive')&nbsp; <i class="fa fa-angle-right" style="color:#fff"></i></button> </td>
                        @elseif($header->status==0 and $header->approve==1 and Auth::user()->customer_id==$header->distributor_id)
                          <td colspan="4"></td>
                          <td><button type="submit" name="createExcel" value="Create Excel" class="btn btn-success btn-block">@lang('shop.createexcel')&nbsp; <i class="fa fa-angle-right" style="color:#fff"></i></button> </td>
                        @elseif($header->status==0 and Auth::user()->customer_id==$header->customer_id)
                          <td colspan="3"></td>
                          <td><button type="submit" name="batal" value="batal" class="btn btn-warning btn-block">@lang('label.cancel') <i class="fa fa-angle-right" style="color:#fff"></i></button></td>
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
</div>
@endsection
@section('js')
<script src="{{ asset('js/myproduct.js') }}"></script>

@endsection
