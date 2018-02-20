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
use Illuminate\Support\Collection;
use Webpatser\Uuid\Uuid;
use App\Notifications\InvitationUser;
use Auth;
use Datatables;
use Carbon\Carbon;



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
        if(is_null($id)) $city = DB::table('regencies')->orderBy('name')->get();
        else $city = DB::table('regencies')->where('province_id','=',$id)->get();
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

    public function getkategoriOutlet()
    {
      $kategori = DB::table('category_outlets')->where('enable_flag','=','Y');
      $id = Input::get('id');
      if(!is_null($id)){
        $kategori =$kategori->whereNotExists(function ($query) use($id) {
                  $query->select(DB::raw(1))
                        ->from('distributor_mappings')
                        ->where('data','=','category_outlets')
                        ->whereRaw('distributor_mappings.data_id= category_outlets.id')
                        ->where('distributor_mappings.distributor_id','=',$id);
              });
      }
      $kategori=$kategori->get();
      return Response::json($kategori);
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
      DB::enableQueryLog();
        $groupid=null;
        $customer = Customer::whereNotNull('oracle_customer_id')->where('status','=','A')
                    ->where('id','=',$id)
                    ->orderBy('customer_name','asc')->first();
        if(isset($customer->subgroup_dc_id)) $groupid =  $customer->subgroupdc->group_id;
        $customer_sites = CustomerSite::where('customer_id','=',$id)->get();
        $customer_contacts = CustomerContact::where('customer_id','=',$id)->get();
        $roles = Role::whereIn('name',['Distributor','Distributor Cabang','Principal','Outlet','Apotik/Klinik'])->get();
        $group = DB::table('Group_DataCenters')->where('enabled_flag','1')->get();
        $principals = DB::table('users as u')->join('role_user as ru','ru.user_id','=','u.id')
                      ->join('roles as r','r.id','=','ru.role_id')
                      ->where('r.name','=','Principal')
                      ->select('u.name','u.id','u.customer_id')
                      ->get();
        $mappings = DB::table('distributor_mappings')
                    ->leftjoin('regencies as r',function($join){
              				$join->on('distributor_mappings.data_id','=','r.id');
              				$join->on('distributor_mappings.data','=',DB::raw("'regencies'"));
              			})
                    ->leftjoin('category_outlets as co',function($join){
              				$join->on('distributor_mappings.data_id','=','co.id');
              				$join->on('distributor_mappings.data','=',DB::raw("'category_outlets'"));
              			})
                    ->where('distributor_id','=',$customer->id)
                    ->select('distributor_mappings.id','data',db::raw("case when data = 'regencies' then r.name else co.name end name"))
                    ->get();
        return view('admin.oracle.customershow',['customer'=>$customer,'customer_sites'=>$customer_sites
                                                ,'customer_contacts'=>$customer_contacts,'roles'=>$roles
                                                ,'principals'=>$principals,'mappings'=>$mappings
                                                ,'menu'=>'customer-oracle','groups'=>$group,'groupid'=>$groupid]);
    }

    public function oracleUpdate(Request $request,$id)
    {
      DB::beginTransaction();
      try{
        //dd($request->all());
        if($request->save_customer=="Send" or $request->save_customer=="Save")
        {
          $customer = Customer::whereNotNull('oracle_customer_id')->where('status','=','A')
                      ->where('id','=',$id)
                      ->orderBy('customer_name','asc')->first();
          //dd($customer);
          if($customer->customer_class_code=="OUTLET" and $request->psc_flag=="1")
          {
            $this->validate($request, [
                'subgroupdc' => 'required',
            ]);
            $customer->subgroup_dc_id = $request->subgroupdc;
          }
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
              $this->validate($request, [
                  'email' => 'required|email|unique:users,email,'.$usercustomer->id
              ]);
              $usercustomer->email = $request->email;
              $usercustomer->save();
          }else{
            $this->validate($request, [
                'email' => 'required|email|unique:users'
            ]);
            $usercustomer = User::firstorCreate(
                              ['customer_id'=>$customer->id],
                              ['email'=>$request->email,'name'=>$request->customer_name
                              ,'api_token'=>str_random(60)
                              , 'id'=>Uuid::generate()->string]
                        );

          }
          if($request->role!="")
          {
            $usercustomer->roles()->sync($request->role);
          }
          DB::commit();
          if($request->save_customer=="Send")
          {
            $usercustomer->validate_flag=1;
            $usercustomer->save();
            $usercustomer->notify(new InvitationUser($usercustomer));
            return redirect()->route('useroracle.show',$id)
                            ->with('success','Successfully Send Email');
          }elseif($request->save_customer=="Save"){
            return redirect()->route('useroracle.show',$id)
                            ->with('success','Customer Oracle updated successfully');
          }
        }elseif($request->action_mapping=="delete"){
          if(isset($request->mapping)){
            foreach($request->mapping as $key=>$value)
            {
              DB::enableQueryLog();
                $deletedata = DB::table('distributor_mappings')->whereraw("distributor_id='".$id."'")
                  ->whereraw("data='".$key."'")
                  ->whereIn('data_id',$value)
                  ->delete();
            }
            DB::commit();
            return redirect()->route('useroracle.show',$id)
                            ->with('success','Successfully delete data mapping');
          }else{
            return redirect()->back()->withInput()
                            ->withErrors('mapping','Pilih salah satu!');
          }
        }
      }catch (\Exception $e) {
        DB::rollback();
        throw $e;
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
                                          ,'address1'=>strtoupper($request->address)
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
                        ,'address1'=>strtoupper($request->address)
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

    public function CustYasaNonOracle()
    {
      $customers = Customer::join('outlet_distributor as od','od.outlet_id','=','customers.id')
                ->join('customers as yasa','od.distributor_id','=','yasa.id')
                ->where('yasa.customer_number','=',config('constant.customer_yasa'))
                ->whereNull('customers.oracle_customer_id')
                ->where('customers.status','=','A')
                ->select ('customers.*')
                ->get();
      return view('admin.oracle.CustYasaNonOracle',['customers'=>$customers,'menu'=>'custYasa']);
    }

    public function mergeCustomer(Request $request,$id)
    {
      if($request->save=="save")
      {
        DB::beginTransaction();
        try{
          $user = User::find($id);
          if(Auth::user()->hasRole('IT Galenium'))
          {
            $oldcustomer=$user->customer_id;
            if(isset($request->c_number))
            {
              $oraclecustomer = Customer::leftjoin('users','customers.id','=','users.customer_id')
                              ->where('customer_number','=',$request->c_number)
                              ->select('customers.*','users.email',DB::raw("ifnull('users.register_flag',0) as register_flag"))
                              ->first();
              if($oraclecustomer)
              {
                if($oraclecustomer->register_flag==0){
                  /*update all po outlet_id*/
                  DB::table('po_draft_headers')->where('customer_id','=',$oldcustomer)->update(['customer_id'=>$oraclecustomer->id]);
                  $soheaders = DB::table('so_headers')->where('customer_id','=',$oldcustomer)
                    //->whereIn('status',[0,1])
                    ->select('cust_ship_to','cust_bill_to','customer_id')
                    ->groupBy('cust_ship_to','cust_bill_to','customer_id')
                    ->get();
                  foreach($soheaders as $sh)
                  {
                    $orasitebill=null;
                    $orasiteship=null;
                    $shipto =CustomerSite::where('site_use_code','SHIP_TO')
                                ->where('customer_id',$sh->customer_id)
                                ->where('id',$sh->cust_ship_to)
                                ->first();

                    $billto =CustomerSite::where('site_use_code','BILL_TO')
                                ->where('customer_id',$sh->customer_id)
                                ->where('id',$sh->cust_bill_to)
                                ->first();

                    if($shipto){
                      $orasiteship =CustomerSite::where('customer_id',$oraclecustomer->id)
                                ->where('site_use_code','=',$shipto->site_use_code)
                                ->where('province','=',$shipto->province)
                                ->where('city','=',$shipto->city)
                                ->where('district','=',$shipto->district)
                                ->first();

                    }

                    if($billto){
                      $orasitebill =CustomerSite::where('customer_id',$oraclecustomer->id)
                                ->where('site_use_code','=',$billto->site_use_code)
                                ->where('province','=',$billto->province)
                                ->where('city','=',$billto->city)
                                ->where('district','=',$billto->district)
                                ->first();

                    }
                    if($orasitebill and $orasiteship){
                      DB::table('so_headers')->where('customer_id','=',$sh->customer_id)
                      ->where('cust_ship_to','=',$sh->cust_ship_to)
                      ->where('cust_bill_to','=',$sh->cust_bill_to)
                      ->update(['customer_id'=>$oraclecustomer->id
                                ,'cust_ship_to'=>$orasiteship->id
                                ,'cust_bill_to'=>$orasitebill->id
                                ,'price_list_id'=>$orasitebill->price_list_id
                                ,'payment_term_id'=>$orasitebill->payment_term_id
                                ,'oracle_ship_to'=>$orasiteship->site_use_id
                                ,'oracle_bill_to'=>$orasitebill->site_use_id
                                ,'oracle_customer_id'=>$oraclecustomer->oracle_customer_id]);
                    }
                  }


                  /*attach to all outlet_distributor*/
                  $distributor = DB::table('outlet_distributor')->where('outlet_id','=',$oldcustomer)->select('distributor_id')->get();

                  if($distributor) $oraclecustomer->hasDistributor()->sync($distributor->pluck('distributor_id')->toArray());
                  /*product stock jika role Apotik/Klinik*/
                  if($user->hasRole('Apotik/Klinik')){
                    DB::table('outlet_products')->where('outlet_id','=',$oldcustomer)->update(['outlet_id'=>$oraclecustomer->id]);
                    DB::table('outlet_stock')->where('outlet_id','=',$oldcustomer)->update(['outlet_id'=>$oraclecustomer->id]);
                  }
                  /*Inactive old customer_id*/
                  $dataoldcustomer = Customer::where('id','=',$oldcustomer)->first();
                  $dataoldcustomer->status = 'I';
                  $dataoldcustomer->save();
                  $oraclecustomer->longitude = $dataoldcustomer->longitude;
                  $oraclecustomer->langitude = $dataoldcustomer->langitude;
                  $oraclecustomer->psc_flag = $dataoldcustomer->psc_flag;
                  $oraclecustomer->pharma_flag = $dataoldcustomer->pharma_flag;
                  $oraclecustomer->outlet_type_id = $dataoldcustomer->outlet_type_id;
                  $oraclecustomer->subgroup_dc_id = $dataoldcustomer->subgroup_dc_id;
                  $oraclecustomer->save();
                  $useroracle=User::where('customer_id','=',$oraclecustomer->id)->where('register_flag','=','0')->delete();

                  $user->customer_id = $oraclecustomer->id;
                  $user->save();
                  DB::commit();
                  return redirect()->route('useroracle.show',['id'=>$id])->withMessage('Berhasil dimerge!');
                }else{
                  return redirect()->back()->withInput()->withErrors(['c_number'=>'Customer number already has registered to another user']);
                }
              }
            }else{
              return redirect()->back()->withInput()->withMessage('Customer Number Oracle harus diisi!');
            }
          }

        }catch (\Exception $e) {
          DB::rollback();
          throw $e;
        }
      }
    }

    public function ajaxAddMappingType(Request $request)
    {
       $error_array = array();
       $success_output = '';
      if($request->get('button_action')=="add")
      {
        $customerid=$request->get('customerid');
        $datatype = $request->get('type');
        foreach ($request->value as $nil){
          $insert = DB::table("distributor_mappings")
                    ->insert(['distributor_id'=>$customerid
                              ,'data'=>$datatype
                              ,'data_id'=>$nil
                              ,'created_at'=>Carbon::now()
                              ,'updated_at'=>Carbon::now()
                            ]);
        }
        $success_output = '<div class="alert alert-success">Data Inserted</div>';
      }
      $output = array(
            'error'     =>  $error_array,
            'success'   =>  $success_output
        );
      echo json_encode($output);

    }

    public function ajaxGetMappingType($id=null)
    {
      $mappings = DB::table('distributor_mappings')
                  ->leftjoin('regencies as r',function($join){
                    $join->on('distributor_mappings.data_id','=','r.id');
                    $join->on('distributor_mappings.data','=',DB::raw("'regencies'"));
                  })
                  ->leftjoin('category_outlets as co',function($join){
                    $join->on('distributor_mappings.data_id','=','co.id');
                    $join->on('distributor_mappings.data','=',DB::raw("'category_outlets'"));
                  });
      if(!is_null($id))
      $mappings =$mappings->where('distributor_id','=',$id);

      $mappings =$mappings->select('distributor_mappings.id','data',db::raw("case when data = 'regencies' then r.name else co.name end name"));

      return Datatables::of($mappings)
            ->editColumn('id',function($mappings){
              return '<input type="checkbox" class="chk-mapping" name="mapping[]" value='.$mappings->id.'>';
            })->rawColumns(['id'])
            ->make(true);
    }

    public function MappingOutletDistributor($id=null)
    {
      DB::enableQueryLog();
      if($id!=null)
      {
        $dists= Customer::where('id','=',$id)
              ->select('customers.id',DB::Raw("ifnull(customers.psc_flag,0) as psc_flag"),DB::Raw("ifnull(customers.pharma_flag,0) as pharma_flag"),'customers.outlet_type_id','customer_number','customer_name')
              ->get();
      }else{
        $dists = Customer::join('users as u','u.customer_id','customers.id' )
              ->leftjoin('role_user as ru','ru.user_id','=','u.id')
              ->leftjoin('roles as r','ru.role_id','=','r.id')
              ->where('customers.status','=','A')
              ->whereIn('r.name',['Distributor','Distributor Cabang','Principal'])
              ->select('customers.id',DB::Raw("ifnull(customers.psc_flag,0) as psc_flag"),DB::Raw("ifnull(customers.pharma_flag,0) as pharma_flag"),'customers.outlet_type_id','customer_number','customer_name')
              ->get();
      }
      foreach ($dists as $dist)
      {
          if($dist->customer_number==config('constant.customer_yasa')) $dist->pharma_flag=0;
          $mappings = DB::table('distributor_mappings')
                ->where('distributor_id','=',$dist->id)
                ->select('data','data_id')->groupBy('data','data_id')->get();
          $data = customer::leftjoin('category_outlets as co','customers.outlet_type_id','co.id')
            ->whereExists(function ($query2){
            $query2->select(DB::raw(1))
                  ->from('users as u')
                  ->leftjoin('role_user as ru','ru.user_id','u.id')
                  ->leftjoin('roles as r','r.id','ru.role_id')
                  ->whereraw("u.customer_id=customers.id")
                  ->whereIn('r.name',['Outlet','Apotik/Klinik']);
                });
          if($dist->psc_flag=="1" and $dist->pharma_flag=="1")
          {
            $data=$data->where('psc_flag','=',$dist->psc_flag)->orwhere('pharma_flag','=',$dist->pharma_flag);
          }elseif($dist->psc_flag=="1") {
            $data=$data->whereraw("ifnull(psc_flag,0) = '".$dist->psc_flag."'");
          }elseif($dist->pharma_flag=="1") {
            $data=$data->whereraw("ifnull(pharma_flag,0)='".$dist->pharma_flag."'");
          }else{
            $data=$data->where('psc_flag','=',$dist->psc_flag)->where('pharma_flag','=',$dist->pharma_flag);
          }

          foreach($mappings->groupBy('data') as $key=>$values)
          {
              $map1 = $values->pluck('data_id')->toArray();
              //$map1 = $mappings->where('data',$key)->pluck('data_id')->toArray();
              if($key == "regencies")
              {
                $data = $data->whereExists(function ($query) use($map1) {
                      $query->select(DB::raw(1))
                            ->from('customer_sites as cs')
                            ->whereraw('cs.customer_id=customers.id')
                            ->whereIn('cs.city_id',$map1);
                });
              }elseif($key=="category_outlets"){
                $data = $data->whereIn('customers.outlet_type_id',$map1);
              }
          }
          $data=$data->select('customers.*','co.name as cat_name')->get();

          foreach($data as $d)
          {
            $d->ada='T';
            if($d->hasDistributor->where('id',$dist->id)->count()>0){
              $d->ada='Y';
            }
          }
          $dist->mapping = $data;
          $dist->delete_mapping = collect([]);
          $oldoutlet =$dist->hasOutlet;
          $oldoutlet = $oldoutlet->filter(function ($value, $key) {
                        return ($value->customer_class_code ==null or $value->customer_class_code=="OUTLET");
                    });
          if($oldoutlet->count()>0 and $data->count()>0)
          {
            $deletemapping = $oldoutlet->whereNotIn('id',$data->pluck('id')->unique());
            if($deletemapping->count()>0)
            {
              $dist->delete_mapping = $deletemapping;
            }
          }
      }
      $menu = "";
      return view('admin.oracle.mappingoutlet',compact('id','dists','menu','deletemapping'));

    }

    public function remappingOutlet(Request $request, $id=null)
    {
      DB::beginTransaction();
      try{
        if(is_array($request->delarray))
        {
          foreach($request->delarray as $keydist=>$dist)
          {
            $distributor = Customer::where('id','=',$keydist)
                          ->whereExists(function ($query2){
                              $query2->select(DB::raw(1))
                                    ->from('users as u')
                                    ->leftjoin('role_user as ru','ru.user_id','u.id')
                                    ->leftjoin('roles as r','r.id','ru.role_id')
                                    ->whereraw("u.customer_id=customers.id")
                                    ->whereIn('r.name',['Principal','Distributor','Distributor Cabang']);
                                  })
                          ->first();
            if(is_array($dist) and $distributor)
            {
              foreach($dist as $keyoutlet=>$outlet)
              {
                echo "delete:".$keydist."-".$outlet."<br>";
                $distributor->hasOutlet()->detach($outlet);
              }
            }
          }
        }
        if(is_array($request->insertarray))
        {
          foreach($request->insertarray as $keydist=>$dist)
          {
            $distributor = Customer::where('id','=',$keydist)
                          ->whereExists(function ($query2){
                              $query2->select(DB::raw(1))
                                    ->from('users as u')
                                    ->leftjoin('role_user as ru','ru.user_id','u.id')
                                    ->leftjoin('roles as r','r.id','ru.role_id')
                                    ->whereraw("u.customer_id=customers.id")
                                    ->whereIn('r.name',['Principal','Distributor','Distributor Cabang']);
                                  })
                          ->first();
            if(is_array($dist) and $distributor)
            {
              foreach($dist as $keyoutlet=>$outlet)
              {
                $distributor->hasOutlet()->attach($outlet);
                echo "insert:".$keydist."-".$outlet."<br>";
              }
            }
          }
        }
        DB::commit();
        return redirect()->route('customer.mappingOutlet',$id)->with('success','Successfull ReMapping Distributor-Outlet');
      }catch (\Exception $e) {
        DB::rollback();
        throw $e;
      }

    }
}
