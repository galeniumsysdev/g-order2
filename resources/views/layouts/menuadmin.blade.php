<ul class="nav navbar-nav side-nav">
    <li>
        <a href="index.html"><i class="fa fa-fw fa-dashboard"></i> Dashboard</a>
    </li>
    <li {{$menu=='user'?'class=active':''}}>
        <a href="{{route('users.index')}}"><i class="fa fa-fw fa-bar-chart-o"></i> User</a>
    </li>
	  <li {{$menu=='product'?'class=active':''}}>
        <a href="{{route('product.show')}}"><i class="fa fa-fw fa-table"></i> Product</a>
    </li>

    <li {{$menu=='role'?'class=active':''}}>
        <a href="{{route('role.index')}}"><i class="fa fa-fw fa-edit"></i>Role</a>
    </li>
    <li {{$menu=='permission'?'class=active':''}}>
        <a href="{{route('permission.index')}}"><i class="fa fa-fw fa-wrench"></i>Permission</a>
    </li>
    <li {{$menu=='CategoryOutlet'?'class=active':''}}>
        <a href="{{route('CategoryOutlet.index')}}"><i class="fa fa-fw fa-desktop"></i> Categories Outlet</a>
    </li>
    <li>
        <a href="bootstrap-grid.html"><i class="fa fa-fw fa-wrench"></i> Outlet/Distributor</a>
    </li>
    <li>
        <a href="javascript:;" data-toggle="collapse" data-target="#demo"><i class="fa fa-fw fa-arrows-v"></i> Dropdown <i class="fa fa-fw fa-caret-down"></i></a>
        <ul id="demo" class="collapse">
            <li>
                <a href="#">Dropdown Item</a>
            </li>
            <li>
                <a href="#">Dropdown Item</a>
            </li>
        </ul>
    </li>
 @if($menu=="blank")
    <li class="active">
@else
	<li>
@endif
        <a href="blank-page.html"><i class="fa fa-fw fa-file"></i> Blank Page</a>
    </li>
    <li>
        <a href="index-rtl.html"><i class="fa fa-fw fa-dashboard"></i> RTL Dashboard</a>
    </li>
</ul>
