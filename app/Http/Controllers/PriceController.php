<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class PriceController extends Controller
{
    public function index(){
      $menu="pricelist";
      $data = DB::table('qp_list_lines_v as qpl')
            ->join('qp_list_headers as qph','qph.list_header_id','qpl.list_header_id')
            ->leftjoin('products as p','qpl.product_attr_value','=','p.inventory_item_id')
            ->select('qph.name as price_name', 'p.title as nm_product', 'qpl.product_uom_code', 'qpl.operand' ,
                'qpl.start_date_active', 'qpl.end_date_active', 'qpl.enabled_flag')
            ->orderby('qph.list_header_id','p.title')
            ->get();
      return view('admin.oracle.priceindex',compact('data','menu'));
    }
}
