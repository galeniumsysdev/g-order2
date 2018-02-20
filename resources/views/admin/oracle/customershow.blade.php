@extends('layouts.tempAdminSB')
@section('content')
    <style type="text/css">
    input[type="text"]:readonly {
      background: #dddddd;
    }
    </style>
    <div class="row" >
        <div id="pesan">
          @if($status= Session::get('success'))
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
                          @if($customer->user->ability(array('Distributor','Distributor Cabang','Principal'),''))
                            <li role="presentation"><a href="#mapping_distributor" aria-controls="mapping_distributor" role="tab" data-toggle="tab">Mapping Distributor</a></li>
                          @endif
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

                          <div class="form-group" id="divkategoridc">
                            <label for="kategori" class="control-label col-sm-2"><strong>@lang('label.categorydc') :</strong></label>
                            <div class="col-sm-4">
                                <select class="form-control" name="groupdc" id="groupdc" onchange="ubahdc('')" >
                                  <option value="">--</option>
                                  @forelse($groups as $groupdc)
                                                        @if($groupdc->id==$groupid)
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


                          <div class="form-group" id="status">
                              <div class="col-sm-12">
                                <button type="submit" name="save_customer" value="Save" class="btn btn-primary">@lang('label.save')</button>
                                @if($customer->user)
                                  @if($customer->user->register_flag==0)
                                    <button type="submit" name="save_customer" value="Send" class="btn btn-success">Send Invitation Email</button>
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
                        <div role="tabpanel" class="tab-pane" id="mapping_distributor">
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
                                <a href="{{route('customer.mappingOutlet',$customer->id)}}" target="_blank" class="btn btn-sm btn-primary">Preview Outlet</a>
                  	        </div>
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

<script type="text/javascript">
var baseurl = window.Laravel.url;
$(document).ready(function() {
  var baseurl = window.Laravel.url;
  ubahdc({{isset($customer->subgroup_dc_id)?$customer->subgroup_dc_id:0}});
  });
  function ubahdc(old){
      var cat_id =$('#groupdc').val();
      $.get(baseurl+'/ajax-subcat?cat_id='+cat_id,function(data){
          //console.log(data);
            $('#subgroupdc').empty();
          $.each(data,function(index,subcatObj){
            if (subcatObj.id==old)
            {
              $('#subgroupdc').append('<option value="'+subcatObj.id+'" selected=selected>'+subcatObj.display_name+'</option>');
            }else{
              $('#subgroupdc').append('<option value="'+subcatObj.id+'">'+subcatObj.display_name+'</option>');
            }

          });

      });
  }
</script>
@endsection
