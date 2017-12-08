<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Excel;
use DB;

class ExcelController extends Controller
{
    public function ExportClients()
    {
      $customer = DB::table('customers')->whereNull('oracle_customer_id')->get();
    	Excel::create('Report_NOO', function($excel) use($customer) {
    		$excel->sheet('Report_NOO', function($sheet) use ($customer) {
    			$sheet->loadView('ExportClients',array('customers'=>$customer));
    		});
    	})->export('xlsx');
    }
}
