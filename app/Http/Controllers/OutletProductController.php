<?php

namespace App\Http\Controllers;

use App\OutletProducts;
use App\OutletStock;
use App\Product;
use App\Customer;

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
      foreach ($data as $key => $prod) {
        $check = OutletProducts::where('title', strtoupper($prod['nama_barang']))->count();
        if($check)
          $data[$key]['exist'] = '<span class="text-danger duplicate">Duplicate</span>';
        else
          $data[$key]['exist'] = '';
      }
	  }

  	return view('admin.outlet.outletImportProduct', array('data'=>$data));
  }

  public function importProductProcess(Request $request)
  {
  	$data = json_decode($request->data);

  	$product = array();
  	foreach ($data as $key => $new) {
  		$product[$key]['id'] = Uuid::generate();
      $product[$key]['outlet_id'] = Auth::user()->customer_id;
  		$product[$key]['title'] = strtoupper($new->nama_barang);
  		$product[$key]['unit'] = strtoupper($new->satuan);
  		$product[$key]['price'] = (intval($new->price)) ? intval($new->price) : 0;
  		$product[$key]['enabled_flag'] = 'Y';
  		$product[$key]['created_at'] = date('Y-m-d H:i:s', time());
  		$product[$key]['updated_at'] = date('Y-m-d H:i:s', time());
  	}
  	$insert_product = OutletProducts::insert($product);

  	return redirect('/outlet/product/import')->with('msg','Product imported successfully.');
  }

  public function downloadTemplateProduct()
  {
  	return redirect('/file/template_upload_new_other_product.xlsx');
  }

  public function downloadTemplateStock()
  {
  	return Excel::create('Product Stock '.date('Ymd His'), function($excel){
  		$excel->setTitle('Product Stock '.date('Ymd His'))
  					->setCreator(Auth::user()->name)
  					->setCompany('PT. Galenium Pharmasia Laboratories')
  					->sheet('Product Stock '.date('Ymd His'), function($sheet){
  						$sheet->row(1, array('ID','Nama Barang','Stock','Satuan','Kelompok','Batch'));
  						$sheet->setColumnFormat(array('D'=>'@'));

  						$productsOutlet = OutletProducts::select('outlet_products.id as op_id','title','unit',DB::raw('sum(qty) as product_qty'),DB::raw('"outlet" as flag'))
  													->leftjoin('outlet_stock as os','os.product_id','outlet_products.id')
                            ->where('outlet_products.enabled_flag','Y')
                            ->where('outlet_products.outlet_id',Auth::user()->customer_id)
  													->groupBy('op_id','unit','title','flag');
              $productsAll = Product::select('products.id as op_id','title','products.satuan_primary as unit',DB::raw('sum(qty) as product_qty'),DB::raw('"galenium" as flag'))
                                      ->leftjoin('outlet_stock as os','os.product_id','products.id')
                                      ->join('category_products as cp','cp.product_id','products.id')
                                      ->join('categories as c','c.flex_value','cp.flex_value')
                                      ->where('c.parent','PHARMA')
                                      ->groupBy('unit','op_id','title','flag')
                                      ->union($productsOutlet)
                                      ->orderBy('title')
                                      ->get();

  						foreach ($productsAll as $key => $prod) {
  							$sheet->row($key+2, array($prod->op_id,
  																				$prod->title,
  																				($prod->product_qty ? $prod->product_qty : 0),
                                          $prod->unit,
                                          $prod->flag,
  																				''
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
      $stock[$idx]['outlet_id'] = Auth::user()->customer_id;
  		$stock[$idx]['event'] = 'adjust';
  		$stock[$idx]['qty'] = ($prod->last_stock > 0) ? '-'.$prod->last_stock : $prod->last_stock;
  		$stock[$idx]['batch'] = NULL;
  		$stock[$idx]['created_at'] = date('Y-m-d H:i:s', time());
  		$stock[$idx]['updated_at'] = date('Y-m-d H:i:s', time());
  		$idx++;

  		$stock[$idx]['product_id'] = $prod->id;
      $stock[$idx]['outlet_id'] = Auth::user()->customer_id;
  		$stock[$idx]['event'] = 'add_upload';
  		$stock[$idx]['qty'] = $prod->stock;
  		$stock[$idx]['batch'] = $prod->batch;
  		$stock[$idx]['created_at'] = date('Y-m-d H:i:s', time());
  		$stock[$idx]['updated_at'] = date('Y-m-d H:i:s', time());
  		$idx++;
  	}
  	$insert_stock = OutletStock::insert($stock);

  	return redirect('/outlet/product/import/stock')->with('msg','Stock imported successfully.');
  }

	public function getListProductStock()
  {
  	$stockOutlet = OutletProducts::select('outlet_products.id as op_id','outlet_products.unit','title',DB::raw('sum(qty) as product_qty'),DB::raw('"outlet" as flag'))
  													->leftjoin('outlet_stock as os','os.product_id','outlet_products.id')
                            ->where('outlet_products.enabled_flag','Y')
                            ->where('outlet_products.outlet_id',Auth::user()->customer_id)
  													->groupBy('op_id','unit','title','flag');
    $stockAll = Product::select('products.id as op_id','products.satuan_primary as unit','title',DB::raw('sum(qty) as product_qty'),DB::raw('"galenium" as flag'))
                              ->leftjoin('outlet_stock as os','os.product_id','products.id')
                              ->join('category_products as cp','cp.product_id','products.id')
                              ->join('categories as c','c.flex_value','cp.flex_value')
                              ->where('c.parent','PHARMA')
                              ->groupBy('unit','op_id','title','flag')
                              ->union($stockOutlet)
                              ->orderBy('title')
                              ->get();

  	return response()->json($stockAll);
  }

  public function listProductStock()
  {
  	$stockOutlet = OutletProducts::select('outlet_products.unit','outlet_products.id as op_id','title',DB::raw('sum(qty) as product_qty'),DB::raw('"outlet" as flag'))
  													->leftjoin('outlet_stock as os','os.product_id','outlet_products.id')
                            ->where('outlet_products.enabled_flag','Y')
                            ->where('outlet_products.outlet_id',Auth::user()->customer_id)
  													->groupBy('unit','op_id','title','flag');
    $stockAll = Product::select('products.satuan_primary as unit','products.id as op_id','title',DB::raw('sum(qty) as product_qty'),DB::raw('"galenium" as flag'))
                            ->leftjoin('outlet_stock as os','os.product_id','products.id')
                            ->join('category_products as cp','cp.product_id','products.id')
                            ->join('categories as c','c.flex_value','cp.flex_value')
                            ->where('c.parent','PHARMA')
                            ->groupBy('unit','op_id','title','flag')
                            ->union($stockOutlet)
                            ->orderBy('title')
  													->get();
  	foreach ($stockAll as $key => $prod) {
      $flag = ($prod->flag == 'galenium') ? 'g' : '';
  		$stockAll[$key]->stock = '<a href="'.route('outlet.detailProductStock',array('product_id'=>$prod->op_id,'flag'=>$flag)).'">'.floatval($prod->product_qty).' '.$prod->unit.'</a>';
  	}
  	return view('admin.outlet.listProductStock', array('stock'=>$stockAll));
  }

  public function detailProductStock($product_id, $flag = '')
  {
    if($flag != 'g'){
      $header = OutletProducts::select('outlet_products.unit','outlet_products.id as p_id','title',DB::raw('sum(qty) as product_qty'))
                              ->leftjoin('outlet_stock as os','os.product_id','outlet_products.id')
                              ->where('outlet_products.id',$product_id)
                              ->groupBy('unit','p_id','title')
                              ->first();

    	$stock = OutletStock::select('outlet_products.unit','outlet_products.title','outlet_stock.*')
    												->leftjoin('outlet_products','outlet_products.id','outlet_stock.product_id')
    												->where('product_id',$product_id)
                            ->where('outlet_stock.outlet_id',Auth::user()->customer_id)
    												->orderBy('outlet_stock.created_at','desc')
    												->get();
    }
    else{
      $header = Product::select('products.satuan_primary as unit','products.id as p_id','title',DB::raw('sum(qty) as product_qty'))
                              ->leftjoin('outlet_stock as os','os.product_id','products.id')
                              ->where('products.id',$product_id)
                              ->groupBy('unit','p_id','title')
                              ->first();

      $stock = OutletStock::select('products.satuan_primary as unit','products.title','outlet_stock.*')
                            ->leftjoin('products','products.id','outlet_stock.product_id')
                            ->where('product_id',$product_id)
                            ->where('outlet_stock.outlet_id',Auth::user()->customer_id)
                            ->orderBy('outlet_stock.created_at','desc')
                            ->get();
    }

  	$trx = array();
  	$count = 0;
  	$idx_adj = -1;
  	$title = $header['title'];
  	$last_stock = (($header['product_qty']) ? $header['product_qty'] : 0).' '.$header['unit'];
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
				$trx[$count]['batch'] = $list->batch;
				$trx[$count]['trx_date'] = $list->created_at;
				$count++;
			}
		}

  	return view('admin.outlet.detailProductStock', array('title'=>$title, 'last_stock'=>$last_stock, 'stock'=>$trx));
  }
  
  public function formProduct($id = '')
  {
    if($id)
      $product = OutletProducts::where('id',$id)->first();
    else{
      $product = new \stdClass;
      $product->id = '';
      $product->title = '';
      $product->unit = '';
      $product->price = 0;
    }

    return view('admin.outlet.outletProductForm', array('product'=>$product));
  }

  public function submitProduct(Request $request)
  {
    $id = $request->id;
    $product_name = strtoupper($request->product_name);
    $unit = strtoupper($request->product_unit);
    $price = $request->product_price;

    $check = OutletProducts::where('title',$product_name)->count();

    if($check)
      return redirect()->back()->withInput()->with('msg','<span class="text-danger">Product is already exist.</span>');

    if(!$id){
      $product = new OutletProducts;
      $product->id = Uuid::generate();
      $product->outlet_id = Auth::user()->customer_id;
      $product->enabled_flag = 'Y';
    }
    else{
      $product = OutletProducts::find($id);
    }
    $product->title = $product_name;
    $product->unit = $unit;
    $product->price = $price;
    $save = $product->save();

    if($save){
      if(!$id)
        return redirect()->back()->with('msg','Product saved successfully.');
      else
        return redirect()->back()->with('msg','Product edited successfully.');
    }
  }

  public function deleteProduct($id)
  {
    $delete = OutletProducts::where('id',$id)->update(array('enabled_flag'=>0));

    if($delete){
      return redirect()->back()->with('msg','Product deleted successfully.');
    }
  }

  public function outletTrx()
  {
  	return view('admin.outlet.outletTrx');
  }

  public function outletTrxList()
  {
  	$stockOutlet = OutletStock::select('title','outlet_stock.*', 'outlet_stock.created_at as trx_date')
  											->leftjoin('outlet_products','outlet_products.id','outlet_stock.product_id')
                        ->where('outlet_products.enabled_flag','Y')
                        ->where('outlet_stock.outlet_id',Auth::user()->customer_id);

    $stockAll = OutletStock::select('title','outlet_stock.*', 'outlet_stock.created_at as trx_date')
                        ->leftjoin('products','products.id','outlet_stock.product_id')
                        ->where('products.Enabled_Flag','Y')
                        ->where('outlet_stock.outlet_id',Auth::user()->customer_id)
                        ->union($stockOutlet)
                        ->orderBy('trx_date','desc')
  											->get();
  	$trx = array();
  	$count = 0;
  	foreach ($stockAll as $key => $list) {
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
	  		$trx[$count]['batch'] = $list->batch;
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
    $instock->outlet_id = Auth::user()->customer_id;
  	$instock->event = 'trx_in';
  	$instock->qty = $request->qty_in;
  	$instock->save();

  	return redirect()->back()->with('msg','Transaction In has been done successfully.');
  }

  public function outletTrxOutProcess(Request $request)
  {
  	$outstock = new OutletStock;
  	$outstock->product_id = $request->product_code_out;
    $outstock->outlet_id = Auth::user()->customer_id;
  	$outstock->event = 'trx_out';
  	$outstock->qty = '-'.$request->qty_out;
  	$outstock->save();

  	return redirect('/outlet/transaction#trx-out')->with('msg','Transaction Out has been done successfully.');
  }

  public function downloadProductStock()
  {
    return view('admin.outlet.outletStockDownload');
  }

  public function downloadProductStockProcess(Request $request)
  {
    $data['start_date'] = date('Y-m-d 00:00:00',strtotime($request->start_date));
    $data['end_date'] = date('Y-m-d 23:59:59',strtotime($request->end_date));
    $data['outlet_name'] = $request->outlet_name;
    $data['province'] = $request->province;
    $data['area'] = $request->area;
    return Excel::create('Report Stock', function($excel) use($data){
      $excel->setTitle('Report Stock')
            ->setCreator(Auth::user()->name)
            ->sheet('Report Stock', function($sheet) use($data){
              $sheet->row(1, array('STOCK OUTLET'));
              $sheet->row(3, array('NAMA OUTLET','PRODUK','BATCH','QUANTITY'));
              $sheet->row(4, array('','','','Beg','In','Out','End'));
              $sheet->mergeCells('D3:G3');
              $sheet->mergeCells('A3:A4');
              $sheet->mergeCells('B3:B4');
              $sheet->mergeCells('C3:C4');

              $stockOutlet = OutletStock::select('outlet_stock.id as os_id','title','outlet_stock.product_id','outlet.customer_name as outlet_name')
                                  ->join('outlet_products','outlet_products.id','outlet_stock.product_id')
                                  ->join('customers as outlet','outlet.id','outlet_stock.outlet_id')
                                  ->join('customer_sites as cs','cs.customer_id','outlet.id')
                                  ->where('outlet_products.enabled_flag','Y')
                                  ->groupby('os_id','title','product_id','outlet_name');
              
              if($data['outlet_name'])
                $stockOutlet = $stockOutlet->where('outlet.customer_name',$data['outlet_name']);

              if($data['province'])
                $stockOutlet = $stockOutlet->where('province',$data['province']);

              if($data['area'])
                $stockOutlet = $stockOutlet->where('city',$data['area']);

              $stockAll = OutletStock::select('outlet_stock.id as os_id','title','outlet_stock.product_id','outlet.customer_name as outlet_name')
                                  ->join('products','products.id','outlet_stock.product_id')
                                  ->join('customers as outlet','outlet.id','outlet_stock.outlet_id')
                                  ->join('customer_sites as cs','cs.customer_id','outlet.id')
                                  ->where('products.Enabled_Flag','Y')
                                  ->groupby('os_id','title','product_id','outlet_name');

              if($data['outlet_name'])
                $stockAll = $stockAll->where('outlet.customer_name',$data['outlet_name']);

              if($data['province'])
                $stockAll = $stockAll->where('province',$data['province']);

              if($data['area'])
                $stockAll = $stockAll->where('city',$data['area']);

              $stockAll = $stockAll->union($stockOutlet)
                                  ->orderBy('title','asc')
                                  ->get();

              foreach ($stockAll as $key => $prod) {
                $begin = OutletStock::where('product_id',$prod->product_id)
                                        ->whereDate('created_at','<=',$data['start_date'])
                                        ->sum('qty');
                $end = OutletStock::where('product_id',$prod->product_id)
                                        ->whereDate('created_at','<=',$data['end_date'])
                                        ->sum('qty');
                $in = OutletStock::where('product_id',$prod->product_id)
                                        ->whereDate('created_at','>=',$data['start_date'])
                                        ->whereDate('created_at','<=',$data['end_date'])
                                        ->where('qty','>',0)
                                        ->sum('qty');
                $out = OutletStock::where('product_id',$prod->product_id)
                                        ->whereDate('created_at','>=',$data['start_date'])
                                        ->whereDate('created_at','<=',$data['end_date'])
                                        ->where('qty','<',0)
                                        ->sum('qty');

                $sheet->row($key+5, array($prod->outlet_name,
                                          $prod->title,
                                          $prod->batch,
                                          $begin,
                                          $in,
                                          $out,
                                          $end
                                          ));
              }
            });
    })->download('xlsx');
  }

  public function getListOutlet()
  {
    $outlet = Customer::all();
    return response()->json($outlet);
  }
}
