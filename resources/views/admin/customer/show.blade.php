@extends('layouts.navbar_product')

@section('content')
<div class="container">
    <div class="row" >
        <div id="pesan">
        </div>
            <div class="panel panel-primary">
                <div class="panel-heading">Customer</div>
                <div class="panel-body">
                      <form action="{{route('customer.update',$user->id)}}" class="form-horizontal" method="post" role="form">
                          {{method_field('PATCH')}}
                          {{csrf_field()}}
                          <input type="hidden" value="{{$customer->id}}" id="customer_id">
                          <input type="hidden" value="{{$notif_id}}" id="notif_id">
                          <input type="hidden" value="{{url('/')}}" id="baseurl">
                          <div class="form-group">
                            <label class="control-label col-sm-2" for="name">@lang('label.outlet') :</label>
                            <div class="col-sm-10">
                              <p class="form-control">{{$customer->customer_name}}</p>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-sm-2" for="email">@lang('label.email') :</label>
                            <div class="col-sm-10">
                              <p class="form-control">{{$email}}</p>
                            </div>
                          </div>
                          <div class="tabcard">
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" class="active"><a href="#personal" aria-controls="Personal" role="tab" data-toggle="tab">@lang('label.personal')</a></li>
                                <li role="presentation"><a href="#address" aria-controls="Address" role="tab" data-toggle="tab">@lang('label.address')</a></li>
                                <li role="presentation"><a href="#contact" aria-controls="Contact" role="tab" data-toggle="tab">@lang('label.contact')</a></li>
                            </ul>
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane active" id="personal">
                                  <div class="form-group">
                                    <label class="control-label col-sm-2" for="npwp">@lang('label.npwp') :</label>
                                    <div class="col-sm-10">
                                      <p class="form-control">{{$customer->tax_reference}}</p>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-sm-2" for="pscpharma">@lang('label.needproduct') :</label>
                                    <div class="col-sm-10">
                                         <input type="checkbox"  name="pharma_flag" value="1" disabled="disabled" {{$customer->pharma_flag=="1"?"checked=checked":""}} > Non PSC/Pharma<br>
                                          <input type="checkbox"  name="psc_flag" value="1" disabled="disabled" {{$customer->psc_flag=="1"?"checked=checked":""}}> PSC<br>
                                    </div>
                                  </div>
                                  @if(isset($categoryoutlet))
                                  <div class="form-group{{ $errors->has('kategori') ? ' has-error' : '' }}">
                                      <label for="kategori" class="control-label col-sm-2">@lang('label.category') :</label>

                                      <div class="col-sm-10">
                                          <p class="form-control">{{$categoryoutlet}}</p>
                                      </div>
                                  </div>
                                  @endif
                                  @if(isset($subgroupname))
                                  <div class="form-group{{ $errors->has('kategori') ? ' has-error' : '' }}">
                                      <label for="kategori" class="control-label col-sm-2">@lang('label.categorydc') :</label>

                                      <div class="col-sm-10">
                                        <p class="form-control">{{$groupdc."-".$subgroupname}}</p>
                                      </div>
                                  </div>
                                  @endif
                                  @if(Auth::user()->hasRole('Principal'))
                                    <div class="form-group{{ $errors->has('c_number') ? ' has-error' : '' }}">
                                        <label for="kategori" class="control-label col-sm-2">Oracle Customer No. :</label>

                                        <div class="col-sm-10">
                                          <input type="text" name="c_number" class="form-control" value="{{$customer->customer_number}}">
                                        </div>
                                    </div>
                                  @endif

                                  <div class="form-group" id="status">
                                      @if(is_null($outletdist->approval))
                                      <div class="col-sm-12">
                                        <!--<button type="submit" name="save" value="reject" id="reject-customer" class="btn btn-warning">@lang('label.reject')</button>
                                        <button type="submit" name="save" value="approve" id="approve-customer" class="btn btn-primary">@lang('label.approve')</button>-->
                                        @if(Auth::user()->hasRole('Principal'))
                                        <button type="submit" name="save" value="save" id="save-customer" class="btn btn-primary">@lang('label.save')</button>
                                        @endif
                                      </div>

                                      @elseif($outletdist->approval)
                                        <label for="statue" class="control-label col-sm-2">Status: </label>
                                        <div class="col-sm-10"><p class="form-control">Approve</p></div>
                                      @elseif(!$outletdist->approval)
                                          <label for="statue" class="control-label col-sm-2">Status: </label>
                                          <div class="col-sm-10"><p class="form-control">Tolak: {{$outletdist->keterangan}}</p></div>
                                      @endif
                                  </div>
                                </div>

                                <div role="tabpanel" class="tab-pane" id="address">
                                  <div class="table-responsive">
                                    <table id="alamat-table" class="table table-striped">
                                      <thead>
                                        <tr>
                                          <th width="60%">@lang('label.address')</th>
                                          <th width="10%">@lang('label.city_regency')</th>
                                          <th width="5%">@lang('label.urban_village')</th>
                                          <th width="5%">@lang('label.postalcode')</th>
                                        </tr>
                                      </thead>
                                      <tbody>
                                        @forelse($customer_sites as $customer_site)
                                          <tr>
                                            <td>{{$customer_site->address1}}</td>
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



                            </div>
                          </div>
                      </form>
                </div>
            </div>
        </div>
</div>
@endsection
@section('js')
<script src="{{ asset('js/approvalcustomer.js') }}"></script>
@endsection
