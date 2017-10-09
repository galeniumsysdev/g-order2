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

  <div class="container dpl-container">
    <div class="row">
      <div class="col-md-10 col-sm-offset-1">
        <div class="panel panel-default">
          <div class="panel-heading"><strong>Discount Form</strong></div>
          <div class="panel-body" style="overflow-x:auto;">
            <div class="panel panel-default">
              <div class="dpl-form-wrapper">
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="dpl-form-label">
                          <label for="outlet">MR</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        {{ Form::hidden('mr',$dpl['dpl_mr_name'],array('id'=>'mr')) }}
                        <span class="dpl-default-value">{{ $dpl['dpl_mr_name'] }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="dpl-form-label">
                          <label for="outlet">Outlet</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        {{ Form::hidden('outlet',$dpl['dpl_outlet_name'],array('id'=>'outlet')) }}
                        <span class="dpl-default-value">{{ $dpl['dpl_outlet_name'] }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="dpl-form-label">
                          <label for="distributor">Distributor</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        {{ Form::hidden('distributor',$dpl['dpl_distributor_name'],array('id'=>'distributor')) }}
                        <span class="dpl-default-value">{{ $dpl['dpl_distributor_name'] }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="dpl-form-label">
                          <label for="discount">Discount</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        <span class="dpl-default-value">{{ $dpl['discount'] }} %</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        &nbsp;
                      </div>
                      <div class="col-md-10">
                      <!-- Form Approve -->
                        <div class="dpl-button-wrapper">
                          {!! Form::open(['url' => '/dpl/discount/approval/Approve', 'id'=>'generate-sugg-no-form']) !!}
                            {{ Form::hidden('action','Approve') }}
                            {{ Form::hidden('suggest_no',$dpl['suggest_no'],array('id'=>'suggest_no')) }}
                            {{ Form::submit('Approve',array('class'=>'btn btn-primary')) }}
                          {{ Form::close() }}
                        </div>
                      <!-- Form Reject -->
                        <div class="dpl-button-wrapper">
                          {!! Form::open(['url' => '/dpl/discount/approval/Reject', 'id'=>'generate-sugg-no-form']) !!}
                            {{ Form::hidden('action','Reject') }}
                            {{ Form::submit('Reject',array('class'=>'btn btn-danger')) }}
                          {{ Form::close() }}
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
@endsection
@section('js')

<script src="{{ asset('js/dpl.js') }}"></script>

@endsection
