<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<li><a href="{{ backpack_url('dashboard') }}"><i class="fa fa-dashboard"></i> <span>{{ trans('backpack::base.dashboard') }}</span></a></li>
 
<!-- <li><a href="{{ backpack_url('elfinder') }}"><i class="fa fa-files-o"></i> <span>{{ trans('backpack::crud.file_manager') }}</span></a></li> -->
@if(Auth::user()->hasPermissionTo('Manage Admins') || Auth::user()->hasPermissionTo('Roles'))
<li class="treeview">
    <a href="#"><i class="fa fa-group"></i> <span>Admins, Roles, Access</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
      @if(Auth::user()->hasPermissionTo('Manage Admins'))
      <li><a href="{{ backpack_url('administrator') }}"><i class="fa fa-user"></i> <span>Manage Admins</span></a></li>
      @endif
      @if(Auth::user()->hasPermissionTo('Roles'))
      <li><a href="{{ backpack_url('role') }}"><i class="fa fa-group"></i> <span>Roles</span></a></li>
      @endif
   
    </ul>
</li>
@endif
@if(Auth::user()->hasPermissionTo('Manage Parents') || Auth::user()->hasPermissionTo('Manage Child'))
<li class="treeview">
    <a href="#"><i class="fa fa-users"></i> <span>Manage Users</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
      @if(Auth::user()->hasPermissionTo('Manage Parents'))
      <li><a href="{{ backpack_url('parent') }}"><i class="fa fa-user"></i> <span>Manage Parents</span></a></li>
      @endif
      @if(Auth::user()->hasPermissionTo('Manage Child'))
      <li><a href="{{ backpack_url('child') }}"><i class="fa fa-child"></i> <span>Manage Child</span></a></li>
      @endif
    </ul>
</li>
@endif
@if(Auth::user()->hasPermissionTo('Manage Spacer Data'))
<li><a href="{{ backpack_url('spacerdata') }}"><i class="fa fa-table"></i> <span>Manage Spacer Data</span></a></li>
@endif
@if(Auth::user()->hasPermissionTo('Manage Reports'))
<li><a href="{{ backpack_url('report') }}"><i class="fa fa-bar-chart"></i> <span>Manage Reports</span></a></li>
@endif
@if(Auth::user()->hasPermissionTo('Manage Feedback'))
 <li><a href="{{ backpack_url('feedback') }}"><i class="fa fa-comments"></i> <span>Manage Feedback</span></a></li>
@endif
@if(Auth::user()->hasPermissionTo('Manage FAQ'))
 <li><a href="{{ backpack_url('faq/1/edit') }}"><i class="fa fa-question-circle"></i> <span>Manage FAQ</span></a></li>
@endif
@if(Auth::user()->hasPermissionTo('Manage Rewards'))
 <li><a href="{{ backpack_url('reward') }}"><i class="fa fa-trophy"></i> <span>Manage Rewards</span></a></li>
@endif