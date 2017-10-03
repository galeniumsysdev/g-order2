<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Customer;
use App\CustomerSite;
use App\CustomerContact;
use App\CategoryOutlet;
use App\SubgroupDatacenter;
use App\Role;
use App\GroupDatacenter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Auth;
use App\Notifications\NewoutletDistributionNotif;
use App\Notifications\MarketingGaleniumNotif;
use App\Notifications\RejectDistributorNotif;
use App\Notifications\FirstLoginRegister;
use App\Notifications\RejectbyGPLNotif;
use App\OutletDistributor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Input;

class CustomerController extends Controller
{
  public function __construct()
  {
      $this->middleware('auth');
  }

  public function show($id,$notif_id)
  {
    $user = User::find($id);
    $customer_email = $user->email;
    $roleid =  DB::table('Users')
      ->join('role_user', 'users.id', '=', 'role_user.user_id')
      ->join('roles', 'role_user.role_id', '=', 'roles.id')
      ->select('role_user.role_id')
      ->where('Users.id','=',$id)
      ->first();
    if (isset($user->customer_id)){
      $customer = $user->customer;
      $customer_contacts = $customer->contact()->where('contact_type','<>', 'EMAIL')->get();

      //dd($groupdcs);
      //$customer_email = $customer->contact()->where('contact_type', 'EMAIL')->first();
      //$customer_contacts= $customer->whereHas(colors, function ($query) {                                         $query->where('color', 'blue'); //'color' is the column on the Color table where 'blue' is stored.}])->get();
      $customer_sites = $customer->sites;

      if (Auth::user()->ability(array('Marketing PSC', 'Marketing Pharma'),'') )
      {
        if ($customer->Status=="R")
        {
          Auth::User()->notifications()
                      ->where('id','=',$notif_id)
                        ->update(['read_at' => Carbon::now()]);
        }
        $customer_email = $customer->contact()->where('contact_type', 'EMAIL')->first();
        if (!empty($customer->subgroup_dc_id))
        {
          $subgroupdc =SubGroupDatacenter::find($customer->subgroup_dc_id);
          $groupdcid = $subgroupdc->group_id;
        }else{
          $groupdcid="";
        }
        $groupdcs = GroupDatacenter::all();//where('enabled_flag','1');
        $categories = CategoryOutlet::where('enable_flag','Y')->get();
        $roles = Role::whereIn('name',['Outlet','Distributor','Distributor Cabang'])->get();
        $distributors = DB::table('customers as a')
                        ->join('outlet_distributor as b','a.id','=','b.outlet_id')
                        ->join('customers as c','b.distributor_id','=','c.id')
                        ->join('Users as d','a.id','=','d.customer_id')
                        ->select ('c.id as distributor_id','c.customer_name as distributor_name','b.approval','b.keterangan')
                        ->where('d.id','=',$id)
                        ->get();
          return view('admin.customer.edit',compact('user','customer','customer_contacts','customer_email','customer_sites','categories','roles','groupdcs','groupdcid','roleid','distributors','notif_id'));
      }elseif(Auth::user()->ability(array('Distributor', 'Principal', 'Distributor Cabang'),'')  )
      {
          if($customer->categoryOutlet){
              $categoryoutlet = $customer->categoryOutlet->name;
          }

          $subgroupdc = $customer->subgroupdc;
          if($subgroupdc)
          {
            $groupdc = $subgroupdc->groupdatacenter->display_name;
            $subgroupname = $subgroupdc->display_name;
          }

          $email = $user->email;
          $outletdist = OutletDistributor::where([
            ['outlet_id','=',$user->customer_id],
            ['distributor_id','=',Auth::User()->customer_id],
            ])->first();

            if($outletdist){
              //dd($outletdist);
            //$id = $user->id;
            //$customer1 = $customer->pluck('id','customer_name','pharma_flag','psc_flag','tax_reference')->toArray();
            //$customer=DB::table('customers')->where('id','=',$user->customer_id)->select('id','customer_name','pharma_flag','psc_flag','tax_reference')->get();
            //dd($customer);
              return view('admin.customer.show',compact('customer','email','categoryoutlet','subgroupname','groupdc','customer_sites','customer_contacts','outletdist','notif_id'));
            }else{
              abort(403, 'Unauthorized action.');
            }

      }else{
          return view('errors.403');
      }

    }
    return view('/home')->withmessage('Data Customer Not Found');
  }

  public function search($search,$id)
  {/*Menampilkan list distributor yang bisa dipilih*/
    //$id adalah id dari outlet
    $user = User::find($id);
    $customer_id = $user->customer_id;

    /*$customers = DB::table('customers')
            ->join('customer_sites', 'customers.id', '=', 'customer_sites.customer_id')
            ->join('users','customers.id','=','users.customer_id')
            ->select('customers.id','customers.customer_name', 'customer_sites.address1','customer_sites.city'
                    ,'customer_sites.state', 'customer_sites.postalcode')
            ->where([
              ['users.register_flag','=',true],
              ['customers.customer_name','like','%'.strtolower($search).'%']
            ]);*/
    $customers = DB::table('customers')
            ->join('users','customers.id','=','users.customer_id')
            ->select('customers.id','customers.customer_name')
            ->where([
              ['users.register_flag','=',true],
              ['customers.customer_name','like','%'.strtolower($search).'%']
            ]);

    if ($user->hasRole("Outlet"))
    {
      $customers = $customers ->whereExists(function ($query) {
                /*$query->select(DB::raw(1))
                      ->from('role_user')
                      ->whereRaw('users.id = role_user.user_id')
                      ->whereIn('role_user.role_id', [6,7,8]);*/
                      $query->select(DB::raw(1))
                            ->from('role_user')
                            ->join('roles', 'role_user.role_id', '=', 'roles.id')
                            ->whereRaw('users.id = role_user.user_id')
                            ->whereIn('roles.name', ['Principal','Distributor','Distributor Cabang']);
            });
    }elseif($user->hasRole("Distributor")  or $user->hasRole("Distributor Cabang"))
    {
      $customers = $customers ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('role_user')
                      ->join('roles', 'role_user.role_id', '=', 'roles.id')
                      ->whereRaw('users.id = role_user.user_id')
                      //->whereIn('role_user.role_id', [8]);
                      ->whereIn('roles.name', ['Principal']);
            });
    }else {
      $customers = $customers ->where('user_id','<','0');
    }

    if(Auth::user()->hasRole('Marketing PSC'))
    {
      $customers = $customers->where('customers.psc_flag','=','1');
    }elseif(Auth::user()->hasRole('Marketing Pharma'))
    {
      $customers = $customers->where('customers.pharma_flag','=','1');
    }

    $customers = $customers ->whereNotExists(function ($query) use($customer_id) {
              $query->select(DB::raw(1))
                    ->from('outlet_distributor')
                    ->whereRaw('outlet_distributor.distributor_id= customers.id')
                    ->where('outlet_distributor.outlet_id','=',$customer_id);
          });

      $customers = $customers->get(); //paginate(5);
      //dd($customers);
      if($customers->count()>0){
        return view('admin.customer.listdistributor',compact('customers'));
      }else{
        return "";
      }


    //return Response::json($customers);
  }

  public function rejectGPL(Request $request)
  {
    $user = User::find($request->id);
    //$user->detachRoles($user->roles);
    $customer = Customer::find($user->customer_id);
    $customer->status = 'R';
    if(Auth::User()->hasRole('Marketing PSC'))
    {
      $customer->id_approval_psc = Auth::User()->id;
      $customer->date_approval_psc =Carbon::now();
    }
    if(Auth::User()->hasRole('Marketing Pharma'))
    {
      $customer->id_approval_pharma = Auth::User()->id;
      $customer->date_approval_pharma =Carbon::now();
    }
    $customer->keterangan = $request->alasan;
    $customer->save();
    Auth::User()->notifications()
                ->where('id','=',$request->notif_id)
                  ->update(['read_at' => Carbon::now()]);
    $user->notify(new RejectbyGPLNotif());
    return response()->json([
                    'result' => 'success',
                    'message' => trans('pesan.reject'),
                  ],200);
  }

  public function update(Request $request, $id)
  {//approve outlet by gpl
      $user = User::find($id);
      $user->detachRoles($user->roles);
      $user->roles()->attach($request->role);
      $customer = Customer::find($user->customer_id);
      if($customer->psc_flag <>  $request->psc_flag and $request->psc_flag==1) {
        //jika sebelumnya psc belum dipilih maka buat notification ke marketing psc
        $marketings_psc = User::whereHas('roles', function($q){
            $q->where('name','Marketing PSC');
        })->get();
        foreach ($marketings_psc as $mpsc)
        {
            $mpsc->notify(new MarketingGaleniumNotif($user));
        }
      }
      if($customer->pharma_flag <>  $request->pharma_flag and $request->pharma_flag==1) {
        //jika sebelumnya pharma belum dipilih maka buat notification ke marketing pharma
        $marketings_pharma = User::whereHas('roles', function($q){
            $q->where('name','Marketing Pharma');
        })->get();
        //$marketings_pharma = Role::with('users')->where('name', 'Marketing Pharma')->get();
        //dd($marketings_pharma);
        foreach ($marketings_pharma as $mpharma)
        {
            $mpharma->notify(new MarketingGaleniumNotif($user));
        }
      }
      $customer->psc_flag =$request->psc_flag;
      $customer->pharma_flag =$request->pharma_flag;
      $customer->status = 'A';
      if($request->role==4){//$user->hasRole('Outlet'//jika outlet
        $customer->outlet_type_id =$request->category;
        $customer->subgroup_dc_id =$request->subgroupdc;
      }else{
        $customer->outlet_type_id =null;
        $customer->subgroup_dc_id =null;
      }
      if(Auth::User()->hasRole('Marketing PSC'))
      {
        $customer->id_approval_psc = Auth::User()->id;
        $customer->date_approval_psc =Carbon::now();
      }
      if(Auth::User()->hasRole('Marketing Pharma'))
      {
        $customer->id_approval_pharma = Auth::User()->id;
        $customer->date_approval_pharma =Carbon::now();
      }
      $customer->save();
      $userlogin = Auth::user();
      $notifications = $userlogin->notifications()
                      ->where([
                        ['id','=',$request->notif_id]
                        /*['type','like','%MarketingGaleniumNotif'],
                        ['notifiable_id','=',$userlogin->id],
                        ['notifiable_type','like', 'App%User'],
                        ['data','like', '%"user":{"id":"'.$id.'"%'],*/
                        ])
                        ->whereNull('read_at')
                        ->get();
      //dd($notifications);
      foreach ($notifications as $notification) {
        if (empty($notification->read_at))
        {
            $notification->markAsRead();
        }
      }
      if ($user->can( 'Create PO'))
      {
        $pesan = " ".trans("pesan.adddistributor");
      }else{
        //mark as read notification for user login and outlet
        $pesan = "";
      }
      //dd($request->notif_id."aaaa");
      //return response()->json(['message'=>'success']);
      return redirect()->route('customer.show',['id'=>$id,'notif_id'=>$request->notif_id])->withMessage(trans("pesan.update").$pesan);

  }

  public function addlist($id, $outletid)
  {/*untuk tambahkan data distributor
    /* id adalah id customer dari distributor yang ingin ditambahkan
    /* $outletid adalah userid dari outlet
    */
    $userlogin = Auth::user();
    /*$notifications = $userlogin->notifications()
                    ->where([
                      ['type','like','%MarketingGaleniumNotif'],
                      ['notifiable_id','=',$userlogin->id],
                      ['notifiable_type','like', 'App%User'],
                      ['data','like', '%{"User":{"id":'.$outletid.'%'],
                      ])
                      ->whereNull('read_at')
                      ->get();
    //dd($notifications);
    foreach ($notifications as $notification) {
      if (empty($notification->read_at))
      {
          $notification->markAsRead();
      }
    }*/
    //notif to distributor and add data to outlet distributor
    $distributor = Customer::find($id);
    $distributor_user = User::where('customer_id','=',$id)->first();
    if ($distributor)
    {
      $user = User::find($outletid);
      $outlet = Customer::find($user->customer_id);
      $distributor_user->notify(new NewoutletDistributionNotif($user,$distributor));
      $outlet->hasDistributor()->attach($id);
      return Response::json($distributor);
    }
  }

    public function approve(Request $request)
    {
      if (Auth::User()->can('ApproveOutlet')){
        DB::table('outlet_distributor')
            ->where([
              ['outlet_id','=',$request->id],
              ['distributor_id','=',Auth::User()->customer_id],
              ])
            ->update(['approval' => true,'tgl_approve'=>Carbon::now()]);
            //unreadmark notification
            Auth::User()->notifications()
                        ->where('id','=',$request->notif_id)
                          ->update(['read_at' => Carbon::now()]);
          $datadist = DB::table('outlet_distributor')
              ->where([
                ['outlet_id','=',$request->id],
                ['distributor_id','!=',Auth::User()->customer_id],
                ['approval','=',1]
                ])->get();
          if (count($datadist)==0)
          {
            $outletusers = User::where('customer_id','=',$request->id)->get();
            foreach($outletusers as $outletuser)
            {
              if (!$outletuser->register_flag)
              {
                  $outletuser->api_token = str_random(60);
                  $outletuser->save();
                  $outletuser->notify(new FirstLoginRegister($outletuser));
              }

            }
          }
          //check outlet. If has not register and just have 1 record outlet approve then email
        return response()->json([
          'result' => 'success',
          'message' => trans('pesan.approve'),
        ],200);
      }else{
        return response()->json([
          'result' => 'failed',
          'message' => trans('auth.noaccess'),
        ],200);
      }

    }

    public function reject(Request $request)
    {
      if (Auth::User()->can('ApproveOutlet')){
       DB::table('outlet_distributor')
            ->where([
              ['outlet_id','=',$request->id],
              ['distributor_id','=',Auth::User()->customer_id],
              ])
            ->update(['approval' => false,'keterangan'=>$request->alasan,'tgl_approve'=> Carbon::now(), 'updated_at'=>Carbon::now()  ]);
        //unreadmark notification
        Auth::User()->notifications()
                    ->where('id','=',$request->notif_id)
                      ->update(['read_at' => Carbon::now()]);
        $outlet = Customer::find($request->id);
        //notif back ke marketing pharma or psc
        if (Auth::User()->customer->psc_flag=="1" and $outlet->psc_flag=="1")
        {
          $dist =OutletDistributor::where([
                  ['outlet_id','=',$request->id],
                  ['distributor_id','=',Auth::User()->customer_id],
                  ])->first();
          //dd($dist);
          $marketings_psc = User::whereHas('roles', function($q){
              $q->where('name','Marketing PSC');
          })->get();
          foreach ($marketings_psc as $mpsc)
          {
              $mpsc->notify(new RejectDistributorNotif($dist,Auth::User()));
          }
        }
        if (Auth::User()->customer->pharma_flag=="1" and $outlet->pharma_flag=="1")
        {
          $dist =OutletDistributor::where([
                  ['outlet_id','=',$request->id],
                  ['distributor_id','=',Auth::User()->customer_id],
                  ])->first();
          //dd($dist);
          $marketings_pharma = User::whereHas('roles', function($q){
              $q->where('name','Marketing Pharma');
          })->get();
          foreach ($marketings_pharma as $mphr)
          {
              $mphr->notify(new RejectDistributorNotif($dist,Auth::User()));
          }

        }
        return response()->json([
          'result' => 'success',
          'message' => trans('pesan.reject'),
        ],200);
      }else{
        return response()->json([
          'result' => 'failed',
          'message' => trans('auth.noaccess'),
        ],200);
      }

    }

    public function ajaxSearchAlamat()
    {
      $search = Input::get('term');
      $results = array();
      if($search!=""){
            $queries = DB::table("customer_sites")
            		->select("id", "address1","state","city","province","postalcode")
                ->where('customer_id','=',auth()->user()->customer_id)
                ->Where(function ($query) use($search) {
                $query->orwhere('city','LIKE',"%$search%")
                      ->orWhere('province','LIKE',"%$search%")
                      ->orWhere('postalcode','LIKE',"%$search%")
                      ->orWhere('address1','LIKE',"%$search%")    ;
                  })->take(10)->get();

                foreach ($queries as $query)
    	          {
                  //if (!is_null($query->state))
    	             $results[] = [ 'id' => $query->id, 'address1' => $query->address1.','.$query->state.','.$query->city.','.$query->province." ".$query->postalcode];
    	          }
                return Response::json($results);
        }

    }
}
