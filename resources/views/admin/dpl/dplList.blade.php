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
      <div class="col-md-12">
        <div class="panel panel-default">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Suggest No.</th>
                <th>Last Approver</th>
                <th>DPL No.</th>
                <th>MR</th>
                <th>Outlet</th>
                <th>Distributor</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($dpl as $list)
              <tr>
                <td style="vertical-align: middle;">{{ $list->suggest_no }}</td>
                <td style="vertical-align: middle;">{{ $list->dpl_appr_name }}</td>
                <td style="vertical-align: middle;">{{ $list->dpl_no }}</td>
                <td style="vertical-align: middle;">{{ $list->dpl_mr_name }}</td>
                <td style="vertical-align: middle;" width="200">{{ $list->dpl_outlet_name }}</td>
                <td style="vertical-align: middle;">{{ $list->dpl_distributor_name }}</td>
                <td style="vertical-align: middle;">
                  {!! $list->btn_discount !!}
                  {!! $list->btn_confirm !!}
                  {!! $list->btn_dpl_no !!}
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

@endsection
@section('js')

<script src="{{ asset('js/dpl.js') }}"></script>

@endsection
