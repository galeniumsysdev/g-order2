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
            <input type="hidden" value="{{$customer->id}}" id="customer_id">
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
            <div class="tabcard">
              <ul class="nav nav-tabs" role="tablist">
                  <li role="presentation" class="active"><a href="#address" aria-controls="Address" role="tab" data-toggle="tab">@lang('label.address')</a></li>
                  <li role="presentation"><a href="#mapping_distributor" aria-controls="mapping_distributor" role="tab" data-toggle="tab">Mapping Distributor</a></li>
              </ul>
              <div class="tab-content">
                  <div role="tabpanel" class="tab-pane active" id="address">
                    <br>
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
                          <button type="submit" name="save" value="update" class="btn btn-primary">
                              @lang('label.save')
                          </button>
                      </div>
                  </div>
                  </div>
                  <div role="tabpanel" class="tab-pane" id="mapping_distributor">
                    <br>
                    <div class="table-responsive">
                      <table id="mapping-table" class="display responsive"  width="100%">
                        <thead>
                          <tr>
                            <th width="15px"><input type="checkbox" name="all" id="check-all">All</th>
                            <th width="45%">Tipe</th>
                            <th width="50%">Value</th>
                          </tr>
                        </thead>
                        <tbody>

                        </tbody>
                      </table>                      
                      <div class="pull">
                          <button type="button" class="btn btn-sm btn-success"  class="add-mapping" data-toggle="modal" data-target="#addMapping"> Add New Mapping</button>
                          <button class="btn btn-sm btn-danger" name="action_mapping" value="delete">Delete</button>
                        <!--  <a href="{{route('customer.mappingOutlet',$customer->id)}}" target="_blank" class="btn btn-sm btn-primary">Preview Outlet</a>-->
                      </div>
                    </div>
                  </div>
              </div>
            </div>
          </form>
      </div>
  </div>
</div>
<div class="modal fade" id="addMapping" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form data-toggle="validator" id="frm-addmapping"  method="POST">
          {{csrf_field()}}
          <input type="hidden" value="{{$customer->id}}" name="customerid">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
          <h4 class="modal-title" id="myModalLabel">Add New Mapping</h4>
        </div>
        <div class="modal-body">
          <span id="form_output"></span>
          <div class="form-group">
            <label class="control-label" for="title">Tipe:</label>
            <select name="type" id="mapping-type" class="form-control" onchange="getvaluemapping()">
              <option value="-">Pilih Salah Satu</option>
              <option value="regencies">Regencies</option>
              <option value="category_outlets">Category Outlet</option>
            </select>
            <div class="help-block with-errors"></div>
          </div>
          <div class="form-group" id="province-div">
            <label class="control-label" for="title">Province:</label>
            <select name="Provinces" id="province-area" class="form-control" onchange="getvalueregencies()">
              <option value="-">Pilih Salah Satu</option>
              @foreach($provinces as $p)
              <option value="{{$p->id}}">{{$p->name}}</option>
              @endforeach
            </select>
            <div class="help-block with-errors"></div>
          </div>
          <div class="form-group">
            <label class="control-label" for="title">Value:</label>
            <select name="value[]" id="mapping-value" class="form-control" multiple>
            </select>
            <div class="help-block with-errors"></div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="form-group">
             <input type="hidden" name="button_action" id="button_action" value="" />
            <button type="submit" name="add" value="add" class="btn crud-submit btn-success">Submit</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
@section('js')

<script src="//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
<script src="//cdn.datatables.net/responsive/2.2.0/js/dataTables.responsive.min.js"></script>
<link rel="stylesheet" href="//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="//cdn.datatables.net/responsive/2.2.0/css/responsive.dataTables.min.css">
<script src="{{ asset('js/customeroracle.js') }}"></script>
<script src="{{ asset('js/register.js') }}"></script>
@endsection
