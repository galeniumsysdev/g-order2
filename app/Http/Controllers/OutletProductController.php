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

  	return redirect('/outlet/product/import');
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

  	return redirect('/outlet/product/import/stock');
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
  	$stock = OutletProducts::select('outlet_products.id as op_id','title',DB::raw('sum(qty) as product_qty'))
  													->leftjoin('outlet_stock as os','os.product_id','outlet_products.id')
  													->groupBy('op_id','title')
  													->get();
  	return view('admin.outlet.listProductStock', array('stock'=>$stock));
  }

  public function outletTrx()
  {
  	return view('admin.outlet.outletTrx');
  }

  public function outletTrxInProcess()
  {
  	/*$instock = new Stock;
  	$instock->product_id = */
  }
}
