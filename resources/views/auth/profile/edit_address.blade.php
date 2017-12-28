@extends('layouts.navbar_product')

@section('content')
<div class="container">
	@if($status= Session::get('message'))
	  <div class="alert alert-info">
	      {{$status}}
	  </div>
	@endif
	<legend><strong>@lang('label.address')</strong></legend>

<div class="row">

    <div class="col-md-4">
      <form class="form-horizontal" role="form" method="post" action="{{route('profile.edit_address',$site->id)}}"autocomplete="off">
				{{ csrf_field() }}
				{{method_field('PATCH')}}
        <fieldset>
        <!-- Text input-->
				<div class="container">
          <div class="form-group{{ $errors->has('fungsi') ? ' has-error' : '' }}">
            <label class="col-sm-2 control-label" for="textinput">@lang('label.function')</label>
            <div class="col-md-10">
              <select name="fungsi" class="form-control {{ $errors->has('fungsi') ? ' has-error' : '' }}">
                <option value="">......</option>
                <option value="SHIP_TO" {{$site->site_use_code=="SHIP_TO"?"selected=selected":""}}>@lang('shop.ShipTo')</option>
                <option value="BILL_TO" {{$site->site_use_code=="BILL_TO"?"selected=selected":""}}>@lang('shop.BillTo')</option>
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
              <textarea id="address" rows="3" placeholder="@lang('label.address')" class="form-control" name="address" value="#" required>{{$site->address1}}</textarea>
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
						<label for="province" class="col-sm-2 control-label">@lang('label.province')</label>

						<div class="col-sm-10">
								<!--<input type="text" data-provide="typeahead"  id = "province-typeahead" name="province" value="{{ $site->province }}" class="form-control">
								<input type="hidden" id="province-id" value="">-->
								<select name="province" class="form-control" id="province" onchange="getListCity(this.value,{{old('city')}})" required>
									<option value="">--</option>
									@foreach($provinces as $province)
										@if($site->province_id==$province->id)
											<option selected='selected' value="{{$province->id}}">{{$province->name}}</option>
										@else
											<option value="{{$province->id}}">{{$province->name}}</option>
										@endif
									@endforeach
								</select>
						</div>
					</div>
			</div>

				<div class="container">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="textinput">@lang('label.city_regency')</label>
            <div class="col-sm-10">
							<select name="city" class="form-control" id="city" onchange="getListDistrict(this.value,{{$site->district_id}})" required>
								<option value="">--</option>
							</select>
							@if ($errors->has('district'))
									<span class="help-block">
											<strong>{{ $errors->first('district') }}</strong>
									</span>
							@endif
              <!--<input type="text" data-provide="typeahead" id="city-name" name="city" value="{{ $site->city }}" placeholder="@lang('label.city')" class="form-control">-->
            </div>
          </div>
				</div>

				<div class="container">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="textinput">@lang('label.subdistrict')</label>
            <div class="col-sm-10">
							<select name="district" class="form-control" id="district" onchange="getListSubdistrict(this.value,{{$site->state_id}})" required>
								<option value="">--</option>
							</select>
							@if ($errors->has('district'))
									<span class="help-block">
											<strong>{{ $errors->first('district') }}</strong>
									</span>
							@endif
              <!--<input type="text" name="district" value="{{ $site->district }}" placeholder="@lang('label.regency')" class="form-control">-->
            </div>
          </div>
				</div>

				<div class="container">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="textinput">@lang('label.urban_village')</label>
            <div class="col-sm-10">
              <!--<input type="text" name="state" placeholder="@lang('label.state')" class="form-control" value="{{ $site->state }}">-->
							<select name="state" class="form-control" id="subdistricts">
								<option value="">--</option>
							</select>
							@if ($errors->has('state'))
									<span class="help-block">
											<strong>{{ $errors->first('subdistricts') }}</strong>
									</span>
							@endif
            </div>
          </div>
				</div>

				<div class="container">
          <div class="form-group{{ $errors->has('postalcode') ? ' has-error' : '' }}">
            <label class="col-sm-2 control-label" for="textinput">@lang('label.postalcode')</label>
            <div class="col-sm-10">
              <input type="text" name="postalcode" placeholder="@lang('label.postalcode')" class="form-control" value="{{ $site->postalcode }}" required>
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
              	<button type="submit" name="edit" class="btn btn-success">Edit</button>
								<input type="hidden" name="prevpage" value="{{is_null($prevpage)?URL::previous():$prevpage}}">
                <a href="{{is_null($prevpage)?URL::previous():$prevpage}}"><button type="button" name="cancel" class="btn btn-danger">@lang('label.cancel')</button></a>
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
@section('js')
<script src="{{ asset('js/register.js') }}"></script>
<script>
    $(document).ready(function() {
			getListCity({{is_null($site->province_id)?0:$site->province_id}},{{is_null($site->city_id)?0:$site->city_id}});
      getListDistrict({{is_null($site->city_id)?0:$site->city_id}},{{is_null($site->district_id)?0:$site->district_id}});
      getListSubdistrict({{is_null($site->district_id)?0:$site->district_id}},{{is_null($site->state_id)?0:$site->state_id}});
    });
</script>
@endsection
