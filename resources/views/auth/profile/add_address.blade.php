@extends('layouts.navbar_product')

@section('content')
<div class="container">
	<legend><strong>@lang('label.addaddress')</strong></legend>

<div class="row">

    <div class="col-md-4">
      <form class="form-horizontal" role="form" method="post" action="{{route('profile.address')}}">
				{{ csrf_field() }}
        <fieldset>
        <!-- Text input-->
				<div class="container">
          <div class="form-group{{ $errors->has('fungsi') ? ' has-error' : '' }}">
            <label class="col-sm-2 control-label" for="textinput">@lang('label.function')</label>
            <div class="col-md-10">
              <select name="fungsi" class="form-control {{ $errors->has('fungsi') ? ' has-error' : '' }}">
                <option value="">......</option>
                <option value="ShipTo" {{old('fungsi')=="ShipTo"?"selected=selected":""}}>@lang('shop.ShipTo')</option>
                <option value="BillTo" {{old('fungsi')=="BillTo"?"selected=selected":""}}>@lang('shop.BillTo')</option>
              </select>
							@if ($errors->has('fungsi'))
									<span class="help-block with-errors">
											<strong>{{ $errors->first('fungsi') }}</strong>
									</span>
							@endif
            </div>
          </div>
				</div>

          <!-- Text input-->
				<div class="container">
          <div class="form-group {{ $errors->has('address') ? ' has-error' : '' }}">
            <label class="col-sm-2 control-label" for="textinput">@lang('label.address')</label>
            <div class="col-sm-10">
              <textarea id="address" rows="3" placeholder="@lang('label.address')" class="form-control" name="address" value="#" required>{{ old('address') }}</textarea>
							@if ($errors->has('address'))
									<span class="help-block with-errors">
											<strong>{{ $errors->first('address') }}</strong>
									</span>
							@endif
            </div>
          </div>
				</div>

				<div class="container">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="textinput">@lang('label.city')</label>
            <div class="col-sm-10">
              <input type="text" name="city" value="{{ old('city') }}" placeholder="@lang('label.city')" class="form-control">
            </div>
          </div>
				</div>

				<div class="container">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="textinput">@lang('label.state')</label>
            <div class="col-sm-10">
              <input type="text" name="state" placeholder="@lang('label.state')" class="form-control" value="{{ old('state') }}">
            </div>
          </div>
				</div>

				<div class="container">
          <div class="form-group{{ $errors->has('postalcode') ? ' has-error' : '' }}">
            <label class="col-sm-2 control-label" for="textinput">@lang('label.postalcode')</label>
            <div class="col-sm-10">
              <input type="text" name="postalcode" placeholder="@lang('label.postalcode')" class="form-control" value="{{ old('postalcode') }}" required>
							@if ($errors->has('postalcode'))
									<span class="help-block with-errors">
											<strong>{{ $errors->first('postalcode') }}</strong>
									</span>
							@endif
            </div>

          </div>
				</div>

          <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
              <div class="pull-right">
              	<button type="submit" name="add" class="btn btn-success">@lang('label.addaddress')</button>
                <a href="{{URL::previous()}}"><button type="button" name="cancel" class="btn btn-danger">@lang('label.cancel')</button></a>
              </div>
            </div>
          </div>

        </fieldset>
      </form>
    </div><!-- /.col-lg-12 -->
</div><!-- /.row -->
<div class="container">
	<legend></legend>
</div>
</div>

@endsection
