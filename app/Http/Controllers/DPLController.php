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

  	$outlet_list = array('---Pilih---');
  	$distributor_list = array('---Silakan Pilih Outlet---');

  	foreach ($outlets as $key => $outlet) {
  		$outlet_list[$outlet->id] = $outlet->customer_name;
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

  public function getDistributorList($outlet_id)
  {
  	$distributors = OutletDistributor::join('customers','customers.id','outlet_distributor.distributor_id')
  																		->where('outlet_id',$outlet_id)
  																		->get(); 
   	
   	return response()->json($distributors);
  }

  public function discountForm($suggest_no)
  {
    $dpl = DPLSuggestNo::select('users.id as dpl_mr_id',
                                'users.name as dpl_mr_name',
                                'outlet.id as dpl_outlet_id',
                                'outlet.customer_name as dpl_outlet_name',
                                'distributor.id as dpl_distributor_id',
                                'distributor.customer_name as dpl_distributor_name',
                                'suggest_no',
                                'discount')
                        ->join('users','users.id','dpl_suggest_no.mr_id')
                        ->join('customers as outlet','outlet.id','dpl_suggest_no.outlet_id')
                        ->join('customers as distributor','distributor.id','dpl_suggest_no.distributor_id')
                        ->where('suggest_no',$suggest_no)
                        ->where('active',1)
                        ->first();
                        
    return view('admin.dpl.discountForm',array('dpl'=>$dpl));
  }

  public function discountSet(Request $request)
  {
    $discount = $request->discount;
    $suggest_no = $request->suggest_no;
    $dpl = DPLSuggestNo::where('suggest_no',$suggest_no)
                        ->update(array('discount'=>$discount));

    return redirect()->back();
  }

}
