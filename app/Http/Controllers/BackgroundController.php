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
use App\Events\PusherBroadcaster;
use App\Notifications\PushNotif;
use Excel;
use App\QpListHeaders;
use App\QpListLine;
use App\OeTransactionType;
use App\User;
use App\SoShipping;
use App\CustomerSite;
use App\CustomerContact;
use App\qp_modifier_summary;
use App\qp_qualifiers;
use App\QpPricingDiskon;
use App\Product;
use App\UomConversion;

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
                  ['status','<',3],
                //  ['notrx','=','PO-20171124-XI-00024']
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
                //  dd($oraSO);
                  if(!is_null($oraSO))
                  {
                    echo "qty:".$oraSO->ordered_quantity."<br>";
                    $sl->qty_confirm =$oraSO->ordered_quantity_primary;
                    //$sl->qty_confirm_primary=$oraSO->ordered_quantity_primary;
                    $sl->list_price=$oraSO->unit_list_price/$oraSO->ordered_quantity;
                    $sl->unit_price=$oraSO->amount/$oraSO->ordered_quantity;
                    $sl->tax_amount=$oraSO->tax_value;
                    $sl->amount=$oraSO->amount;
                    $sl->disc_reg_amount = null;
                    $sl->disc_reg_percentage = null;
                    $sl->disc_product_amount = null;
                    $sl->disc_product_percentage=null;
                    $orapriceadj = $this->getadjustmentSO(null,$h->notrx,$sl);
                    foreach($orapriceadj as $adj )
                    {
                      echo "bucket:".$adj->pricing_group_sequence."<br>";
                      echo "amount:".$adj->adjusted_amount."<br>";
                      echo "percentage:".$adj->operand."<br>";
                      if($adj->pricing_group_sequence==1)
                      {
                        $sl->disc_reg_amount = $adj->adjusted_amount*-1;
                        $sl->disc_reg_percentage = $adj->operand;
                      }elseif($adj->pricing_group_sequence==2)
                      {
                        $sl->disc_product_amount = $adj->adjusted_amount*-1;
                        $sl->disc_product_percentage = $adj->operand;
                      }else{
                        $sl->disc_product_amount += $adj->adjusted_amount*-1;
                      }
                    }
                    $sl->save();
                  }
                }//endforeach soline
                //
                //$oraheader = $connoracle->selectone('select min(booked_date)');
                $newline =$this->getadjustmentHeaderSO($h->notrx,$h->id);
                $oraheader = $connoracle->selectone("select min(booked_date) as booked_date from oe_order_headers_all oha
                              where  oha.flow_status_code ='BOOKED' and exists (select 1 from oe_ordeR_lines_all ola
                                          where ola.headeR_id =oha.headeR_id
                                            and nvl(ola.attribute1,oha.orig_sys_document_ref)='".$h->notrx."')");
                //dd($oraheader);
                if(!is_null($oraheader->booked_date))
                {
                  $h->status=1;
                  $h->interface_flag="Y";
                  $h->status_oracle ="BOOKED";
                  $h->save();
                  //notification to user
                   $customer = Customer::where('id','=',$h->customer_id)->first();
                   $content ='PO Anda nomor '.$h->notrx.' telah dikonfirmasi oleh '.$h->distributor->customer_name.'. Silahkan check PO anda kembali.<br>';
                   $content .= 'Terimakasih telah menggunakan aplikasi '.config('app.name', 'g-Order');
                   $data = [
           					'title' => 'Konfirmasi PO',
           					'message' => 'Konfirmasi PO '.$h->customer_po.' oleh distributor.',
           					'id' => $h->id,
           					'href' => route('order.notifnewpo'),
           					'mail' => [
           						'greeting'=>'Konfirmasi PO '.$h->customer_po.' oleh distributor.',
                      'content' =>$content,
           					]
           				];
                   foreach($customer->users as $u)
                   {
                    $data['email']= $u->email;
                     //$u->notify(new BookOrderOracle($h,$customer->customer_name));
                    // event(new PusherBroadcaster($data, $u->email));
                     $u->notify(new PushNotif($data));
                   }
                }


              }//endif status==0 (belum di booked)
              elseif($h->status>0 and $h->status<3 )
              {
                echo "status sudah booked belum kirim untuk notrx:".$h->notrx."<br>";
                $mysoline = SoLine::where([
                                    ['header_id','=',$h->id],
                                    ['qty_confirm','!=',0]
                                    ])
                          ->get();
                $berubah=false;
                //getSo di Oracle
                foreach($mysoline as $sl)
                {
                  $jumshippingbefore = $sl->qty_shipping;
                  echo "line:".$sl->line_id."<br>";
                  $ship = $this->getShippingSO($h->notrx,$sl->line_id,$lasttime,$sl->product_id,$h->id);
                  if($ship==1)
                  {
                    $jmlkirim = $sl->shippings()->sum('qty_shipping');
                    if ($sl->qty_shipping != $jmlkirim)
                    {
                      $sl->qty_shipping = $jmlkirim;
                      $sl->save();
                      $berubah=true;
                    }

                    //dd($jmlkirim);
                  }
                }

                //notif to customer jika berubah
                if($berubah)
                {
                  $soline_notsend = DB::table('so_lines_sum_v')
                                    ->where('header_id','=',$h->id)
                                    //->where('qty_confirm_primary','<>','qty_shipping_primary')
                                    ;
                  $soline_notsend = $soline_notsend->first();
                  //dd($soline_notsend);
                  //echo "count: ".$soline_notsend->qty_confirm_primary()."<br>";
                  //if($soline_notsend->count()>0){
                  if($soline_notsend->qty_confirm_primary<>$soline_notsend->qty_shipping_primary){
                    $h->status=2;
                  }else{
                    $h->status=3;
                  }
                  $h->save();
                  $content = 'PO Anda nomor '.$h->customer_po.' telah dikirimkan oleh '.$h->distributor->customer_name.'. ';
                  $content .='Silahkan check PO anda kembali.<br>' ;
                  $content .='Terimakasih telah menggunakan aplikasi '.config('app.name', 'g-Order');
                  $data=[
                    'title' => 'Pengiriman PO',
                    'message' => 'PO #'.$h->customer_po.' telah dikirim',
                    'id' => $h->id,
                    'href' => route('order.notifnewpo'),
                    'mail' => [
                      'greeting'=>'Pengriman Barang PO #'.$h->customer_po.'.',
                      'content' =>$content,
                    ]
                  ];
                  foreach ($h->outlet->users as $u)
                  {
                    $data['email']=$u->email;
                    //event(new PusherBroadcaster($data, $u->email));
                    $u->notify(new PushNotif($data));
                  }
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
      DB::beginTransaction();
      try{
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
          $newrequest= DB::table('tbl_request')->insertGetId([
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now(),
            'event'=>'synchronize',
          ]);
          echo "request id:".$newrequest."<br>";
          $this->getMasterItem($lasttime);
          $this->getConversionItem($lasttime);
          //dd();
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
          $qp_listlines =$connoracle->table('qp_list_lines_v as qll')
                          ->join('qp_list_headers_all qlh','qll.list_headeR_id','=','qlh.list_header_id')
                          ->where('qll.last_update_date','>=',$lasttime)
                          ->where('qll.list_line_type_code','=','PLL')
                          ->where('qll.product_attribute','=','PRICING_ATTRIBUTE1')
                          ->select('qll.list_line_id', 'qll.list_header_id', 'product_attribute_context','product_attr_value'
                                  ,'product_uom_code','qll.start_date_active','qll.end_date_active','revision_date','operand'
                                  ,'qlh.currency_code','qlh.active_flag')
                          ->get();
          if($qp_listlines)
          {
            foreach($qp_listlines as $ql)
            {
              $myqplines = QpListLine::updateOrCreate(
                ['list_line_id'=>$ql->list_line_id],
                ['list_header_id'=>$ql->list_header_id
                ,'product_attribute_context'=>$ql->product_attribute_context
                , 'product_attr_value'=>$ql->product_attr_value
                , 'product_uom_code'=>$ql->product_uom_code
                ,'start_date_active'=>$ql->start_date_active
                ,'end_date_Active'=>$ql->end_date_active
                ,'revision_date'=>$ql->revision_date
                ,'operand'=>$ql->operand
                ,'currency_code'=>$ql->currency_code
                ,'enabled_flag'=>$ql->active_flag
              ]);
            }
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
          $this->getCustomer($lasttime);

          //$customrsite =
          DB::table('tbl_request')->where('id','=',$newrequest)->update(['tgl_selesai'=>Carbon::now()]);
        }
        DB::commit();
      }catch (\Exception $e) {
        DB::rollback();
        throw $e;
      }
    }

    public function getCustomer($lasttime = null)
    {
      DB::beginTransaction();
      try{
        if($lasttime==null)
        {
          $request= DB::table('tbl_request')->where('event','=','customer')
                    ->max('created_at');
          if($request)
          {
            $lasttime = date_create($request);
            echo"type:".gettype($lasttime);
          }else{
            $lasttime = date_create("2017-07-01");
          }
          echo "lasttime:".date_format($lasttime,"Y/m/d H:i:s")."<br>";
        }
        $connoracle = DB::connection('oracle');
        $newrequestcust= DB::table('tbl_request')->insertGetId([
          'created_at'=>Carbon::now(),
          'updated_at'=>Carbon::now(),
          'event'=>'customer',
        ]);
        echo "request id:".$newrequestcust."<br>";
        $customers = $connoracle->table('ar_customers as ac')
                    ->leftjoin('HZ_CUSTOMER_PROFILES as hcp','ac.customer_id', 'hcp.cust_Account_id')
                    ->leftjoin('ra_terms as rt','hcp.STANDARD_TERMS','rt.term_id')
                    ->whereIn('customer_class_code',['REGULER','DISTRIBUTOR PSC','DISTRIBUTOR PHARMA','OUTLET','EXPORT','TOLL IN'])
                    ->where('ac.last_update_date','>=',$lasttime)
                    ->select('customer_name' , 'customer_number','customer_id', 'ac.status', 'ac.attribute3 as CUSTOMER_CATEGORY_CODE'
                          , DB::raw('ac.CUSTOMER_CLASS_CODE as customer_class_code')
                          , 'primary_salesrep_id'
                          , 'tax_reference'
                          , 'tax_code'
                          , 'price_list_id'
                          , 'order_type_id'
                          , 'customer_name_phonetic'
                          , 'rt.name as payment_term' )
                    ->orderBy('customer_number','asc')
                    ->get();
        if(count($customers)){
          echo "<h2>Data Customer Oracle</h2>";
          echo "<table><tr><th>Customer Number</th><th>Customer Name</th></tr>";
          foreach($customers as $c)
          {
            echo"<tr>";
            echo "<td>".$c->customer_number."</td>";
            echo "<td>".$c->customer_name."</td>";
            echo "</tr>";
            $psc_flag=null;
            $pharma_flag=null;
            $export_flag=null;
            $tollin_flag=null;
            if($c->customer_class_code == 'DISTRIBUTOR PSC' or $c->customer_class_code=='OUTLET')
            {
              $psc_flag="1";
            }elseif($c->customer_class_code == 'DISTRIBUTOR PHARMA'){
              $pharma_flag="1";
            }elseif($c->customer_class_code == 'TOLL IN'){
              $tollin_flag="1";
            }elseif($c->customer_class_code == 'EXPORT'){
              $export_flag="1";
            }
            $mycustomer = Customer::updateOrCreate(
              ['oracle_customer_id'=>$c->customer_id],
              ['customer_name'=>$c->customer_name,'customer_number'=>$c->customer_number,'status'=>$c->status
              ,'customer_category_code'=>$c->customer_category_code,'customer_class_code'=>$c->customer_class_code
              ,'primary_salesrep_id'=>$c->primary_salesrep_id,'tax_reference'=>$c->tax_reference,'tax_code'=>$c->tax_code
              ,'price_list_id'=>$c->price_list_id,'order_type_id'=>$c->order_type_id,'customer_name_phonetic'=>$c->customer_name_phonetic
              ,'payment_term_name'=>$c->payment_term,'psc_flag'=>$psc_flag,'pharma_flag'=>$pharma_flag,'export_flag'=>$export_flag,'tollin_flag'=>$tollin_flag
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
          echo"</table>";
        }
        $customersite = $this->getCustomerSites($lasttime);
        $customercontacts = $this->getCustomerContacts($lasttime);
        DB::table('tbl_request')->where('id','=',$newrequestcust)->update(['tgl_selesai'=>Carbon::now()]);
        DB::commit();
      }catch (\Exception $e) {
        DB::rollback();
        throw $e;
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
                    ->join('ar_customers ac','ac.customer_id', '=', 'hcas.cust_Account_id' )
                    ->whereIn('site_use_code', ['SHIP_TO','BILL_TO'])
                    /*->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                              ->from('ar_customers as ac')
                              ->whereRaw("ac.customer_id = hcas.cust_Account_id")
                              ->wherein('customer_class_code',['REGULER','DISTRIBUTOR PSC','DISTRIBUTOR PHARMA','OUTLET','EXPORT','TOLL IN']);
                            })*/
                    ->wherein('ac.customer_class_code',['REGULER','DISTRIBUTOR PSC','DISTRIBUTOR PHARMA','OUTLET','EXPORT','TOLL IN'])
                    ->where(function ($query) use($lasttime) {
                            $query->where('hcsua.last_update_date','>=',$lasttime)
                                  ->orwhere('hcas.last_update_date', '>=', $lasttime)
                                  ->orwhere('hps.last_update_date', '>=', $lasttime)
                                  ->orwhere('hl.last_update_date', '>=', $lasttime);
                        })
                    ->select('cust_account_id', 'hcas.cust_acct_site_id as cust_acct_site_id', 'hcas.party_site_id', 'bill_to_flag', 'ship_to_flag', 'hcas.orig_system_reference', 'hcas.status as status', 'hcas.org_id as org_id'
                        , 'hcsua.SITE_USE_id as site_use_id'
                        , 'hcsua.site_use_code as site_use_code', 'hcsua.BILL_TO_SITE_USE_ID as bill_to_site_use_id'
                        , 'hcsua.payment_term_id as payment_term_id'
                        , 'hcsua.price_list_id as price_list_id'
                        , 'hcsua.order_type_id as order_type_id'
                        , 'hcsua.tax_code as tax_code'
                        ,  'hl.ADDRESS1', 'hl.address2 as kecamatan','hl.address3 as kelurahan', 'hl.address4 as wilayah'
                        ,  'hl.city', 'hl.province', 'hl.country'
                        , 'hcsua.WAREHOUSE_ID','hl.POSTAL_CODE','hcsua.primary_flag','ac.customer_number','ac.customer_name')
                    ->get();
        if(count($sites))
        {
          echo "<h2>Data Customer Site Oracle</h2>";
          echo "<table><tr><th>Customer Number</th><th>Customer Name</th>";
          echo "<th>Site Use Code</th><th>Address</th><th>Province</th><th>City</th><th>Status</th></tr>";
          foreach ($sites as $site)
          {
              //echo "Sites:".$site->cust_account_id."<br>";
              echo "<tr>";
              echo "<td>".$site->customer_number."</td>";
              echo "<td>".$site->customer_name."</td>";
              echo "<td>".$site->site_use_code."</td>";
              echo "<td>".$site->address1."</td>";
              echo "<td>".$site->province."</td>";
              echo "<td>".$site->city."</td>";
              $province_id=null;
              $city_id=null;
              $desa_id=null;
              $kecamatan_id=null;
              $customer = Customer::where('oracle_customer_id','=',$site->cust_account_id)->first();
              $city =DB::table('regencies')->where('name','=',$site->city)->first() ;
              $provinces = DB::table('provinces')->where('name','=',$site->province)->first() ;
              if($city) $city_id = $city->id;
              if($provinces) $province_id = $provinces->id;
              $kecamatan = DB::table('districts')->whereRaw("upper(name)=upper('".addslashes($site->kecamatan)."') and ifnull('".$city_id."',regency_id)=regency_id")->first();
              if($kecamatan) $kecamatan_id = $kecamatan->id;
              $villages = DB::table('villages')->whereRaw("upper(name)=upper('".addslashes($site->kelurahan)."') and district_id like '".$city_id."%'")->first();
              if($villages)
              {
                $desa_id=$villages->id;
                if(is_null($kecamatan_id))
                {
                  $kecamatan_id = $villages->district_id;
                }
              }

              if($customer)
              {
                $mycustomersite = CustomerSite::updateOrCreate(
                  ['oracle_customer_id'=>$site->cust_account_id,'cust_acct_site_id'=>$site->cust_acct_site_id,'site_use_id'=>$site->site_use_id],
                  ['site_use_code'=>$site->site_use_code,'primary_flag'=>$site->primary_flag,'status'=>$site->status,'bill_to_site_use_id'=>$site->bill_to_site_use_id
                  ,'payment_term_id'=>$site->payment_term_id,'price_list_id'=>$site->price_list_id
                  ,'order_type_id'=>$site->order_type_id,'tax_code'=>$site->tax_code
                  ,'address1'=>$site->address1,'state'=>$site->kelurahan,'district'=>$site->kecamatan
                  ,'city'=>$site->city,'province'=>$site->province,'postal_code'=>$site->postal_code,'Country'=>$site->country
                  ,'org_id'=>$site->org_id,'warehouse'=>$site->warehouse_id,'customer_id'=>$customer->id
                  ,'city_id'=>$city_id,'province_id'=>$province_id,'district_id'=>$kecamatan_id,'state_id'=>$desa_id,'area'=>$site->wilayah
                  ]
                );
                echo "<td>Sites berhasil ditambah/update</td>";
                echo "</tr>";
              }

          }
          echo"</table>";
          return true;
        }
      }else{
        return false;
      }
    }

    public function getCustomerContacts($lasttime)
    {
      $connoracle = DB::connection('oracle');
      if($connoracle){
        $contacts = $connoracle->table('hz_cust_accounts hca')
                    ->join('hz_parties obj','hca.party_id', '=', 'obj.party_id')
                    ->join('hz_relationships rel','hca.party_id','=','rel.object_id')
                    ->join('hz_contact_points hcp', 'rel.party_id','=','hcp.owner_table_id')
                    ->join('hz_parties sub','rel.subject_id', '=', 'sub.party_id' )
                    ->where('rel.relationship_type','=','CONTACT')
                    ->where('rel.directional_flag','=','F')
                    ->where('hcp.owner_table_name','=','HZ_PARTIES')
                    /*->where(function ($query) use($lasttime) {
                            $query->where('hcp.last_update_date','>=',$lasttime)
                                  ->orwhere('rel.last_update_date', '>=', $lasttime);
                        })*/
                    ->select('sub.party_id','hca.cust_account_id'
                             , 'account_number as customer_number', 'obj.party_name as customer_name'
                             , 'sub.party_name as contact_name' , 'hcp.contact_point_type'
                             ,  DB::raw("DECODE(hcp.contact_point_type, 'EMAIL', hcp.email_address
                                    , 'PHONE', hcp.phone_country_code||hcp.phone_area_code || '-' || hcp.phone_number
                                    , 'WEB'  , hcp.url
                                    , 'Unknow contact Point Type ' || hcp.contact_point_type
                                      ) as Contact")
                             , 'hCP.phone_line_type', 'hcp.CONTACT_POINT_PURPOSE','hcp.contact_point_id')
                    ->get();
        if(count($contacts))
        {
          echo "<h2>Data Customer Contact Oracle</h2>";
          echo "<table><tr><th>Customer Number</th><th>Customer Name</th>";
          echo "<th>Contact Name</th><th>Contact Point Type</th><th>Contact</th><th>Line type</th><th>Status</th></tr>";
          foreach ($contacts as $contact)
          {
              //echo "Sites:".$site->cust_account_id."<br>";
              echo "<tr>";
              echo "<td>".$contact->customer_number."</td>";
              echo "<td>".$contact->customer_name."</td>";
              echo "<td>".$contact->contact_name."</td>";
              echo "<td>".$contact->contact_point_type."</td>";
              echo "<td>".$contact->contact."</td>";
              echo "<td>".$contact->phone_line_type."</td>";

              $customer = Customer::where('oracle_customer_id','=',$contact->cust_account_id)->first();

              if($customer)
              {
                $mycustomersite = CustomerContact::updateOrCreate(
                  ['oracle_customer_id'=>$contact->cust_account_id,'customer_id'=>$customer->id,'contact'=>$contact->contact,'contact_point_id'=>$contact->contact_point_id],
                  ['account_number'=>$contact->customer_number,'contact_name'=>$contact->contact_name,'contact_type'=>$contact->contact_point_type
                  ]
                );
                echo "<td>Contact berhasil ditambah/update</td>";
                echo "</tr>";
              }

          }
          echo"</table>";
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
                      ,'wnd.waybill'
                    )
                  ->get();
          //  var_dump($oraship);
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
            ,'batchno'=>$ship->lot_number
            ,'split_source_id'=>$ship->split_from_delivery_detail_id
            ,'tgl_kirim'=>$ship->transaction_date
            ,'conversion_qty'=>$ship->convert_qty
            ,'header_id' =>$headerid
            ,'line_id'=>$lineid
            ,'waybill'=>$ship->waybill
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
      echo "notrx:".$notrx.", uom:".$line->uom."<br>";
      $connoracle = DB::connection('oracle');
      if($connoracle){
        $oraSO=$connoracle->selectone("select sum(ordered_quantity*inv_convert.inv_um_convert(ola.inventory_item_id,ola.order_quantity_uom, '".$line->uom."')) as ordered_quantity
                  , sum(ordered_quantity*inv_convert.inv_um_convert(ola.inventory_item_id,ola.order_quantity_uom, '".$line->uom_primary."')) as ordered_quantity_primary
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
                    and oha.flow_status_code ='BOOKED'
                having sum(ordered_quantity*inv_convert.inv_um_convert(ola.inventory_item_id,ola.order_quantity_uom, '".$line->uom."')) <> 0");

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
          return $connoracle->select("select pricing_group_sequence,sum(adjusted_amount*inv_convert.inv_um_convert(ola.inventory_item_id,'".$line->uom."',ola.ORDER_QUANTITY_UOM)) as adjusted_amount, sum(operand) as operand
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
          return $connoracle->selectone("select pricing_group_sequence,,sum(adjusted_amount*inv_convert.inv_um_convert(ola.inventory_item_id,'".$line->uom."',ola.ORDER_QUANTITY_UOM)) as adjusted_amount, sum(operand) as operand
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

    public function getadjustmentHeaderSO($notrx,$headerid)
    {
      $connoracle = DB::connection('oracle');
      $newadjustment=false;
      if($connoracle){
        $oraSOheader = $connoracle->table('oe_order_lines_all as ola')
                      ->whereRaw( "ola.attribute1 = '".$notrx."'")
                      ->where('ola.booked_flag','=','Y')
                      ->select('ola.header_id')
                      ->groupBy('ola.header_id')
                      ->get();
        foreach($oraSOheader as $soheader)
        {
          echo "header id:".$soheader->header_id."<br>";
          $adjustmentso = $connoracle->table('oe_order_lines_all as ola')
                        ->join('mtl_system_items as msi',function($query1){
                            $query1->on('msi.inventory_item_id','=','ola.inventory_item_id')
                                    ->on('msi.organization_id','=','ola.ship_from_org_id');
                        })
                        ->where('header_id','=',$soheader->header_id)
                        ->where('ola.ORDERED_QUANTITY','!=',0)
                        ->whereNull('ola.attribute1')
                        ->whereNull('ola.attribute2')
                        ->where('ola.line_category_code','=','ORDER')
                        ->whereExists(function($query){
                            $query->select(DB::raw(1))
                                  ->from('oe_price_adjustments as opa')
                                  ->whereRaw(' opa.header_id=ola.headeR_id and opa.line_id = ola.line_id');
                        })
                        ->select('ola.headeR_id', 'ola.line_id', 'ola.ORDERED_QUANTITY', 'ola.INVENTORY_ITEM_ID'
                                , 'ola.ORDERED_QUANTITY', 'ola.unit_list_price'
                                , 'ola.ORDER_QUANTITY_UOM', 'ola.unit_selling_price'
                                , DB::raw('inv_convert.inv_um_convert(ola.inventory_item_id,ola.ORDER_QUANTITY_UOM, msi.primary_uom_code ) as conversion')
                                ,'ola.tax_value','msi.primary_uom_code'
                              );

          $adjustmentso=$adjustmentso->get() ;
          foreach($adjustmentso as $soline)
          {
            echo $soline->line_id;
            $adj_price = $connoracle->table('oe_price_adjustments as opa')
                        ->where('opa.header_id','=',$soline->header_id)
                        ->where('opa.line_id','=',$soline->line_id)
                        ->where('applied_flag','=','Y')
                        ->select('list_line_id')
                        ->first();
            if($adj_price) $list_line_id = $adj_price->list_line_id;else $list_line_id = -1;

            $product = DB::table('products')->where('inventory_item_id','=',$soline->inventory_item_id)->select('id')->first();
            $newline = SoLine::updateOrCreate(['bonus_list_line_id'=>$list_line_id
                                                ,'product_id'=>$product->id],
                        ['header_id'=>$headerid
                        , 'uom'=> $soline->order_quantity_uom
                        ,'qty_request'=>$soline->ordered_quantity
                        ,'qty_confirm'=>$soline->ordered_quantity*$soline->conversion
                        ,'list_price'=>$soline->unit_list_price
                        , 'unit_price'=>$soline->unit_selling_price
                        ,'amount'=>$soline->unit_selling_price*$soline->ordered_quantity
                        ,'tax_amount'=>$soline->tax_value
                        ,'oracle_line_id'=>$soline->line_id
                        ,'conversion_qty'=>$soline->conversion
                        ,'inventory_item_id'=>$soline->inventory_item_id
                        ,'uom_primary'=>$soline->primary_uom_code
                        ,'qty_request_primary'=>$soline->ordered_quantity*$soline->conversion
                        ,'oracle_line_id'=>$soline->line_id
                        ]
                        );
            $updateoraline = $connoracle->table('oe_order_lines_all as ola')
                            ->where('ola.line_id','=',$soline->line_id)
                            ->update(['attribute1'=>$notrx,'attribute2'=>$newline->line_id]);
            $newadjustment =true;
          }
        }
        return $newadjustment;
      }else return $newadjustment;
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
                        ,'end_date_active','pricing_group_sequence','incompatibility_grp_code','list_line_no','product_precedence','pricing_phase_id'
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
                ,'end_date_active'=>$m->end_date_active,'pricing_group_sequence'=>$m->pricing_group_sequence,'incompatibility_grp_code'=>$m->incompatibility_grp_code
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

    public function updateDiskonTable($tglskrg){
      if(is_null($tglskrg))
      {
          $tglskrg =date('Y-m-d');
      }

      $listheader = QpListHeaders::whereIn('list_type_code',['DLT'])
                    ->whereRaw("'".$tglskrg."' between ifnull(start_date_active,date('2017-01-01'))
              and ifnull(end_date_active,DATE_ADD('".$tglskrg."',INTERVAL 1 day))")
               ->where('list_header_id','=',22289)
                ->get();

      foreach ($listheader as $priceheader)
      {
        if(is_null($priceheader->start_date_active))
        {
          $tglawheader=date_create('2017-01-01');
        }else{
          $tglawheader = $priceheader->start_date_active;
        }
        if(is_null($priceheader->end_date_active))
        {
          $tglakheader=date_create(date('Y').'-12-31');
        }else{
          $tglakheader = $priceheader->end_date_active;
        }

        $listdiskon = qp_modifier_summary::where('list_header_id','=',$priceheader->list_header_id)
                          ->whereRaw("'".$tglskrg."' between ifnull(start_date_active,date('2017-01-01'))
                                  and ifnull(end_date_active,DATE_ADD('".$tglskrg."',INTERVAL 1 day))")
                          ->where('product_attr', '=','PRICING_ATTRIBUTE1')
                          ->where('list_line_type_code','=','DIS')
                          ->where('list_line_id','=',23301)
                          ->orderBy('list_header_id','asc')
                          ->orderBy('list_line_id','asc')
                          ->orderBy('pricing_group_sequence','asc')
                          ->get();
        foreach($listdiskon as $diskon)
        {
          $a=$diskon->list_line_id;
          echo ('line_id:'.$a);
          $qualifierlist = qp_qualifiers::where('list_header_id','=',$diskon->list_header_id)
                          ->where(function ($query) use ($a) {
                                $query->where('list_line_id', '=', $a)
                                      ->orWhere('list_line_id', '=', -1);
                            })
                            ->whereRaw("'".$tglskrg."' between ifnull(start_date_active,date('2017-01-01'))
                                    and ifnull(end_date_active,DATE_ADD('".$tglskrg."',INTERVAL 1 day))")
                          ;


          if($qualifierlist->get())
          {
            /*customer id condition*/
            $customerlist = $qualifierlist->where('qualifier_context','=','customer')
              ->where('qualifier_attribute','=','QUALIFIER_ATTRIBUTE2')
              ->get();
            if($customerlist)
            {
              foreach ($customerlist as $cust)
              {

                QpPricingDiskon::updateorCreate(
                  ['list_header_id'=>$diskon->list_header_id
                  , 'list_line_id'=>$diskon->list_line_id
                  ,'list_line_no' =>$diskon->list_line_no
                  , 'item_id'=>$diskon->product_attr_val
                  , 'customer_id'=>$cust->qualifier_attr_value]
                  ,[
                  'list_line_type_code'  =>$diskon->list_line_type_code
                  ,'modifier_level_code'=>$diskon->modifier_level_code
                  ,'operand'=>$diskon->operand
                  ,'arithmetic_operator_code'=>$diskon->arithmetic_operator_code
                  ,'start_date_active'=>$tglawheader
                  ,'end_date_active'=>$tglakheader
                  ,'uom_code'=>$diskon->product_uom_code
                  ,'comparison_operator_code'=>$diskon->comparison_operator_code
                  ,'pricing_attribute_context'=>$diskon->pricing_attribute_context
                  ,'pricing_attr'=>$diskon->pricing_atr
                  ,'pricing_attr_value_from'=>$diskon->pricing_attr_value_from
                  ,'pricing_attr_value_to'=>$diskon->pricing_attr_value_to
                  ]
                );
              }
            }
          }
        }
      }


    }

    public function getMasterItem($tglskrg)
    {
      $connoracle = DB::connection('oracle');
      if($connoracle){
        $master_products = $connoracle->table('mtl_system_items as msi')
            ->where('customer_order_enabled_flag','=','Y')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('mtl_parameters as mp')
                      ->whereRaw(' master_organization_id = msi.organization_id');
            })
            ->where('last_update_date','>=',$tglskrg)
            ->select('inventory_item_id', 'organization_id', 'segment1', 'description',  'primary_uom_code', 'secondary_uom_code'
                      ,DB::raw("inv_convert.inv_um_convert(msi.inventory_item_id,msi.secondary_uom_code, msi.primary_uom_code) as conversion")
                      , 'enabled_flag'
                      , 'attribute1')
            ->orderBy('segment1')->get();

        if($master_products){
          $insert_flag=false;
            foreach($master_products as $mp)
            {
              echo ('Product:'.$mp->segment1."<br>");
              $query1 = Product::where('inventory_item_id','=',$mp->inventory_item_id)->first();
              if($query1){//update
                $update = Product::updateOrCreate(
                       ['inventory_item_id'=>$mp->inventory_item_id],
                       ['title'=>$mp->description
                        ,'itemcode'=>$mp->segment1
                        ,'satuan_primary'=>$mp->primary_uom_code
                        ,'satuan_secondary'=>$mp->secondary_uom_code
                        ,'conversion'=>$mp->conversion
                        ,'enabled_flag'=>$mp->enabled_flag
                      ]);
              }else{//insert
                $insert = Product::Create(
                      ['inventory_item_id'=>$mp->inventory_item_id
                        ,'title'=>$mp->description
                        ,'itemcode'=>$mp->segment1
                        ,'satuan_primary'=>$mp->primary_uom_code
                        ,'satuan_secondary'=>$mp->secondary_uom_code
                        ,'conversion'=>$mp->conversion
                        ,'enabled_flag'=>$mp->enabled_flag
                      ]);
                $insert_flag =true;
              }
            }

            if($insert_flag)
            {
              //notif ke sysadmin

            }
            return true;
        }

      }
    }

    public function getMasterDiscount($tglskrg){
      $connoracle = DB::connection('oracle');
      if($connoracle){
        $modifiers = $connoracle->table('qp_list_headers as qlh')
                      ->join('qp_modifier_summary_v as qms','qlh.list_header_id','=','qms.list_header_id')
                      ->where('product_attribute_context','=','ITEM')
                      ->whereRaw('nvl(qlh.end_date_active,trunc(sysdate)+1) >= trunc(sysdate) and
                                  nvl(qms.end_date_active,trunc(sysdate)+1) >= trunc(sysdate)')
                      ->select('qlh.list_header_id', 'qms.list_line_id', 'qms.list_line_no', 'qms.list_line_type_code', 'qms.MODIFIER_LEVEL_CODE'
                          , 'qms.operand', 'qms.arithmetic_operator'
                          , 'qms.comparison_operator_code','qms.PRICING_ATTRIBUTE_CONTEXT'
                          , 'qms.pricing_attr', 'qms.pricing_attr_value_from', 'qms.pricing_attr_value_to'
                          , 'qms.PRICING_GROUP_SEQUENCE', 'qlh.ORIG_ORG_ID'
                          , 'qms.product_attr_val','qms.PRODUCT_UOM_CODE','qms.product_attr')
                      ->orderBy('qlh.list_header_id','asc')
                      ->orderBy('qlh.list_line_no','asc')
                      ->get();
        if($modifiers)
        {
          foreach ($modifiers as $m){
            $qualifiers = $connoracle->table('qp_qualifiers_v as qqv')
                          ->whereRaw("qqv.list_header_id =".$m->list_header_id."and (qqv.list_line_id=-1 or qqv.list_line_id=".$m->list_line_id." )" )
                          ->whereRaw("nvl(qms.end_date_active,trunc(sysdate)+1) >= trunc(sysdate) ")
                          ->select('qualifier_id','comparison_operator_code','qualifier_context','qualifier_attribute'
                                  ,  'qualifier_attr_value'
                                  )
                          ->get();
            if($quelifiers)
            {
              foreach($qualifiers as $q)
              {
                if($q->qualifier_context == "CUSTOMER" and $q->qualifier_attribute=="QUALIFIER_ATTRIBUTE2")
                  $discount = QpPricingDiskon::updateOrCreate(
                        ['list_header_id'=>$m->list_header_id
                        , 'list_line_id'=>$m->list_line_id
                        ,'list_line_no' =>$m->list_line_no],
                        [ 'item_id'=>$m->product_attr_val
                        ,'list_line_type_code'  =>$m->list_line_type_code
                        ,'modifier_level_code'=>$m->modifier_level_code
                        ,'operand'=>$m->operand
                        ,'arithmetic_operator_code'=>$m->arithmetic_operator_code
                        ,'start_date_active'=>$m->start_date_active
                        ,'end_date_active'=>$m->end_date_active
                        ,'uom_code'=>$m->product_uom_code
                        ,'comparison_operator_code'=>$m->comparison_operator_code
                        ,'pricing_attribute_context'=>$m->pricing_attribute_context
                        ,'pricing_attr'=>$m->pricing_atr
                        ,'pricing_attr_value_from'=>$m->pricing_attr_value_from
                        ,'pricing_attr_value_to'=>$m->pricing_attr_value_to
                        ,'product_attr'=>$m->product_attr
                        ,'customer_id'=>$q->qualifier_attr_value
                      ]);
                elseif($q->qualifier_context == "CUSTOMER" and $q->qualifier_attribute=="QUALIFIER_ATTRIBUTE11")
                $discount = QpPricingDiskon::updateOrCreate(
                      ['list_header_id'=>$m->list_header_id
                      , 'list_line_id'=>$m->list_line_id
                      ,'list_line_no' =>$m->list_line_no],
                      [ 'item_id'=>$m->product_attr_val
                      ,'list_line_type_code'  =>$m->list_line_type_code
                      ,'modifier_level_code'=>$m->modifier_level_code
                      ,'operand'=>$m->operand
                      ,'arithmetic_operator_code'=>$m->arithmetic_operator_code
                      ,'start_date_active'=>$m->start_date_active
                      ,'end_date_active'=>$m->end_date_active
                      ,'uom_code'=>$m->product_uom_code
                      ,'comparison_operator_code'=>$m->comparison_operator_code
                      ,'pricing_attribute_context'=>$m->pricing_attribute_context
                      ,'pricing_attr'=>$m->pricing_atr
                      ,'pricing_attr_value_from'=>$m->pricing_attr_value_from
                      ,'pricing_attr_value_to'=>$m->pricing_attr_value_to
                      ,'product_attr'=>$m->product_attr
                      ,'ship_to_id'=>$q->qualifier_attr_value
                    ]);
                elseif($q->qualifier_context == "CUSTOMER")  {
                    if($q->qualifier_attribute=="QUALIFIER_ATTRIBUTE33")
                    {
                      /*address4*/
                    }elseif($q->qualifier_attribute=="QUALIFIER_ATTRIBUTE32"){
                      /*Subchannel*/
                    }

                }elseif($q->qualifier_context == "CUSTOMER" and $q->qualifier_attribute=="QUALIFIER_ATTRIBUTE32")  {

                }
              }

            } else{
              $discount = QpPricingDiskon::updateOrCreate(
                ['list_header_id'=>$m->list_header_id
                , 'list_line_id'=>$m->list_line_id
                ,'list_line_no' =>$m->list_line_no],
                [ 'item_id'=>$m->product_attr_val
                ,'list_line_type_code'  =>$m->list_line_type_code
                ,'modifier_level_code'=>$m->modifier_level_code
                ,'operand'=>$m->operand
                ,'arithmetic_operator_code'=>$m->arithmetic_operator_code
                ,'start_date_active'=>$m->start_date_active
                ,'end_date_active'=>$m->end_date_active
                ,'uom_code'=>$m->product_uom_code
                ,'comparison_operator_code'=>$m->comparison_operator_code
                ,'pricing_attribute_context'=>$m->pricing_attribute_context
                ,'pricing_attr'=>$m->pricing_atr
                ,'pricing_attr_value_from'=>$m->pricing_attr_value_from
                ,'pricing_attr_value_to'=>$m->pricing_attr_value_to
                ,'product_attr'=>$m->product_attr
                ]
              );
            }
          }
        }
      }
    }

    public function getConversionItem($tglskrg){
      $connoracle = DB::connection('oracle');
      if($connoracle){
        $conversions = $connoracle->table('mtl_uom_conversions as muc')
                    ->join('mtl_system_items as msi','muc.INVENTORY_ITEM_ID' ,'=','msi.inventory_item_id')
                    ->join('MTL_UNITS_OF_MEASURE_VL as mum','muc.uom_class','=','mum.uom_class')
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                              ->from('mtl_parameters as mp')
                              ->whereRaw(' master_organization_id = msi.organization_id');
                    })
                    ->where('mum.BASE_UOM_FLAG','=','Y')
                    ->where('msi.CUSTOMER_ORDER_FLAG','=','Y')
                    ->where('muc.last_update_date','>=',$tglskrg)
                    ->select('msi.inventory_item_id','msi.segment1','msi.description','muc.uom_code','muc.uom_class'
                              ,DB::raw("INV_CONVERT.inv_um_convert(muc.inventory_item_id, muc.UOM_CODE, mum.uom_code) as conversion_rate")
                              ,'mum.uom_code as base_uom','muc.width','muc.height','muc.dimension_uom')
                    ->get();
        //dd($conversions->toSQL());
        foreach($conversions as $c){
          echo ('konversi'.$c->inventory_item_id.'dari '.$c->uom_code.' ke '.$c->base_uom."<br>");
          $mysqlproduct = Product::where('inventory_item_id','=',$c->inventory_item_id)
                      ->select('id')
                      ->first();
          if($mysqlproduct) {
            echo"Product id :".$mysqlproduct->id."<br>";
            $mysqlconversion = DB::table('uom_conversions')->where(['product_id'=>$mysqlproduct->id,
              'uom_code'=>$c->uom_code,
              'base_uom'=>$c->base_uom])->first();
            if($mysqlconversion)
            {
              $mysqlconversion->uom_class = $c->uom_class;
              $mysqlconversion->rate = $c->conversion_rate;
              $mysqlconversion->width = $c->width;
              $mysqlconversion->height = $c->height;
              $mysqlconversion->dimension_uom = $c->dimension_uom;
              $mysqlconversion->save();
            } else{
              DB::table('uom_conversions')->insert([
                  'product_id'=>$mysqlproduct->id,
                  'uom_code'=>$c->uom_code,
                  'base_uom'=>$c->base_uom,
                  'uom_class'=>$c->uom_class
                  ,'rate'=>$c->conversion_rate
                  ,'width'=>$c->width
                  ,'height'=>$c->height
                  ,'dimension_uom'=>$c->dimension_uom
              ]);
            }

          }

        }
       }
       return true;
    }

}
