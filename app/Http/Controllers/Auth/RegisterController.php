<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Customer;
use App\CustomerSite;
use App\CustomerContact;
use App\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Mail;
use App\Notifications\VerificationUser;
use App\Notifications\MarketingGaleniumNotif;
use Webpatser\Uuid\Uuid;
use DB;
use Image;
use File;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'address' => 'required|string|max:255',
            'province' => 'required',
            'city' => 'required|string|max:100',
            'subdistricts' => 'required',
            'district' => 'required',
            'postal_code' => 'required|digits:5',
            'HP_1' => 'required|regex:/(8)[0-9]{8,}/',
            'HP_2' => 'nullable|regex:/(8)[0-9]{8,}/',
            'no_tlpn' => 'nullable|regex:/[0-9]{9}/',
            'category' => 'required',
        ]);
    }

    protected function verification(array $data)
    {
        return Validator::make($data, [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6|confirmed',
            'langitude'=> 'required|numeric',
            'longitude'=> 'required|numeric',
            'token1' => 'required|string',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
      //$customer->save();
        return User::create([
            'id' => Uuid::generate()->string,
            'name' => $data['name'],
            'email' => $data['email'],
            //'password' => bcrypt($data['password']),
            'api_token' => str_random(60),
        ]);
    }

    protected function update(array $data)
    {
       $user_check = User::where('email', $data['email'])->where('api_token',$data['token1'])->first();

       if (!$user_check) {
           return back()->with('status', trans('auth.failed'));
       }
       if(!$user_check->register_flag)
       {
         if (isset($user_check->customer_id)){
            $customer = Customer::find($user_check->customer_id);
            $customer->longitude = $data['longitude'];
            $customer->langitude = $data['langitude'];
            $customer->save();
         }
         $user_check->password = bcrypt($data['password']);
         $user_check->register_flag=1;
         $user_check->first_login = date('Y-m-d H:i:s');
         $user_check->api_token='';

         if($user_check->roles->count()==0)
         {
           $roleoutlet = Role::where('name','=','Outlet')->first();
           $user_check->roles()->attach($roleoutlet->id);
         }
         //$user_check->save();
         return $user_check;
       }else{
         //return back()->with('status', trans('auth.alreadyregistered'));
         return $user_check;
       }

    }

    protected function register(Request $request)
    {
      $input = $request->all();

      $validator = $this->validator($input)->validate();
      if ($request->psc=="" and $request->pharma==""){
        //$errors = new array(['error' => [trans('auth.pscflag')]]);
	      //return Redirect::back()->withErrors('psc', trans('auth.pscflag'))->withInput();
        return redirect(route('register'))->withErrors(['psc'=> [trans('auth.pscflag')]])->withInput();
      }
      if($request->hasFile('imgphoto')) {
        $validator = Validator::make($request->all(), [
            'imgphoto' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ])->validate();

      }
      $customer = Customer::create([
          'customer_name' => strtoupper($request->name),
          'status' => 'A',
          'tax_reference' => $request->NPWP,
          'pharma_flag' => $request->pharma,
          'psc_flag' => $request->psc,
          'outlet_type_id' => $request->category,
        ]);
       $customer->save();
       $province=DB::table('provinces')->where('id','=',$request->province)->first();
       $city=DB::table('regencies')->where('id','=',$request->city)->first();
       $district=DB::table('districts')->where('id','=',$request->district)->first();
       $state=DB::table('villages')->where('id','=',$request->subdistricts)->first();

       $custsites = new CustomerSite();
       $custsites->site_use_code = "SHIP_TO";
       $custsites->status = "A";
       $custsites->address1 = strtoupper($request->address);
       $custsites->province = $province->name;
       $custsites->city = $city->name;
       $custsites->district = $district->name;
       $custsites->state = $state->name;
       $custsites->postalcode = $request->postal_code;
       $custsites->Country = 'ID';
       $custsites->customer_id = $customer->id;
       $custsites->primary_flag='Y';
       $custsites->save();

       $custcontact= new CustomerContact();
        $custcontact->contact_name = $request->contact_person;
        $custcontact->contact_type = 'EMAIL';
        $custcontact->contact =$request->email;
        $custcontact->customer_id=$customer->id;
        $custcontact->save();

        $custcontact1= new CustomerContact();
        $custcontact1->contact_name = $request->contact_person;
        $custcontact1->contact_type = 'PHONE';
        $custcontact1->contact ='+62'.$request->HP_1;
        $custcontact1->customer_id=$customer->id;
        $custcontact1->save();

        if(isset($request->HP_2)){
            $custcontact2= new CustomerContact();
            $custcontact2->contact_name = $request->contact_person;
            $custcontact2->contact_type = 'PHONE';
            $custcontact2->contact ='+62'.$request->HP_2;
            $custcontact2->customer_id=$customer->id;
            $custcontact2->save();
        }
        if(isset($request->no_tlpn)){
             $custcontact3= new CustomerContact();
             $custcontact3->contact_name = $request->contact_person;
             $custcontact3->contact_type = 'PHONE';
             $custcontact3->contact ='+62'.$request->no_tlpn;
             $custcontact3->customer_id=$customer->id;
             $custcontact3->save();
        }

      //$user=$this->create($input);
      $user= new User();
      $user->id = Uuid::generate()->string;
      $user->name = strtoupper($request->name);
      $user->email = $request->email;
      if($request->hasFile('imgphoto')) {
        $avatar = $request->file('imgphoto');
        $filename = time() . '.' . $avatar->getClientOriginalExtension();
        Image::make($avatar)->resize(300, 300)->save( public_path('uploads/avatars/' . $filename));
        $user->avatar = $filename;
      }

      $user->api_token =str_random(60);
      $customer->users()->save($user);

      $data = $user->toArray();
      $user->notify(new VerificationUser($user));
      /*Mail::send('emails.confirmation',$data,function($message) use($data){
            $message->to($data['email']);
            $message->subject('Registration Confirmation');
        });*/
        return redirect(route('login'))->with('status',trans("passwords.confirm"));
    }


    public function confirmation($token)
    {
      $user = User::where('api_token',$token)->first();
      //dd($user->customer->psc_flag);
      if (!is_null($user)){
        if(!$user->validate_flag){
          $marketings = User::whereHas('roles', function($q){
              $q->where('name','MarketingGPL');
          })->get();

          foreach ($marketings as $sales)
          {
              $sales->notify(new MarketingGaleniumNotif($user));
          }
          /*
          if ($user->customer->psc_flag ==="1"){
            $marketings_psc = User::whereHas('roles', function($q){
                $q->where('name','Marketing PSC');
            })->get();

            foreach ($marketings_psc as $mpsc)
            {
                $mpsc->notify(new MarketingGaleniumNotif($user));
            }
          }
          if ($user->customer->pharma_flag ==="1"){
            $marketings_pharma = User::whereHas('roles', function($q){
                $q->where('name','Marketing Pharma');
            })->get();
            //$marketings_pharma = Role::with('users')->where('name', 'Marketing Pharma')->get();
            //dd($marketings_pharma);
            foreach ($marketings_pharma as $mpharma)
            {
                $mpharma->notify(new MarketingGaleniumNotif($user));
            }
          }*/
          $user->validate_flag=1;
          //$user->api_token='';
          $user->save();
          //return redirect(route('verification','token'=>$token))->with('status','Your activation is completed. Please Input Your Password')
          return view('auth.verification',['data' => $user]);
          //return redirect(route('login'))->with('status',trans("auth.registerverified"));
        }else{
            return redirect(route('login'))->with('status',trans("auth.alreadyverified"));
        }
      }
      return redirect(route('login'))->with('status',trans("auth.registerfailed"));
    }

    public function verification_email($token)
    {
      $user = User::where('api_token',$token)->first();
      if ($user){
          return view('auth.verification',['data' => $user]);
      }else{
        redirect(route('login'))->with('status',trans("auth.registerfailed"));
      }
    }

}
