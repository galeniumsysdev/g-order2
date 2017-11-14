<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\SoHeader;
use App\SoLine;
use Auth;
use App\Customer;
use Carbon\Carbon;
use App\Notifications\BookOrderOracle;
use App\Notifications\RejectPOByDistributor;
use App\Notifications\ShippingOrderOracle;
use Excel;
use App\QpListHeaders;
use App\OeTransactionType;
use App\User;
use App\SoShipping;
use App\CustomerSite;
use App\qp_modifier_summary;
use App\qp_qualifiers;

class BackgroundController extends Controller
{
    public function getStatusOrderOracle()
    {
      $request= DB::table('tbl_request')->where('event','=','SalesOrder')
                ->max('created_at');
      if($request)
      {
        $lasttime = date_create($request);
        //echo"type:".gettype($lasttime);
      }else{
        $lasttime = date_create("2017-07-01");
      }
      /*$newrequest= DB::table('tbl_request')->insertGetId([
        'created_at'=>Carbon::now(),
        'updated_at'=>Carbon::now(),
        'event'=>'SalesOrder'
      ]);*/
      $connoracle = DB::connection('oracle');
      if($connoracle){
        echo "masuk<br>";
        $headers = SoHeader::whereNotNull('oracle_customer_id')->where([
                  ['approve','=',1],
                  ['status','>=',0],
                  ['status','<',2],
                  ['notrx','=','PO-20171002-X-00010']
        ])->get();
        if($headers){
          foreach($headers as $h)
          {
            echo "notrx:".$h->notrx."<br>";

              /*if($h->status==0 and $h->interface_flag=="N")//jika blm terinterface
              {
                echo "insert interface oracle<br>";
                $this->insert_interface_oracle($h);
                $h->interface_flag="Y";
                $h->save();
              }elseif($h->status>=0 and $h->interface_flag=="Y")//jika blm dibook dan sudah terinterface*/
              if($h->status==0)
              {
                $mysoline = SoLine::where([
                                    ['header_id','=',$h->id],
                                    ['qty_confirm','!=',0]
                                    ])
                          ->get();
                //getSo di Oracle
                foreach($mysoline as $sl)
                {
                  echo "line:".$sl->line_id."<br>";
                  /*$oraSO = $connoracle->table('oe_ordeR_headers_all as oha')
                        ->join('oe_order_lines_all as ola','ola.headeR_id','=','oha.headeR_id')
                        ->where([
                          ['nvl(ola.attribute1,oha.orig_sys_document_ref)','=',$notrx],
                          [' nvl(ola.attribute2,ola.ORIG_SYS_LINE_REF)','=',$lineid],
                          ['ola.line_Category_code', '=','ORDER'],
                          ['oha.flow_status_code','=','BOOKED'],
                        ])->select('ola.header_id','ola.line_id','ola.ordered_quantity','ola.unit_selling_price'
                          ,'ola.unit_list_price','tax_value'
                          ,DB::raw("inv_convert.inv_um_convert(ola.inventory_item_id,'".$uom."','".$uomprimary."') as rate_primary'")
                          ,DB::raw("inv_convert.inv_um_convert(ola.inventory_item_id,'ola.order_quantity_uom','".$uom."') as rate'")
                          )->get();*/
                  $oraSO=$this->getSalesOrder($h->notrx,$sl);
                  //dd($oraSO);
                  if($oraSO)
                  {
                    echo "qty:".$oraSO->ordered_quantity."<br>";
                    $sl->qty_confirm =$oraSO->ordered_quantity;
                    $sl->qty_confirm_primary=$oraSO->ordered_quantity_primary;
                    $sl->list_price=$oraSO->unit_list_price/$oraSO->ordered_quantity;
                    $sl->unit_price=$oraSO->amount/$oraSO->ordered_quantity;
                    $sl->tax_amount=$oraSO->tax_value;
                    $sl->amount=$oraSO->amount;
                    $orapriceadj = $this->getadjustmentSO(null,$h->notrx,$sl);
                    foreach($orapriceadj as $adj )
                    {
                      echo "bucket1:".$adj->pricing_group_sequence."<br>";
                      echo "amount:".$adj->adjusted_amount."<br>";
                      echo "percentage:".$adj->operand."<br>";
                      if($adj->pricing_group_sequence==1)
                      {
                        $sl->disc_reg_amount = $adj->adjusted_amount;
                        $sl->disc_reg_percentage = $adj->operand;
                      }elseif($adj->pricing_group_sequence==2)
                      {
                        $sl->disc_product_amount = $adj->adjusted_amount;
                        $sl->disc_product_percentage = $adj->operand;
                      }
                    }
                    $sl->save();
                  }
                }//endforeach soline
                //
                //$oraheader = $connoracle->selectone('select min(booked_date)');
                $h->status=1;
                $h->interface_flag="Y";
                $h->status_oracle ="BOOKED";
                $h->save();
                //notification to user
                 $customer = Customer::where('id','=',$h->customer_id)->first();
                 foreach($customer->users as $u)
                 {
                   $u->notify(new BookOrderOracle($h,$customer->customer_name));
                 }


              }//endif status==0 (belum di booked)
              elseif($h->status==1)
              {
                echo "status sudah booked belum kirim untuk notrx:".$h->notrx."<br>";
                $mysoline = SoLine::where([
                                    ['header_id','=',$h->id],
                                    ['qty_confirm','!=',0]
                                    ])
                          ->get();
                //getSo di Oracle
                foreach($mysoline as $sl)
                {
                  echo "line:".$sl->line_id."<br>";
                  $ship = $this->getShippingSO($h->notrx,$sl->line_id,$lasttime,$sl->product_id,$h->id);
                  if($ship==1)
                  {
                    $jmlkirim = $sl->shippings()->sum('qty_shipping');
                    $sl->qty_shipping = $jmlkirim;
                    $sl->save();
                    //dd($jmlkirim);
                  }
                }
                $soline_notsend = SoLine::where([['header_id','=',$h->id],['qty_request_primary','!=','qty_shipping']])->get();
                if($soline_notsend){
                  $h->status=2;
                }else{
                  $h->status=4;
                }
              }
            }//foreach

        }//if$headers


      }// end if(connoracle){
        else{
          echo "can't connect to oracle";
        }

    }

    public function insert_interface_oracle(SoHeader $h)
    {
      $oraheader = $connoracle->table('oe_headers_iface_all')->insert([
        'order_source_id'=>config('constant.order_source_id')
        ,'orig_sys_document_ref'=>$h->notrx
        ,'org_id'=>$h->org_id
        ,'sold_from_org_id'=>$h->org_id
        //,'ship_from_org_id'=>$h->warehouse
        ,'ordered_date'=>$h->tgl_order
        ,'order_type_id'=>$h->order_type_id
        ,'sold_to_org_id'=>$h->oracle_customer_id
        ,'payment_term_id'=>$h->payment_term_id
        ,'operation_code'=>'INSERT'
        ,'created_by'=>-1
        ,'creation_date'=>Carbon::now()
        ,'last_updated_by'=>-1
        ,'last_update_date'=>Carbon::now()
        ,'customer_po_number'=>$h->customer_po
        ,'price_list_id'=>$h->price_list_id
        ,'ship_to_org_id'=>$h->oracle_ship_to
        ,'invoice_to_org_id'=>$h->oracle_bill_to
      ]);

      $solines = DB::table('so_lines')->where('header_id','=',$h->id)->get();
      $i=0;
      foreach($solines as $soline)
      {
        $i+=1;
          if($oraheader){
            $oraline = $connoracle->table('oe_lines_iface_all')->insert([
              'order_source_id'=>config('constant.order_source_id')
              ,'orig_sys_document_ref' => $h->notrx
              ,'orig_sys_line_ref'=>$soline->line_id
              ,'line_number'=>$i
              ,'inventory_item_id'=>$soline->inventory_item_id
              ,'ordered_quantity'=>$soline->qty_confirm
              ,'order_quantity_uom'=>$soline->uom
              /*,'ship_from_org_id'=>$soline->qty_shipping*/
              ,'org_id'=>$h->org_id
              //,'pricing_quantity'
              //,'unit_selling_price'
              ,'unit_list_price'=>$soline->unit_price
              //,'price_list_id'
              //,'payment_term_id'
              //,'schedule_ship_date'
              ,'request_date'=>$h->tgl_order
              ,'created_by'=>-1
              ,'creation_date'=>Carbon::now()
              ,'last_updated_by'=>-1
              ,'last_update_date'=>Carbon::now()
              //,'line_type_id'
              ,'calculate_price_flag'=>'Y'
            ]);
          }

      }

    }



    public function synchronize_oracle(){
      $request= DB::table('tbl_request')->where('event','=','synchronize')
                ->max('created_at');
      if($request)
      {
        $lasttime = date_create($request);
        echo"type:".gettype($lasttime);
      }else{
        $lasttime = date_create("2017-07-01");
      }
      echo "lasttime:".date_format($lasttime,"Y/m/d H:i:s")."<br>";
      $connoracle = DB::connection('oracle');
      if($connoracle){
        /*$newrequest= DB::table('tbl_request')->insertGetId([
          'created_at'=>Carbon::now(),
          'updated_at'=>Carbon::now(),
          'event'=>'synchronize',
        ]);
        echo "request id:".$newrequest."<br>";*/

        $qp_listheader = $connoracle->table('qp_list_headers')
                    ->where('last_update_date','>=',$lasttime)
                    ->select('List_header_id','name', 'description','version_no', 'currency_code'
                    , 'start_date_active', 'end_date_active', 'automatic_flag', 'list_type_code', 'terms_id', 'rounding_factor'
                    , 'discount_lines_flag', 'active_flag', 'orig_org_id', 'global_flag')->get();
        //dd($qp_listheader);
        foreach($qp_listheader as $ql){
            echo "list header id:".$ql->list_header_id."<br>";
          $mylistheader = QpListHeaders::updateOrCreate (
            ['list_header_id'=>$ql->list_header_id],
            ['name'=>$ql->name,'description'=>$ql->description,'version_no'=>$ql->version_no,'currency_code'=>$ql->currency_code
            ,'start_date_active'=>$ql->start_date_active,'end_date_active'=>$ql->end_date_active,'automatic_flag'=>$ql->automatic_flag
            ,'list_type_code'=>$ql->list_type_code,'discount_lines_flag'=>$ql->discount_lines_flag,'active_flag'=>$ql->active_flag
            ,'orig_org_id'=>$ql->orig_org_id,'global_flag'=>$ql->global_flag
            ]
          );
        }
        $transactiontype = $connoracle->table('oe_transaction_types_all as otta')
                          ->join('oe_transaction_types_tl as ottt','otta.transaction_type_id','=','ottt.transaction_type_id')
                          ->where([['otta.transaction_type_code', '=', 'ORDER'],
                                  ['otta.order_category_code', '=','ORDER']
                                ])
                          ->select('otta.transaction_type_id','ottt.name', 'ottt.description', 'otta.start_date_active', 'end_date_active', 'currency_code','price_list_id'
                            , 'warehouse_id', 'org_id' )
                    ->where('otta.last_update_date','>=',$lasttime)
                    ->get();
        foreach($transactiontype as $ott)
        {
            echo "transaction_type_id:".$ott->transaction_type_id."<br>";
          $mytransactiontype = OeTransactionType::updateOrCreate(
            ['transaction_type_id'=>$ott->transaction_type_id],
            ['name'=>$ott->name,'description=>$ott->description','start_date_active'=>$ott->start_date_active
            ,'end_date_active'=>$ott->end_date_active,'currency_code'=>$ott->currency_code,'price_list_id'=>$ott->price_list_id
            ,'warehouse_id'=>$ott->warehouse_id,'org_id'=>$ott->org_id
            ]
          );
        }
        $customers = $connoracle->table('ar_customers as ac')
                    ->whereIn('customer_class_code',['REGULER','DISTRIBUTOR PSC','DISTRIBUTOR PHARMA','RETAIL','EXPORT'])
                    ->where('ac.last_update_date','>=',$lasttime)
                    ->select('customer_name' , 'customer_number','customer_id', 'status', 'attribute2 as CUSTOMER_CATEGORY_CODE'
                          , DB::raw('nvl(ac.CUSTOMER_CLASS_CODE,attribute3) as customer_class_code')
                          , 'primary_salesrep_id'
                          , 'tax_reference'
                          , 'tax_code'
                          , 'price_list_id'
                          , 'order_type_id'
                          , 'customer_name_phonetic' )
                    ->get();
        foreach($customers as $c)
        {
          echo "customer:".$c->customer_id."<br>";
          $mycustomer = Customer::updateOrCreate(
            ['oracle_customer_id'=>$c->customer_id],
            ['customer_name'=>$c->customer_name,'customer_number'=>$c->customer_number,'status'=>$c->status
            ,'customer_category_code'=>$c->customer_category_code,'customer_class_code'=>$c->customer_class_code
            ,'primary_salesrep_id'=>$c->primary_salesrep_id,'tax_reference'=>$c->tax_reference,'tax_code'=>$c->tax_code
            ,'price_list_id'=>$c->price_list_id,'order_type_id'=>$c->order_type_id,'customer_name_phonetic'=>$c->customer_name_phonetic
            ]
          );
          if($c->status=='I'){
            $updateuser = User::where('customer_id','=',$c->customer_id)
            ->update(['validate_flag'=>0]);
          }elseif($c->status=='A'){
            $updateuser = User::where('customer_id','=',$c->customer_id)
                          ->whereNotNull('password')->first();
            if ($updateuser)
            {
              if($updateuser->validate_flag==0)
              {
                $updateuser->validate_flag=1;
                $updateuser->save();
              }
            }
          }
        }
        $customersite = $this->getCustomerSites($lasttime);
        //$customrsite =
        //DB::table('tbl_request')->where('id','=',$newrequest)->update(['tgl_selesai'=>Carbon::now()]);
      }
    }

    public function getCustomerSites($lasttime)
    {
      $connoracle = DB::connection('oracle');
      if($connoracle){
        $sites = $connoracle->table('HZ_CUST_ACCT_SITES_ALL hcas')
                    ->join('hz_party_sites hps','hcas.PARTY_SITE_ID', '=', 'hps.party_site_id')
                    ->join('hz_locations hl','hps.location_id','=','hl.location_id')
                    ->join('HZ_CUST_SITE_USES_ALL hcsua', 'hcas.CUST_ACCT_SITE_ID','=','hcsua.CUST_ACCT_SITE_ID')
                    ->whereIn('site_use_code', ['SHIP_TO','BILL_TO'])
                    //->where('ac.last_update_date','>=',$lasttime)
                    ->select('cust_account_id', 'hcas.cust_acct_site_id as cust_acct_site_id', 'hcas.party_site_id', 'bill_to_flag', 'ship_to_flag', 'hcas.orig_system_reference', 'hcas.status as status', 'hcas.org_id as org_id'
                        , 'hcsua.SITE_USE_id as site_use_id'
                        , 'hcsua.site_use_code as site_use_code', 'hcsua.BILL_TO_SITE_USE_ID as bill_to_site_use_id'
                        , 'hcsua.payment_term_id as payment_term_id'
                        , 'hcsua.price_list_id as price_list_id'
                        , 'hcsua.order_type_id as order_type_id'
                        , 'hcsua.tax_code as tax_code'
                        ,  'hl.ADDRESS1', 'hl.address2 as kecamatan','hl.address3 as kabupaten', 'hl.address4 as wilayah'
                        ,  'hl.city', 'hl.province', 'hl.country'
                        , 'hcsua.WAREHOUSE_ID','hl.POSTAL_CODE')
                    ->get();
        if($sites)
        {
          foreach ($sites as $site)
          {
              echo "Sites:".$site->cust_account_id."<br>";
              $customer = Customer::where('oracle_customer_id','=',$site->cust_account_id)->first();
              if($customer)
              {
                $mycustomersite = CustomerSite::updateOrCreate(
                  ['oracle_customer_id'=>$site->cust_account_id,'cust_acct_site_id'=>$site->cust_acct_site_id,'site_use_id'=>$site->site_use_id],
                  ['site_use_code'=>$site->site_use_code,'status'=>$site->status,'bill_to_site_use_id'=>$site->bill_to_site_use_id
                  ,'payment_term_id'=>$site->payment_term_id,'price_list_id'=>$site->price_list_id
                  ,'order_type_id'=>$site->order_type_id,'tax_code'=>$site->tax_code
                  ,'address1'=>$site->address1,'state'=>$site->kecamatan,'district'=>$site->kabupaten
                  ,'city'=>$site->city,'province'=>$site->province,'postal_code'=>$site->postal_code,'Country'=>$site->country
                  ,'org_id'=>$site->org_id,'warehouse'=>$site->warehouse_id,'customer_id'=>$customer->id
                  ]
                );
                echo "Sites berhasil ditambah/update<br>";
              }

          }
          return true;
        }
      }else{
        return false;
      }
    }

    public function getShippingSO($notrx,$lineid,$lasttime, $productid,$headerid)
    {
      $connoracle = DB::connection('oracle');
      if($connoracle){
        //$lasttime = date_create("2017-07-01");
        $oraship = $connoracle->table('wsh_delivery_Details as wdd')
                  ->join( 'wsh_Delivery_assignments as wda','wdd.delivery_detail_id','=','wda.delivery_detail_id')
                  ->join('wsh_new_deliveries as wnd','wda.delivery_id','=','wnd.delivery_id')
                  ->join('oe_order_lines_all as ola',function($join){
                      $join->on('wdd.source_header_id','=','ola.header_id');
                      $join->on('wdd.source_line_id','=','ola.line_id');
                  })
                  ->where([//['wdd.source_header_id','=',$headerid]
                          //,['wdd.source_line_id','=',$lineid]]
                          //,
                          ['wdd.source_code','=','OE']
                          ,[DB::raw('nvl(ola.attribute1,ola.orig_sys_document_ref)'),'=',$notrx]
                          ,[DB::raw('nvl(ola.attribute2,ola.orig_sys_line_ref)'),'=',strval($lineid)]
                          ,['wdd.last_update_date','>',$lasttime]
                        ])
                  ->where(function ($query) {
                              $query->whereNotNull('ola.attribute1')
                                    ->orWhere('ola.order_source_id','=',config('constant.order_source_id'));
                          })
                  ->select('wnd.name as delivery_no',  'wdd.source_header_id', 'wdd.source_line_id',  'wdd.delivery_detail_id','wdd.inventory_item_id'
                      , 'wdd.src_requested_quantity_uom', 'wdd.src_Requested_quantity'
                      , 'wdd.requested_quantity_uom as primary_uom', 'wdd.requested_quantity'
                      , 'wdd.picked_quantity'
                      , 'wdd.shipped_quantity'
                      , DB::raw('inv_convert.inv_um_convert(wdd.inventory_item_id,wdd.requested_quantity_uom,wdd.src_requested_quantity_uom) as convert_qty')
                      , 'wdd.lot_number'
                      , 'wdd.transaction_id'
                      , 'wdd.split_from_delivery_detail_id'
                      , DB::raw('(select mmt.transaction_date
                            from mtl_material_transactions mmt
                            where wdd.transaction_id =mmt.transaction_id
                            and wdd.inventory_item_id = mmt.inventory_item_id
                            and mmt.TRANSACTION_TYPE_ID=52) as transaction_date')
                      ,'wdd.inventory_item_id'
                    )
                  ->get();
            var_dump($oraship);
        foreach($oraship as $ship)
        {
          //$productid = Product::where('inventory_item_id','=',$ship->inventory_item_id)->select('id')->first();
          $my_so_ship =SoShipping::updateOrCreate(
            ['delivery_detail_id'=>$ship->delivery_detail_id],
            ['deliveryno'=>$ship->delivery_no,'source_header_id'=>$ship->source_header_id
            ,'source_line_id'=>$ship->source_line_id,'product_id'=>$productid
            ,'uom'=>$ship->src_requested_quantity_uom,'qty_request'=>$ship->src_requested_quantity
            ,'uom_primary'=>$ship->primary_uom,'qty_request_primary'=>$ship->requested_quantity
            ,'qty_shipping'=>$ship->picked_quantity
            ,'batch_no'=>$ship->lot_number
            ,'split_source_id'=>$ship->split_from_delivery_detail_id
            ,'tgl_kirim'=>$ship->transaction_date
            ,'conversion_qty'=>$ship->convert_qty
            ,'header_id' =>$headerid
            ,'line_id'=>$lineid
            ]
          );
        }
        return 1;
      }else{
        return 0;
      }
    }

    public function getSalesOrder($notrx, SoLine $line)
    {
      $connoracle = DB::connection('oracle');
      if($connoracle){
        $oraSO=$connoracle->selectone("select sum(ordered_quantity*inv_convert.inv_um_convert(ola.inventory_item_id,ola.order_quantity_uom, '".$line->uom."')) as ordered_quantity
                  , sum(ordered_quantity*inv_convert.inv_um_convert(ola.inventory_item_id,'".$line->uom."', '".$line->primary_uom."')) as ordered_quantity_primary
                  , sum(ordered_quantity*unit_selling_price) as amount
                  , sum(ordered_quantity*unit_list_price) as unit_list_price
                  , sum(tax_value) tax_value
                from oe_order_headers_all oha
                    , oe_order_lines_all ola
                where oha.headeR_id=ola.header_id
                    and nvl(ola.attribute1,oha.orig_sys_document_ref) = '".$notrx."'
                    and nvl(ola.attribute2,ola.orig_sys_line_ref) = '".$line->line_id."'
                    and ola.inventory_item_id = '".$line->inventory_item_id."'
                    and ola.line_category_code ='ORDER'
                    and nvl(ola.CANCELLED_FLAG,'N')='N'
                    and oha.flow_status_code ='BOOKED'");
        if($oraSO)
        {

          return $oraSO;
        }else{
          return null;
        }
      }
    }

    public function getadjustmentSO($bucket, $notrx, SoLine $line)
    {
      $connoracle = DB::connection('oracle');
      if($connoracle){

        if(is_null($bucket))
        {
          return $connoracle->select("select pricing_group_sequence,sum(adjusted_amount) as adjusted_amount, sum(operand) as operand
                                from oe_price_adjustments opa
                                    , oe_order_lines_all ola
                                    , oe_order_headers_all oha
                                where applied_flag='Y'
                                    and opa.line_id =ola.line_id
                                    and opa.header_id=oha.header_id
                                    and ola.header_id=oha.header_id
                                    and nvl(ola.attribute1,oha.orig_sys_document_ref) = '".$notrx."'
                                    and nvl(ola.attribute2,ola.ORIG_SYS_LINE_REF) = '$line->line_id'
                                    and ola.inventory_item_id = '$line->inventory_item_id'
                                    and list_line_type_code ='DIS'
                                    and modifier_level_code='LINE'
                                    and oha.FLOW_STATUS_CODE='BOOKED'
                                    and ola.line_category_code ='ORDER'
                                    and nvl(ola.CANCELLED_FLAG,'N')='N'
                                group by pricing_group_sequence
                                order by pricing_group_sequence");
        }else{
          return $connoracle->selectone("select pricing_group_sequence,sum(adjusted_amount) as adjusted_amount, sum(operand) as operand
                                from oe_price_adjustments opa
                                    , oe_order_lines_all ola
                                    , oe_order_headers_all oha
                                where applied_flag='Y'
                                    and opa.line_id =ola.line_id
                                    and opa.header_id=oha.header_id
                                    and ola.header_id=oha.header_id
                                    and nvl(ola.attribute1,oha.orig_sys_document_ref) = '".$notrx."'
                                    and nvl(ola.attribute2,ola.ORIG_SYS_LINE_REF) = '$line->line_id'
                                    and ola.inventory_item_id = '$line->inventory_item_id'
                                    and list_line_type_code ='DIS'
                                    and modifier_level_code='LINE'
                                    and oha.FLOW_STATUS_CODE='BOOKED'
                                    and ola.line_category_code ='ORDER'
                                    and nvl(ola.CANCELLED_FLAG,'N')='N'
                                    and pricing_group_sequence=".$bucket."
                                group by pricing_group_sequence
                                order by pricing_group_sequence ");
        }

      }else{
        return null;
      }
    }

    public function getModifierSummary()
    {
      $connoracle = DB::connection('oracle');
      if($connoracle){
          echo "Connect to oracle<br>";
          $modifiers = $connoracle->table('qp_modifier_summary_v')
                      ->select(  'list_line_id','list_header_id','list_line_type_code','automatic_flag','modifier_level_code'
                        ,'list_price','list_price_uom_code','primary_uom_flag','inventory_item_id','organization_id'
                        ,'operand','arithmetic_operator','override_flag','print_on_invoice_flag','start_date_active'
                        ,'end_date_active','incompatibility_grp_code','list_line_no','product_precedence','pricing_phase_id'
                        ,'pricing_attribute_id','product_attribute_context','product_attr','product_attr_val'
                        ,'product_uom_code','comparison_operator_code','pricing_attribute_context','pricing_attr'
                        ,'pricing_attr_value_from','pricing_attr_value_to','pricing_attribute_datatype'
                        ,'product_attribute_datatype')
                      ->get();
          if($modifiers)
          {
            foreach ($modifiers as $m){
              $modifier = qp_modifier_summary::updateOrCreate(['list_line_id'=>$m->list_line_id, 'list_header_id'=>$m->list_header_id],
                ['list_line_type_code'=>$m->list_line_type_code,'automatic_flag'=>$m->automatic_flag,'modifier_level_code'=>$m->modifier_level_code
                ,'list_price'=>$m->list_price,'list_price_uom_code'=>$m->list_price_uom_code,'primary_uom_flag'=>$m->primary_uom_flag
                ,'inventory_item_id'=>$m->inventory_item_id,'organization_id'=>$m->organization_id,'operand'=>$m->operand
                ,'arithmetic_operator'=>$m->arithmetic_operator,'override_flag'=>$m->override_flag
                ,'print_on_invoice_flag'=>$m->print_on_invoice_flag,'start_date_active'=>$m->start_date_active
                ,'end_date_active'=>$m->end_date_active,'incompatibility_grp_code'=>$m->incompatibility_grp_code
                ,'list_line_no'=>$m->list_line_no,'product_precedence'=>$m->product_precedence
                ,'pricing_phase_id'=>$m->pricing_phase_id,'pricing_attribute_id'=>$m->pricing_attribute_id
                ,'product_attribute_context'=>$m->product_attribute_context,'product_attr'=>$m->product_attr
                ,'product_attr_val'=>$m->product_attr_val,'product_uom_code'=>$m->product_uom_code
                ,'comparison_operator_code'=>$m->comparison_operator_code,'pricing_attribute_context'=>$m->pricing_attribute_context
                ,'pricing_attr'=>$m->pricing_attr,'pricing_attr_value_from'=>$m->pricing_attr_value_from
                ,'pricing_attr_value_to'=>$m->pricing_attr_value_to,'pricing_attribute_datatype'=>$m->pricing_attribute_datatype
                ,'product_attribute_datatype'=>$m->product_attribute_datatype
                ]
              );
              echo "Modifier: ".$m->list_line_id." berhasil ditambah/update<br>";
            }

          }
      }
    }

    public function getQualifiers()
    {
      $connoracle = DB::connection('oracle');
      if($connoracle){
        $qualifiers = $connoracle->table('qp_qualifiers_v')
                      ->select('qualifier_id','excluder_flag','comparision_operator_code','qualifier_context','qualifier_attribute'
                      ,'qualifier_grouping_no','qualifier_attr_value','list_header_id','list_line_id','start_date_active'
                      ,'end_date_active','qualifier_datatype','qualifier_precedence')
                      ->get();
        if($qualifiers)
        {
          echo "Connect to oracle<br>";
          foreach ($qualifiers as $q){
            $qualifier = qp_qualifiers::updateOrCreate(['qualifier_id'=>$q->qualifier_id],
              ['excluder_flag'=>$q->excluder_flag,'comparision_operator_code'=>$q->comparision_operator_code
              ,'qualifier_context'=>$q->qualifier_context,'qualifier_attribute'=>$q->qualifier_attribute
              ,'qualifier_grouping_no'=>$q->qualifier_grouping_no,'qualifier_attr_value'=>$q->qualifier_attr_value
              ,'list_header_id'=>$q->list_header_id,'list_line_id'=>$q->list_line_id
              ,'start_date_active'=>$q->start_date_active,'end_date_active'=>$q->end_date_active
              ,'qualifier_datatype'=>$q->qualifier_datatype,'qualifier_precedence'=>$q->qualifier_precedence
              ]
            );
            echo "qualifier: ".$q->qualifier_id." berhasil ditambah/update<br>";
          }

        }
      }
    }

}
