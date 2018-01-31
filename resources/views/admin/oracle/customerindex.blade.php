@extends('layouts.tempAdminSB')
@section('content')
	<div class="row">
	    <div class="col-lg-12 margin-tb">
	        <div class="pull-left">
	            <h2>Customer Oracle</h2>
	        </div>
					<div class="pull-right">
	            <a href="{{route('oracle.synchronize.customer')}}" target="_blank" class="btn btn-success">Synchronize Customer Oracle</a>	            
	        </div>
	    </div>
	</div>
	@if ($message = Session::get('success'))
		<div class="alert alert-success">
			<p>{{ $message }}</p>
		</div>
	@endif
	<table  class="table" id="table">
    <thead>
  		<tr>
        <th>Customer Number</th>
  			<th>Customer Name</th>
  			<th>Email</th>
  			<th>Roles</th>
				<th>Register</th>
				<th>Invite Email</th>
  			<th>Action</th>
  		</tr>
    </thead>
    <tbody>
    	@foreach ($customers as $cust)
    	<tr>
        <td>{{ $cust->customer_number }}</td>
    		<td>{{ $cust->customer_name }}</td>
    		@if($cust->users->count()>0)
          <td>{{$cust->user->email}}</td>
          <td>@if(!empty($cust->user->roles))
    				@foreach($cust->user->roles as $v)
    					<label class="label label-success">{{ $v->display_name }}</label>
    				@endforeach
    			@endif</td>
					<td>
						@if($cust->user->register_flag)
							Y
						@else
							N
				 		@endif
			    </td>
					<td>
						@if($cust->user->validate_flag)
							Y
						@else
							N
						@endif
					</td>
        @else
          <td>-</td>
          <td>-</td>
					<td>-</td>
					<td>-</td>
        @endif

    		<td>
    			<a class="btn btn-info" href="{{route('useroracle.show',$cust->id)}}">Show</a>
    		</td>
    	</tr>
    	@endforeach
    </tbody>
	</table>

@endsection
@section('js')
<script src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<script
    src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>

<link rel="stylesheet"
    href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css">
    <script>
      $(document).ready(function() {
        $('#table').DataTable({
          'columnDefs' : [
                            {
                               'searchable'    : true,
                               'targets'       : [0,1,2,3]
                            },{
                               'orderable'    : true,
                               'targets'       : [0,1,2,3]
                            }
                        ]
        });
    } );
     </script>
@endsection
