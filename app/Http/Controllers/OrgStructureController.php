<?php

namespace App\Http\Controllers;

use App\OrgStructure;
use App\User;

use Illuminate\Http\Request;

class OrgStructureController extends Controller
{
	public function __construct()
  {
    $this->middleware('auth');
  }

  public function index()
  {
  	$menu = 'OrgStructure';
  	$users = User::select('users.*','org_structure.*','sup.name as sup_name','users.id as user_id')
  								->join('org_structure','org_structure.user_id','users.id')
  								->leftjoin('users as sup','sup.id','org_structure.directsup_user_id')
  								->whereNull('users.customer_id')
  								->get();

  	return view('admin.org.orgList', compact('users','menu'));
  }

  public function setting($user_id)
  {
  	$menu = 'OrgStructure';

  	$org = OrgStructure::where('user_id',$user_id)->first();

  	$users_sup = User::select('users.*','org_structure.*','users.id as user_id')
  								->leftjoin('org_structure','org_structure.user_id','users.id')
  								->whereNotIn('users.id',[$user_id])
  								->whereNull('customer_id')
  								->get();

  	$users_sup_list = array(''=>'---Choose User---');
  	foreach ($users_sup as $key => $user) {
  		$users_sup_list[$user->user_id] = $user->name;
  	}

  	return view('admin.org.orgSetting', compact('org','users_sup_list','user_id','menu'));
  }

  public function saveSetting(Request $request, $user_id)
  {
  	$user_code = $request->user_code;
  	$user_sup = $request->directsup;

  	$org = OrgStructure::where('user_id',$user_id)->first();

  	if(!$org){
  		$org = new OrgStructure;
  		$org->user_id = $user_id;
  	}

  	$org->user_code = $user_code;
  	$org->directsup_user_id = $user_sup;
  	$org->save();

  	return redirect()->route('org.list')
  	                ->with('success','Setting saved successfully.');
  }
}
