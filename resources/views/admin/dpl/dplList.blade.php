<!--
/**
* created by WK Productions
*/
-->
@extends('layouts.navbar_product')
@section('content')
  <link href="{{ asset('font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
  <link href="{{ asset('css/table.css') }}" rel="stylesheet">
  <link href="{{ asset('css/dpl.css') }}" rel="stylesheet">
  @if($status= Session::get('msg'))
    <div class="alert alert-info">
        {{$status}}
    </div>
  @endif

  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading"><strong>Suggestion Number List</strong></div>
          <div class="panel-body" style="overflow-x:auto;">
            <div class="table-responsive">
              <table id="dpl-list" class="display responsive nowrap" width="100%">
                <thead>
                  <tr>
                    <th>Suggest No.</th>
                    <th></th>
                    <th>No. Trx</th>
                    <th>Last Approver</th>
                    <th>DPL No.</th>
                    <th>SPV</th>
                    <th>Outlet</th>
                    <th>Distributor</th>
                    <th>Status PO</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($dpl as $list)
                  <tr>
                    <td class="text-center">
                      @if($list->notrx)
                        <a href="{{ route('dpl.discountView',$list->suggest_no) }}">{{ $list->suggest_no }}</a>
                      @else
                        {{ $list->suggest_no }}
                      @endif
                    </td>
                    <td class="text-center" width="10">
                      @if($list->notrx)
                        <a href="{{ route('dpl.dplHistory',$list->suggest_no) }}"><i class="fa fa-history" aria-hidden="true"></i></a>
                      @endif
                    </td>
                    <td>
                      {{ $list->notrx }}
                    </td>
                    <td>{{ $list->dpl_appr_name }}</td>
                    <td class="text-center">
                      @if($list->dpl_no)
                        {{ 'G'.$list->dpl_no }}
                      @endif
                    </td>
                    <td>{{ $list->dpl_mr_name }}</td>
                    <td width="200">{{ $list->dpl_outlet_name }}</td>
                    <td width="200">{{ $list->dpl_distributor_name }}</td>
                    <td>
                      @if($list->status_po)
                      {!! $list->status_po !!}
                      @endif
                    </td>
                    <td class="text-center">
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
    </div>
  </div>

@endsection
@section('js')

<script src="//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
<script src="//cdn.datatables.net/responsive/2.2.0/js/dataTables.responsive.min.js"></script>
<link rel="stylesheet" href="//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="//cdn.datatables.net/responsive/2.2.0/css/responsive.dataTables.min.css">
<script src="{{ asset('js/dpl.js') }}"></script>

@endsection
