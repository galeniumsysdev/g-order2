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
@endsection
@section('js')
<!--<script src="//code.jquery.com/jquery-1.12.3.js"></script>-->
<script src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<script
    src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
<link rel="stylesheet"
    href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css">
    <script>
      $(document).ready(function() {
        $('#table').DataTable();
    } );
     </script>
@endsection
