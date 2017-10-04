<?php
/**
* created by WK Productions
*/

namespace App\Http\Controllers;

use App\Customer;
use App\OutletDistributor;
use App\DPLSuggestNo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Auth;

class DPLController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function generateSuggestNoForm()
  {
  	$user = Auth::User();
  	$outlets = OutletDistributor::join('customers','customers.id','outlet_distributor.outlet_id')
  															->get();

  	$distributors = OutletDistributor::join('customers','customers.id','outlet_distributor.distributor_id')
  															->get();

  	$outlet_list = array();
  	$distributor_list = array();

  	foreach ($outlets as $key => $outlet) {
  		$outlet_list[$outlet->id] = $outlet->customer_name;
  	}

  	foreach ($distributors as $key => $distributor) {
  		$distributor_list[$distributor->id] = $distributor->customer_name;
  	}

  	return view('admin.dpl.genSuggestNo', array(
  																			'outlet_list'=>$outlet_list,
  																			'distributor_list'=>$distributor_list
  																			));
  }

  public function generateExec(Request $request)
  {
	  $token = "";
  	do{
	    $codeAlphanum = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	    $codeAlphanum.= "0123456789";

	    mt_srand();

	    for($i=0;$i<8;$i++){
	        $token .= $codeAlphanum[mt_rand(0,35)];
	    }

	    $checkSuggestNo = DPLSuggestNo::where('suggest_no',$token)->count();
	  }while($checkSuggestNo);

	  $dplSuggestNo = new DPLSuggestNo;
	  $dplSuggestNo->mr_id = Auth::User()->id;
	  $dplSuggestNo->outlet_id = $request->outlet;
	  $dplSuggestNo->distributor_id = $request->distributor;
	  $dplSuggestNo->suggest_no = $token;
		$dplSuggestNo->save();

		\Session::flash('suggest_no', $token); 
	  
	  return redirect('/dpl/suggestno/success');
  }

  public function generateSuccess()
  {
  	if(\Session::has('suggest_no'))
  	{
  		return view('admin.dpl.genSuggestNoSuccess', array('suggest_no'=>session('suggest_no')));
  	}
  	else
  	{
  		return redirect('/dpl/suggestno/form');
  	}
  }

}
