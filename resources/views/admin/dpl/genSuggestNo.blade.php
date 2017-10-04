<!-- 
/**
* created by WK Productions
*/ 
-->
@extends('layouts.navbar_product')
@section('content')
  <link href="{{ asset('css/table.css') }}" rel="stylesheet">
  @if($status= Session::get('msg'))
    <div class="alert alert-info">
        {{$status}}
    </div>
  @endif

  {!! Form::open(['url' => '/dpl/suggestno/generate', 'id'=>'generate-sugg-no-form']) !!}
  <div class="container">
    <table class="col-xs-12">
      <tr>
        <td>Outlet</td>
        <td>:</td>
        <td>{{ Form::select('outlet',$outlet_list,0,array('class'=>'form-control','id'=>'outlet')) }}</td>
      </tr>
      <tr>
        <td>Distributor</td>
        <td>:</td>
        <td>{{ Form::select('distributor',$distributor_list,0,array('class'=>'form-control','id'=>'distributor')) }}</td>
      </tr>
      <tr>
        <td colspan="3">
          {{ Form::submit('Generate',array('class'=>'btn btn-primary')) }}
        </td>
      </tr>
    </table>
  </div>
  {{ Form::close() }}
@endsection
@section('js')

<script src="{{ asset('js/dpl.js') }}"></script>

@endsection
