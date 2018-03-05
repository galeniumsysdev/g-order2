<?php

namespace App\Http\Controllers;

use App\OrgStructure;
use App\User;
use DB;
use Webpatser\Uuid\Uuid;

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
  	$users = OrgStructure::select('users.*','org_structure.*','sup.name as sup_name','users.id as user_id')
  								->leftjoin('users','org_structure.user_id','users.id')
  								->leftjoin('users as sup','sup.id','org_structure.directsup_user_id')
  								->whereNull('users.customer_id')
  								->get();

  	return view('admin.org.orgList', compact('users','menu'));
  }

  public function setting($user_id)
  {
  	$menu = 'OrgStructure';
		$roles =DB::table('roles')
					->wherein('name',['SPV','ASM'])
					->select('id','name')
					->get();
		$role_list = array(''=>'---Choose Roles---');
		foreach ($roles as $key => $role) {
  		$role_list[$role->id] = $role->name;
  	}
  	$org = OrgStructure::leftjoin('users as u','u.id','org_structure.user_id')
					 ->leftjoin('role_user as ru','ru.user_id','u.id')
					 ->leftjoin('roles as r','r.id','ru.role_id')
					 ->where('org_structure.id',$user_id)
					 ->select('org_structure.*','u.email','r.id as role_id','r.name as role_name')->first();

  	$users_sup = User::select('users.*','org_structure.*','users.id as user_id')
  								->leftjoin('org_structure','org_structure.user_id','users.id')
  								->where('org_structure.id','!=',$user_id)
  								->whereNull('customer_id')
									->orderBy('org_structure.description')
  								->get();

  	$users_sup_list = array(''=>'---Choose User---');
  	foreach ($users_sup as $key => $user) {
  		$users_sup_list[$user->user_id] = $user->name;
  	}

  	return view('admin.org.orgSetting', compact('org','users_sup_list','user_id','menu','role_list'));
  }

  public function saveSetting(Request $request, $user_id)
  {
  	$user_code = $request->user_code;
  	$user_sup = $request->directsup;

  	$org = OrgStructure::where('id',$user_id)->first();
		if(!$org){
  		$org = new OrgStructure;
  	}
		if($org->user_id!=$request->user_id and $request->user_id!='')
		{
			if($request->user_id=='')
			$user =User::where('email',$request->email)->first();
			else
			$user =User::where('id',$request->user_id)->first();
			if($user) $org->user_id = $user->id;
		}elseif($request->email!=''){
			$user =User::where('email',$request->email)->first();
			if(!$user) {
				$user = User::Create(
					['id'=>Uuid::generate()->string
					,'email'=>$request->email
					,'name'=>$request->user_name
					,'passswrod'=>bcrypt('123456')
					,'validate_flag'=>1
					,'register_flag'=>1]
				);
				$user->roles()->sync($request->role);
			}
			$org->user_id = $user->id;
		}else{
			$org->user_id =null;
		}
		$org->description = $request->user_name;
  	$org->user_code = $user_code;
  	$org->directsup_user_id = $user_sup;
  	$org->save();

  	return redirect()->route('org.list')
  	                ->with('success','Setting saved successfully.');
  }

	public function create()
	{
		$menu = 'OrgStructure';
		$roles =DB::table('roles')
					->wherein('name',['SPV','ASM'])
					->select('id','name')
					->get();
		$role_list = array(''=>'---Choose Roles---');
		foreach ($roles as $key => $role) {
  		$role_list[$role->id] = $role->name;
  	}
		$users_sup = User::select('users.*','org_structure.*','users.id as user_id')
  								->leftjoin('org_structure','org_structure.user_id','users.id')
									->whereExists(function ($query) {
												$query->select(DB::raw(1))
															->from('role_user as ru')
															->join('roles as r','ru.role_id','r.id')
															->whereraw('ru.user_id = users.id')
															->whereIn('r.name',['ASM','HSM','FSM']);
									})->whereNull('customer_id')
  								->get();

  	$users_sup_list = array(''=>'---Choose User---');
  	foreach ($users_sup as $key => $user) {
  		$users_sup_list[$user->user_id] = $user->name;
  	}
		return view('admin.org.orgSettingAdd',compact('users_sup_list','role_list','menu'));
	}

	public function addSetting(Request $request)
	{
		$this->validate($request, [
			 'email' => 'unique:users,email,'.$request->user_id.',id',
			 'user_code' => 'required|unique:org_structure,user_code|max:16',
		]);

		if(!is_null($request->email))
		{
			$newuser = User::where('id','=',$request->user_id)->orwhere('email','=',$request->email)->first();
			if($newuser)
			{
				$newuser->name = $request->user_name;
				$newuser->save();
			}else{
				$newuser= User::Create(
					['id'=>Uuid::generate()->string
					,'email'=>$request->email
					,'name'=>$request->user_name
					,'passswrod'=>bcrypt('123456')
					,'validate_flag'=>1
					,'register_flag'=>1]
				);
			}

			$newuser->roles()->sync($request->role);
			$request->user_id = $newuser->id;
		}else{
			$request->user_id =null;
		}

		$exists = OrgStructure::where('user_code','=',$request->user_code)->first();
		if($exists)
		{
			$exists->user_id = $request->user_id;
			$exists->directsup_user_id =$request->directsup;
			$exists->save();
			return redirect()->route('org.create') ->with('success','Organization Structure updated successfully.');
		}else{
			$neworg = new OrgStructure();
			$neworg->user_code = $request->user_code;
			$neworg->user_id = $request->user_id;
			$neworg->directsup_user_id = $request->directsup;
			$neworg->description = $request->user_name;
			$neworg->save();
			return redirect()->route('org.create') ->with('success','Organization Structure added successfully.');
		}

	}
}
