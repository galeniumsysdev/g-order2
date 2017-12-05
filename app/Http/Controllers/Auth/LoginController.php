<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest')->except('logout');
    }
    protected function credentials(Request $request)
    {
        $credentials = $request->only($this->username(), 'password');
        $credentials['validate_flag']=1;
        $credentials['register_flag']=1;
        return $credentials;
    }
    protected function authenticated(Request $request, $user)
    {
      if(Auth::check()) {
        if(Auth::user()->hasRole('IT Galenium')) {
            return redirect('/admin');
        }elseif(Auth::user()->hasRole('Distributor') || Auth::user()->hasRole('Outlet') || Auth::user()->hasRole('Apotik/Klinik') ) {
              return redirect('/product/buy');
        }elseif(Auth::user()->hasRole('KurirGPL'))      {
              return redirect()->route('order.shippingSO');
        }else{/*if(Auth::user()->hasRole('Marketing PSC') || Auth::user()->hasRole('Marketing Pharma')) {*/
            return redirect('/home');
        }
      }
    }

  
}
