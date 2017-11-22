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
          <!--<div class="panel panel-default">-->
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
                  <div class="col-md-10">
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
          <!--</div>-->
          <div class="col-sm-12">
            <div class="tabcard col-sm-12">
              @if($deliveryno->count()>1 and $header->status>1)
              <ul class="nav nav-tabs" role="tablist">
                  <li role="presentation" class="active" ><a href="#order" aria-controls="Order" role="tab" data-toggle="tab"><strong>Order</strong></a></li>
                  <li role="presentation"><a href="#shipping" aria-controls="Shipping" role="tab" data-toggle="tab"><strong>Shipping</strong></a></li>
              </ul>
              @endif
              <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="order">
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
                  					<th style="width:35%;" class="text-center">@lang('shop.Product')</th>
                            <th style="width:10%" class="text-center">@lang('shop.listprice')</th>
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
                              <td data-th="@lang('shop.listprice')" class="xs-only-text-left text-center">{{ number_format($line->list_price,2) }}</td>
                  					<td data-th="@lang('shop.Price')" class="xs-only-text-left text-center" >{{ number_format($line->unit_price,2) }}</td>
                            <td data-th="@lang('shop.uom')" class="xs-only-text-left text-center" >
                              {{ $line->uom }}
                              <input type="hidden" name="uom[{{$id}}]" value="{{ $line->uom_primary }}">
                            </td>
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
                          @if($header->status<0 or ($header->status==0 and Auth::user()->customer_id==$header->customer_id ))
                               @php ($colgab = 4)
                          @elseif($header->status==1 or ($header->status==0 and Auth::user()->customer_id==$header->distributor_id) )
                               @php ($colgab = 5)
                          @elseif($header->status==2 and  Auth::user()->customer_id==$header->distributor_id)
                                @php ($colgab = 6)
                          @else
                                @php ($colgab = 7)
                          @endif
                          @if($header->currency=='IDR')
                            @php($curr = "Rp")
                          @elseif($header->currency=='USD')
                            @php($curr = "$")
                          @endif
                  				<tr>
                            <td colspan="{{$colgab}}"> Discount Reguler: Rp. {{number_format($header->disc_reg,2)}}</td>
            					      <td style="text-align:right">
                              <strong>SubTotal: {{$curr}}</strong>
                            </td>
                  					<td style="text-align:right"><strong id="totprice2">
                              {{ number_format($totamount,2) }}</strong>
                            </td>
                  				</tr>
                          <tr>
                            <td colspan="{{$colgab}}">Discount Product: Rp. {{number_format($header->disc_product,2)}}</td>
                            <td style="text-align:right">
                              <strong>Tax: {{$curr}}</strong>
                            </td>
                  					<td style="text-align:right"><strong id="taxprice">
                              {{ number_format($taxtotal,2) }}</strong>
                            </td>
                  				</tr>
                          <tr>
                            <td colspan="{{$colgab}}">Total Discount   : Rp. {{number_format( ($header->disc_product+$header->disc_reg),2)}}</td>
                            <td style="text-align:right">
                              <strong>Total: {{$curr}}<strong>
                            </td>
                  					<td style="text-align:right"><strong id="total">
                              {{ number_format($total,2) }}</strong></td>
                  				</tr>
                  			</tfoot>
                  		</table>
                      <div id="btnorder">
                      <div class="col-sm-3 col-xs-6" style="padding-bottom:10px;">
                        @if($header->status==0 and $header->approve==0 and Auth::user()->customer_id==$header->distributor_id)
                        <input type="hidden" id="alasanreject" value="" name="alasan">
                        <button type="submit" onclick="return inputreason();" name="approve" value="reject" class="btn btn-warning btn-block" >
                          <i class="fa fa-angle-left" style="color:#fff"></i>@lang('label.reject')
                        </button>
                        @else
                            <a href="{{URL::previous()}}" class="btn btn-warning btn-block" ><i class="fa fa-angle-left" style="color:#fff"></i>@lang('label.back')</a>
                        @endif

                      </div>
                        <div class="col-sm-6 hidden-xs"></div>
                      <div class="col-sm-3 col-xs-6" style="padding-bottom:10px;">
                        @if($header->status==0 and $header->approve==0 and Auth::user()->customer_id==$header->distributor_id)
                          <button type="submit" name="approve" value="approve" class="btn btn-success btn-block text-right">@lang('label.approve')&nbsp; <i class="fa fa-angle-right" style="color:#fff"></i></button>
                        @elseif($header->status==0 and $header->approve==1 and Auth::user()->customer_id==$header->distributor_id)
                          <button type="submit" name="createExcel" value="Create Excel" class="btn btn-success btn-block">@lang('shop.createexcel')&nbsp; <i class="fa fa-angle-right" style="color:#fff"></i></button>
                        @elseif($header->status==0 and $header->approve==0 and Auth::user()->customer_id==$header->customer_id)
                          <button type="submit" name="batal" value="batal" class="btn btn-warning btn-block">@lang('label.cancel') <i class="fa fa-angle-right" style="color:#fff"></i></button>
                        @elseif($header->status==3 and Auth::user()->customer_id==$header->customer_id and $deliveryno->count()==1)
                        @endif
                      </div>
                      </div>
                    </form>

                </div>
                @if(isset($deliveryno))
                <div role="tabpanel" class="tab-pane" id="shipping">
                  <div class="panel-group" id="accordion">

                    @foreach($deliveryno as $key => $delivery)
                    <div class="panel panel-default">
                      <div class="panel-heading">
                        <h6 class="panel-title kirim-panel">
                          Delivery No:<a data-toggle="collapse" data-parent="#accordion" href="#{{$key}}">{{$key}}</a>
                          <p class="pull-right">Date: {{$delivery->first()->tgl_kirim}}</p>
                        </h6>
                      </div>
                      <div id="{{$key}}" class="panel-collapse collapse">
                        <div class="panel-body">
                          <table class="table">
                            <thead>
                              <tr>
                                <th style="width:45%;" class="text-center">@lang('shop.Product')</th>
                                <th style="width:10%;" class="text-center">@lang('shop.uom')</th>
                                <th style="width:10%;" class="text-center">@lang('shop.qtyship')</th>
                                <th style="width:10%;" class="text-center">@lang('shop.qtyreceive')</th>
                              </tr>
                            </thead>
                            <tbody>
                              @foreach($delivery as $detail)
                              <tr>
                                <td>{{$detail->product->title}}</td>
                                <td style="text-align:center">{{$detail->uom_primary}}</td>
                                <td style="text-align:center">{{(float)$detail->qty_shipping}}</td>
                                <td style="text-align:center">{{(float)$detail->qty_accept}}</td>
                              </tr>
                              @endforeach
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                    @endforeach
                  </div>
                </div>
                @endif
              </div>
            </div>
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
