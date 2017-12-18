@extends('layouts.tempAdminSB')
@section('content')
    <style type="text/css">
    input[type="text"]:readonly {
      background: #dddddd;
    }
    </style>
    <div class="row" >
        <div id="pesan">
          @if($status= Session::get('message'))
    			<div class="alert alert-info">
    				{{$status}}
    			</div>
    			@endif
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">Customer Oracle</div>
            <div class="panel-body">
              <form action="{{route('useroracle.update',$customer->id)}}" class="form-horizontal" method="post" role="form">
                {{method_field('PATCH')}}
                  {{csrf_field()}}
                  <input type="hidden" value="{{$customer->id}}" id="customer_id">
                  <div class="form-group">
                    <label class="control-label col-sm-2" for="name">@lang('label.outlet') :</label>
                    <div class="col-sm-10">
                      <input type="text"  class="form-control" name = "customer_name" value="{{$customer->customer_name}}" readonly>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label col-sm-2" for="email">@lang('label.email') :</label>
                    <div class="col-sm-10">
                      @if($customer->user)
                      <input type="text" class="form-control disabled" name="email" value="{{$customer->user->email}}">
                      @else
                      <input type="text" class="form-control disabled" name="email" value="">
                      @endif
                    </div>
                  </div>
                  <div class="tabcard">
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#personal" aria-controls="Personal" role="tab" data-toggle="tab">@lang('label.personal')</a></li>
                        <li role="presentation"><a href="#address" aria-controls="Address" role="tab" data-toggle="tab">@lang('label.address')</a></li>
                        <li role="presentation"><a href="#contact" aria-controls="Contact" role="tab" data-toggle="tab">@lang('label.contact')</a></li>
                        @if($customer->user)
                          @if($customer->user->hasRole('Distributor'))
                          <li role="presentation"><a href="#distributor_cabang" aria-controls="distributor_cabang" role="tab" data-toggle="tab">Distributor Cabang</a></li>
                          @endif
                        @endif
                    </ul>
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="personal">
                          <br>
                          <div class="form-group">
                            <label class="control-label col-sm-2" for="npwp">@lang('label.npwp') :</label>
                            <div class="col-sm-4">
                              <input type="text" name="tax" class="form-control" value="{{$customer->tax_reference}}" readonly>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-sm-2" for="npwp">Customer Number :</label>
                            <div class="col-sm-4">
                              <input type="text" name="customer_number" value="{{$customer->customer_number}}" class="form-control" readonly>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-sm-2" for="npwp">Classification :</label>
                            <div class="col-sm-4">
                              <input type="text" name="customer_class_code" class="form-control" value="{{$customer->customer_class_code}}" readonly>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-sm-2" for="npwp">PKP/Non PKP :</label>
                            <div class="col-sm-4">
                              <input type="text" name="category_code" class="form-control" value="{{$customer->customer_category_code}}" readonly>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-sm-2" for="npwp">Role :</label>
                            <div class="col-sm-4">
                              <select class="form-control" name="role">
                                <option value="">--</option>
                                @foreach ($roles as $role)
                                @if($customer->user)
                                  <option value="{{$role->id}}" {{in_array($role->id,$customer->user->roles->pluck('id')->toArray())?'selected':''}}>{{$role->display_name}}</option>
                                @else
                                  <option value="{{$role->id}}" >{{$role->display_name}}</option>
                                @endif
                                @endforeach
                              </select>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-sm-2" for="pscpharma">Jenis Produk :</label>
                            <div class="col-sm-10">
                                 <input type="checkbox"  name="pharma_flag" value="1" {{$customer->pharma_flag=="1"?"checked=checked":""}} > Non PSC/Pharma<br>
                                 <input type="checkbox"  name="psc_flag" value="1"  {{$customer->psc_flag=="1"?"checked=checked":""}}> PSC<br>
                                 <input type="checkbox"  name="export_flag" value="1" {{$customer->export_flag=="1"?"checked=checked":""}}> Export<br>
                                 <input type="checkbox"  name="tollin_flag" value="1" {{$customer->tollin_flag=="1"?"checked=checked":""}}> Toll-In<br>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-sm-2" for="pscpharma">Distributor :</label>
                            <div class="col-sm-4">
                                 <select name="distributor" class="form-control">
                                   <option value="">--</option>
                                   @foreach($principals as $principal)
                                   <option value="{{$principal->customer_id}}" {{in_array($principal->customer_id,$customer->hasDistributor()->get()->pluck('id')->toArray())?'selected':''}}>{{$principal->name}}</option>
                                   @endforeach
                                 </select>
                            </div>
                          </div>

                          @if(isset($subgroupname))
                          <div class="form-group{{ $errors->has('kategori') ? ' has-error' : '' }}">
                              <label for="kategori" class="control-label col-sm-2">@lang('label.categorydc') :</label>

                              <div class="col-sm-10">
                                <p class="form-control"></p>
                              </div>
                          </div>
                          @endif

                          <div class="form-group" id="status">
                              <div class="col-sm-12">
                                <button type="submit" name="save_customer" class="btn btn-primary">@lang('label.save')</button>
                                @if($customer->user)
                                  @if($customer->user->register_flag==0)
                                    <button type="submit" name="send_customer" class="btn btn-success">Send Invitation Email</button>
                                  @endif
                                @endif
                              </div>
                          </div>
                        </div>

                        <div role="tabpanel" class="tab-pane" id="address">
                          <div class="table-responsive">
                            <table id="alamat-table" class="table table-striped">
                              <thead>
                                <tr>
                                  <th width="10%">Fungsi</th>
                                  <th width="50%">@lang('label.address')</th>
                                  <th width="10%">@lang('label.province')</th>
                                  <th width="10%">@lang('label.city_regency')</th>
                                  <th width="5%">@lang('label.subdistrict')</th>
                                  <th width="5%">@lang('label.postalcode')</th>
                                </tr>
                              </thead>
                              <tbody>
                                @forelse($customer_sites as $customer_site)
                                  <tr>
                                    <td>{{$customer_site->site_use_code}}</td>
                                    <td>{{$customer_site->address1}}</td>
                                    <td>{{$customer_site->province}}</td>
                                    <td>{{$customer_site->city}}</td>
                                    <td>{{$customer_site->state}}</td>
                                    <td>{{$customer_site->postalcode}}</td>
                                  </tr>
                                @empty
                                  <tr><td colspan="5">No data</td></tr>
                                @endforelse
                              </tbody>
                            </table>
                            <!--
                            <div class="pull-right">
                               <a href="#" class="btn btn-success">@lang('label.addaddress')</a>
                             </div>-->

                          </div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="contact">
                          <div class="table-responsive">
                            <table id="contact-table" class="table table-striped">
                              <thead>
                                <tr>
                                  <th width="30%">@lang('label.cp')</th>
                                  <th width="20%">@lang('label.type')</th>
                                  <th width="30%">Data</th>
                                </tr>
                              </thead>
                              <tbody>
                                @forelse($customer_contacts as $cc)
                                <tr>
                                  <td>{{$cc->contact_name}}</td>
                                  <td>{{$cc->contact_type}}</td>
                                  <td>{{$cc->contact}}</td>
                                </tr>
                                @empty
                                  <tr><td colspan="4">No data</td></tr>
                                @endforelse
                              </tbody>
                            </table>

                          </div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="distributor_cabang">
                          <div class="table-responsive">
                            <table id="dist-table" class="table table-striped">
                              <thead>
                                <tr>
                                  <th>Name</th>
                                  <th>Role</th>
                                  <th>Action</th>
                                </tr>
                              </thead>
                              <tbody>
                                @forelse($customer->cabang as $cab)
                                <tr>
                                  <td>{{$cab->customer_name}}</td>
                                  <td>
                                    @foreach($cab->user->roles as $v)
                          					     <label class="label label-success">{{ $v->display_name }}</label>
                          				  @endforeach
                                  </td>
                                  <td>
                                    <a href="{{route('usercabang.edit',$cab->id)}}" class="btn btn-sm btn-primary">Edit</a>
                                  </td>
                                </tr>
                                @empty
                                  <tr>
                                    <td>No Data Found</td>
                                  </tr>
                                @endforelse
                              </tbody>
                            </table>
                            <div class="pull">
                  	            <a class="btn btn-primary" href="{{route('usercabang.create',$customer->id)}}"> Create Distributor Cabang</a>
                  	        </div>
                          </div>
                        </div>


                    </div>
                  </div>
              </form>
            </div>
        </div>
    </div>
@endsection
@section('js')
@endsection
