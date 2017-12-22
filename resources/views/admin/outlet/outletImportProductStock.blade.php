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
          <div class="panel-heading"><strong>Import Stock</strong></div>
          <div class="panel-body" style="overflow-x:auto;">
            <div class="panel panel-default">
              <div class="form-wrapper">
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-12">
                        <div class="default-value">
                          <label for="outlet">
                            [ <a href="{{route('outlet.downloadTemplateStock')}}">Download Template</a> ]
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                {!! Form::open(['url' => route('outlet.importProductStockView'), 'files'=>'true']) !!}
                  <div class="form-group">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-md-2">
                          <div class="form-label">
                            <label for="file-import">Choose File</label>
                          </div>
                        </div>
                        <div class="col-md-10">
                          {{ Form::file('file_import', array('class'=>'form-control','id'=>'file-import'))}}
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
                          {{ Form::submit('Submit', array('class'=>'btn btn-info', 'id'=>'btn-import')) }}
                          <a href="{{ route('outlet.listProductStock') }}" class="btn btn-default">Back</a>
                        </div>
                      </div>
                    </div>
                  </div>
                {{ Form::close() }}
                @if ($data)
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="form-label">
                          <label for="outlet">Confirmation</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        <span class="default-value">
                          <div class="table-responsive">
                            <table id="import-stock" class="display responsive nowrap" width="100%">
                              <thead>
                                <tr>
                                  <th>Nama Barang</th>
                                  <th>Stok Terakhir</th>
                                  <th>Stok Baru</th>
                                  <th>Satuan</th>
                                  <th>ID</th>
                                  <th>Batch</th>
                                </tr>
                              </thead>
                              <tbody>
                              @foreach ($data as $cell)
                                <tr>
                                  <td>{{ $cell['nama_barang'] }}</td>
                                  <td>{{ $cell['last_stock'] }}</td>
                                  <td>{{ $cell['stock'] }}</td>
                                  <td>{{ $cell['satuan'] }}</td>
                                  <td>{{ $cell['id'] }}</td>
                                  <td>{{ (string)$cell['batch'] }}</td>
                                </tr>
                              @endforeach
                              </tbody>
                            </table>
                          </div>
                          {!! Form::open(['url' => route('outlet.importProductStockProcess')]) !!}
                          {{ Form::hidden('data',$data) }}
                          {{ Form::submit('Execute', array('class'=>'btn btn-primary')) }}
                          {{ Form::close() }}
                        </span>
                      </div>
                    </div>
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
@endsection
@section('js')

<script src="//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
<script src="//cdn.datatables.net/responsive/2.2.0/js/dataTables.responsive.min.js"></script>
<link rel="stylesheet" href="//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="//cdn.datatables.net/responsive/2.2.0/css/responsive.dataTables.min.css">
<script src="{{ asset('js/outletproduct.js') }}"></script>

@endsection
