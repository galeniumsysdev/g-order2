@extends('layouts.tempAdminSB')
@section('content')
<div class="row">
  <div id="pesan">
    @if($status= Session::get('message'))
    <div class="alert alert-info">
      {{$status}}
    </div>
    @endif
  </div>
  <div class="panel panel-default">
      <div class="panel-heading">Distributor Cabang</div>
      <div class="panel-body">
          <form action="{{route('usercabang.update',$customer->id)}}" class="form-horizontal" method="post" role="form">
            {{method_field('PATCH')}}
            {{ csrf_field() }}
            <input type="hidden" name="siteid" value="{{$alamat->id}}">
            <div class="form-group">
              <label class="control-label col-sm-2" for="name">Distributor Pusat :</label>
              <div class="col-sm-10">
                <input type="text"  class="form-control" name = "dist_pusat" value="{{$customer->pusat->customer_name}}" readonly>
              </div>
            </div>
            <div class="form-group{{ $errors->has('customer_name') ? ' has-error' : '' }}">
              <label class="control-label col-sm-2" for="name">@lang('label.outlet') :</label>
              <div class="col-sm-10">
                <input type="text"  class="form-control" name = "customer_name" value="{{$customer->customer_name}}">
                @if ($errors->has('customer_name'))
                    <span class="help-block">
                        <strong>{{ $errors->first('customer_name') }}</strong>
                    </span>
                @endif
              </div>
            </div>
            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
              <label class="control-label col-sm-2" for="email">@lang('label.email') :</label>
              <div class="col-sm-10">
                <input type="text" class="form-control disabled" name="email" value="{{ old('email')?old('email'):$customer->user->email}}">
                @if ($errors->has('email'))
                    <span class="help-block">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                @endif
              </div>
            </div>
            <div class="form-group{{ $errors->has('address') ? ' has-error' : '' }}">
                <label for="outlet" class="col-sm-2 control-label">*@lang('label.address')</label>

                <div class="col-sm-8">
                    <textarea id="address" rows="3" class="form-control" name="address" align="left" required>
                      @if($alamat){{ trim($alamat->address1) }}@endif
                    </textarea>

                    @if ($errors->has('address'))
                        <span class="help-block">
                            <strong>{{ $errors->first('address') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group{{ $errors->has('province') ? ' has-error' : '' }}">
              <label for="province" class="col-sm-2 control-label">*@lang('label.province')</label>

              <div class="col-sm-8">
                  <select name="province" class="form-control" id="province" onchange="getListCity(this.value,{{$alamat?$alamat->city_id:''}})" required>
                    <option value="">--</option>
                    @foreach($provinces as $province)
                      @if($alamat)
                        @if($alamat->province_id==$province->id)
                          <option selected='selected' value="{{$province->id}}">{{$province->name}}</option>
                        @else
                          <option value="{{$province->id}}">{{$province->name}}</option>
                        @endif
                      @else
                        <option value="{{$province->id}}">{{$province->name}}</option>
                      @endif
                    @endforeach
                  </select>

                  @if ($errors->has('province'))
                      <span class="help-block">
                          <strong>{{ $errors->first('province') }}</strong>
                      </span>
                  @endif
              </div>
            </div>

            <div class="form-group{{ $errors->has('city') ? ' has-error' : '' }}">
                <label for="city" class="col-sm-2 control-label">*@lang('label.city_regency')</label>

                <div class="col-sm-8">
                  <select name="city" class="form-control" id="city" onchange="getListDistrict(this.value,{{$alamat?$alamat->district_id:''}})" required>
                    <option value="">--</option>
                    @foreach($regencies as $reg)
                    <option value="{{$reg->id}}" {{ ($reg->id==$alamat->city_id)?'selected=selected':''}}>{{$reg->name}}</option>
                    @endforeach
                  </select>
                    <!--<input id="city" type="city" class="form-control" name="city" value="{{ old('city') }}" required>-->

                    @if ($errors->has('city'))
                        <span class="help-block">
                            <strong>{{ $errors->first('city') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group{{ $errors->has('district') ? ' has-error' : '' }}">
                <label for="district" class="col-sm-2 control-label">*@lang('label.subdistrict')</label>

                <div class="col-sm-8">
                    <!--<input id="regency" type="regency" class="form-control" name="regency" value="{{ old('regency') }}" required>-->
                    <select name="district" class="form-control" id="district" onchange="getListSubdistrict(this.value,{{$alamat?$alamat->state_id:''}})" required>
                      <option value="">--</option>
                      @foreach($districts as $d)
                      <option value="{{$d->id}}" {{ ($d->id==$alamat->district_id)?'selected=selected':''}}>{{$d->name}}</option>
                      @endforeach
                    </select>

                    @if ($errors->has('district'))
                        <span class="help-block">
                            <strong>{{ $errors->first('district') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group{{ $errors->has('subdistricts') ? ' has-error' : '' }}">
                <label for="subdistricts" class="col-sm-2 control-label">*@lang('label.urban_village')</label>

                <div class="col-sm-8">
                  <select name="subdistricts" class="form-control" id="subdistricts" required>
                    <option value="">--</option>
                    @foreach($villages as $v)
                    <option value="{{$v->id}}" {{ ($v->id==$alamat->state_id)?'selected=selected':''}}>{{$v->name}}</option>
                    @endforeach
                  </select>
                    <!--<input id="districts" type="districts" class="form-control" name="districts" value="{{ old('districts') }}">-->

                    @if ($errors->has('subdistricts'))
                        <span class="help-block">
                            <strong>{{ $errors->first('subdistricts') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group{{ $errors->has('postal_code') ? ' has-error' : '' }}">
                <label for="postal_code" class="col-sm-2 control-label">@lang('label.postalcode')</label>

                <div class="col-sm-8">
                    <input id="postal_code" type="postal_code" class="form-control" name="postal_code" value="{{ old('postal_code')?old('postal_code'):$alamat->postalcode }}">

                    @if ($errors->has('postal_code'))
                        <span class="help-block">
                            <strong>{{ $errors->first('postal_code') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group{{ $errors->has('role') ? ' has-error' : '' }}">
                <label for="role" class="col-sm-2 control-label">Role</label>

                <div class="col-sm-8">
                    <select class="form-control" name="role">
                      <option value="">--</option>
                      @foreach($roles as $r)
                      <option value="{{$r->id}}" {{in_array($r->id,$customer->user->roles->pluck('id')->toArray())?'selected':''}}>{{$r->display_name}}</option>
                      @endforeach
                    </select>

                    @if ($errors->has('role'))
                        <span class="help-block">
                            <strong>{{ $errors->first('role') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

          <div class="form-group">
              <div class="col-sm-12">
                  <button type="submit" class="btn btn-primary">
                      @lang('label.save')
                  </button>
              </div>
          </div>
          </form>
      </div>
  </div>
</div>
@endsection
@section('js')
<script src="{{ asset('js/register.js') }}"></script>
<script type="text/javascript">
@if($alamat)
$(document).ready(function() {
/*  getListCity({{is_null($alamat->province_id)?0:$alamat->province_id}},{{is_null($alamat->city_id)?0:$alamat->city_id}});
  getListDistrict({{is_null($alamat->city_id)?0:$alamat->city_id}},{{is_null($alamat->district_id)?0:$alamat->district_id}});
  getListSubdistrict({{is_null($alamat->district_id)?0:$alamat->district_id}},{{is_null($alamat->state_id)?0:$alamat->state_id}});*/
});
@endif
</script>
@endsection
