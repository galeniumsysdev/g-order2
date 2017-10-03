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
            'city' => 'required|string|max:100',
            'postal_code' => 'required|digits:5',
            'HP_1' => 'required|regex:/(8)[0-9]{8,}/',
            'HP_2' => 'nullable|regex:/(8)[0-9]{8,}/',
            'no_tlpn' => 'nullable|regex:/[0-9]{9}/',
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
         $user_check->save();
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
      $customer = Customer::create([
          'customer_name' => $request->name,
          'status' => 'X',
          'tax_reference' => $request->NPWP,
          'pharma_flag' => $request->pharma,
          'psc_flag' => $request->psc,
          'outlet_type_id' => $request->category,
        ]);
        $customer->save();

       $custsites = new CustomerSite();
       $custsites->site_use_code = "SHIP_TO";
       $custsites->status = "A";
       $custsites->address1 = strtoupper($request->address);
       $custsites->state = strtoupper($request->state);
       $custsites->city = strtoupper($request->city);
       $custsites->postalcode = $request->postal_code;
       $custsites->Country = 'ID';
       $custsites->customer_id = $customer->id;
       $custsites->save();

       $custcontact= new CustomerContact();
        $custcontact->contact_name = $request->contact_person;
        $custcontact->contact_type = 'EMAIL';
        $custcontact->contact =$request->email;
        $custcontact->customer_id=$customer->id;
        $custcontact->save();

        $custcontact1= new CustomerContact();
        $custcontact->contact_name = $request->cp;
        $custcontact1->contact_type = 'PHONE';
        $custcontact1->contact ='+62'.$request->HP_1;
        $custcontact1->customer_id=$customer->id;
        $custcontact1->save();

        if(isset($request->HP_2)){
            $custcontact2= new CustomerContact();
            $custcontact2->contact_type = 'PHONE';
            $custcontact2->contact ='+62'.$request->HP_2;
            $custcontact2->customer_id=$customer->id;
            $custcontact2->save();
        }
        if(isset($request->no_tlpn)){
            $custcontact3= new CustomerContact();
             $custcontact3->contact_type = 'PHONE';
             $custcontact3->contact ='+62'.$request->no_tlpn;
             $custcontact3->customer_id=$customer->id;
             $custcontact3->save();
        }


      //$user=$this->create($input);
      $user= new User();
      $user->id = Uuid::generate()->string;
      $user->name = $request->name;
      $user->email = $request->email;
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
          }
          $user->validate_flag=1;
          //$user->api_token='';
          $user->save();
          //return redirect(route('verification','token'=>$token))->with('status','Your activation is completed. Please Input Your Password')
          //return view('auth.verification',['data' => $user]);
          return redirect(route('login'))->with('status',trans("auth.registerverified"));
        }else{
            return redirect(route('login'))->with('status',trans("auth.alreadyverified"));
        }
      }
      return redirect(route('login'))->with('status',trans("auth.registerfailed"));
    }

    public function verification_email($token)
    {
      $user = User::where('api_token',$token)->first();
      return view('auth.verification',['data' => $user]);
    }

}
