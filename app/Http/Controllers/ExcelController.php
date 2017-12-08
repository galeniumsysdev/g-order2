<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Excel;
use DB;

class ExcelController extends Controller
{
    public function ExportClients(Request $request)
    {
    	$customer = DB::table('customers as c')
    				->join('customer_sites as cs','c.id', 'cs.customer_id')
    				->leftjoin('subgroup_datacenters as sd','c.subgroup_dc_id','sd.id')
    				->whereNull('c.oracle_customer_id');
    	if($request->psc_flag=="1" and $request->pharma_flag!="1")
        {
            $customer = $customer->where('psc_flag','=','1');
        }
        if($request->pharma_flag=="1" and $request->psc_flag!="1")
        {
            $customer = $customer->where('pharma_flag','=','1');
        }
        if($request->pharma_flag=="1" and $request->psc_flag=="1")
        {
            $customer = $customer->where(function ($query) {
              $query->where('pharma_flag','=','1')
                    ->orWhere('psc_flag','=','1');
            });
        }
        //dd($request->subgroupdc);
        if($request->subgroupdc)
        {
          $customer = $customer->whereIn('subgroup_dc_id',$request->subgroupdc);
        }
        
        			
    	$customer = $customer->select('c.customer_name','cs.province','cs.city','c.longitude','c.langitude','c.created_at','c.Status','c.psc_flag','c.pharma_flag','c.subgroup_dc_id','sd.name as subgroup_name');
    	$customer = $customer->get(); 

    	Excel::create('Report_NOO', function($excel) use($customer) {
    		$excel->sheet('Report_NOO', function($sheet) use ($customer) {
    			$sheet->loadView('ExportClients',array('customers'=>$customer));
    		});
    	})->export('xlsx');
    }
}
