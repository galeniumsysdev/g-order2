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

class BackgroundController extends Controller
{
    public function getStatusOrderOracle()
    {

      $request= DB::table('tbl_request')->insert([
        'created_at'=>Carbon::now(),
        'updated_at'=>Carbon::now()
      ]);
      $connoracle = DB::connection('oracle');
      if($connoracle){
        $headers = SoHeader::whereNotNull('oracle_customer_id')->where([
                  ['approve','=',1],
                  ['status','>=',0],
                  ['status','<',2],
                //  ['notrx','=','PO-20170927-IX-00009']
        ])->get();
        if($headers){
          foreach($headers as $h)
          {
            echo "notrx:".$h->notrx."<br>";

              if($h->status==0 and $h->interface_flag=="N")//jika blm terinterface
              {
                echo "insert interface oracle<br>";
                $this->insert_interface_oracle($h);
                $h->interface_flag="Y";
                $h->save();
              }elseif($h->status>=0 and $h->interface_flag=="Y")//jika blm dibook dan sudah terinterface
              {
                $oraheader = $connoracle->table('oe_order_headers_all')
                            ->where([
                              ['orig_sys_document_ref','=',$h->notrx],
                              ['org_id','=',$h->org_id],
                              ['order_source_id','=',config('constant.order_source_id')]
                            ])->select('header_id','flow_status_code','booked_date');

                $oraheader =$oraheader->get();
                //dd($oraheader);
                //$oraheader = $connoracle->select('select header_id, flow_status_code from oe_order_headers_all where orig_sys_document_ref = ? and org_id=? and order_source_id=?',array($h->notrx,$h->org_id, config('constant.order_source_id')));
                //var_dump($oraheader);
                if($oraheader)
                {

                  foreach($oraheader as $oh)
                  {
                    echo "masuk".$oh->header_id."<br>";
                    if($oh->flow_status_code!="ENTERED")
                    {
                      if($oh->flow_status_code=="CANCELLED")
                      {
                        $h->oracle_header_id = $oh->header_id;
                        $h->status=-2;
                        $h->interface_flag="Y";
                        $h->status_oracle =$oh->flow_status_code;
                        $h->updated_at=Carbon::now();
                        $h->save();
                        $customer = Customer::where('id','=',$h->customer_id)->first();
                        foreach($customer->users as $u)
                        {
                          $u->notify(new RejectPOByDistributor($h));
                        }
                      }else
                      {
                        $tglkirim =null;
                        $oraline = $connoracle->table('oe_order_lines_all')
                                    ->where([
                                      ['orig_sys_document_ref','=',$h->notrx],
                                      ['org_id','=',$h->org_id],
                                      ['order_source_id','=',config('constant.order_source_id')],
                                      ['header_id','=',$oh->header_id],
                                      //['orig_sys_line_ref','=',$l->line_id]
                                    ])->select('orig_sys_line_ref','unit_selling_price','tax_value','order_quantity_uom','ordered_quantity','shipping_quantity','booked_flag','flow_status_code','line_id','inventory_item_id')->get();
                        //dd($oraline);
                        $tmpkirim=null;
                        foreach($oraline as $ol)
                        {
                          echo "oracle_line_id:".$ol->line_id."<br>";
                          $line = SoLine::where([
                            ['line_id','=',$ol->orig_sys_line_ref],
                            ['header_id','=',$h->id],
                          ])->first();

                          $line->unit_price= $ol->unit_selling_price;
                          $line->qty_confirm=$ol->ordered_quantity;
                          $line->uom= $ol->order_quantity_uom;
                          $line->tax_amount=$ol->tax_value;
                          $line->qty_shipping=$ol->shipping_quantity;
                          $line->oracle_line_id=$ol->line_id;

                          if(is_null($line->qty_shipping))
                          {
                          $wshline = $connoracle->selectOne("select
                              sum(inv_convert.inv_um_convert(
                                wdd.inventory_item_id,
                                wdd.requested_quantity_uom,
                                wdd.src_requested_quantity_uom ) * nvl(picked_quantity,0)) picked_quantity
                              , sum(inv_convert.inv_um_convert(
                                wdd.inventory_item_id,
                                requested_quantity_uom,
                                src_requested_quantity_uom ) * nvl(shipped_quantity,0)) shipped_quantity
                              , requested_quantity_uom
                              ,min(mmt.transaction_date) shipped_date
                          from wsh_delivery_details wdd
                              , mtl_material_transactions mmt
                          where wdd.source_code='OE' and wdd.source_header_id =".$oh->header_id."
                            and wdd.source_line_id = ".$ol->line_id."
                            and src_requested_quantity_uom = '".$ol->order_quantity_uom."'
                            and wdd.inventory_item_id = ".$ol->inventory_item_id."
                            and wdd.transaction_id =mmt.transaction_id(+)
                            and wdd.inventory_item_id = mmt.inventory_item_id(+)
                            and mmt.TRANSACTION_TYPE_ID(+)=52
                          group by wdd.inventory_item_id, wdd.source_header_id, wdd.source_line_id
                                , src_requested_quantity_uom,requested_quantity_uom");
                            //dd($wshline);
                            if($wshline){
                              if($wshline->picked_quantity>0)
                              {
                                $line->qty_shipping = $wshline->picked_quantity;
                                $tglkirim = $wshline->shipped_date;
                                $tmpkirim=1;
                              }
                            }else{
                              $line->qty_shipping = null;
                            }

                          }
                          $line->save();

                        }//foreach oraline
                        if($tmpkirim==1)// sudah dikirim
                        {
                          echo "update kirim<br>";
                          $h->oracle_header_id = $oh->header_id;
                          $h->status=2;
                          $h->tgl_kirim=$tglkirim;
                          $h->interface_flag="Y";
                          $h->status_oracle ="SHIPPING";
                          $h->updated_at=Carbon::now();
                          $h->save();
                          $customer = Customer::where('id','=',$h->customer_id)->first();
                          foreach($customer->users as $u)
                          {
                            $u->notify(new ShippingOrderOracle($h,$customer->customer_name));
                          }
                        }else{
                          if($h->status==0)
                          {
                            $h->oracle_header_id = $oh->header_id;
                            $h->status=1;
                            $h->tgl_approve=$oh->booked_date;
                            $h->interface_flag="Y";
                            $h->status_oracle ="BOOKED";
                            $h->updated_at=Carbon::now();
                            $h->save();
                            //notification to user
                            $customer = Customer::where('id','=',$h->customer_id)->first();
                            foreach($customer->users as $u)
                            {
                              $u->notify(new BookOrderOracle($h,$customer->customer_name));
                            }
                          }
                        }

                      }
                    }

                  }//foreach oraheader
                }else{/*jika tidak ada di table so header*/
                  /*check di interface apakah error*/
                  $ifaceheader = $connoracle->table('oe_headers_iface_all')
                              ->where([
                                ['orig_sys_document_ref','=',$h->notrx],
                                ['org_id','=',$h->org_id],
                                ['order_source_id','=',config('constant.order_source_id')]
                              ])->select('error_flag')->get();
                  if($ifaceheader)
                  {
                    if($ifaceheader->error_flag=="Y")
                    {
                      /*check apakah sudah pernah notif ke distributor*/
                      if($h->status_oracle!="Error")
                      {
                        $h->status_oracle="Error";
                          /*notif ke distributor*/
                        $distr=Customer::find($h->distributor_id);
                        foreach($distr->users as $d)
                        {
                          $u->notify(new InterfaceOracleErrpr($h,$u->name));
                        }
                      }


                    }
                  }
                  //interface_flag so header=Y
                  /*else{//input ke table so header and soline
                    if(!$h->interface_flag=="N")
                    {
                        $this->insert_interface_oracle($h);
                        $h->interface_flag="Y";
                        $h->save();
                    }
                  }*/
                }

              }


          }//enforeach
        }//  if($headers){

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
}
