@extends('layouts.tempAdminSB')
@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Organization Structure</h2>
        </div>
    </div>
</div>
{!! Form::open(['url' => '/Organization/'.$user_id.'/setting/save']) !!}
<table class="table" id="table">
  <tr>
    <td>Code</td>
    <td>:</td>
    <td>{{ Form::text('user_code', $org['user_code'], array('class'=>'form-control')) }}</td>
  </tr>
  <tr>
    <td>Role</td>
    <td>:</td>
    <td>{{ Form::select('role', $role_list, $org->role_id, array('class'=>'form-control','id'=>'role')) }}</td>
  </tr>
  <tr>
    <td>Email</td>
    <td>:</td>
    <td>
      <div class="input-group sm-12">
        {{ Form::text('email',$org->email, array('class'=>'form-control','id'=>'email-spv','autocomplete'=>'off','aria-describedby'=>"change-email")) }}
        <span class="input-group-addon" id="basic-addon2"><i class="fa fa-times" aria-hidden="true"></i></span>
      </div>      
    {{Form::hidden('user_id',$org->user_id, array('class'=>'form-control','id'=>'user-id')) }}
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
    <td>{{ Form::text('user_name',$org->description, array('class'=>'form-control','id'=>'user-name','required'=>'required')) }}
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
    <td>{{ Form::select('directsup', $users_sup_list, $org['directsup_user_id'], array('class'=>'form-control')) }}</td>
  </tr>
  <tr>
    <td colspan="3">
      {{ Form::submit('Save',array('class'=>'btn btn-primary')) }}
      <a href="/Organization" class="btn btn-default">Back</a>
    </td>
  </tr>
</table>
<div class="input-group">
  <input type="text" class="form-control" placeholder="Recipient's username" aria-describedby="basic-addon2">
  <span class="input-group-addon" id="basic-addon2">@example.com</span>
</div>
@endsection
@section('js')
<script src="{{ asset('js/bootstrap3-typeahead.min.js') }}"></script>
<script src="{{ asset('js/ui/1.12.1/jquery-ui.js') }}"></script>
<script src="{{ asset('js/org.js') }}"></script>
@endsection
