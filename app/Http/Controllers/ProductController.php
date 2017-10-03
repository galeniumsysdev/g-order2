<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Product;
use App\Cart;
use App\SoHeader;
use App\SoLine;
use App\User;
use Session;
use Auth;
use File;
use Illuminate\Support\Facades\DB;
use App\Customer;
use App\CustomerSite;
use App\Category;
use App\ProductFlexfield;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Notifications\NewPurchaseOrder;

class ProductController extends Controller
{
  protected function validatorImage(array $data)
  {
      return Validator::make($data, [
          'name' => 'required|string|max:255',
          'email' => 'required|string|email|max:255|unique:users',
      ]);
  }

  public function getPrice(Request $request)
  {
    if (isset(Auth::user()->customer_id))
    {
      $vid=Auth::user()->id;
    }else{
      $vid='';
    }
    $price = DB::select("select getItemPrice ( :cust, :prod, :uom ) AS harga from dual", ['cust'=>$vid,'prod'=>$request->product,'uom'=>$request->uom]);
    $price = $price[0];
  //  dd($price->harga);
    return response()->json([
                    'result' => 'success',
                    'price' => $price->harga,
                  ],200);
  }

  public function getDistributor()
  {
    if(isset(Auth::user()->customer_id)){
      $customer = Customer::find(Auth::user()->customer_id);
      $oldDisttributor = Session::has('distributor_to')?Session::get('distributor_to'):null;
      if (is_null($oldDisttributor) or $oldDisttributor ==null)
      {
        if($customer->hasDistributor()->count()>1)
        {
          $distributor = DB::table('outlet_distributor as o')
                        ->join('customers as c','o.distributor_id','=','c.id')
                        ->join('users as u','u.customer_id','=','c.id')
                        ->where('o.outlet_id','=',Auth::user()->customer_id)
                        ->select('c.id','c.customer_name','u.avatar')
                        ->get();
          return view('shop.sites',['distributors' => $distributor]);

        }else{//if(count($distributor)>1) {

            foreach ($customer->hasDistributor as $dist) {
              //dd('distributor:'.$dist->pivot->customer_name);
              $request->session()->put('distributor_to',['id'=>$dist->id,'customer_name'=>$dist->customer_name,'pharma_flag'=>$dist->pharma_flag,'psc_flag'=>$dist->psc_flag,'export_flag'=>$dist->export_flag]);
              $oldDisttributor = Session::has('distributor_to')?Session::get('distributor_to'):null;
            }
        }
      }
    }
  }

  public function getIndex(Request $request)
  {
    $perPage = 12; // Item per page
    $currentPage = Input::get('page') - 1;
    $pilihandistributor = Input::get('dist','');
    if ($pilihandistributor!='')
    {
      $dist = Customer::where('id','=',$pilihandistributor)->select('id','customer_name','pharma_flag','psc_flag','export_flag')->first();
      $request->session()->put('distributor_to',['id'=>$dist->id,'customer_name'=>$dist->customer_name,'pharma_flag'=>$dist->pharma_flag,'psc_flag'=>$dist->psc_flag]);
      $oldDisttributor = Session::has('distributor_to')?Session::get('distributor_to'):null;
    }
    $sqlproduct = "select id, title, imagePath,satuan_secondary,satuan_primary, inventory_item_id, getItemPrice ( :cust, p.id, p.satuan_secondary  ) AS harga, substr(itemcode,1,2) as item from products as p where enabled_flag='Y' ";
    if(isset(Auth::user()->customer_id)){
      $customer = Customer::find(Auth::user()->customer_id);
      $oldDisttributor = Session::has('distributor_to')?Session::get('distributor_to'):null;
      if (is_null($oldDisttributor) or $oldDisttributor ==null)
      {
        if($customer->hasDistributor()->count()>1)
        {
          $distributor = DB::table('outlet_distributor as o')
                        ->join('customers as c','o.distributor_id','=','c.id')
                        ->join('users as u','u.customer_id','=','c.id')
                        ->where('o.outlet_id','=',Auth::user()->customer_id)
                        ->select('c.id','c.customer_name','u.avatar')
                        ->get();
          return view('shop.sites',['distributors' => $distributor]);

        }else{//if(count($distributor)>1) {

            foreach ($customer->hasDistributor as $dist) {
              //dd('distributor:'.$dist->pivot->customer_name);
              $request->session()->put('distributor_to',['id'=>$dist->id,'customer_name'=>$dist->customer_name,'pharma_flag'=>$dist->pharma_flag,'psc_flag'=>$dist->psc_flag,'export_flag'=>$dist->export_flag]);
              $oldDisttributor = Session::has('distributor_to')?Session::get('distributor_to'):null;
            }
        }
      }

      if($customer->psc_flag!="1" or $oldDisttributor['psc_flag']!="1")
      {
        $sqlproduct .= " and exists (select 1
						from category_products  as cp
              ,categories cat
						where cp.product_id = p.id
              and cp.flex_value = cat.flex_value
							and cat.parent not like 'PSC')";
      }
      if($customer->pharma_flag!="1" or $oldDisttributor['pharma_flag']!="1")
      {
        $sqlproduct .= " and exists (select 1
            from category_products as cp
              ,categories cat
            where cp.product_id = p.id
              and cp.flex_value = cat.flex_value
              and cat.parent not like 'PHARMA')";
      }
      if($customer->export_flag!="1" or $oldDisttributor['export_flag']!="1")
      {
        $sqlproduct .= " and exists (select 1
            from category_products as cp
              ,categories cat
            where cp.product_id = p.id
              and cp.flex_value = cat.flex_value
              and cat.parent not like 'INTERNATIONAL')";
      }


    }
    //var_dump($sqlproduct);

      if (isset(Auth::user()->customer_id))
      {
        $vid=Auth::user()->id;
      }else{
        $vid='';
      }
    // /  dd($sqlproduct);
    //  $sqlproduct .= " limit 12";
    $dataproduct = DB::select($sqlproduct, ['cust'=>$vid]);

    foreach($dataproduct as $dp)
    {
      $uom = DB::table('mtl_uom_conversions_v')->where('product_id','=',$dp->id)->select('uom_code')->get();
      $dp->uom=$uom;
    }

    $products = collect($dataproduct);


    $pagedData = $products->slice($currentPage * $perPage, $perPage)->all();
    $products= new LengthAwarePaginator($pagedData, count($products), $perPage);

  //  dd($products);
    $products ->setPath(url()->current());
    //", ['cust'=>$vid,'prod'=>$request->product,'uom'=>$request->uom]);
  /*  $products = Product::where('Enabled_Flag','=','Y')->select('id','title','imagePath','satuan_primary','satuan_secondary','price','inventory_item_id');//all();

    if(isset(Auth::user()->customer_id)){
      $customer = Customer::find(Auth::user()->customer_id);
      if($customer->psc_flag!="1")
      {
        $products =$products->whereHas('categories',function($q){
          $q->where('categories.flex_value','not like','1%');
        });
      }
      if($customer->pharma_flag!="1")
      {
        $products =$products->whereHas('categories',function($q){
          $q->where('categories.flex_value','not like','2%');
        });
      }
    }
    $products =$products->paginate(12);*/
    return view('shop.index',['products' => $products]);
  }

  public function search(Request $request)
  {
    $perPage = 12; // Item per page
    $currentPage = Input::get('page') - 1;
    $sqlproduct = "select id, title, imagePath,satuan_secondary,satuan_primary, inventory_item_id, getItemPrice ( :cust, p.id, p.satuan_secondary  ) AS harga, substr(itemcode,1,2) as item from products as p where enabled_flag='Y' ";
    if(isset(Auth::user()->customer_id)){
      $customer = Customer::find(Auth::user()->customer_id);
      $oldDisttributor = Session::has('distributor_to')?Session::get('distributor_to'):null;
      if(!is_null($oldDisttributor))
      {
        if($oldDisttributor['psc_flag']!="1")
        {
          $sqlproduct .= " and exists (select 1
              from category_products as cp
                ,categories cat
              where cp.product_id = p.id
                and cp.flex_value = cat.flex_value
                and cat.parent not like 'PSC')";
        }
        if($oldDisttributor['pharma_flag']!="1")
        {
          $sqlproduct .= " and exists (select 1
              from category_products as cp
                ,categories cat
              where cp.product_id = p.id
                and cp.flex_value = cat.flex_value
                and cat.parent not like 'PHARMA')";
        }
        if($oldDisttributor['export_flag']!="1")
        {
          $sqlproduct .= " and exists (select 1
              from category_products as cp
                ,categories cat
              where cp.product_id = p.id
                and cp.flex_value = cat.flex_value
                and cat.parent not like 'INTERNATIONAL')";
        }
      }
      if($customer->psc_flag!="1")
      {
        $sqlproduct .= " and exists (select 1
            from category_products as cp
              ,categories cat
            where cp.product_id = p.id
              and cp.flex_value = cat.flex_value
              and cat.parent not like 'PSC')";
      }
      if($customer->pharma_flag!="1")
      {
        $sqlproduct .= " and exists (select 1
            from category_products as cp
              ,categories cat
            where cp.product_id = p.id
              and cp.flex_value = cat.flex_value
              and cat.parent not like 'PHARMA')";
      }
      if($customer->export_flag!="1")
      {
        $sqlproduct .= " and exists (select 1
            from category_products as cp
              ,categories cat
            where cp.product_id = p.id
              and cp.flex_value = cat.flex_value
              and cat.parent not like 'INTERNATIONAL')";
      }
    }
    if(isset($request->search_product))
    {
      //$products = Product::where('title','like','%'.$request->search_product.'%')->paginate(12);//all();
      $sqlproduct .= " and title like '".$request->search_product."%'";

    }
    if (isset(Auth::user()->customer_id))
    {
      $vid=Auth::user()->id;
    }else{
      $vid='';
    }
    $dataproduct = DB::select($sqlproduct, ['cust'=>$vid]);

    foreach($dataproduct as $dp)
    {
      $uom = DB::table('mtl_uom_conversions_v')->where('product_id','=',$dp->id)->select('uom_code')->get();
      $dp->uom=$uom;
    }
    //  $sqlproduct .= " limit 12";
    $products = collect($dataproduct);
    $pagedData = $products->slice($currentPage * $perPage, $perPage)->all();
    $products= new LengthAwarePaginator($pagedData, count($products), $perPage);
    $products->appends($_REQUEST)->render();
    $products ->setPath(url()->current());
    return view('shop.index',['products' => $products]);
  }

  public function category($id)
  {
      $kategory = Category::find($id);
      //$products = $kategory->products()->paginate(12);
      $perPage = 12; // Item per page
      $currentPage = Input::get('page') - 1;
      $sqlproduct = "select id, title, imagePath,satuan_secondary,satuan_primary, inventory_item_id, getItemPrice ( :cust, p.id, p.satuan_secondary  ) AS harga, substr(itemcode,1,2) as item from products as p where enabled_flag='Y' ";
      if(isset(Auth::user()->customer_id)){
        $customer = Customer::find(Auth::user()->customer_id);
        if($customer->psc_flag!="1")
        {
          $sqlproduct .= " and exists (select 1
              from category_products as cp
                ,categories cat
              where cp.product_id = p.id
                and cp.flex_value = cat.flex_value
                and cat.parent not like 'PSC')";
        }
        if($customer->pharma_flag!="1")
        {
          $sqlproduct .= " and exists (select 1
              from category_products as cp
                ,categories cat
              where cp.product_id = p.id
                and cp.flex_value = cat.flex_value
                and cat.parent not like 'PHARMA')";
        }
        if($customer->export_flag!="1")
        {
          $sqlproduct .= " and exists (select 1
              from category_products as cp
                ,categories cat
              where cp.product_id = p.id
                and cp.flex_value = cat.flex_value
                and cat.parent not like 'INTERNATIONAL')";
        }
      }
      $sqlproduct .= " and exists (select 1
          from category_products as cat
          where cat.product_id = p.id
            and cat.flex_value = '".$id."')";
      if (isset(Auth::user()->customer_id))
      {
        $vid=Auth::user()->id;
      }else{
        $vid='';
      }
      //  $sqlproduct .= " limit 12";
      $dataproduct = DB::select($sqlproduct, ['cust'=>$vid]);

      foreach($dataproduct as $dp)
      {
        $uom = DB::table('mtl_uom_conversions_v')->where('product_id','=',$dp->id)->select('uom_code')->get();
        $dp->uom=$uom;
      }
      //  $sqlproduct .= " limit 12";
      $products = collect($dataproduct);
      $pagedData = $products->slice($currentPage * $perPage, $perPage)->all();
      $products= new LengthAwarePaginator($pagedData, count($products), $perPage);
      $products ->setPath(url()->current());
    return view('shop.index',['products' => $products,'nama_kategori'=>$kategory->description]);
  }

  public function show($id)
  {
    //$product=Product::find($id);
    $sqlproduct = "select id, title, imagePath,description, description_en,satuan_secondary,satuan_primary, inventory_item_id, getItemPrice ( :cust, p.id, p.satuan_secondary  ) AS harga, substr(itemcode,1,2) as item from products as p where p.id = '".$id."'";
    if (isset(Auth::user()->customer_id))
    {
      $vid=Auth::user()->id;
    }else{
      $vid='';
    }
    //  $sqlproduct .= " limit 12";
     $product = DB::select($sqlproduct, ['cust'=>$vid]);
     $uom = DB::table('mtl_uom_conversions_v')->where('product_id','=',$product[0]->id)->select('uom_code')->get();
     $product[0]->uom=$uom;

     return view('shop.detailProduct01',['product' => $product[0]]);
  }

  public function index()
  {
    $products=Product::all();//paginate(10);
     return view('admin.product.index',['products' => $products,'menu'=>'product']);
  }



  public function master($id)
  {
    $product=Product::find($id);
    return view('admin.product',['product' => $product,'menu'=>'product']);
  }

  public function update(Request $request,$id)
  {
    $product=Product::find($id);
    $image = $request->file('input_img');

    //echo($request->file('input_img')->getClientOriginalName());
    /*product image yg lama di hapus jika file input tidak kosong*/
    if($image){
      if($image->getClientOriginalName()!=$product->imagePath and
        $product->imagePath!=""){
          //echo ("<br>try to delete image");
        if (isset($product->imagePath)){
          if ($product->imagePath!=""){
            if (File::exists(public_path('img\\'.$product->imagePath))){
              unlink(public_path('img\\'.$product->imagePath));
              //echo ("<br>delete image");
            }
            $product->imagePath="";
          }
        }
      }
    }

    //echo("<br>Product:".$product->imagePath);
    if(is_null($product->imagePath) or $product->imagePath=="")
    {
      $this->validate($request, [
          'input_img' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
      ]);

      $input['imagename'] = time().'.'.$image->getClientOriginalExtension();
      $destinationPath = public_path('img');
    //  dd($destinationPath);
      $image->move($destinationPath, $input['imagename']);
      $product->imagePath = $input['imagename'] ;
    }
    //dd('stop');
    //$product=Product::find($id);
    $product->description =$request->id_descr;
    $product->description_en =$request->en_descr;
    $product->save();

    return redirect()->route('product.master',$id)->withMessage('Product Updated');
  }

  //show cart
    public function getCart(){
      if (!Session::has('cart')){
        //Session::forget('cart');
        return view('shop.shopping-cart',['products'=>null]);
      }

      $dist = Session::get('distributor_to','');
      if(is_null($dist))
      {
        $this->getDistributor();
        dd($oldDisttributor);
      }
      $oldCart = Session::get('cart');
      $cart =  new Cart($oldCart);

      //dd($alamat);
      return view('shop.shopping-cart',['products'=>$cart->items, 'totalPrice'=>$cart->totalPrice,'distributor'=>$dist]);
    }

    public function checkOut(){
      if (!Session::has('cart')){
        //Session::forget('cart');
        return view('shop.shopping-cart',['products'=>null]);
      }
      $oldCart = Session::get('cart');
      $dist = Session::get('distributor_to','');
      //dd($oldCart);

      $cart =  new Cart($oldCart);
      $alamat = DB::table("customer_sites as c")
          ->select("id", DB::raw("concat(c.address1,
												IF(c.state IS NULL, '', concat(',',c.state)),
												IF(c.city IS NULL, '', concat(',',c.city)),
												IF(c.province IS NULL, '', concat(',',c.province)),
												IF(c.postalcode IS NULL, '', concat(',',c.postalcode))) as address1"))
          ->where('customer_id','=',auth()->user()->customer_id)
          ->where('site_use_code','=','SHIP_TO')
          ->get();
      $billto=null;
      if ($dist!='')
      {
        $userdist = User::where('customer_id','=',$dist['id'])->first();

        if ($userdist->hasRole('Principal'))
        {
          $billto = DB::table("customer_sites as c")
              ->select("id", DB::raw("concat(c.address1,
    												IF(c.state IS NULL, '', concat(',',c.state)),
    												IF(c.city IS NULL, '', concat(',',c.city)),
    												IF(c.province IS NULL, '', concat(',',c.province)),
    												IF(c.postalcode IS NULL, '', concat(',',c.postalcode))) as address1"))
              ->where('customer_id','=',auth()->user()->customer_id)
              ->where('site_use_code','=','BILL_TO')
              ->get();
        }
        //dd($billto);
      }

      //dd($alamat);
      return view('shop.checkout',['products'=>$cart->items, 'totalPrice'=>$cart->totalPrice,'addresses'=> $alamat,'distributor'=>$dist,'billto'=>$billto]);
    }

    //function click add to cart button
    public function getAddToCart(Request $request,$id){
        //$request->hrg = floatval($request->hrg);
        $product = Product::where('id','=',$id)->select('id','title','imagePath','satuan_primary','satuan_secondary','inventory_item_id')->first();
        $uom = DB::table('mtl_uom_conversions_v')->where('product_id','=',$id)->select('uom_code')->get();
        $product->uom=$uom;
        $oldCart = Session::has('cart')?Session::get('cart'):null;
        $cart = new Cart($oldCart);
        $cart->add($product,$id,$request->qty,$request->satuan,floatval($request->hrg) );

        $request->session()->put('cart',$cart);
        return response()->json([
                        'result' => 'success',
                        'totline' => $cart->totalQty,
                      ],200);
        //return redirect()->route('product.index');

      }

    public function getRemoveItem($id){
      $oldCart = Session::has('cart')?Session::get('cart'):null;
      $cart = new Cart($oldCart);
      $cart->removeItem($id);

      if (count($cart->items)>0){
        Session::put('cart',$cart);
      }else{
        Session::forget('cart');
        Session::forget('distributor_to');
      }
      return redirect()->route('product.shoppingCart');
    }

    public function getEditToCart(Request $request,$id){
        $uom = substr($id,-3,3);
        if (isset(Auth::user()->customer_id))
        {
          $vid=Auth::user()->id;
        }else{
          $vid='';
        }

        $price = DB::select("select getItemPrice ( :cust, :prod, :uom ) AS harga from dual", ['cust'=>$vid,'prod'=>$request->product,'uom'=>$request->satuan]);
        $request->hrg = $price[0]->harga;

        $oldCart = Session::has('cart')?Session::get('cart'):null;
        $cart = new Cart($oldCart);
        if($uom!=$request->satuan)
        {
          $cart->removeItem($id);
          $product = Product::where('id','=',$request->product)->select('id','title','imagePath','satuan_primary','satuan_secondary','inventory_item_id')->first();
          $uomdata = DB::table('mtl_uom_conversions_v')->where('product_id','=',$request->product)->select('uom_code')->get();
          $product->uom=$uomdata;
          //$this->getAddToCart($request,$request->product);
          $cart->add($product,$request->product,$request->qty,$request->satuan,floatval($request->hrg) );
        }else{

          $cart->editItem($id,$request->qty,$request->satuan,$request->id,$request->hrg);
        }
        Session::put('cart',$cart);
        return response()->json([
                        'result' => 'success',
                        'price' => number_format($request->hrg,2),
                        'amount' => number_format($request->hrg*$request->qty,2),
                        'total' => number_format($cart->totalPrice,2)
                      ],200);
        //return redirect()->route('product.index');

    }

    public function postOrder(Request $request){
      if(!Session::has('cart'))
      {
        return view('shop.shopping-cart',['products'=>null]);
      }
      $oldCart = Session::has('cart')?Session::get('cart'):null;
      $cart = new Cart($oldCart);
      $billid=null;
      $shipid=null;
      $orgid=null;
      $warehouseid=null;
      if(!Session::has('distributor_to'))
      {
        return view('shop.shopping-cart',['products'=>null])->withMessage('Pilih distributor terlebih dahulu');
      }else{
        $oldDisttributor =  $request->session()->get('distributor_to');
        //$distributor = Customer::find($oldDisttributor['id']);
        $customer =  Customer::find(auth()->user()->customer_id);
        if($customer->export_flag=="1")
        {
            $currency='USD';
        }else{$currency='IDR';}
        $userdistributor = User::where('customer_id','=',$oldDisttributor['id'])->first();
        if($userdistributor->hasRole('Principal')) /*jika ke gpl atau YMP maka data oracle harus diisi*/
        {
          Validator::make($request->all(), [
              'alamat' => 'required|numeric',
              'billto' => 'required|numeric',
           ])->validate();
          $oracleshipid = CustomerSite::where('id','=',$request->alamat)->select('site_use_id','org_id','warehouse')->first();
          if($oracleshipid)
          {
            $shipid = $oracleshipid->site_use_id;
            if(is_null($oracleshipid->org_id))
            {
              $orgid = config('constant.org_id');
            }else{
                $orgid = $oracleshipid->org_id;
            }

            if(is_null($oracleshipid->warehouse))
            {
              if($orgid==106)
              {
                  $warehouseid = config('constant.warehouseid_YMP');
              }else{$warehouseid = config('constant.warehouseid_GPL');}

            }else{
              $warehouseid = $oracleshipid->warehouse;
            }

          }else{
            return Redirect::back()->with('msg', 'Ship to not found');
          }
          $alamatbillto=$request->billto;
          if ($alamatbillto=="")
          {
            //redirect back
              return Redirect::back()->with('msg', 'Bill to not found');
          }else{
            $oraclebillid = CustomerSite::where('id','=',$alamatbillto)->select('site_use_id')->first();
            if($oraclebillid)
            {
              $billid = $oraclebillid->site_use_id;
            }else{
                return Redirect::back()->with('msg', 'Bill to not found');
            }
          }
        }
      }
      $thn = date('Y');
      $bln=date('m');
      $tmpnotrx = DB::table('tmp_notrx')->where([
          ['tahun','=',$thn],
          ['bulan','=',$bln],
      ])->select('lastnum')->first();
      if(!$tmpnotrx)
      {
          $tmpnotrx = 0;
      }else{
          $tmpnotrx = (int)$tmpnotrx->lastnum;
      }
      $lastnumber=$tmpnotrx+1;
      $path=null;
      $notrx = 'PO-'.date('Ymd').'-'.$this->getRomanNumerals(date('m')).'-'.str_pad($lastnumber,5,'0',STR_PAD_LEFT) ;
      if($request->hasFile('filepo'))
      {
        $validator = Validator::make($request->all(), [
            'filepo' => 'required|mimes:pdf|max:10240',
        ])->validate();
        $path = $request->file('filepo')->storeAs(
            'PO', $notrx.".".$request->file('filepo')->getClientOriginalExtension()
        );
      }

       Validator::make($request->all(), [
           'no_order' => 'required|max:50',
           'alamat' => 'required',
        ])->validate();
      $header= SoHeader::create([
          'distributor_id' => $oldDisttributor['id'],
          'customer_id' => auth()->user()->customer_id,
          'cust_ship_to' => $request->alamat,
          'cust_bill_to' => $request->billto,
          'customer_po' => $request->no_order,
          'file_po' => $path,
          'approve' => 0,
          'tgl_order'=> Carbon::now(),
          'currency'=> $currency,
          'payment_term_id'=> null,
          'price_list_id'=> $customer->price_list_id,
          'order_type_id'=> $customer->order_type_id,
          'oracle_ship_to'=> $shipid,
          'oracle_bill_to'=> $billid,
          'oracle_customer_id' =>$customer->oracle_customer_id,
          'notrx' => $notrx,
          'status' => 0,
          'org_id' => $orgid,
          'warehouse' =>$warehouseid
        ]);
      if($header){
        if($tmpnotrx==0){
          DB::table('tmp_notrx')->insert(
              ['tahun' => $thn,'bulan'=>$bln,'lastnum'=>$lastnumber]
          );
        }else{
          DB::table('tmp_notrx')->where([
              ['tahun','=',  $thn],
              ['bulan','=',$bln]
            ])
            ->update(
              ['lastnum'=>$lastnumber]
          );
        }

        foreach($cart->items as $product)
        {
          SoLine::Create([
            'header_id'=> $header->id,
            'product_id'=> $product['item']['id'],
            'uom'=> $product['uom'],
            'qty_request' => $product['qty'],
            'list_price' => $product['price'],
            'unit_price' =>$product['price'],
            'amount' => $product['amount'],
            'inventory_item_id'=> $product['item']['inventory_item_id']
          ]);
        }
        //notification to distributor
        $data= ['distributor'=>$oldDisttributor['id'],'user'=>$userdistributor->id,'so_header_id'=>$header->id,'customer'=>auth()->user()->customer_id];
        //$data= ['distributor'=>$oldDisttributor['id'],'user'=>$u->id,'so_header_id'=>1,'customer'=>auth()->user()->customer_id];
        $userdistributor->notify(new NewPurchaseOrder($data));
      }


      //foreach($distributor->users as $u)
      //{

      //}

      //lepas session
      Session::forget('cart');
      Session::forget('distributor_to');
      return redirect()->route('order.listPO');
    }

    public function getRomanNumerals($decimalInteger)
    {
     $n = intval($decimalInteger);
     $res = '';

     $roman_numerals = array(
        'M'  => 1000,
        'CM' => 900,
        'D'  => 500,
        'CD' => 400,
        'C'  => 100,
        'XC' => 90,
        'L'  => 50,
        'XL' => 40,
        'X'  => 10,
        'IX' => 9,
        'V'  => 5,
        'IV' => 4,
        'I'  => 1);

     foreach ($roman_numerals as $roman => $numeral)
     {
      $matches = intval($n / $numeral);
      $res .= str_repeat($roman, $matches);
      $n = $n % $numeral;
     }

     return $res;
    }
}
