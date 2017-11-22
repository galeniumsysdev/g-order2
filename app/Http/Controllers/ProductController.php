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
//use App\Notifications\NewPurchaseOrder;
use Mail;
use App\Mail\CreateNewPo;
use Illuminate\Database\Eloquent\Collection;
use App\DPLSuggestNo;
use App\PoDraftHeader;
use App\PoDraftLine;
use App\Events\PusherBroadcaster;
use App\Notifications\PushNotif;


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
    $price = DB::select("select getProductPrice ( :cust, :prod, :uom ) AS harga from dual", ['cust'=>$vid,'prod'=>$request->product,'uom'=>$request->uom]);
    $price = $price[0];
    $hargadiskon = DB::select("select getDiskonPrice ( :cust, :prod, :uom, 1 ) AS harga from dual", ['cust'=>$vid,'prod'=>$request->product,'uom'=>$request->uom]);
    $hargadiskon = $hargadiskon[0];
    $rate = DB::select("select p.satuan_primary, getItemRate ( p.satuan_primary, :uom, p.id ) AS rate, p.itemcode from products as p where id = :id", ['id'=>$request->product,'uom'=>$request->uom]);
    $konversi = $rate[0];
  //  dd($price->harga);
    return response()->json([
                    'result' => 'success',
                    'price' => (float)$price->harga,
                    'diskon' => (float)$hargadiskon->harga,
                    'konversi' => (float)$konversi->rate,
                    'uomprimary' =>$konversi->satuan_primary,
                    'itemcode' =>$konversi->itemcode
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
          return $distributor;
          //return view('shop.sites',['distributors' => $distributor]);

        }else{//if(count($distributor)>1) {

            foreach ($customer->hasDistributor as $dist) {
              //dd('distributor:'.$dist->pivot->customer_name);
              Session::put('distributor_to',['id'=>$dist->id,'customer_name'=>$dist->customer_name,'pharma_flag'=>$dist->pharma_flag,'psc_flag'=>$dist->psc_flag,'export_flag'=>$dist->export_flag]);
              $oldDisttributor = Session::has('distributor_to')?Session::get('distributor_to'):null;
            }
            return $oldDisttributor;
        }
      }
    }
  }
  public function getAjaxProduct(Request $request)
  {
    $products =Product::where([['enabled_flag','=','Y'],['title','like',$request->input('query')."%"]]);
    $products =$products->select('title','id')->get();
    return response()->json($products);
  }

  public function updatePareto(Request $request)
  {

  /*  if($request->isMethod('post'))
    {*/
      $product=Product::where('id','=',$request->idpareto)->update(['pareto'=>1]);
      return redirect()->route('product.pareto')->withMessage('Product Updated');
    /*}elseif($request->isMethod('DELETE')){
      $product=Product::where('id','=',$id)->update(['pareto'=>0]);
      return redirect()->route('product.pareto')->withMessage('Product Removed');
    }*/


  }
  public function destroyPareto($id)
  {
    $product=Product::where('id','=',$id)->update(['pareto'=>0]);
    return redirect()->route('product.pareto')->withMessage('Product removed from pareto products');
  }

  public function getSqlProduct()
  {
    if(Auth::user()->hasRole('Distributor'))
    {
        $sqlproduct = "select id, title, imagePath,satuan_secondary,satuan_primary, inventory_item_id, getProductPrice ( :cust, p.id, p.satuan_secondary  ) AS harga, substr(itemcode,1,2) as item,getitemrate(p.satuan_primary, p.satuan_secondary, p.id) as rate, getDiskonPrice(:cust1, p.id, p.satuan_secondary,1 ) price_diskon from products as p where enabled_flag='Y' ";
    }else{
        $sqlproduct = "select id, title, imagePath,satuan_secondary,satuan_primary, inventory_item_id, getProductPrice ( :cust, p.id, p.satuan_primary  ) AS harga, substr(itemcode,1,2) as item,getitemrate(p.satuan_primary, p.satuan_primary, p.id) as rate, getDiskonPrice(:cust1, p.id, p.satuan_primary,1 ) price_diskon from products as p where enabled_flag='Y' ";
    }

    if(isset(Auth::user()->customer_id)){
      $customer = Customer::find(Auth::user()->customer_id);
      /*$oldDisttributor = Session::has('distributor_to')?Session::get('distributor_to'):null;
      if(!is_null($oldDisttributor)){*/
        if($customer->psc_flag!="1" )
        {
          $sqlproduct .= " and exists (select 1
              from category_products  as cp
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
        if(Auth::user()->hasRole('Apotik/Klinik') or Auth::user()->hasRole('Outlet'))
        {
          $sqlproduct .= " and exists (select 1
              from category_products as cp
                ,categories cat
              where cp.product_id = p.id
                and cp.flex_value = cat.flex_value
                and cat.description <> 'BPJS')";
          if(isset(Auth::user()->customer->outet_type_id) and $customer->pharma_flag=="1")
          {
            if(!in_array(Auth::user()->customer->categoryOutlet->name,['Apotik', 'PBF', 'Rumah Sakit/Klinik']))
            {
              $sqlproduct .= " and p.tipe_dot != ('Merah')";
            }
            if(!in_array(Auth::user()->customer->categoryOutlet->name,['Apotik', 'PBF', 'Rumah Sakit/Klinik','Toko Obat Berijin']))
            {
              $sqlproduct .= " and p.tipe_dot != ('Biru')";
            }
          }

        }


      //}
    }
    return $sqlproduct;
  }

  public function getIndex()
  {
    $products = Product::where([['enabled_flag','=','Y'],['pareto','=',1]])->get();
    return view('shop.index',['products' => $products]);
  }

  public function displayProduct(Request $request)
  {
    $perPage = 12; // Item per page
    $currentPage = Input::get('page') - 1;
    /*$pilihandistributor = Input::get('dist','');
    if ($pilihandistributor!='')
    {
      $dist = Customer::where('id','=',$pilihandistributor)->select('id','customer_name','pharma_flag','psc_flag','export_flag')->first();
      $request->session()->put('distributor_to',['id'=>$dist->id,'customer_name'=>$dist->customer_name,'pharma_flag'=>$dist->pharma_flag,'psc_flag'=>$dist->psc_flag,'export_flag'=>$dist->export_flag]);
      $oldDisttributor = Session::has('distributor_to')?Session::get('distributor_to'):null;
    }
      $oldDisttributor = $this->getDistributor();

      if(!is_null($oldDisttributor))
      {
        if(!is_array($oldDisttributor))
        {
          return view('shop.sites',['distributors' => $oldDisttributor]);
        }
      }
    */

    $sqlproduct = $this->getSqlProduct();

    //var_dump($sqlproduct);

      if (isset(Auth::user()->customer_id))
      {
        $vid=Auth::user()->id;
      }else{
        $vid='';
      }
    // /  dd($sqlproduct);
    //  $sqlproduct .= " limit 12";
    $dataproduct = DB::select($sqlproduct, ['cust'=>$vid,'cust1'=>$vid ]);

    foreach($dataproduct as $dp)
    {
      $uom = DB::table('mtl_uom_conversions_v')->where('product_id','=',$dp->id)->select('uom_code')->get();
      $dp->uom=$uom;
    }

    $products = collect($dataproduct);


    /*$pagedData = $products->slice($currentPage * $perPage, $perPage)->all();
    $products= new LengthAwarePaginator($pagedData, count($products), $perPage);

  //  dd($products);
    $products ->setPath(url()->current());*/

    return view('shop.product',['products' => $products]);
  }

  public function search(Request $request)
  {
    $perPage = 12; // Item per page
    $currentPage = Input::get('page') - 1;
    $oldDisttributor = $this->getDistributor();

    if(!is_null($oldDisttributor))
    {
      if(!is_array($oldDisttributor))
      {
        return view('shop.sites',['distributors' => $oldDisttributor]);
      }
    }
    $sqlproduct = $this->getSqlProduct();

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
    $dataproduct = DB::select($sqlproduct, ['cust'=>$vid,'cust1'=>$vid]);

    foreach($dataproduct as $dp)
    {
      $uom = DB::table('mtl_uom_conversions_v')->where('product_id','=',$dp->id)->select('uom_code')->get();
      $dp->uom=$uom;
    }
    //  $sqlproduct .= " limit 12";
    $products = collect($dataproduct);
    /*$pagedData = $products->slice($currentPage * $perPage, $perPage)->all();
    $products= new LengthAwarePaginator($pagedData, count($products), $perPage);
    $products->appends($_REQUEST)->render();
    $products ->setPath(url()->current());*/
    return view('shop.index',['products' => $products]);
  }

  public function category($id)
  {
      $kategory = Category::find($id);
      //$products = $kategory->products()->paginate(12);
      $perPage = 30; // Item per page
      $oldDisttributor = $this->getDistributor();

      if(!is_null($oldDisttributor))
      {
        if(!is_array($oldDisttributor))
        {
          return view('shop.sites',['distributors' => $oldDisttributor]);
        }
      }
      $sqlproduct = $this->getSqlProduct();
      $currentPage = Input::get('page') - 1;
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
      $dataproduct = DB::select($sqlproduct, ['cust'=>$vid,'cust1'=>$vid]);

      foreach($dataproduct as $dp)
      {
        $uom = DB::table('mtl_uom_conversions_v')->where('product_id','=',$dp->id)->select('uom_code')->get();
        $dp->uom=$uom;
      }
      //  $sqlproduct .= " limit 12";
      $products = collect($dataproduct);
      /*$pagedData = $products->slice($currentPage * $perPage, $perPage)->all();
      $products= new LengthAwarePaginator($pagedData, count($products), $perPage);
      $products ->setPath(url()->current());*/
    return view('shop.index',['products' => $products,'nama_kategori'=>$kategory->description]);
  }

  public function show($id)
  {
    //$product=Product::find($id);
    $sqlproduct = "select id, title, imagePath,description, description_en,satuan_secondary,satuan_primary, inventory_item_id, getItemPrice ( :cust, p.id, p.satuan_secondary  ) AS harga, substr(itemcode,1,2) as item,getitemrate(p.satuan_primary, p.satuan_secondary, p.id) as rate  from products as p where p.id = '".$id."'";
    //$sqlproduct = $this->getSqlProduct();
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
    //$product=Product::find($id);
    $categories = Category::where('enabled_flag','=','Y')->get();
    $product = DB::table('products as p')
              ->leftjoin('category_products as cp','p.id','=','cp.product_id')
              ->leftjoin('categories as c', 'cp.flex_value','=','c.flex_value')
              ->where('p.id','=',$id)
              ->select('p.id as id','p.title','p.itemcode','p.description','p.description_en','p.imagePath','p.satuan_primary','c.description as category_name','c.flex_value','c.parent','p.inventory_item_id','p.enabled_flag','p.pareto','p.long_description')
              ->first();
    //dd($product) ;
    return view('admin.product.edit',['product' => $product,'categories'=>$categories,'menu'=>'product']);
  }

  public function update(Request $request,$id)
  {
    $product=Product::find($id);
    $image = $request->file('input_img');
    $this->validate($request, [
        'nama' => 'required|unique:products,title,'.$id.',id|max:191',
        'enabledflag'=>'required',
    ]);
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
    $product->title =  $request->nama;
    $product->pareto = $request->pareto;
    $product->enabled_flag = $request->enabledflag;
    $product->description =$request->id_descr;
    $product->description_en =$request->en_descr;
    $product->save();
    //$product->categories()->detach();
    //$product->categories()->attach($request->category);
  //  $product->categories()->sync([$request->category]);

    return redirect()->route('product.master',$id)->withMessage('Product Updated');
  }

  //show cart
    public function getCart(){
      if (!Session::has('cart')){
        //Session::forget('cart');
        return view('shop.shopping-cart',['products'=>null]);
      }

      /*$dist = Session::get('distributor_to','');
      if(is_null($dist))
      {
        $this->getDistributor();
        dd($oldDisttributor);
      }*/
      $dist = Auth::user()->customer->hasDistributor;

      $oldCart = Session::get('cart');
      $cart =  new Cart($oldCart);

      //dd($alamat);
      return view('shop.shopping-cart',['products'=>$cart->items, 'totalPrice'=>$cart->totalPrice,'totalDiscount'=>$cart->totalDiscount,'totalAmount'=>$cart->totalAmount, 'tax'=>$cart->totalTax ,'distributor'=>$dist]);
    }

    public function checkOut($distributorid){
      if (!Session::has('cart')){
        //Session::forget('cart');
        return view('shop.shopping-cart',['products'=>null]);
      }
      $oldCart = Session::get('cart');
      //$dist = Session::get('distributor_to','');
      $dist = Customer::where('id','=',$distributorid)->first();
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
          ->where('status','=','A')
          ->get();
      $billto=null;
      if ($dist!='')
      {
        $userdist = User::where('customer_id','=',$distributorid)->first();

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
              ->where('status','=','A')
              ->get();
        }
        //dd($billto);
      }

      //dd($alamat);
      return view('shop.checkout',['products'=>$cart->items, 'totalPrice'=>$cart->totalPrice,'totalDiscount'=>$cart->totalDiscount,'totalAmount'=>$cart->totalAmount, 'tax'=>$cart->totalTax ,'addresses'=> $alamat,'billto'=>$billto,'distributor'=>$dist]);

    }

    //function click add to cart button
    public function getAddToCart(Request $request,$id){
        //$request->hrg = floatval($request->hrg);
        $product = Product::where('id','=',$id)->select('id','title','imagePath','satuan_primary','satuan_secondary','inventory_item_id')->first();
        $uom = DB::table('mtl_uom_conversions_v')->where('product_id','=',$id)->select('uom_code')->get();
        $product->uom=$uom;
        $oldCart = Session::has('cart')?Session::get('cart'):null;
        if(!is_null($oldCart))
        {
          if(array_key_exists($id.'-'.$request->satuan, $oldCart->items)){
            return response()->json([
                            'result' => 'exist',
                            'totline' => $oldCart->totalQty,
                          ],200);
          }
        }
        $cart = new Cart($oldCart);
        $cart->add($product,$id,$request->qty,$request->satuan,floatval($request->hrg),floatval($request->disc) );
        /*$headerpo = PoDraftHeader::firstorCreate(
                              ['customer_id'=>Auth::user()->customer_id]
                            );
        $linepo  = PoDraftLine::where(
                    [['po_header_id','=',$headerpo->id],['product_id','=',$product->id],['uom','=',$request->satuan]])->first();
        if($linepo)
        {
          $linepo->qty_request += $request->qty;
          $linepo->conversion_qty = $product->getConversion($request->satuan);
          $linepo->qty_request_primary=$linepo->conversion_qty*$request->qty;
          $linepo->primary_uom = $product->satuan_primary;
          $linepo->listprice  = floatval($request->hrg);
          $linepo->unit_price = floatval($request->disc);
          $linepo->amount = $linepo->qty_request*floatval($request->hrg);
          $linepo->discount = floatval($request->hrg)-floatval($request->disc);
          $linepo->save();
        }else{
          $linepo = PoDraftLine::updateorCreate(
                      ['po_header_id'=>$headerpo->id,'product_id'=>$product->id,'uom'=>$request->satuan],
                      ['qty_request'=>$request->qty
                      ,'qty_request_primary'=>$product->getConversion($request->satuan)*$request->qty
                      ,'primary_uom'=>$product->satuan_primary
                      ,'conversion_qty'=>$product->getConversion($request->satuan)
                      ,'inventory_item_id'=>$product->inventory_item_id
                      ,'list_price'=>floatval($request->hrg)
                      ,'unit_price'=>floatval($request->disc)
                      ,'amount'=>$request->qty*floatval($request->hrg)
                      ,'discount'=>floatval($request->hrg)-floatval($request->disc)
                      ]
            );

        }

        $headerpo->subtotal +=($request->qty*floatval($request->hrg));
        $headerpo->discount+= (floatval($request->hrg)-floatval($request->disc)) *$request->qty;
        if(Auth::user()->customer->customer_category_code=="PKP")
        {
          $headerpo->tax =($headerpo->subtotal-$headerpo->discount)*0.1;
        }else{
            $headerpo->tax =0;
        }

        $headerpo->Amount= $headerpo->subtotal - $headerpo->discount - $headerpo->tax;
        $headerpo->save();*/

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

        $price = DB::select("select getProductPrice ( :cust, :prod, :uom ) AS harga from dual", ['cust'=>$vid,'prod'=>$request->product,'uom'=>$request->satuan]);
        $request->hrg = $price[0]->harga;

        $disc = DB::select("select getDiskonPrice ( :cust, :prod, :uom,1 ) AS harga from dual", ['cust'=>$vid,'prod'=>$request->product,'uom'=>$request->satuan]);
        if($disc){
            $request->disc = $disc[0]->harga;
        }else{$request->disc = $request->hrg;}


        $oldCart = Session::has('cart')?Session::get('cart'):null;
        $cart = new Cart($oldCart);
        if($uom!=$request->satuan)
        {
          $cart->removeItem($id);
          $product = Product::where('id','=',$request->product)->select('id','title','imagePath','satuan_primary','satuan_secondary','inventory_item_id')->first();
          $uomdata = DB::table('mtl_uom_conversions_v')->where('product_id','=',$request->product)->select('uom_code')->get();
          $product->uom=$uomdata;
          //$this->getAddToCart($request,$request->product);
          $cart->add($product,$request->product,$request->qty,$request->satuan,floatval($request->hrg), floatval($request->disc) );
        }else{
          /*$headerpo = PoDraftHeader::where(['customer_id','=',Auth::user()->customer_id]) ->first();
          $linepo = PoDraftLine::where([['po_header_id','=',$headerpo->id],
                        ['product_id','=',$request->product]
                        ['uom','=',$uom]
                    ])->get();
          $linepo->qty          */
          $cart->editItem($id,$request->qty,$request->satuan,$request->id,$request->hrg,$request->disc);

        }
        Session::put('cart',$cart);
        return response()->json([
                        'result' => 'success',
                        'price' => number_format($request->hrg,2),
                        'disc' => number_format($request->disc,2),
                        'amount' => number_format($request->hrg*$request->qty,2),
                        'subtotal' => number_format($cart->totalPrice,2),
                        'disctot' => number_format($cart->totalDiscount,2),
                        'tax' => number_format($cart->totalTax,2),
                        'total' => number_format($cart->totalAmount,2),
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
      $check_dpl =null;
      if(isset($request->coupon_no))
      {
        $check_dpl = DPLSuggestNo::where('outlet_id',Auth::user()->customer_id)
                      ->where('suggest_no',$request->coupon_no)
                      ->whereNull('notrx')
                      ->get();
        if($check_dpl->isEmpty())
        {
          return redirect()->back()
                       ->withErrors(['coupon_no'=>trans('pesan.notmatchdpl')])
                       ->withInput();
        }
        $statusso = -99;
      }

      //dd($check_dpl);
      /*if(!Session::has('distributor_to'))
      {
        return view('shop.shopping-cart',['products'=>null])->withMessage('Pilih distributor terlebih dahulu');
      }else{*/
        //$oldDisttributor =  $request->session()->get('distributor_to');
        $distributor = Customer::find($request->dist_id);
        $customer =  Customer::find(auth()->user()->customer_id);
        if($customer->export_flag=="1")
        {
            $currency='USD';
        }else{
            $currency='IDR';
        }

        $tipetax=$customer->customer_category_code;
        $userdistributor = User::where('customer_id','=',$request->dist_id)->first();
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
            return redirect()->back()->with('msg', 'Ship to not found');
          }
          $alamatbillto=$request->billto;
          if ($alamatbillto=="")
          {
            //redirect back
              return redirect()->back()->with('msg', 'Bill to not found');
          }else{
            $oraclebillid = CustomerSite::where('id','=',$alamatbillto)->select('site_use_id')->first();
            if($oraclebillid)
            {
              $billid = $oraclebillid->site_use_id;
            }else{
                return redirect()->back()->with('msg', 'Bill to not found');
            }
          }
        }
      //}
      $statusso=0;
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
            'filepo' => 'required|mimes:jpeg,jpg,png,pdf|max:10240',
        ])->validate();
        $path = $request->file('filepo')->storeAs(
            'PO', $notrx.".".$request->file('filepo')->getClientOriginalExtension()
        );
      }



       Validator::make($request->all(), [
           'no_order' => 'required|unique:so_headers,customer_po,null,null,customer_id,'.Auth::user()->customer_id.'|max:50',
           'alamat' => 'required',
        ])->validate();
        if(isset($request->coupon_no) and isset($checkdpl))
        {
          $checkdpl->notrx = $notrx;
          $checkdpl->distributor_id= $request->dist_id;
          $checkdpl->save();
        }
      $header= SoHeader::create([
          'distributor_id' => $request->dist_id,
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
          'status' => $statusso,
          'org_id' => $orgid,
          'warehouse' =>$warehouseid,
          'suggest_no'=>$request->coupon_no

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

        $total =0;
        foreach($cart->items as $product)
        {
          /*if($product['uom']!=$product['item']['satuan_primary'])
          {
            $rate = DB::select("select p.satuan_primary, getItemRate ( p.satuan_primary, :uom, p.id ) AS rate, p.itemcode from products as p where id = :id", ['id'=>$product['item']['id'],'uom'=>$product['uom']]);
            if($rate)
            {
              $konversi = $rate[0]->rate;
              $qtyprimary = $product['qty']*$konversi;
            }else{
              $qtyprimary = null;
              $konversi=null;
            }
          }else{
            $konversi=1;
            $qtyprimary = $product['qty'];
          }*/
          $item = Product::where('id','=',$product['item']['id'])->first();
          $konversi = $item->getConversion($product['uom']);
          $qtyprimary = $product['qty']*$konversi;
          if($konversi==0)
          {
            $konversi=1;
            $qtyprimary=$product['qty'];
          }
          $tmpprice = $product['price'];
          $operand_reg =$item->getRateDiskon()->where('pricing_group_sequence','=',1)->first();
          if($operand_reg)
          {
            $disc1 =$operand_reg->operand;
            $amountdisc1=$tmpprice*$disc1/100;
            $tmpprice =$tmpprice-$amountdisc1;
          }else{
            $disc1 = 0;
            $amountdisc1=0;
          }
          $operand_product = $item->getRateDiskon()->where('pricing_group_sequence','=',2)->first();
          if($operand_product)
          {
            $disc2 =$operand_product->operand;
            $amountdisc2=$tmpprice*$disc2/100;
            $tmpprice =$tmpprice-$amountdisc2;
          }else{
            $disc2 =0;
            $amountdisc2=0;
          }


          $amount = ($product['price'] - $product['disc']) * $product['qty'];

          if($tipetax=="PKP")
          {
            $taxamount = ($product['price'] - $product['disc']) * $product['qty'] *0.1;
          }else{
            $taxamount = 0;
          }
          $total += ($amount+$taxamount);

          SoLine::Create([
            'header_id'=> $header->id,
            'product_id'=> $product['item']['id'],
            'uom'=> $product['uom'],
            'qty_request' => $product['qty'],
            'list_price' => $product['price'],
            'unit_price' =>$product['price'] - $product['disc'],
            'amount' => $amount,
            'inventory_item_id'=> $product['item']['inventory_item_id'],
            'uom_primary' => $product['item']['satuan_primary'],
            'qty_request_primary' => $qtyprimary,
            'conversion_qty' => $konversi,
            'tax_type'=>$tipetax,
            'tax_amount'=>$taxamount
            ,'disc_reg_percentage'=>$disc1
            ,'disc_product_percentage'=>$disc2
            ,'disc_reg_amount'=>$amountdisc1
            ,'disc_product_amount'=>$amountdisc2
          ]);
        }

        if($statusso==-99)/*jika DPL notify ke SPV dan selanjutnya*/
        {
          $suggest_no=$request->coupon_no;
          $notified_users = app('App\Http\Controllers\DplController')->getArrayNotifiedEmail($suggest_no,'');
    			if(!empty($notified_users)){
    				$data = [
    					'title' => 'Pengajuan DPL',
    					'message' => 'Pengajuan DPL #'.$request->coupon_no,
    					'id' => $suggest_no,
    					'href' => route('dpl.readNotifApproval'),
    					'email' => [
    						'markdown'=>'',
    						'attribute'=> array()
    					]
    				];
    				foreach ($notified_users as $key => $email) {
    					foreach ($email as $key => $mail) {
    						event(new PusherBroadcaster($data, $mail));
    						$apps_user = User::where('email',$mail)->first();
    						$apps_user->notify(new PushNotif($data));
    					}
    					break;
    				}
    			}

        }else{
          /*$data= ['distributor'=>$distributor->id,'user'=>$userdistributor->id,'so_header_id'=>$header->id,'customer'=>auth()->user()->customer_id];
          //$data= ['distributor'=>$oldDisttributor['id'],'user'=>$u->id,'so_header_id'=>1,'customer'=>auth()->user()->customer_id];
          $userdistributor->notify(new NewPurchaseOrder($data));*/
          $newlines = DB::table('so_Lines_v')->where('header_id','=',$header->id)->get();
          $data = [
            'title'=> 'New PO',
    				'message' => 'New PO '.$notrx.' From '.auth()->user()->customer->customer_name,
    				'id' => $header->id,
    				'href' => route('order.notifnewpo')
            ,'email' => [ 'greeting'=> "",
                            'content' => "",
                    		    'markdown'=> "emails.orders.create",
                    		    'attribute'=>	["so_headers"=>$header
                                          ,"lines"=>$newlines
                                          ,'total'=>$total
                                          ,'customer'=>auth()->user()->customer->customer_name]
                        ]
    			];
          event(new PusherBroadcaster($data, $userdistributor->email));
          $userdistributor->notify(new PushNotif($data));
        }
        //notification to distributor

        Mail::to(Auth::user())->send(new CreateNewPo($header));
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

    public function listParetoProduct()
    {
      $products = Product::where('pareto','=',1)->get();
      return view('admin.product.pareto',['products' => $products,'menu'=>'pareto']);
    }

    public function getRateDiskon( $groupid,$productid,$customerid)
    {
      $diskon = DB::table('list_discount_v')
              ->where([
                ['pricing_group_sequence','=',$groupid]
                ,['product_id','=',$productid]
                ,['customer_id','=',$customerid]
              ])->select('pricing_group_sequence',DB::raw("sum(operand) as operand"))
              ->groupBy('pricing_group_sequence')
              ->get();
      dd($diskon->where('pricing_group_sequence','=',1)->first());
      return $diskon;
    }


}
