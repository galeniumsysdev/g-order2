<!--
/**
* created by WK Productions
*/
-->
@extends('layouts.navbar_product')
@section('content')
  <link href="{{ asset('css/table.css') }}" rel="stylesheet">
  <link href="{{ asset('css/dpl.css') }}" rel="stylesheet">
  @if($status= Session::get('msg'))
    <div class="alert alert-info">
        {{$status}}
    </div>
  @endif

  <div class="container">
    <div class="row">
      <div class="col-md-10 col-sm-offset-1">
        <div class="panel panel-default">
          <div class="panel-heading"><strong>Discount Form</strong></div>
          <div class="panel-body" style="overflow-x:auto;">
            <div class="panel panel-default">
              <div class="form-wrapper">
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="form-label">
                          <label for="outlet">MR</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        {{ Form::hidden('mr',$dpl['dpl_mr_name'],array('id'=>'mr')) }}
                        <span class="default-value">{{ $dpl['dpl_mr_name'] }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="form-label">
                          <label for="outlet">Outlet</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        {{ Form::hidden('outlet',$dpl['dpl_outlet_name'],array('id'=>'outlet')) }}
                        <span class="default-value">{{ $dpl['dpl_outlet_name'] }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="form-label">
                          <label for="distributor">Distributor</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        <span class="default-value">{{ $header->distributor_name }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="form-label">
                          <label for="distributor">No. Usulan</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        <span class="default-value">{{ $dpl->suggest_no }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="form-label">
                          <label for="distributor">Disetujui oleh</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        <span class="default-value">{{ $dpl->approver_name }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                @if($header->dpl_no)
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="form-label">
                          <label for="distributor">No. DPL</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        <span class="default-value">{{ $header->dpl_no }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                @endif
                <div class="form-group">
                  <table id="cart" class="table table-hover table-condensed">
                      <thead>
                      <tr>
                        <th style="width:45%" class="text-center" rowspan="2">@lang('shop.Product')</th>
                        <th style="width:10%" class="text-center" rowspan="2">@lang('shop.Price')</th>
                        <th style="width:5%" class="text-center" rowspan="2">@lang('shop.uom')</th>
                        <th style="width:5%" class="text-center" rowspan="2">@lang('shop.qtyorder')</th>
                        <th style="width:15%" class="text-center" rowspan="2">@lang('shop.SubTotal')</th>
                        <th style="width:10%" class="text-center" rowspan="2">Discount<br/>Distributor</th>
                        <th class="text-center" colspan="2">GPL</th>
                      </tr>
                      <tr>
                        <th style="width:10%" class="text-center">Discount</th>
                        <th style="width:10%" class="text-center">Bonus</th>
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
                        <td data-th="@lang('shop.Price')" class="xs-only-text-left text-center" >{{ number_format($line->list_price,2) }}</td>
                        <td data-th="@lang('shop.uom')" class="xs-only-text-left text-center" >{{ $line->uom }}</td>
                        <td data-th="@lang('shop.qtyorder')" class="text-center xs-only-text-left">
                            {{ $line->qty_request }}
                        </td>
                        <td data-th="@lang('shop.SubTotal')" class="xs-only-text-left text-right">
                            {{  number_format($line->amount,2) }}
                        </td>
                        <td data-th="Discount Distributor" class="xs-only-text-left text-center">
                          {{ $line->discount }} %
                        </td>
                        <td data-th="Discount" class="xs-only-text-left text-center">
                          {{ $line->discount_gpl }} %
                        </td>
                        <td data-th="Bonus" class="xs-only-text-left text-center">
                          {{ $line->bonus_gpl }}
                        </td>
                      </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                    </tfoot>
                  </table>
                </div>
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        &nbsp;
                      </div>
                      <div class="col-md-10">
                      <!-- Form Approve -->
                        <div class="button-wrapper">
                          {!! Form::open(['url' => route('dpl.discountApprovalSet'), 'id'=>'generate-sugg-no-form']) !!}
                            {{ Form::hidden('action','Approve') }}
                            {{ Form::hidden('suggest_no',$dpl['suggest_no'],array('id'=>'suggest_no')) }}
                            {{ Form::submit('Approve',array('class'=>'btn btn-primary')) }}
                          {{ Form::close() }}
                        </div>
                      <!-- Form Reject -->
                        <div class="button-wrapper">
                          <a href="#" class="btn btn-danger" data-toggle="modal" data-target="#reasonReject">Reject</a>
                        </div>
                        <div class="button-wrapper">
                          <a href="{{ route('dpl.list') }}" class="btn btn-default">Back</a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="reasonReject" 
       tabindex="-1" role="dialog" 
       aria-labelledby="reasonRejectModalLabel">
    <div class="modal-dialog" id="modal-dialog-reason-reject" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" 
            data-dismiss="modal" 
            aria-label="Close">
            <span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" 
          id="authenticationModalLabel">Reason Reject</h4>
        </div>
        <div class="modal-body text-center">
          {!! Form::open(['url' => route('dpl.discountApprovalSet'), 'id'=>'generate-sugg-no-form']) !!}
            {{ Form::hidden('action','Reject') }}
            {{ Form::hidden('suggest_no',$dpl['suggest_no'],array('id'=>'suggest_no')) }}
            {{ Form::textarea('reason_reject','',array('class'=>'form-control')) }}
            <br/>
            {{ Form::submit('Reject',array('class'=>'btn btn-danger')) }}
          {{ Form::close() }}
        </div>
        <div class="modal-footer">
          <button type="button" 
             class="btn btn-default" 
             data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
@endsection
@section('js')

<script src="{{ asset('js/dpl.js') }}"></script>

@endsection
