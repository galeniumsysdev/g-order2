@extends('layouts.navbar_product')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-sm-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading"><strong>Pengiriman Surat Jalan</strong></div>
                <div class="panel-body" style="overflow-x:auto;">
                  <div id="frmsearch" class="panel panel-default">
                    <form method="post" action="{{route('order.searchShippingOracle')}}">
                      {{ csrf_field() }}
                      <div class="form-group">
                        <label for="name" class="col-md-2 control-label">@lang('shop.deliveryno')</label>
                          <div class="col-md-10" >
                            <input type="text" name="nosj" value="" class="form-control" required>
                          </div>
                      </div>

                      <div class="form-group">
                          <div class="col-md-10 col-md-offset-2">
                            <button type="submit" id="btn-search" class="btn btn-primary">
                                Search
                            </button>
                          </div>
                      </div>
                    </form>
                    &nbsp;
                  </div>
                  @if(!empty($ship))
                    @foreach($ship as $key => $delivery)
                      <form method="post" action="#">
                        <div class="form-group">
                          <label for="name" class="col-md-2 control-label">@lang('shop.deliveryno')</label>
                            <div class="col-md-10" >
                              <input type="text" name="nosj" value="{{$key}}" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group">
                          <label for="name" class="col-md-2 control-label">Customer</label>
                            <div class="col-md-10" >
                              <input type="text" class="form-control" value="{{$delivery->header->outlet->customer_name}}" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                          <label for="name" class="col-md-2 control-label">@lang('shop.ShipTo')</label>
                            <div class="col-md-10" >
                              <textarea class="form-control" readonly></textarea>
                            </div>
                        </div>
                      </form>
                    @endforeach
                  @endif


                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('js')
@endsection
