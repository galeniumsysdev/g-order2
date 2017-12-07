<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Excel;

class ExcelController extends Controller
{
    public function ExportClients()
    {
    	Excel::create('Report_NOO', function($excel) {
    		$excel->sheet('Report_NOO', function($sheet) {
    			$sheet->loadView('ExportClients');
    		}); 
    	})->export('xlsx');
    }
}