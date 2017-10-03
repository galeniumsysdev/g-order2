@extends('layouts.home')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-primary">
                <div class="panel-heading panel-blue">@lang('label.register')</div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form" method="POST" action="{{ route('register') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label for="name" class="col-sm-4 control-label">@lang('label.outlet')</label>

                            <div class="col-sm-6">
                                <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required autofocus>

                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-sm-4 control-label">@lang('label.email')</label>

                            <div class="col-sm-6">
                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

						<!--<div class="form-group{{ $errors->has('outlet') ? ' has-error' : '' }}">
                            <label for="outlet" class="col-md-4 control-label">Outlet</label>

                            <div class="col-md-6">
                                <input id="outlet" type="outlet" class="form-control" name="outlet" value="{{ old('outlet') }}" required>

                                @if ($errors->has('outlet'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('outlet') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>-->

						<div class="form-group{{ $errors->has('address') ? ' has-error' : '' }}">
                            <label for="outlet" class="col-sm-4 control-label">@lang('label.address')</label>

                            <div class="col-sm-6">
                                <textarea id="address" rows="5" class="form-control" name="address" required>{{ old('address') }}</textarea>

                                @if ($errors->has('address'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('address') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

						<div class="form-group{{ $errors->has('city') ? ' has-error' : '' }}">
                            <label for="city" class="col-sm-4 control-label">@lang('label.city')</label>

                            <div class="col-sm-6">
                                <input id="city" type="city" class="form-control" name="city" value="{{ old('city') }}" required>

                                @if ($errors->has('city'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('city') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

						<div class="form-group{{ $errors->has('districts') ? ' has-error' : '' }}">
                            <label for="districts" class="col-sm-4 control-label">@lang('label.state')</label>

                            <div class="col-sm-6">
                                <input id="districts" type="districts" class="form-control" name="districts" value="{{ old('districts') }}">

                                @if ($errors->has('districts'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('districts') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

						<div class="form-group{{ $errors->has('postal_code') ? ' has-error' : '' }}">
                            <label for="postal_code" class="col-sm-4 control-label">@lang('label.postalcode')</label>

                            <div class="col-sm-6">
                                <input id="postal_code" type="postal_code" class="form-control" name="postal_code" value="{{ old('postal_code') }}" required>

                                @if ($errors->has('postal_code'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('postal_code') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

						<div class="form-group{{ $errors->has('contact_person') ? ' has-error' : '' }}">
                            <label for="contact_person" class="col-sm-4 control-label">@lang('label.cp')</label>

                            <div class="col-sm-6">
                                    <!--<div class="col-xs-2 country-code">
                                        <input id="contact_person" type="contact_person" class="form-control" name="contact_person" fieldset disabled placeholder="+62" required>
                                    </div>
                                    <div class="col-md-6">-->
                                        <input id="contact_person" type="contact_person" class="form-control" name="contact_person" value="{{ old('contact_person') }}">
                                        <!--</div>
                                    </div>-->

                                @if ($errors->has('contact_person'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('contact_person') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

						<div class="form-group{{ $errors->has('HP_1') ? ' has-error' : '' }}">
                            <label for="HP_1" class="col-sm-4 control-label">@lang('label.hp1')</label>

                            <div class="col-sm-6">
                                <div class="row">
                                    <div class="col-xs-2 country-code">
                                        <input id="HP_1" type="HP_1" class="form-control" name="ext_HP1" fieldset disabled placeholder="+62" required>
                                    </div>
                                    <div class="col-xs-10">
                                        <input id="HP_1" type="HP_1" class="form-control" name="HP_1" value="{{ old('HP_1') }}">
                                    </div>
                                </div>

                                @if ($errors->has('HP_1'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('HP_1') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

						<div class="form-group{{ $errors->has('HP_2') ? ' has-error' : '' }}">
                            <label for="HP_2" class="col-sm-4 control-label">@lang('label.hp2')</label>

                            <div class="col-sm-6">
                                <div class="row">
                                    <div class="col-xs-2 country-code">
                                        <input id="HP_2" type="HP_2" class="form-control" name="ext_HP2" fieldset disabled placeholder="+62" required>
                                    </div>
                                    <div class="col-xs-10">
                                        <input id="HP_2" type="HP_2" class="form-control" name="HP_2" value="{{ old('HP_2') }}">
                                    </div>
                                </div>

                                @if ($errors->has('HP_2'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('HP_2') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

						<div class="form-group{{ $errors->has('no_tlpn') ? ' has-error' : '' }}">
                            <label for="no_tlpn" class="col-sm-4 control-label">@lang('label.phone')</label>

                            <div class="col-sm-6">
                                <div class="row">
                                    <div class="col-xs-2 country-code">
                                        <input id="no_tlpn" type="no_tlpn" class="form-control" name="extnotelp" fieldset disabled placeholder="+62" required>
                                    </div>
                                    <div class="col-xs-10">
                                        <input id="no_tlpn" type="no_tlpn" class="form-control" name="no_tlpn" value="{{ old('no_tlpn') }}">
                                    </div>
                                </div>

                                @if ($errors->has('no_tlpn'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('no_tlpn') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

						<div class="form-group{{ $errors->has('NPWP') ? ' has-error' : '' }}">
                            <label for="NPWP" class="col-sm-4 control-label">@lang('label.npwp')</label>

                            <div class="col-sm-6">
                                <input id="NPWP" type="NPWP" class="form-control" name="NPWP" value="{{ old('NPWP') }}">

                                @if ($errors->has('NPWP'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('NPWP') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

						            <div class="form-group{{ $errors->has('kategori') ? ' has-error' : '' }}">
                            <label for="kategori" class="col-sm-4 control-label">@lang('label.category')</label>

                            <div class="col-sm-6">
                							<select class="form-control" name="category" required>
                                @forelse($categories as $category)
                                  @if(old('category')==$category->name)
                                    <option selected='selected' value="{{$category->id}}">{{$category->name}}</option>
                                  @else
                  								  <option value="{{$category->id}}">{{$category->name}}</option>
                                  @endif
                                @empty
                                <tr><td colspan="4">No Category</td></tr>
                                @endforelse
							                </select>
                                @if ($errors->has('kategori'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('kategori') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('psc') ? ' has-error' : '' }}">
                            <label for="PSY" class="col-sm-4 control-label">@lang('label.needproduct')</label>

                            <div class="col-sm-6">
                                <div class="checkbox">
                                  <label>
                                    @if( old('psc')=="1")
                                      <input type="checkbox" id="blankCheckbox" value="1" name="psc" checked="checked">
                                    @else
                                      <input type="checkbox" id="blankCheckbox" value="1" name="psc">
                                    @endif
                                    <strong> PSC
                                  </strong><div style="font-size:8pt">(Caladyne, Oilum, v-mina, Bellsoap, JFSulfur)</div></label>
                                </div>


                                <div class="checkbox">
                                  <label>
                                    @if( old('pharma')=="1")
                                      <input type="checkbox" id="blankCheckbox" value="1" name="pharma" checked="checked">
                                    @else
                                      <input type="checkbox" id="blankCheckbox" value="1" name="pharma">
                                    @endif
                                    <strong> NON-PSC/Pharma
                                  </strong></label>
                                </div>

                                @if ($errors->has('psc'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('psc') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-6 col-sm-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Register
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
<script>
    $(document).ready(function() {
      $('#name').keyup(function(){
        $("#name").val(($("#name").val()).toUpperCase());
      });
    });
</script>
@endsection
