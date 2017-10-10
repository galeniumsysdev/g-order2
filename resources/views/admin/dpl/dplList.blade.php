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
        <td align="center">{{ $list->suggest_no }}</td>
        <td>{{ $list->dpl_appr_name }}</td>
        <td align="center">{{ $list->dpl_no }}</td>
        <td>{{ $list->dpl_mr_name }}</td>
        <td>{{ $list->dpl_outlet_name }}</td>
        <td>{{ $list->dpl_distributor_name }}</td>
        <td align="center">
          {!! $list->btn_discount !!}
          {!! $list->btn_confirm !!}
          {!! $list->btn_dpl_no !!}
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
@endsection
@section('js')

<script src="{{ asset('js/dpl.js') }}"></script>

@endsection
