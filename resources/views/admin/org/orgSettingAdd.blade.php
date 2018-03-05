@extends('layouts.tempAdminSB')
@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Organization Structure</h2>
        </div>
    </div>
</div>
@if ($message = Session::get('success'))
  <div class="alert alert-success">
    <p>{{ $message }}</p>
  </div>
@endif
{!! Form::open(['url' => '/Organization/setting/add']) !!}
<table class="table" id="table">
  <tr>
    <td>Code</td>
    <td>:</td>
    <td>{{ Form::text('user_code','', array('class'=>'form-control','required'=>'required')) }}
      @if ($errors->has('user_code'))
          <span class="help-block">
              <strong>{{ $errors->first('user_code') }}</strong>
          </span>
      @endif
    </td>
  </tr>
  <tr>
    <td>Role</td>
    <td>:</td>
    <td>{{ Form::select('role', $role_list, '', array('class'=>'form-control','id'=>'role')) }}</td>
  </tr>
  <tr>
    <td>Email</td>
    <td>:</td>
    <td>
      <div class="input-group sm-12">
        {{ Form::text('email','', array('class'=>'form-control','id'=>'email-spv','autocomplete'=>'off')) }}
        <div class="input-group-append">
          <div class="input-group-text" id="change-email">
          <i class="fa fa-times" aria-hidden="true"></i>
        </div>
        </div>
      </div>
    {{Form::hidden('user_id','', array('class'=>'form-control','id'=>'user-id')) }}
    @if ($errors->has('email'))
        <span class="help-block">
            <strong>{{ $errors->first('email') }}</strong>
        </span>
    @endif
  </td>
  </tr>
  <tr>
    <td>Description</td>
    <td>:</td>
    <td>{{ Form::text('user_name','', array('class'=>'form-control','id'=>'user-name','required'=>'required')) }}
      @if ($errors->has('user_name'))
          <span class="help-block">
              <strong>{{ $errors->first('user_name') }}</strong>
          </span>
      @endif
    </td>
  </tr>
  <tr>
    <td>Direct Supervisor</td>
    <td>:</td>
    <td>{{ Form::select('directsup', $users_sup_list, '', array('class'=>'form-control')) }}</td>
  </tr>
  <tr>
    <td colspan="3">
      {{ Form::submit('Save',array('class'=>'btn btn-primary')) }}
      <a href="/Organization" class="btn btn-default">Back</a>
    </td>
  </tr>
</table>
@endsection
@section('js')
<script src="{{ asset('js/bootstrap3-typeahead.min.js') }}"></script>
<script src="{{ asset('js/ui/1.12.1/jquery-ui.js') }}"></script>
<script src="{{ asset('js/org.js') }}"></script>
@endsection
