@extends('layouts.navbar_product')
@section('content')
<!--tidak ke oracle-->
  <link href="{{ asset('css/table.css') }}" rel="stylesheet">
  @if($status= Session::get('message'))
    <div class="alert alert-info">
        {{$status}}
    </div>
  @endif
  @if($status= Session::get('error'))
  <div class=" alert alert-danger">{{Session::get('error')}}</div>
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
              @if($header->suggest_no)
              <div class="form-group">
                <label for="name" class="col-md-2 control-label">@lang('shop.suggestiondpl')</label>
                  <div class="col-md-10" >
                    <input type="text" class="form-control" value="{{$header->suggest_no}}" readonly>
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
                    <textarea class="form-control" rows="2" readonly>{{$header->ship_to_addr}}</textarea>
                  </div>
              </div>
              @if($header->dpl_no)
                <div class="form-group">
                  <label for="name" class="col-md-2 control-label">DPL No</label>
                    <div class="col-md-10" >
                      <input type="text" class="form-control" name="dpl_no" value="{{$header->dpl_no}}" readonly>
                    </div>
                </div>
              @endif
            </div>
          <br>

            <div class="tabcard col-sm-12">
              @if(($deliveryno->count()>0 and $header->status>=2)
            and (!($deliveryno->count()==1 and $header->status>2)))
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active" ><a href="#order" aria-controls="Order" role="tab" data-toggle="tab"><strong>Order</strong></a></li>
                    <li role="presentation"><a href="#shipping" aria-controls="Shipping" role="tab" data-toggle="tab"><strong>Shipping</strong></a></li>
                </ul>
              @endif
              <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="order">
                @if(Auth::user()->customer_id==$header->distributor_id)
                  <form class="form-horizontal" id="frmorder" action="{{route('order.approvalSO')}}" method="post">
                @elseif(Auth::user()->customer_id==$header->customer_id)
                  <form class="form-horizontal" id="frmorder" action="{{route('order.cancelPO')}}" method="post">
                @endif
                    {{ csrf_field() }}
                    <input type="hidden" name="header_id" value="{{$header->id}}">
                    @if (($deliveryno->count()==0 and ($header->status>=1 and $header->distributor_id == Auth::user()->customer_id))
                    or ($deliveryno->count()<=1 and $header->status!= 2 and $header->status>= 0 and $header->status<= 4 and $header->customer_id == Auth::user()->customer_id))
                      <div class="form-group {{ $errors->has('deliveryno') ? ' has-error' : '' }}">
                        <label for="subject" class="col-md-2 control-label">@lang('shop.deliveryno')</label>
                        <div class="col-md-4 ">
                          @if($deliveryno->count()==1)
                            <input type="text" name="deliveryno" value="{{$deliveryno->first()[0]->deliveryno}}" class="form-control" required readonly>
                          @else
                            <input type="text" name="deliveryno" value="" class="form-control" >
                          @endif
                          @if ($errors->has('deliveryno'))
                              <span class="help-block">
                                  <strong>{{ $errors->first('deliveryno') }}</strong>
                              </span>
                          @endif
                        </div>
                      </div>
                    @endif

                    <table id="cart" class="table table-hover table-condensed">
                				<thead>
                				<tr>
                					<th style="width:45%" class="text-center">@lang('shop.Product')</th>
                					<th style="width:10%" class="text-center">@lang('shop.Price')</th>
                          <th style="width:7%" class="text-center">@lang('shop.uom')</th>
                					<th class="text-center">@lang('shop.qtyorder')</th>
                          @if($header->dpl_no)
                          <th style="width:5%" class="text-center">Disc Distributor(%)</th>
                          <th style="width:5%" class="text-center">Disc GPL(%)</th>
                          <th style="width:5%" class="text-center">Bonus GPL</th>
                          @endif
                          @if($header->status>=0)
                              @if(Auth::user()->customer_id==$header->distributor_id or
                               (Auth::user()->customer_id==$header->customer_id and $header->status>=1)
                               )
                                <th style="width:7%" class="text-center">@lang('shop.qtyavailable')</th>
                              @endif
                              @if($header->status>1 or ($header->status==1 and Auth::user()->customer_id==$header->distributor_id))
                                <th style="width:7%" class="text-center">@lang('shop.qtyship')</th>
                              @endif
                              @if(Auth::user()->customer_id==$header->customer_id
                                or (Auth::user()->customer_id==$header->distributor_id and $header->status>1 )
                                )
                                <th style="width:7%" class="text-center">@lang('shop.qtyreceive')</th>
                              @endif
                          @endif

                					<th style="width:15%" class="text-center">@lang('shop.SubTotal')</th>
                				</tr>
                			</thead>
                			<tbody>
                        @foreach($lines as $line)
                          @php ($id  = $line->line_id)
                				<tr>
                					<td>
                						<div class="row">
                							<div class="col-sm-2 hidden-xs"><img src="{{ asset('img//'.$line->imagePath) }}" alt="..." class="img-responsive"/></div>
                							<div class="col-sm-10">
                								<h4 >{{ $line->title }}</h4>
                							</div>
                						</div>
                					</td>
                					<td data-th="@lang('shop.Price')" class="xs-only-text-left text-center" id="hrg-{{$id}}">
                            {{ number_format($line->list_price/$line->conversion_qty,2) }}
                          </td>
                          <td data-th="@lang('shop.uom')" class="xs-only-text-left text-center">
                              {{ $line->uom_primary }}
                              <input type="hidden" name="uom[{{$id}}]" value="{{ $line->uom_primary }}">
                          </td>

                					<td data-th="@lang('shop.qtyorder')" class="text-center xs-only-text-left" id="ord-{{$id}}">
                              {{ $line->qty_request_primary }}
                					</td>
                          @if($header->dpl_no)
                          <td data-th="Disc Distributor(%)" class="xs-only-text-left text-center">{{$line->discount}}</td>
                          <td data-th="Disc GPL(%)" class="xs-only-text-left text-center">{{$line->discount_gpl}}</td>
                          <td data-th="Bonus GPL" class="xs-only-text-left text-center">{{(int)$line->bonus_gpl." ".$line->uom_primary}}</td>
                          @endif
                          @if($header->status>=0)
                              @if(Auth::user()->customer_id==$header->distributor_id or
                               (Auth::user()->customer_id==$header->customer_id and $header->status>=1)
                               )
                                <td data-th="@lang('shop.qtyavailable')"  class="text-center xs-only-text-left" id="avl-{{$id}}">
                                  @if($header->status>=1)
                                  {{number_format($line->qty_confirm_primary,2)}}
                                  @else
                                    <input type="number" name="qtyshipping[{{$id}}]" class="form-control" value="{{(float)$line->qty_request_primary+$line->bonus_gpl}}">
                                  @endif
                                </td>
                              @endif
                              @if($header->status>1 or ($header->status==1 and Auth::user()->customer_id==$header->distributor_id))
                                <td data-th="@lang('shop.qtyship')"  class="text-center xs-only-text-left">
                                  @if($header->status==1)
                                    <input type="number" name="qtyshipping[{{$id}}]" value="{{(float)$line->qty_confirm_primary}}" class="form-control"  id="ship-{{$id}}">
                                  @else
                                    {{$line->qty_shipping_primary}}
                                  @endif
                                </td>
                              @endif
                              @if(Auth::user()->customer_id==$header->distributor_id and $header->status>1)
                              <td  data-th="@lang('shop.qtyreceive')"  class="text-center xs-only-text-left">
                                  {{number_format($line->qty_accept,2)}}
                              </td>
                              @endif
                              @if(Auth::user()->customer_id==$header->customer_id)
                                <td  data-th="@lang('shop.qtyreceive')"  class="text-center xs-only-text-left">

                                  @if (($header->status>=2  or $deliveryno->count()>1 )
                                    and !($header->status==3 and $deliveryno->count()==1 )
                                     )
                                    {{number_format($line->qty_accept_primary,2)}}
                                  @elseif($header->status == 0 )
                                    <input type="number" name="qtyreceive[{{$id}}]" value="{{(float)$line->qty_request_primary}}" class="form-control">
                                  @elseif($header->status==1)
                                    <input type="number" name="qtyreceive[{{$id}}]" value="{{(float)$line->qty_confirm_primary}}" class="form-control">
                                  @elseif($header->status==3)
                                    <input type="number" name="qtyreceive[{{$id}}]" value="{{(float)$line->qty_shipping_primary}}" class="form-control">
                                  @endif
                                </td>
                              @endif
                          @endif

                					<td data-th="@lang('shop.SubTotal')" class="xs-only-text-left text-right">
                            {{number_format($line->amount_confirm,2)}}
                          </td>
                				</tr>
                        @endforeach
                			</tbody>
                      <tfoot>
                        @if($header->currency=='IDR')
                          @php($curr= "Rp.")
                        @elseif($header->currency=='USD')
                          @php($curr= "$")
                        @endif
                        @if($header->status< 0)
                             @php($colgab=4)
                        @elseif($header->status==0)
                             @php($colgab=5)
                        @elseif($header->status==1)
                             @php($colgab=6)
                        @elseif($header->status>1)
                             @php($colgab=7)
                        @endif
                        @if($header->dpl_no)
                          @php($colgab=$colgab+3)
                        @endif

                				<tr>
                					<td colspan="{{$colgab}}" class="hidden-xs text-right">SubTotal: </td>
                					<td class="text-right xs-only-text-center"><strong id="totprice2"><label class="visible-xs-inline">SubTotal:</label>
                            {{ $curr." ".number_format($header->amount_confirm,2) }}</strong></td>
                				</tr>
                        <tr>
                          <td colspan="{{$colgab}}" class="hidden-xs text-right">Tax </td>
                					<td class="text-right xs-only-text-center"><strong id="totprice2"><label class="visible-xs-inline">Tax:</label>
                            {{ $curr." ".number_format($header->tax_amount,2) }}</strong>
                          </td>
                				</tr>
                        <tr>
                          <td colspan="{{$colgab}}" class="hidden-xs text-right">Total </td>
                					<td class="text-right xs-only-text-center"><strong id="totprice2"><label class="visible-xs-inline">Total:</label>
                            {{ $curr." ".number_format(($header->amount_confirm+$header->tax_amount),2) }}</strong>
                          </td>
                				</tr>

                			</tfoot>
                		</table>
                    <div class="col-xs-12 col-sm-3">
                        @if($header->status==0 and Auth::user()->customer_id==$header->distributor_id)
                          <!--<button type="submit" name="approve" value="reject" class="btn btn-block btn-warning btnorder" ><i class="fa fa-angle-left" style="color:#fff"></i>@lang('label.reject') PO-->
                          <input type="hidden" id="alasanreject" value="" name="alasan">
                          <button type="submit" onclick="return inputreason();" name="approve" value="reject" class="btn btn-warning btn-block btnorder" >
                            <i class="fa fa-angle-left" style="color:#fff"></i>@lang('label.reject') PO
                          </button>
                        @elseif($header->status==0 and Auth::user()->customer_id==$header->customer_id)
                          <button type="submit"  name="batal" value="batal" class="btn btn-block btn-warning btnorder" ><i class="fa fa-angle-left" style="color:#fff"></i> @lang('shop.cancelpo')</button>
                        @elseif($header->status==1 or ($header->status==3 and $deliveryno->count()==1))
                          <a href="{{URL::previous()}}" class="btn btn-warning btn-block" ><i class="fa fa-angle-left" style="color:#fff"></i>@lang('label.back')</a>
                        @endif

                    </div>
                    <div class="col-sm-6 hidden-xs"></div>
                    <div class="col-xs-12 col-sm-3">
                      @if($header->status>=0 )
                        @if(($header->status>=0 and $header->status < 2 and Auth::user()->customer_id==$header->customer_id)
                           or ($header->status==3 and $deliveryno->count()==1 and Auth::user()->customer_id==$header->customer_id)
                           )
                          <button type="submit" name="terima" value="terima" class="btn btn-success btn-block btnorder">@lang('shop.Receive')&nbsp; <i class="fa fa-angle-right" style="color:#fff"></i></button>
                        @elseif($header->status==0 and Auth::user()->customer_id==$header->distributor_id)
                          <button type="submit" name="approve" value="approve" class="btn btn-success btn-block btnorder">@lang('label.approve')&nbsp;PO <i class="fa fa-angle-right" style="color:#fff"></i></button>
                        @elseif($header->status==1 and Auth::user()->customer_id==$header->distributor_id and (Auth::user()->hasRole('Distributor') or Auth::user()->hasRole('Distributor Cabang')))
                          <button type="submit" name="kirim" value="kirim" class="btn btn-success btn-block btnorder">@lang('shop.Send')&nbsp; <i class="fa fa-angle-right" style="color:#fff"></i></button>
                        @endif
                      @endif
                    </div>
                  </form>
                </div>
                @if(isset($deliveryno))
                  <div role="tabpanel" class="tab-pane" id="shipping">
                    <div class="panel-group" id="accordion">
                      @foreach($deliveryno as $key => $delivery)
                        <div class="panel panel-default">
                          <form action="{{route('order.cancelPO')}}" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="header_id" value="{{$header->id}}">
                            <div class="panel-heading">
                              <h6 class="panel-title kirim-panel">
                                Delivery No:<a data-toggle="collapse" data-parent="#accordion" href="#{{$key}}">{{$key}}</a>
                                <input type="hidden" name="deliveryno" value="{{$key}}">
                                <p class="pull-right">Date: {{$delivery->first()->tgl_kirim}}</p>
                              </h6>
                            </div>
                            @if(Auth::user()->customer_id==$header->customer_id and $delivery->sum('qty_accept')==0)
                            <div id="{{$key}}" class="panel-collapse collapse in">
                            @else
                            <div id="{{$key}}" class="panel-collapse collapse">
                            @endif
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
                                      <td style="text-align:center">
                                        @if(Auth::user()->customer_id==$header->customer_id and is_null($detail->qty_accept))
                                          <input type="number" class="form-control input-sm" value="{{(float)$detail->qty_shipping}}" name="qtyreceive[{{$detail->line_id}}][{{$detail->id}}]">
                                        @else
                                          {{(float)$detail->qty_accept}}
                                        @endif
                                      </td>
                                    </tr>
                                    @endforeach
                                  </tbody>
                                </table>

                                @if(Auth::user()->customer_id==$header->customer_id and $delivery->sum('qty_accept')==0)
                                <div class="col-xs-4 col-sm-2 pull-right">
                                  <button type="submit" name="terima" value="terima" class="btn btn-success btn-block btnorder">@lang('shop.Receive')&nbsp;</button>
                                </div>
                                @endif
                              </div>
                            </div>
                          </form>
                        </div>

                      @endforeach

                      @if($header->status >1 and  $header->status< 3)
                        <div class="panel panel-default">
                          <form action="{{Auth::user()->customer_id==$header->customer_id?route('order.cancelPO'):route('order.approvalSO')}}" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="header_id" value="{{$header->id}}">
                            <div class="panel-heading">
                              <h6 class="panel-title kirim-panel">
                                <a data-toggle="collapse" data-parent="#accordion" href="#New-Delivery">New Delivery:</a>
                              </h6>
                            </div>
                            <div id="New-Delivery" class="panel-collapse collapse in">
                              <div class="panel-body">
                                <div class="form-group">
                                  <label for="subject" class="col-md-2 control-label">@lang('shop.deliveryno')</label>
                                  <div class="col-md-10 ">
                                      <input type="text" name="deliveryno" value="" class="form-control" required>
                                  </div>
                                </div>
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
                                    @foreach($lines as $newdelivery)
                                    @if(floatval($newdelivery->qty_shipping_primary) < floatval($newdelivery->qty_confirm_primary)
                                      or floatval($newdelivery->qty_accept_primary) < floatval($newdelivery->qty_request_primary))
                                    <tr>
                                      <td>{{$newdelivery->title}}</td>
                                      <td style="text-align:center">{{$newdelivery->uom_primary}}</td>
                                      <td style="text-align:center">
                                        @if(Auth::user()->customer_id ==$header->distributor_id)
                                          <input type="number" name="qtyshipping[{{$newdelivery->line_id}}]" value="{{(float)$newdelivery->qty_confirm - floatval($newdelivery->qty_shipping)}}" class="form-control input-sm">
                                        @else
                                          0
                                        @endif

                                      </td>
                                      <td style="text-align:center">
                                        @if(Auth::user()->customer_id ==$header->customer_id)
                                          @if(is_null($newdelivery->qty_confirm_primary))
                                            <input type="number" name="qtyreceive[{{$newdelivery->line_id}}]" value="{{(float)$newdelivery->qty_request_primary - floatval($newdelivery->qty_accept_primary)}}" class="form-control input-sm">
                                          @else
                                            <input type="number" name="qtyreceive[{{$newdelivery->line_id}}]" value="{{(float)$newdelivery->qty_confirm_primary - floatval($newdelivery->qty_accept_primary)}}" class="form-control input-sm">
                                          @endif
                                        @endif

                                      </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                  </tbody>
                                </table>

                                <div class="col-xs-4 col-sm-2 pull-right">
                                  @if(Auth::user()->customer_id==$header->customer_id and $header->status!=4)
                                  <button type="submit" name="terima" value="terima" class="btn btn-success btn-block">@lang('shop.Receive')&nbsp;</button>
                                  @elseif(Auth::user()->customer_id==$header->distributor_id and $header->status==2)
                                  <button type="submit" name="kirim" value="kirim" class="btn btn-success btn-block">@lang('shop.Send')&nbsp;</button>
                                  @endif
                                </div>

                              </div>
                            </div>
                          </form>
                        </div>
                      @endif
                    </div>
                  </div>
                @endif
              </div>
            </div>

            @if(($header->status>=2 and $header->status<=4
              and ($lines->sum('qty_shipping_primary')!=0 or $lines->sum('qty_accept_primary')!=0 )
              and (!($header->status==3 and $deliveryno->count()==1))
              ) or ($header->status< 0))

              <div class="col-xs-12 col-sm-3">
                    <a href="{{URL::previous()}}" class="btn btn-warning btn-block" ><i class="fa fa-angle-left" style="color:#fff"></i>@lang('label.back')</a>
              </div>
            @endif

        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('js')

<script src="{{ asset('js/myproduct.js') }}"></script>

@endsection
