@extends('layouts.navbar_product')

@section('content')

<div class="container">
    @if(!$downloads)
    <div class="alert alert-success">
      'Not Found'
    </div>
    @endif
    <div class="wrapper">
        <section class="panel panel-default">
            <div class="panel-heading">
              <strong>DETAIL FILES CMO</strong>
            </div>
          <div class="panel-body" style="overflow-x:auto;">
              <div id="frmsearch" class="panel panel-default">
                <br>
                  <form class="form-horizontal" role="form" method="POST" action="{{route('files.postviewfile')}}">
                      {{ csrf_field() }}
                    @if(Auth::user()->hasRole('Principal'))
                    <div class="form-group">
                      <label for="name" class="col-md-2 control-label">@lang('label.distributor')</label>
                        <div class="col-md-9" >
                          <div class="input-group">
                            <input type="text" name="distributor" class="form-control" placeholder="@lang('label.search') distributor.." aria-label="@lang('label.search') distributor.." value="{{$distributor}}">
                            <span class="input-group-btn">
                              <button class="btn btn-secondary" type="button"><i class="fa fa-search" aria-hidden="true"></i></button>
                            </span>
                          </div>

                          </div>
                    </div>
                    <div class="form-group">
                      <label for="name" class="col-md-2 control-label">Status</label>
                        <div class="col-md-9" >
                          <select class="form-control" name="status">
                              <option value="%" {{$status=='%'?'selected=selected':''}}>--@lang('label.showall')--</option>
                              <option value="" {{$status==''?'selected=selected':''}}>@lang('label.none')</option>
                              <option value="0" {{$status=='0'?'selected=selected':''}}>@lang('label.reject')</option>
                              <option value="1" {{$status=='1'?'selected=selected':''}}>@lang('label.approve')</option>
                          </select>

                        </div>
                    </div>
                    @endif
                      <div class="form-group">
                        <label for="name" class="col-md-2 control-label">@lang('label.month')</label>
                          <div class="col-md-9" >
                            <select class="form-control" name="bulan">
                                <option value="">--@lang('label.showall')--</option>
                                {{ $i=0 }}
                                @for ($i = 1; $i <= 12; $i++)
                                  <option value="{{$i}}" {{$i==$bulan?'selected=selected':''}}>{{date('F', mktime(0, 0, 0, $i, 10))}}</option>
                                @endfor
                            </select>
                            <!--{{ Form::selectMonth('month',date('m',strtotime('+1 month')) ,['class'=>'form-control']) }}-->
                          </div>
                      </div>
                      <div class="form-group">
                        <label for="name" class="col-md-2 control-label">@lang('label.year')</label>
                          <div class="col-md-9" >
                            {{ Form::selectYear('tahun', 2017, date('Y'), $tahun, ['class' => 'form-control']) }}
                          </div>
                      </div>

                    <div class="form-group">
                        <div class="col-md-10 col-md-offset-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-search" aria-hidden="true"></i>&nbsp;@lang('label.search')
                            </button>
                        </div>
                    </div>
                  </form>
              </div>
            </div>
            <div class="panel-heading">
            <div id="no-more-tables">
                <table class="table table-sm"  id="table">
                    <thead>
                      <tr>
                        @if(Auth::user()->hasRole('Principal'))
                        <th>Distributor</th>
                        @endif
                        <th>Period</th>
						            <th>Version</th>
                        <th>Upload Date</th>
                        <th>Download</th>
                        <th>Status</th>
                      </tr>
                    </thead>

                    <tbody>
                    @forelse($downloads as $down)
                        <tr>
                            @if(Auth::user()->hasRole('Principal'))
                            <td data-title="Distributor">{{$down->customer_name}}</td>
                            @endif
                            <td data-title="Period">{{ $down->period }}</td>
							              <td data-title="Version">{{ $down->version }}</td>
                            <td data-title="Upload Date">{{ $down->created_at }}</td>
                            <td data-title="Download">
                              <a href="{{url('/downloadCMO/'.$down->file_pdf) }}" download="{{ $down->file_pdf }}" title="PDF">
                                  <button type="button" class="btn btn-primary btn-sm">
                                    <strong>PDF</strong>
                                  </button>
                							</a>
                							<a href="{{ url('/downloadCMO/'.$down->file_excel) }}" download="{{ $down->file_excel }}" title="Excel">
                								<button type="button" class="btn btn-primary btn-sm">
                                  <strong>EXCEL</strong>
                                </button>
                              </a>
                            </td>
                              <td data-title="Status">
                              @if(Auth::user()->hasRole('Principal') and is_null($down->approve))
                                {!! Form::open(['method' => 'PUT','route' => ['files.approvecmo', $down->id],'style'=>'display:inline']) !!}
                                {!! Form::token() !!}
                                <input type="hidden" name="bulan" value="{{$bulan}}">
                                <input type="hidden" name="tahun" value="{{$tahun}}">
                                <input type="hidden" name="distributor" value="{{$distributor}}">
                                <input type="hidden" name="status" value="{{$status}}">
                                <button type="submit" name="approve" value="approve" class="btn btn-success btn-sm"  title="@lang('label.approve')">
                                  <i class="fa fa-check" aria-hidden="true"></i>@lang('label.approve')
                                </button>

                                <button type="submit" name="approve" value="reject" class="btn btn-danger btn-sm"  title="@lang('label.reject')">
                                  <i class="fa fa-ban" aria-hidden="true"></i>@lang('label.reject')
                                </button>

                            </form>
                              @elseif(is_null($down->approve))
                                @lang('label.none')
                              @elseif($down->approve)
                                @lang('label.approve')
                              @elseif($down->approve==0)
                                @lang('label.reject')
                              @endif
                            </td>


                        </tr>
                    @empty
                        <tr>
                          <td colspan="0"><strong>@lang('label.notfound')</strong></td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
              </div>
            </div>
			<br></br>

        </section>
    </div>
  </div>

@endsection
