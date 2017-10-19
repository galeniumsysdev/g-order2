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
          <div class="panel-heading"><strong>Upload Product</strong></div>
          <div class="panel-body" style="overflow-x:auto;">
            <div class="panel panel-default">
              <div class="form-wrapper">
                {!! Form::open(['url' => '/outlet/product/import/view', 'files'=>'true']) !!}
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
                            <table id="import-product" class="table table-striped table-hover table-center-header">
                              <thead>
                                <tr>
                                  <th>Title</th>
                                  <th>Satuan</th>
                                  <th>Harga</th>
                                </tr>
                              </thead>
                              <tbody>
                              @foreach ($data as $cell)
                                <tr>
                                  <td>{{ $cell['title'] }}</td>
                                  <td>{{ $cell['unit'] }}</td>
                                  <td>{{ number_format($cell['price'],0,',','.') }}</td>
                                </tr>
                              @endforeach
                              </tbody>
                            </table>
                            {!! Form::open(['url' => '/outlet/product/import/process']) !!}
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

<script src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<script
    src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
<link rel="stylesheet"
    href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css">
<script src="{{ asset('js/outletproduct.js') }}"></script>

@endsection