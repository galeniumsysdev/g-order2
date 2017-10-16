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
          <div class="panel-heading"><strong>Upload Product</strong></div>
          <div class="panel-body" style="overflow-x:auto;">
            <div class="panel panel-default">
              <div class="dpl-form-wrapper">
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="dpl-form-label">
                          <label for="outlet">Choose File</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        <span class="dpl-default-value">
                          {!! Form::open(['url' => '/outlet/product/import/view', 'files'=>'true']) !!}
                          {{ Form::file('file_import', array('class'=>'form-control','id'=>'file-import'))}}
                          {{ Form::submit('Submit', array('class'=>'btn btn-info', 'id'=>'btn-import')) }}
                          {{ Form::close() }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
                @if ($data)
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="dpl-form-label">
                          <label for="outlet">Confirmation</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        <span class="dpl-default-value">
                          <table class="table table-striped table-hover table-dpl">
                            <tr>
                              <th>Title</th>
                              <th>Satuan</th>
                              <th>Harga</th>
                            </tr>
                          @foreach ($data as $cell)
                            <tr>
                              <td>{{ $cell['title'] }}</td>
                              <td>{{ $cell['unit'] }}</td>
                              <td>{{ $cell['price'] }}</td>
                            </tr>
                          @endforeach
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

<script src="{{ asset('js/dpl.js') }}"></script>

@endsection
