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
        <th>Action</th>
        <th>Done By</th>
        <th>Role</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      @foreach($dpl as $history)
      <tr>
        <td>{{ $history->type }}</td>
        <td>{{ $history->done_by }}</td>
        <td>{{ $history->done_by }}</td>
        <td>{{ $history->created_at }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
@endsection
@section('js')

<script src="{{ asset('js/dpl.js') }}"></script>

@endsection
