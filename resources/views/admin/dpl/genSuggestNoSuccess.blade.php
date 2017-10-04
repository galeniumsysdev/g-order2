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

  {!! Form::open(['url' => '/dpl/suggestno/generate']) !!}
  <div class="container text-center">
    No. Usulan Anda adalah
    <h1>{{ $suggest_no }}</h1>
  </div>
  {{ Form::close() }}
@endsection
@section('js')

<!-- <script src="{{ asset('js/myproduct.js') }}"></script> -->

@endsection
