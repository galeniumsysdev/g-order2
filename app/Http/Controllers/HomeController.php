<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      if(Auth::user()->can('Create PO'))
      {
        return redirect()->route('product.index');
      }
        $request->status_read="0";
        $notifications = Auth::User()->notifications()->whereNull('read_at')->get();
        $group = array_column(Auth::User()->notifications()->get()->pluck('data')->toArray(),'tipe');
        $jnsnotif= array_unique($group);
        return view('home', compact('notifications','request','jnsnotif'));
    }

    public function search(Request $request)
    {
      $notifications = Auth::User()->notifications();
      if (isset($request->tipe))
      {
        $notifications = $notifications->where('data','like','%"tipe":"'.$request->tipe.'"%');
      }
      if (isset($request->subject))
      {
        $notifications = $notifications->where('data','like','%"subject":"%'.$request->subject.'%"%');
      }
      /*if (isset($request->tgl_aw_kirim) and isset($request->tgl_ak_kirim) )
      {

      }else
      */
      if (isset($request->tgl_aw_kirim))
      {
        $notifications = $notifications->where('created_at','>=',$request->tgl_aw_kirim);
      }elseif (isset($request->tgl_ak_kirim))
      {
        $notifications = $notifications->where('created_at','<=',$request->tgl_aw_kirim);
      }

      if($request->status_read=="0")
      {
        $notifications = $notifications->whereNull('read_at');
      }elseif($request->status_read=="1"){
        $notifications = $notifications->whereNotNull('read_at');
      }
      $notifications = $notifications->get();
    //  var_dump($notifications->pluck('data')->toArray());
      $group = array_column(Auth::User()->notifications()->pluck('data')->toArray(),'tipe');
      $jnsnotif= array_unique($group);
      return view('home', compact('notifications','request','jnsnotif'));

    }
}
