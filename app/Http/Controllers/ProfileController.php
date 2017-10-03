<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Customer;
use App\CustomerSite;
use App\CustomerContact;
use DB;
use Auth;
use Image;
use File;
use Illuminate\Support\Facades\Validator;


class ProfileController extends Controller
{

  public function profile() {
    $user = auth()->user();
    if(!is_null($user->customer_id) or $user->customer_id!="")
    {
      $customer = DB::table('Users as u')
                  ->leftjoin('customers as c','u.customer_id','=','c.id')
                  ->leftjoin('category_outlets as co','c.outlet_type_id','=','co.id')
                  ->where([
                    ['u.id','=',$user->id],
                    ['c.status','=','A'],
                  ])
                  ->select('u.email','c.id','u.name','co.name as tipeoutlet','c.tax_reference','u.avatar')
                  ->first();
      $customer_sites = CustomerSite::where('customer_id','=',$user->customer_id)->orderBy('site_use_code')->get();
      $customer_contacts = CustomerContact::where('customer_id','=',$user->customer_id)->get();
    }else{
      $customer = DB::table('Users as u')
                  ->where('u.id','=',$user->id                    )
                  ->select('u.email','u.name','u.avatar')
                  ->first();
      $customer_sites ="";
      $customer_contacts ="";
    }
    return view('auth.profile.index', ['customer'=>$customer,'customer_sites'=>$customer_sites,'customer_contacts'=>$customer_contacts]);
  }

  public function update_avatar(Request $request) {

    // Handle the user upload of avatar
    if($request->hasFile('avatar')) {
      $validator = Validator::make($request->all(), [
          'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
      ]);
      $user = Auth::user();
      $tmp = $user->avatar ;

      if ($validator->fails())
      return array(
          'fail' => true,
          'errors' => $validator->getMessageBag()->toArray(),
          'filename' => $tmp
      );

      if ($tmp!="default.jpg")
      {
        if (File::exists(public_path('uploads/avatars/'.$tmp ))){
          unlink(public_path('uploads/avatars/'.$tmp ));
          //echo ("<br>delete image");
        }
      }

      $avatar = $request->file('avatar');
      $filename = time() . '.' . $avatar->getClientOriginalExtension();
      Image::make($avatar)->resize(300, 300)->save( public_path('uploads/avatars/' . $filename));
      $user->avatar = $filename;
      $user->save();
      return response()->json([
                      'result' => 'success',
                      'filename' => $filename,
                    ],200);
    }else{
      return array(
          'fail' => true,
          'errors' => 'file not found',
          'filename' => $tmp
      );
    }

    //return view('profile', array('user' => Auth::user()));
  }
  public function addaddress(Request $request)
  {
    if(!is_null(auth()->user()->customer_id))
    {
      $validator = Validator::make($request->all(), [
        'fungsi' => 'required|string|max:10',
        'address' => 'required|string|max:255',
        'postalcode' => 'required|digits:5',
      ])->validate();


      $custsites = new CustomerSite();
      $custsites->site_use_code = $request->fungsi;
      $custsites->status = "A";
      $custsites->address1 = strtoupper($request->address);
      $custsites->state = strtoupper($request->state);
      $custsites->city = strtoupper($request->city);
      $custsites->postalcode = $request->postalcode;
      $custsites->Country = 'ID';
      $custsites->customer_id = auth()->user()->customer_id;
      $custsites->save();

      return redirect(route('profile.index'))->with('message',trans("pesan.successaddaddress"));
    }else{
      return redirect(route('profile.index'))->with('message',trans("pesan.failed"));
    }

  }

  public function addcontact(Request $request)
  {
    if(!is_null(auth()->user()->customer_id))
    {
      $validator = Validator::make($request->all(), [
        'cp' => 'required|string|max:10',
        'no_tlpn' => 'nullable|regex:/[0-9]{9}/',
        'tipe_kontak' => 'required|string',
      ])->validate();


      $custcontact= new CustomerContact();
       $custcontact->contact_name = $request->cp;
       $custcontact->contact_type = $request->tipe_kontak;
       $custcontact->contact ='+62'.$request->no_tlpn;
       $custcontact->customer_id=auth()->user()->customer_id;
       $custcontact->save();

      return redirect(route('profile.index'))->with('message',trans("pesan.successaddcontact"));
    }else{
      return redirect(route('profile.index'))->with('message',trans("pesan.failed"));
    }

  }

  public function removeaddress($id)
  {
    $delete = DB::table("customer_sites")->where([
      ['id','=',$id],
      ['customer_id','=',auth()->user()->customer_id],
      ])->delete();
    return back()->withMessage(trans('pesan.delete'));
  }

  public function removecontact($id)
  {
    $delete = DB::table("customer_contacts")->where([
      ['id','=',$id],
      ['customer_id','=',auth()->user()->customer_id],
      ])->delete();
    return back()->withMessage(trans('pesan.delete'));
  }
}
