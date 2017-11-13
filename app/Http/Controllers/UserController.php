<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Role;
use App\Customer;
use App\CustomerSite;
use App\CustomerContact;
use DB;
use Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Webpatser\Uuid\Uuid;
use App\Notifications\InvitationUser;



class UserController extends Controller
{
  protected $menu;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
      $this->menu="user";
    }
    public function index(Request $request)
    {
        $data = User::whereNull('customer_id')->orderBy('id','name')->get();
        return view('admin.user.index',['data'=>$data,'menu'=>$this->menu]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::pluck('display_name','id')->all();
        return view('admin.user.create',['roles'=>$roles,'menu'=>$this->menu]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles' => 'required'
        ]);

        $input = $request->all();
        $input['id']= Uuid::generate()->string;
        $input['password'] = Hash::make($input['password']);
        $input['validate_flag']=1;
        $input['register_flag']=1;
        $user = User::create($input);
        foreach ($request->input('roles') as $key => $value) {
            $user->attachRole($value);
        }
        return redirect()->route('users.index')
                        ->with('success','User created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        return view('admin.user.show',['user'=>$user,'menu'=>$this->menu]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        //$roles = Role::lists('display_name','id');
        $roles = Role::pluck('display_name','id');

        //$userRole = $user->roles->lists('id','id')->toArray();
        $userRole = $user->roles->pluck('id','id')->toArray();
        return view('admin.user.edit',['user'=>$user,'roles'=>$roles,'userRole'=>$userRole,'menu'=>$this->menu]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'roles' => 'required'
        ]);

        $input = $request->all();
        /*if(!empty($input['password'])){
            $input['password'] = Hash::make($input['password']);
        }else{
            $input = array_except($input,array('password'));
        }*/

        $user = User::find($id);
        $user->update($input);
        DB::table('role_user')->where('user_id',$id)->delete();

        foreach ($request->input('roles') as $key => $value) {
            $user->attachRole($value);
        }

        return redirect()->route('users.index')
                        ->with('success','User updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        User::find($id)->delete();
        return redirect()->route('users.index')
                        ->with('success','User deleted successfully');
    }

    public function getListCity()
    {
        $id = Input::get('id');
        $city = DB::table('regencies')->where('province_id','=',$id)->get();
        return Response::json($city);
    }

    public function getListDistrict()
    {
      $id = Input::get('id');
        $districts = DB::table('districts')->where('regency_id','=',$id)->get();
        return Response::json($districts);
    }

    public function getListSubdistrict()
    {
      $id = Input::get('id');
        $subdistricts = DB::table('villages')->where('district_id','=',$id)->get();
        return Response::json($subdistricts);
    }

    public function oracleIndex()
    {
        $customers = Customer::whereNotNull('oracle_customer_id')->where('status','=','A')
                    ->orderBy('customer_name','asc')->get();
          /*$customers = DB::table('customers as c')->leftjoin('users as u','c.id','=','u.customer_id')->leftjoin('role_user as ru','u.id','=','ru.user_id')
                      ->leftjoin('roles as r','ru.role_id','=','r.id')
                      ->whereNotNull('oracle_customer_id')
                      ->where('c.status','=','A')
                      ->select('c.id','c.customer_name','c.customer_number','c.tax_reference','customer_category_code'
                              ,'c.customer_class_code','c.pharma_flag','c.psc_flag','c.export_flag','c.tollin_flag','c.subgroup_dc_id','u.email','ru.role_id','r.name as role_name')
                      ->orderBy('customer_name','asc')->get();*/
        return view('admin.oracle.customerindex',['customers'=>$customers,'menu'=>'customer-oracle']);
    }

    public function oracleShow($id)
    {
        $customer = Customer::whereNotNull('oracle_customer_id')->where('status','=','A')
                    ->where('id','=',$id)
                    ->orderBy('customer_name','asc')->first();

        $customer_sites = CustomerSite::where('customer_id','=',$id)->get();
        $customer_contacts = CustomerContact::where('customer_id','=',$id)->get();
        $roles = Role::whereIn('name',['Distributor','Distributor Cabang','Principal','Outlet','Apotik/Klinik'])->get();
        $principals = DB::table('users as u')->join('role_user as ru','ru.user_id','=','u.id')
                      ->join('roles as r','r.id','=','ru.role_id')
                      ->where('r.name','=','Principal')
                      ->select('u.name','u.id','u.customer_id')
                      ->get();
        return view('admin.oracle.customershow',['customer'=>$customer,'customer_sites'=>$customer_sites
                                                ,'customer_contacts'=>$customer_contacts,'roles'=>$roles
                                                ,'principals'=>$principals,'menu'=>'customer-oracle']);
    }

    public function oracleUpdate(Request $request,$id)
    {
        $customer = Customer::whereNotNull('oracle_customer_id')->where('status','=','A')
                    ->where('id','=',$id)
                    ->orderBy('customer_name','asc')->first();
        //dd($customer);
        $customer->psc_flag = $request->psc_flag;
        $customer->pharma_flag = $request->pharma_flag;
        $customer->export_flag = $request->export_flag;
        $customer->tollin_flag = $request->tollin_flag;
        $customer->save();
        if($request->distributor!="")
        {
          $customer->hasDistributor()->sync($request->distributor);
        }

        $usercustomer = User::where('customer_id','=',$customer->id)->first();
        if($usercustomer)
        {
            $usercustomer->email = $request->email;
            $usercustomer->save();
        }else{
          $usercustomer = User::firstorCreate(
                            ['customer_id'=>$customer->id],
                            ['email'=>$request->email,'name'=>$request->customer_name
                            ,'api_token'=>str_random(60)]
                      );
        }
        if($request->role!="")
        {
          $usercustomer->roles()->sync($request->role);
        }
        if(isset($request->send_customer))
        {
          $usercustomer->notify(new InvitationUser($usercustomer));
          return redirect()->route('useroracle.show',$id)
                          ->with('success','Successfully Send Email');
        }else{
          return redirect()->route('useroracle.show',$id)
                          ->with('success','Customer Oracle updated successfully');
        }


    }
    public function cabangCreate($parent_id)
    {
      $customer = Customer::whereNotNull('oracle_customer_id')->where('status','=','A')
                  ->where('id','=',$parent_id)
                  ->orderBy('customer_name','asc')->first();
      $provinces = DB::table('provinces')->get();
      return view('admin.oracle.cabangcreate',['parent'=>$customer,'provinces'=>$provinces,'menu'=>'customer-cabang']);
    }


    public function cabangStore(Request $request,$parent_id)
    {
      $this->validate($request, [
          'customer_name' => 'required',
          'email' => 'required|email|unique:users,email',
          'address'=>'required',
          'province'=>'required',
          'city'=>'required',
          'district'=>'required',
          'subdistricts'=>'required',
      ]);
      $customer = Customer::where('customer_name','like',$request->customer_name)->first();
      if($customer)
      {
          return redirect()->back()->withErrors(['customer_name', trans('validation.unique')]);
      }else{
        $parentcustomer =Customer::where('id','=',$parent_id)->first();
        if($parentcustomer)
        {
          $newcustomer =  Customer::create(['customer_name'=>strtoupper($request->customer_name)
                                            ,'status'=>'A'
                                            ,'pharma_flag'=>$parentcustomer->pharma_flag
                                            ,'psc_flag'=>$parentcustomer->psc_flag
                                            ,'export_flag'=>$parentcustomer->export_flag
                                            ,'tollin_flag'=>$parentcustomer->tollin_flag
                                            ,'parent_dist'=>$parentcustomer->id
                                          ]);
          $province=DB::table('provinces')->where('id','=',$request->province)->first();
          $city=DB::table('regencies')->where('id','=',$request->city)->first();
          $district=DB::table('districts')->where('id','=',$request->district)->first();
          $state=DB::table('villages')->where('id','=',$request->subdistricts)->first();
          $newsite = CustomerSite::create(['site_use_code'=>'SHIP_TO'
                                          ,'primary_flag'=>'P'
                                          ,'status'=>'A'
                                          ,'address1'=>$request->address
                                          ,'state'=>$state->name
                                          ,'district'=>$district->name
                                          ,'city'=>$city->name
                                          ,'province'=>$province->name
                                          ,'province_id'=>$request->province
                                          ,'city_id'=>$request->city
                                          ,'district_id'=>$request->district
                                          ,'state_id'=>$request->subdistricts
                                          ,'postal_code'=>$request->postal_code
                                          ,'country'=>'ID'
                                          ,'customer_id'=>$newcustomer->id

          ]);
          $custcontact= new CustomerContact();
           $custcontact->contact_name = '';
           $custcontact->contact_type = 'EMAIL';
           $custcontact->contact =$request->email;
           $custcontact->customer_id=$newcustomer->id;
           $custcontact->save();
          $newuser = User::create(['id'=>Uuid::generate()->string
                                  ,'email'=> $request->email
                                  ,'name'=>strtoupper($request->customer_name)
                                  ,'customer_id'=>$newcustomer->id
                                  ,'api_token'=>str_random(60)
                    ]);
          //$newuser = User::where('id','=','ebf4e3f0-c6f9-11e7-9af5-8b1784dd6434')->first();
          $roleoutlet = Role::where('name','=','Distributor Cabang')->first();
          $newuser->roles()->attach($roleoutlet->id);
          $newuser->notify(new InvitationUser($newuser));
          return redirect()->route('useroracle.show',$parent_id)
                          ->with('message','Distributor Cabang berhasil ditambahkan dan email verifikasi telah dikirim.');
        }
      }
    }

    public function cabangEdit($id)
    {
      $distcabang = Customer::where('id','=',$id)->first();
      $alamat = CustomerSite::where([['customer_id','=',$id],['primary_flag','=','P']])->orderBy('created_at','asc')->first();
      $provinces = DB::table('provinces')->get();
      $roles = DB::table('roles')->whereIn('name',['Distributor','Distributor Cabang'])->get();
      return view('admin.oracle.cabangedit',['customer'=>$distcabang,'alamat'=>$alamat,'roles'=>$roles,'provinces'=>$provinces,'menu'=>'customer-cabang']);
    }

    public function cabangUpdate(Request $request,$id)
    {
      $this->validate($request, [
          'customer_name' => 'required',
          'email' => 'required|email|unique:users,email',
          'address'=>'required',
          'province'=>'required',
          'city'=>'required',
          'district'=>'required',
          'subdistricts'=>'required',
      ]);
      $distcabang = Customer::where('id','=',$id)->first();
      $distcabang->customer_name = $request->customer_name;
      $province=DB::table('provinces')->where('id','=',$request->province)->first();
      $city=DB::table('regencies')->where('id','=',$request->city)->first();
      $district=DB::table('districts')->where('id','=',$request->district)->first();
      $state=DB::table('villages')->where('id','=',$request->subdistricts)->first();
      $alamat = CustomerSite::where([['customer_id','=',$id],['primary_flag','=','P']])
              ->update(['province_id'=>$request->province
                        ,'city_id'=>$request->city
                        ,'district_id'=>$request->district
                        ,'state_id'=>$request->state
                        ,'address1'=>$request->address
                        ,'state'=>$state->name
                        ,'district'=>$district->name
                        ,'city'=>$city->name
                        ,'province'=>$province->name
                        ,'postal_code'=>$request->postal_code
              ]);
      $user = User::where('customer_id','=',$id)->first();
      if ($user->email!=$request->email)
      {
        $user = User::where('customer_id','=',$id)
                  ->update(['email'=>$request->email]);
      }
      return redirect()->route('usercabang.edit',$id)->with('message','Successfully edit data');;
    }
}
