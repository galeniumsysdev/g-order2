<?php

namespace App\Http\Controllers;

use App\OutletProducts;
use App\OutletStock;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Webpatser\Uuid\Uuid;

use Auth;
use Excel;

class OutletProductController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function importProduct()
  {
  	return view('admin.outlet.outletImportProduct',array('data'=>''));
  }

  public function importProductView(Request $request)
  {
  	if($request->file_import){
	    $file = $request->file_import;
	
	    $data = Excel::load($file, function($reader){})->get();
	  }

  	return view('admin.outlet.outletImportProduct', array('data'=>$data));
  }

  public function importProductProcess(Request $request)
  {
  	$data = json_decode($request->data);

  	$product = array();
  	foreach ($data as $key => $new) {
  		$product[$key]['id'] = Uuid::generate();
  		$product[$key]['title'] = $new->title;
  		$product[$key]['unit'] = $new->unit;
  		$product[$key]['price'] = $new->price;
  		$product[$key]['enabled_flag'] = 'Y';
  		$product[$key]['created_at'] = date('Y-m-d H:i:s', time());
  		$product[$key]['updated_at'] = date('Y-m-d H:i:s', time());
  	}
  	$insert_product = OutletProducts::insert($product);

  	return redirect('/outlet/product/import')->with('msg','Product imported successfully.');
  }

  public function downloadTemplate()
  {
  	return Excel::create('Product Stock '.date('Ymd His'), function($excel){
  		$excel->setTitle('Product Stock '.date('Ymd His'))
  					->setCreator(Auth::user()->name)
  					->setCompany('PT. Galenium Pharmasia Laboratories')
  					->sheet('Product Stock '.date('Ymd His'), function($sheet){
  						$sheet->row(1, array('ID','Title','Stock'));

  						$products = OutletProducts::select('outlet_products.id as op_id','title',DB::raw('sum(qty) as product_qty'))
  													->leftjoin('outlet_stock as os','os.product_id','outlet_products.id')
  													->groupBy('op_id','title')
  													->get();

  						foreach ($products as $key => $prod) {
  							$sheet->row($key+2, array($prod->op_id,
  																				$prod->title,
  																				($prod->product_qty ? $prod->product_qty : 0)
  																				));
  						}
  					});
  	})->download('xlsx');
  }

  public function importProductStock()
  {
  	return view('admin.outlet.outletImportProductStock',array('data'=>''));
  }

  public function importProductStockView(Request $request)
  {
  	if($request->file_import){
	    $file = $request->file_import;
	
	    $data = Excel::load($file, function($reader){})->get();

	    foreach ($data as $key => $value) {
	    	$last_stock = OutletStock::where('product_id',$value->id)->sum('qty');
	    	$data[$key]['last_stock'] = $last_stock;
	    }
	  }

  	return view('admin.outlet.outletImportProductStock', array('data'=>$data));
  }

  public function importProductStockProcess(Request $request)
  {
  	$data = json_decode($request->data);

  	$stock = array();
  	$idx = 0;
  	foreach ($data as $key => $prod) {
  		$stock[$idx]['product_id'] = $prod->id;
  		$stock[$idx]['event'] = 'adjust';
  		$stock[$idx]['qty'] = '-'.$prod->last_stock;
  		$stock[$idx]['created_at'] = date('Y-m-d H:i:s', time());
  		$stock[$idx]['updated_at'] = date('Y-m-d H:i:s', time());
  		$idx++;

  		$stock[$idx]['product_id'] = $prod->id;
  		$stock[$idx]['event'] = 'add_upload';
  		$stock[$idx]['qty'] = $prod->stock;
  		$stock[$idx]['created_at'] = date('Y-m-d H:i:s', time());
  		$stock[$idx]['updated_at'] = date('Y-m-d H:i:s', time());
  		$idx++;
  	}
  	$insert_stock = OutletStock::insert($stock);

  	return redirect('/outlet/product/import/stock')->with('msg','Stock imported successfully.');
  }

	public function getListProductStock()
  {
  	$stock = OutletProducts::select('outlet_products.id as op_id','outlet_products.unit','title',DB::raw('sum(qty) as product_qty'))
  													->leftjoin('outlet_stock as os','os.product_id','outlet_products.id')
  													->groupBy('op_id','unit','title')
  													->get();

  	return response()->json($stock);
  }  

  public function listProductStock()
  {
  	$stock = OutletProducts::select('outlet_products.unit','outlet_products.id as op_id','title',DB::raw('sum(qty) as product_qty'))
  													->leftjoin('outlet_stock as os','os.product_id','outlet_products.id')
  													->groupBy('unit','op_id','title')
  													->get();
  	foreach ($stock as $key => $prod) {
  		$stock[$key]->stock = '<a href="/outlet/product/detail/'.$prod->op_id.'">'.floatval($prod->product_qty).' '.$prod->unit.'</a>';
  	}
  	return view('admin.outlet.listProductStock', array('stock'=>$stock));
  }

  public function detailProductStock($product_id)
  {
  	$header = OutletProducts::select('outlet_products.unit','outlet_products.id as op_id','title',DB::raw('sum(qty) as product_qty'))
  													->leftjoin('outlet_stock as os','os.product_id','outlet_products.id')
  													->where('product_id',$product_id)
  													->groupBy('unit','op_id','title')
  													->first();

  	$stock = OutletStock::select('outlet_products.unit','outlet_products.title','outlet_stock.*')
  												->leftjoin('outlet_products','outlet_products.id','outlet_stock.product_id')
  												->where('product_id',$product_id)
  												->orderBy('created_at','desc')
  												->get();

  	$trx = array();
  	$count = 0;
  	$idx_adj = -1;
  	$title = $header['title'];
  	$last_stock = $header['product_qty'].' '.$header['unit'];
		foreach ($stock as $key => $list) {
			if($list->qty != 0 && $list->event != 'adjust'){
				if($list->event == 'trx_in'){
					$trx[$count]['class'] = 'bg-success';
					$trx[$count]['event'] = 'Add';
					$trx[$count]['qty'] = $list->qty.' '.$list->unit;
				}
				elseif($list->event == 'trx_out'){
					$trx[$count]['class'] = 'bg-danger';
					$trx[$count]['event'] = 'Out';
					$trx[$count]['qty'] = $list->qty.' '.$list->unit;
				}
				elseif($list->event == 'add_upload'){
					$trx[$count]['class'] = 'bg-info';
					$trx[$count]['event'] = ($idx_adj == -1) ? '<strong>Adjustment</strong>' : 'Adjustment';
					$trx[$count]['qty'] = ($idx_adj == -1) ? '<strong>'.$list->qty.' '.$list->unit.'</strong>' : $list->qty.' '.$list->unit;
					$idx_adj = 0;
				}
				$trx[$count]['trx_date'] = $list->created_at;
				$count++;
			}
		}
  	
  	return view('admin.outlet.detailProductStock', array('title'=>$title, 'last_stock'=>$last_stock, 'stock'=>$trx));
  }

  public function outletTrx()
  {
  	return view('admin.outlet.outletTrx');
  }

  public function outletTrxList()
  {
  	$stocks = OutletStock::select('outlet_stock.*', 'outlet_stock.created_at as trx_date', 'outlet_products.*')
  											->leftjoin('outlet_products','outlet_products.id','outlet_stock.product_id')
  											->get();
  	$trx = array();
  	$count = 0;
  	foreach ($stocks as $key => $list) {
  		if($list->qty != 0 && $list->event != 'adjust'){
  			if($list->event == 'trx_in'){
  				$trx[$count]['class'] = 'bg-success';
	  			$trx[$count]['event'] = 'Add';
  			}
	  		elseif($list->event == 'trx_out'){
	  			$trx[$count]['class'] = 'bg-danger';
	  			$trx[$count]['event'] = 'Out';
	  		}
	  		elseif($list->event == 'add_upload'){
	  			$trx[$count]['class'] = 'bg-info';
	  			$trx[$count]['event'] = 'Adjustment';
	  		}
	  		$trx[$count]['title'] = $list->title;
	  		$trx[$count]['qty'] = $list->qty.' '.$list->unit;
	  		$trx[$count]['trx_date'] = $list->trx_date;
	  		$count++;
	  	}
  	}
  	return view('admin.outlet.outletTrxList',array('data'=>$trx));
  }

  public function outletTrxInProcess(Request $request)
  {
  	$instock = new OutletStock;
  	$instock->product_id = $request->product_code_in;
  	$instock->event = 'trx_in';
  	$instock->qty = $request->qty_in;
  	$instock->save();

  	return redirect()->back()->with('msg','Transaction In has been done successfully.');
  }

  public function outletTrxOutProcess(Request $request)
  {
  	$outstock = new OutletStock;
  	$outstock->product_id = $request->product_code_out;
  	$outstock->event = 'trx_out';
  	$outstock->qty = '-'.$request->qty_out;
  	$outstock->save();

  	return redirect('/outlet/transaction#trx-out')->with('msg','Transaction Out has been done successfully.');
  }
}