@extends('layouts.navbar_product')

@section('content')
<div class="container">
    <div class="row">
		<div id="pesan">
			@if($status= Session::get('message'))
			<div class="alert alert-info">
				{{$status}}
			</div>
			@endif
		</div>
		<div class="container">
        <div class="panel panel-primary">
			       <div class="panel-heading"><strong>CUSTOMER</strong></div>
                <div class="panel-body">
						            <form action="{{route('customer.update',$user->id)}}" class="form-horizontal" enctype="multipart/form-data" method="post" role="form">
            							{{csrf_field()}}
                          {{method_field('PATCH')}}
            							<input type="hidden" value="{{$user->id}}" id="user_id">
            							<input type="hidden" value="{{$notif_id}}" name="notif_id" id="notif_id">
            							<input type="hidden" value="{{url('/')}}" id="baseurl">
            							<input type="hidden" value="{{app()->getLocale()}}" id="language">
                          <div class="col-sm-1">
                            <a href="javascript:changeProfile()" style="text-decoration: none;" title="@lang('label.changeimage')">
                              @if(isset($user->avatar))
                                <img src="{{asset('/uploads/avatars/'.$user->avatar)}}" style="width:80px; height:80px; float:center; border-radius:50%; margin-left:15px; margin-right:15px; border-style: solid;" id="img_avatar">
                              @else
                                <img src="{{asset('/uploads/avatars/default.jpg')}}" style="width:80px; height:80px; float:center; border-radius:50%; margin-left:15px; margin-right:15px; border-style: solid;" id="img_avatar">
                              @endif
                            </a>
                              <input type="file" accept="image/*" name="avatar" id="fileavatar" style="display: none">

                          </div>
                          <div class="col-sm-11">
              							<div class="form-group">
                              <label class="control-label col-sm-2" for="name"><strong>@lang('label.outlet') :</strong></label>
              								<div class="col-sm-10">
              									<input type="text" class="form-control mb-8 mr-sm-8 mb-sm-4" name="name" id="name" value="{{ old('name')?old('name'):$customer->customer_name }}">
              									@if ($errors->has('name'))
              										<span class="help-block">
              											<strong>{{ $errors->first('name') }}</strong>
              										</span>
              									@endif
              								</div>
              							</div>

              							<div class="form-group">
              								<label class="control-label col-sm-2" for="email"><strong>@lang('label.email') :</strong></label>
              									<div class="col-sm-10">
              										<input type="text" class="form-control mb-8 mr-sm-8 mb-sm-4" name="email" id="name" value="{{ old('email')?old('email'):$customer_email }}" readonly>
              										@if ($errors->has('email'))
              										<span class="help-block">
              											<strong>{{ $errors->first('email') }}</strong>
              										</span>
              										@endif
              									</div>
              							</div>
                          </div>

            							<div class="tabcard col-sm-12">
                              <ul class="nav nav-tabs" role="tablist">
            			                <li role="presentation" class="active" ><a href="#personal" aria-controls="Personal" role="tab" data-toggle="tab"><strong>@lang('label.personal')</strong></a></li>
                                  <li role="presentation"><a href="#address" aria-controls="Address" role="tab" data-toggle="tab"><strong>@lang('label.address')</strong></a></li>
                                  <li role="presentation"><a href="#contact" aria-controls="Contact" role="tab" data-toggle="tab"><strong>@lang('label.contact')</strong></a></li>
                                  <li role="presentation" id="divdistributor"><a href="#distributor" aria-controls="distributor" role="tab" data-toggle="tab"><strong>@lang('label.distributor')</strong></a></li>

                              </ul>

                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane active" id="personal">
                                  <div class="form-group">
                                      <label class="control-label col-sm-2" for="npwp"><strong>@lang('label.npwp') :</strong></label>
                                      <div class="col-sm-10">
                                        <input type="text" class="form-control mb-8 mr-sm-8 mb-sm-4" name="npwp" id="npwp" value="{{ old('npwp')?old('npwp'):$customer->tax_reference }}">
                                      </div>
                                  </div>

                                  <div class="form-group">
  									                  <label class="control-label col-sm-2" for="status"><strong>@lang('label.needproduct') :</strong></label>
                                      <div class="col-sm-10">
                                          @if($customer->pharma_flag=="1")
                                          <input type="checkbox"  name="pharma_flag" id="pharma_flag" value="1" checked="checked"> Pharma (Non PSC)<br>
                                          @else
                                          <input type="checkbox"  name="pharma_flag" id="pharma_flag" value="1"> Pharma (Non PSC)<br>
                                          @endif
                                          @if($customer->psc_flag=="1")
                                          <input type="checkbox"  name="psc_flag" id="psc_flag" value="1" checked="checked"> PSC<br>
                                          @else
                                          <input type="checkbox"  name="psc_flag" id="psc_flag" value="1"> PSC<br>
                                          @endif
                                      </div>
                                  </div>

                                  <div class="form-group">
                                      <label for="role" class="control-label col-sm-2"><strong>Role :</strong></label>
                                      <div class="col-sm-10">
                                          <select class="form-control" name="role" id="role" required>
                                            <option value="">--</option>
                      											@foreach($roles as $role)
                        											@if(!is_null($roleid))
                        												<option value="{{$role->id}}" {{$roleid->role_id==$role->id?"selected=selected":""}}>{{$role->display_name}}</option>
                        											@else
                                                <option value="{{$role->id}}">{{$role->display_name}}</option>
                        											@endif
                      											@endforeach
                                          </select>
                    											@if ($errors->has('role'))
                    												<span class="help-block">
                    													<strong>{{ $errors->first('role') }}</strong>
                    												</span>
                    											@endif
                                      </div>
                                  </div>

                                  <div class="form-group" id="divkategori">
                                      <label for="kategori" class="control-label col-sm-2"><strong>@lang('label.category') :</strong></label>
                                      <div class="col-sm-10">
                                          <select class="form-control" name="category" required>
                      											@foreach($categories as $category)
                                                                  @if($customer->outlet_type_id==$category->id)
                      												<option selected='selected' value="{{$category->id}}">{{$category->name}}</option>
                                                                  @else
                      												<option value="{{$category->id}}">{{$category->name}}</option>
                                                                  @endif
                      											@endforeach
                                          </select>
                    											@if ($errors->has('kategori'))
                    												<span class="help-block">
                    													<strong>{{ $errors->first('kategori') }}</strong>
                    												</span>
                    											@endif
                    									</div>
                                  </div>
                                    @if($customer->psc_flag=="1")
                                      <div class="form-group" id="divkategoridc">
                                        <label for="kategori" class="control-label col-sm-2"><strong>@lang('label.categorydc') :</strong></label>
                                        <div class="col-sm-4">
                                            <select class="form-control" name="groupdc" id="groupdc" onchange="ubah('')" required >
                        											@forelse($groupdcs as $groupdc)
                                                                    @if($groupdc->id==$groupdcid)
                        												<option selected='selected' value={{ $groupdc->id }}>{{ $groupdc->display_name }}</option>
                                                                    @else
                        												<option value={{$groupdc->id}}>{{$groupdc->display_name}}</option>
                                                                    @endif
                        											@empty
                        												<tr><td colspan="4">...</td></tr>
                        											@endforelse
                                            </select>
                      											@if ($errors->has('groupdc'))
                      												<span class="help-block">
                      													<strong>{{ $errors->first('groupdc') }}</strong>
                      												</span>
                      											@endif
                                        </div>

                                        <div class="col-sm-6">
                                            <select class="form-control" name="subgroupdc" id="subgroupdc">

                                            </select>
                      											@if ($errors->has('subgroupdc'))
                      												<span class="help-block">
                      													<strong>{{ $errors->first('subgroupdc') }}</strong>
                      												</span>
                      											@endif
                                        </div>
                                    </div>
                                    @endif


                                    @if($customer->Status=="R")
                                    <div class="form-group" id="status">
										                    <label for="statue" class="control-label col-sm-2">Status: </label>
                                        <div class="col-sm-10"><p class="form-control">Tolak: {{$customer->keterangan}}</p></div>
                                    </div>
                                    @endif

                                    <div class="form-group" id="status">
                  										<div class="col-sm-12">
                                        @if($customer->Status!="A")
                  											<!--<button type="button" id="reject-customer" class="btn btn-warning" name="reject"><strong>@lang('label.reject')</strong></button>
                  											<button type="submit" id="edit-customer" class="btn btn-success" value="approve" name="save"><strong>@lang('label.approve')</strong></button>&nbsp;-->
                                        @endif
                                        <button type="submit" id="save-customer" class="btn btn-primary" value="save" name="save"><i class="fa fa-floppy-o" aria-hidden="true"></i>&nbsp; @lang('label.save')</button>&nbsp;&nbsp;
                  										</div>
                                    </div>


                                </div>

                                <div role="tabpanel" class="tab-pane" id="address">
									               <div id="no-more-tables">
              										<table id="alamat-table" class="table table-striped">
              											<thead>
              												<tr>
              													<th width="40%"><strong>@lang('label.address')</strong></th>
              													<th width="10%"><strong>@lang('label.city_regency')</strong></th>
              													<th width="10%"><strong>@lang('label.subdistrict')</strong></th>
              													<th width="5%"><strong>@lang('label.postalcode')</strong></th>
                                        <th width="10%"><strong>@lang('label.action')</strong></th>
              												</tr>
              											</thead>
              											<tbody>
              												@forelse($customer_sites as $customer_site)
              													<tr>
              														<td data-title="@lang('label.address')">{{$customer_site->address1}}</td>
              														<td data-title="@lang('label.city_regency')">{{$customer_site->city." "}}</td>
              														<td data-title="@lang('label.urban_village')">{{$customer_site->state." "}}</td>
              														<td data-title="@lang('label.postalcode')">{{$customer_site->postalcode." "}}</td>
                                          <td data-title="@lang('label.action')">
                                            <a class="btn btn-info btn-sm" href="{{route('profile.edit_address',$customer_site->id)}}"><span class="glyphicon glyphicon-pencil"></span></a>
                                            <a class="btn btn-info btn-sm" href="#" id="{{$customer_site->id}}"><i class="fa fa-trash-o"></i></a>
                                          </td>
              													</tr>
              												@empty
              													<tr><td colspan="4">@lang("label.notfound")</td></tr>
              												@endforelse
              											</tbody>
              										</table>
									               </div>

                                </div>

                                <div role="tabpanel" class="tab-pane" id="contact">

                									<div id="no-more-tables">
                										<table id="contact-table" class="table table-striped">
                											<thead>
                												<tr>
                													<th width="30%"><strong>@lang('label.cp')</strong></th>
                													<th width="20%"><strong>@lang('label.type')</strong></th>
                													<th width="30%"><strong>Data</strong></th>

                												</tr>
                											</thead>
                											<tbody>
                												@forelse($customer_contacts as $cc)
                													<tr>
                														<td data-title="@lang('label.cp')">
                                              @if(is_null($cc->contact_name))
                                                -
                                              @else
                                                {{$cc->contact_name}}
                                              @endif
                                            </td>
                														<td data-title="@lang('label.type')">{{$cc->contact_type}}</td>
                														<td data-title="Data">{{$cc->contact}}</td>

                													</tr>
                												@empty
                													<tr><td colspan="4">No data</td></tr>
                												@endforelse
                											</tbody>
                										</table>
                									</div>

                                </div>

                                <div role="tabpanel" class="tab-pane" id="distributor">
									                 <div class="form-group">
										                <div class="col-sm-12">
                											<table id="listdistributor" class="table table-striped">
                													<thead>
                														<tr>
                															<th width="50%">@lang('label.distributor')</th>
                                              <th width="50%">Status</th>
                															<!--<th width="10%">@lang('label.address')</th>
                															<th width="10%">@lang('label.city')</th>
                															<th width="5%">@lang('label.state')</th>-->
                														</tr>
                													</thead>
                												<tbody>
                													@foreach($distributors as $dist)
                														<tr>
                															<td>{{$dist->distributor_name}}</td>
                															<td>
                                                @if(is_null($dist->approval))
                                                  -
                                                @else
                                                  @if($dist->approval)
                                                    @lang("label.approve")
                                                  @else
                                                    @lang("label.reject") : {{$dist->keterangan}}
                                                  @endif
                                                @endif
                                              </td>
                															<!--<td></td>
                															<td></td>-->
                														</tr>
                													@endforeach
                												</tbody>
                											</table>
                                    </div>
									                 </div>

                                   <div class="form-group" id="divdistributor">
                                      <label for="distributor" class="control-label col-sm-2">@lang('label.distributor') :</label>
                    									<div class="col-sm-10">
                    										<input type="text" name="searchdistributor" id="search_text" class="form-control mb-8 mr-sm-8 mb-sm-4" placeholder="Search distributor">
                    									</div>
                                   </div>
                                   <div class="form-group">
    									                    <div class="col-sm-12" id="add_distributor">
                                          </div>
                                   </div>
                                </div>
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
<!--<script>
window.Laravel = {
               role: '{{Auth::user()->roles->first()->name}}',
            }
</script>-->
<script src="{{ asset('js/customer.js') }}"></script>
<script>
    $(document).ready(function() {
      ubah({{isset($customer->subgroup_dc_id)?$customer->subgroup_dc_id:0}});
      });
    function changeProfile()
    {
      $('#fileavatar').click();
    }
    function readURL(input) {
      if (input.files && input.files[0]) {
          var reader = new FileReader();
          reader.onload = function (e) {
              $('#img_avatar').attr('src', e.target.result);
          }
          reader.readAsDataURL(input.files[0]);
      }
    }
  /*  $('#psc_flag').click(function (event) {
      if(window.Laravel.role =="Marketing PSC")
      {
        if (this.checked) {
            $('#reject-customer').prop('disabled', false);
            $('#edit-customer').prop('disabled', false);
        } else {
          $('#reject-customer').prop('disabled', true);
          $('#edit-customer').prop('disabled', true);
        }
      }
    });
    $('#pharma_flag').click(function (event) {
      if(window.Laravel.role =="Marketing Pharma")
      {
        if (this.checked) {
            $('#reject-customer').prop('disabled', false);
            $('#edit-customer').prop('disabled', false);
        } else {
          $('#reject-customer').prop('disabled', true);
          $('#edit-customer').prop('disabled', true);
        }
      }
    });*/
    $("#fileavatar").change(function(){
      if ($(this).val() != '') {
          readURL(this);
      }
    });
</script>
@endsection
