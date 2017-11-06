@extends('layouts.home')

@section('content')
	<link rel="stylesheet" href="{{ URL::to('css/login.css') }}">
	<div class="container">
		<div class="row">
			<div class="container">
				@if($status= Session::get('status'))
					<div class="alert alert-info">
						{{$status}}
					</div>
				@endif
				@if($errors->has('credentials'))
					<div class="alrt alert-info">
						{{ $errors->first('credentials') }}
					</div>
				@endif
				
				<style>
					.imgcontainer {
					text-align: center;
					margin: 24px 0 12px 0;
					}
					img.avatar {
					width: 50%;
					border-radius: 0%;
					}
				</style>
				
				<div class="card card-container">
					<p id="profile-name" class="profile-name-card"><strong>@lang('label.login')</strong></p>
					<hr>
						<div class="imgcontainer">
							<img src="{{ URL::to('img/1.png') }}" alt="Avatar" class="avatar">
						</div>
						
					<form class="form-horizontal" role="form" method="POST" action="{{ route('login') }}">
						{{ csrf_field() }}
						<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
							<input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="@lang('label.email')" required autofocus>
							@if ($errors->has('email'))
								<span class="help-block">
									<strong>{{ $errors->first('email') }}</strong>
								</span>
							@endif
						</div>
						
						<div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
							<input id="password" type="password" class="form-control" name="password" placeholder="@lang('label.password')" required>
							@if ($errors->has('password'))
								<span class="help-block">
									<strong>{{ $errors->first('password') }}</strong>
								</span>
							@endif
						</div>
						
						<div class="form-group">
							<div id="remember" class="checkbox">
								<label>
									<input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>@lang('label.remember')
								</label>
								<a class="btn btn-link" href="{{ route('password.request') }}">
									@lang('label.forgotpassword')
								</a>
							</div>
						</div>
						
						<div class="form-group">
							<button type="submit" class="btn btn-lg btn-primary btn-block btn-signin">
								@lang('label.login')
							</button>
							<a class="btn btn-regis" href="{{ url('/register') }}">
								@lang('label.donthaveaccount')
							</a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
@endsection