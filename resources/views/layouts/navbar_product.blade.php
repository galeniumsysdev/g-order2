<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'g-Order') }}</title>

    <!-- Styles -->
		<!--<link rel="stylesheet" href="product.css">-->
	<link href="https://fonts.googleapis.com/css?family=Quicksand:300,400,500,700&subset=latin-ext,vietnamese" rel="stylesheet">
	 <!--<link href="css/bootstrap.min.css" rel="stylesheet">-->
  <link href="https://fonts.googleapis.com/css?family=Pacifico&subset=latin-ext,vietnamese" rel="stylesheet">
	<link rel='stylesheet prefetch' href='http://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.0.1/sweetalert.min.css'>
  <!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css">-->
  <link rel="stylesheet" href="{{ URL::to('font-awesome-4.7.0/css/font-awesome.min.css') }}">
  <!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">-->
  <link rel="stylesheet" href="{{ URL::to('css/bootstrap.min.css') }}">
  <link href="{{ asset('css/app20170913.css') }}" rel="stylesheet">
  <link rel="stylesheet" href="{{ URL::to('css/mystyle.css') }}">
  <link rel="stylesheet" href="{{ URL::to('css/loading.css') }}">

    <!-- Scripts -->
    <script>
    window.Laravel = {
                   csrfToken: '{{csrf_token()}}',
                   url: "{{url('/')}}",
                   auth: {
                       user: '{{auth()->user()}}'
                   }
                }
    </script>
</head>
<body onload="myFunction()" style="margin:0;">
    <div id="loader"></div><!-- ini loadingnya-->
    <div style="display:none;" id="myDiv" class="animate-bottom"><!-- ini id myDiv yang akan dihide ketika loading -->
      <div id="app">
          <nav class="navbar navbar-default navbar-static-top header">
              <div class="container">
                  <div class="navbar-header">

                      <!-- Collapsed Hamburger -->
                      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                          <span class="sr-only">Toggle Navigation</span>
                          <span class="icon-bar"></span>
                          <span class="icon-bar"></span>
                          <span class="icon-bar"></span>
                      </button>

                      <!-- Branding Image -->
                      @if(Auth::guest()||Auth::user()->can('Create PO'))
                        <a class="navbar-brand" href="{{ url('/') }}" style="color:#fff">
                      @else
                          <a class="navbar-brand" href="{{ url('/home') }}" style="color:#fff">
                      @endif
                          <strong><img class="img-header" src="{{ URL::to('img/logoe1.png') }}"/></strong>
                        </a>
                      @if(Auth::user())
                        @if(Auth::user()->can('Create PO'))
                          <ul class="nav navbar-right-xs pull-right visible-xs">
                            <li>
                              <a href="{{ route('product.shoppingCart')}}" title="@lang('label.shopcart')"><i class="fa fa-shopping-cart fa-lg" aria-hidden="true"></i>
                              <span class="badge" id="shopcart2">{{ Session::has('cart')?Session::get('cart')->totalQty:"" }}</span></a>
                            </li>
                          </ul>
                        @endif
                        <ul class="nav navbar-right-xs pull-right visible-xs">
                          <li>
                            <a href="{{url('/home')}}" title="@lang('label.showall') Notification"><i class="fa fa-bell fa-lg"></i>
                            <span class="badge">{{count(Auth::user()->unreadNotifications)}}</span>
                          </a>
                          </li>
                        </ul>
                      @endif
                  </div>
                  @if (Auth::check()&&Auth::user()->can('Create PO'))
                    <form method="post" action="{{route('product.search')}}" class="navbar-form navbar-left" role="search">
                       {{csrf_field()}}
                      <!--<div class="form-group">
                        <input type="text" name="search_product" class="form-control" placeholder="Search">
                      </div>
                      <button type="submit" class="btn btn-default"><i class="fa fa-search" aria-hidden="true"></i></button>-->
                      <div class="input-group">
                        <input type="text" class="form-control" name="search_product" placeholder="@lang('label.searchproduct')" aria-label="Search for..." value="">
                        <span class="input-group-btn">
                          <button type="submit" class="btn btn-default"><i class="fa fa-search" aria-hidden="true"></i></button>
                        </span>
                      </div>
                    </form>
                  @endif

                  <div class="collapse navbar-collapse" id="app-navbar-collapse">
                      <!-- Left Side Of Navbar -->

                      <!-- Right Side Of Navbar -->
                      <ul class="nav navbar-nav navbar-right" >
                          <!-- Authentication Links -->

                        @if (Auth::guest())
                          <!--<li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><strong>@lang('label.categoryproduct')</strong>&nbsp; <i class="fa fa-caret-down" aria-hidden="true"></i></a>
                            <ul class="dropdown-menu">
                              @foreach($product_flexfields as $flexfield)
                              <li><a href="{{route('product.category',$flexfield->flex_value)}}">{{$flexfield->description}}</a></li>
                              @endforeach
                            </ul>
                          </li>-->
                          <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-flag" aria-hidden="true"></i>&nbsp; <strong>{{ app()->getLocale() }}</strong>&nbsp; <i class="fa fa-caret-down" aria-hidden="true"></i></a>
                            <ul class="dropdown-menu">
                              <li id="LI_201">
                                  <a href="#" id="idLang" onclick="changeLanguage('id');return false;"><img src="https://images.apple.com/support/assets/images/layout/icons/flags/country/indonesia.png" alt="" width="20" height="20" id="IMG_203" /><span id="SPAN_204"><span id="SPAN_205">&nbsp; Indonesia (id)</span></span></a>
                              </li>
                              <li id="LI_489">
                                <a href="#" id="enLang" onclick="changeLanguage('en');return false;"><img src="https://images.apple.com/support/assets/images/layout/icons/flags/country/united_kingdom.png" alt="" width="20" height="20" id="IMG_491" /><span id="SPAN_492"><span id="SPAN_493">&nbsp; English (en)</span></span></a>
                              </li>
                            </ul>
                          </li>
                          <li><a href="{{ route('login') }}"><strong><i class="fa fa-sign-in" aria-hidden="true"></i>&nbsp; Login</strong></a></li>
                          <li><a href="{{ route('register') }}"><strong><i class="fa fa-user-plus" aria-hidden="true"></i>&nbsp; Register</strong></a></li>
                        @else
                          <notification :email="{{json_encode(Auth::user()->email)}}" :count="{{json_encode(count(Auth::user()->unreadNotifications))}}" :notif="{{json_encode(Auth::user()->unreadNotifications->take(5))}}"></notification>
                          @if(Auth::user()->can('product'))
                          <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><strong>Product</strong>&nbsp; <i class="fa fa-caret-down" aria-hidden="true"></i></a>
                            <ul class="dropdown-menu">
                              <li><a href="{{route('outlet.listProductStock')}}">Product List</a></li>
                              <li><a href="{{route('outlet.importProduct')}}">Import Product</a></li>
                              <li><a href="{{route('outlet.importProductStock')}}">Import Stock</a></li>
                              <li><a href="{{route('outlet.trx')}}">Transaction In/Out</a></li>
                              <li><a href="{{route('outlet.trxList')}}">Transaction List</a></li>
                            </ul>
                          </li>
                          @endif
                          @if(Auth::user()->can('Outlet_Distributor'))
                            <li><a href="{{ route('customer.listNoo') }}"><strong>List Noo</strong></a></li>
                          @endif
                          @if(Auth::user()->can('PengajuanDPL'))
                            <!--
                            /**
                            * added by WK Productions
                            */
                            -->
                            <li class="dropdown">
                              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><strong>DPL</strong>&nbsp; <i class="fa fa-caret-down" aria-hidden="true"></i></a>
                              <ul class="dropdown-menu">
                                <li><a href="{{route('dpl.generateForm')}}">@lang('label.generatesuggestno')</a></li>
                                <li><a href="{{route('dpl.list')}}">@lang('label.listsuggestno')</a></li>
                              </ul>
                            </li>
                            <!-- End of addition -->
                          @endif

                          @if(Auth::user()->can('uploadCMO') or Auth::user()->can('DownloadCMO'))
                            <li class="dropdown">
                              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><strong>CMO</strong>&nbsp; <i class="fa fa-caret-down" aria-hidden="true"></i></a>
                              <ul class="dropdown-menu">
                              @if(Auth::user()->can('UploadCMO'))
                                <li><a href="{{route('files.uploadcmo')}}">@lang('label.upload') CMO</a></li>
                              @endif
                              @if(Auth::user()->can('DownloadCMO'))
                                  <li><a href="{{route('files.viewfile')}}">@lang('label.download') CMO</a></li>
                              @endif
                              </ul>
                            </li>
                          @endif
                          @if(Auth::user()->hasRole('Distributor') or Auth::user()->hasRole('Distributor Cabang') or Auth::user()->hasRole('Outlet') or Auth::user()->hasRole('Apotik/Klinik') or Auth::user()->hasRole('Principal'))
                            <li class="dropdown">
                              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><strong>List Order</strong>&nbsp; <i class="fa fa-caret-down" aria-hidden="true"></i></a>
                              <ul class="dropdown-menu">
                                @if(Auth::user()->can('CheckStatusSO'))
                                  <li><a href="{{route('order.listSO')}}">Check SO</a></li>
                                @endif
                                @if(Auth::user()->can('Create PO'))
                                  <li><a href="{{route('order.listPO')}}">Check PO</a></li>
                                @endif
                              </ul>
                            </li>
                          @endif
                          @if (Auth::user()->hasRole('Apotik/Klinik'))
                            <li class="dropdown">
                              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><strong>Product</strong>&nbsp; <i class="fa fa-caret-down" aria-hidden="true"></i></a>
                              <ul class="dropdown-menu">
                                <li><a href="{{route('outlet.listProductStock')}}">Product List</a></li>
                                <li><a href="{{route('outlet.importProduct')}}">Import Product</a></li>
                                <li><a href="{{route('outlet.importProductStock')}}">Import Stock</a></li>
                                <li><a href="{{route('outlet.trxList')}}">Transaction List</a></li>
                              </ul>
                            </li>
                          @endif
                          @if(Auth::user()->can('Create PO'))
                              <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><strong>@lang('label.categoryproduct')</strong>&nbsp; <i class="fa fa-caret-down" aria-hidden="true"></i></a>
                                <ul class="dropdown-menu">
                                  @foreach($product_flexfields as $flexfield)
                                  <li><a href="{{route('product.category',$flexfield->flex_value)}}">{{$flexfield->description}}</a></li>
                                  @endforeach
                                </ul>
                              </li>
                              <li class="hidden-xs">
                                <a href="{{ route('product.shoppingCart')}}" title="@lang('label.shopcart')"><i class="fa fa-shopping-cart" aria-hidden="true"></i>&nbsp;<strong class="visible-xs-inline">@lang('label.shopcart')</strong>
                                  <span class="badge" id="shopcart">{{ Session::has('cart')?Session::get('cart')->totalQty:"" }}</span>
                                </a>
                              </li>
                          @endif


                          <!--bahasa dropdown-->
                          <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-flag" aria-hidden="true"></i>&nbsp; <strong>{{ app()->getLocale() }}</strong>&nbsp; <i class="fa fa-caret-down" aria-hidden="true"></i></a>
                            <ul class="dropdown-menu">
                              <li id="LI_201">
                                  <a href="#" id="idLang" onclick="changeLanguage('id');return false;"><img src="https://images.apple.com/support/assets/images/layout/icons/flags/country/indonesia.png" alt="" width="20" height="20" id="IMG_203" /><span id="SPAN_204"><span id="SPAN_205">&nbsp; Indonesia (id)</span></span></a>
                              </li>
                              <li id="LI_489">
                                <a href="#" id="enLang" onclick="changeLanguage('en');return false;"><img src="https://images.apple.com/support/assets/images/layout/icons/flags/country/united_kingdom.png" alt="" width="20" height="20" id="IMG_491" /><span id="SPAN_492"><span id="SPAN_493">&nbsp; English (en)</span></span></a>
                              </li>
                            </ul>
                          </li>
                        <!--{{--notification--}}
                          --  <notification userid="{!!auth()->id()!!}" :unreads="{{auth()->user()->unreadNotifications}}"></notification>-->
                            <!-- <li class="dropdown hidden-xs">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bell"></i> Notifications
                                  <span class="badge">{{count(Auth::user()->unreadNotifications)}}</span>
                                </a>
                                <ul class="dropdown-menu alert-dropdown dropdown-notif" role="menu" >
                                  <li>
                                    @forelse (Auth::user()->unreadNotifications->take(5)  as $notification)
                                    @include('notifications.'.snake_case(class_basename($notification->type)))
                                    @empty
                                      no unread notification
                                      @endforelse
                                  </li>
                                  <li class="divider"></li>
                                  <li style="text-align:center">
                                      <a href="{{url('/home')}}">@lang('label.showall')</a>
                                  </li>
                                </ul>
                            </li> -->

                            <!--user dropdown-->
                            <li class="dropdown">
                                <!--<a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <strong><i class="fa fa-user" aria-hidden="true"></i> {{ Auth::user()->name }} </strong>
                                </a>-->
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" style="position:relative; padding-left:50px">
                                  <img id="img_profile2" src="{{asset('/uploads/avatars/'.Auth::user()->avatar) }}" style="width:32px; height:32px; position:absolute; top:10px; left:10px; border-radius:50%">
                                </a>

                                <ul class="dropdown-menu">
                                    <li>
                                        <div class="navbar-login">
                                            <div class="row">
                                                <div class="col-lg-4">
                                                    <p class="text-center xs-hidden">
                                                        <img id="img_profile" src="{{asset('/uploads/avatars/'.Auth::user()->avatar) }}" style="width:58px; height:58px;  border-radius:50%">
                                                    </p>
                                                </div>
                                                <div class="col-lg-8">
                                                    <p class="text-left"><strong>{{Auth::user()->name}}</strong></p>
                                                    <p class="text-left small">{{Auth::user()->email}}</p>
                                                    <p class="text-left">
                                                        <a href="{{route('profile.index')}}" class="btn btn-black btn-block btn-sm">@lang('label.viewacct')</a>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="divider"></li>
                                    <li>
                                        <div class="navbar-login navbar-login-session">
                                                <div class="links">
                                                    <p>
                                                      <a href="{{ route('logout') }}"
                                                          onclick="event.preventDefault();
                                                                   document.getElementById('logout-form').submit();">
                                                          @lang('label.logout')
                                                      </a>

                                                      <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                                          {{ csrf_field() }}
                                                      </form>
                                                    </p>
                                                </div>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        @endif
                      </ul>
                  </div>
              </div>

          </nav>

          @yield('content')
      </div>
      <!--@yield('footer')-->
      <!--@include('layouts.footer')-->
    </div>
    <!-- Scripts -->


    <script>
    var myVar;

    function myFunction() {
        myVar = setTimeout(showPage, 500);
    }

    function showPage() {
      document.getElementById("loader").style.display = "none";
      document.getElementById("myDiv").style.display = "block";
    }
    </script>
<script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="http://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.0.1/sweetalert.min.js"></script>
<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/customlang.js') }}"></script>
<script src="{{ asset('js/index.js') }}"></script>
@yield('js')

</body>
</html>
