<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;
use App\SoHeader;
use App\SoLine;
use App\SoShipping;
use Auth;
use File;
use Storage;
use Carbon\Carbon;
use PDO;
use PDF;
use App\Customer;

use App\Notifications\RejectPoByDistributor;
use App\Notifications\ReceiveItemsPo;
use App\Notifications\ShippingOrderOracle;
use Illuminate\Support\Facades\Input;
use Excel;
use App\Events\PusherBroadcaster;
use App\Notifications\PushNotif;


class OrderController extends Controller
{
    public function checkOrder($id)
    {
      $header = DB::table('so_header_v as sh')
              ->where('id','=',$id)
              ->where('status','!=',-99)
              ->where(function ($query) {
                $query->orWhere('distributor_id','=',Auth::user()->customer_id)
                      ->orWhere('customer_id','=',Auth::user()->customer_id);
                  })->first();
      /*$header = SoHeader::where('id','=',$id)
        ->where(function ($query) {
          $query->orWhere('distributor_id','=',Auth::user()->customer_id)
                ->orWhere('customer_id','=',Auth::user()->customer_id);
            })->first();*/
      if(!$header)
      {
            return view('errors.403');
      }

      $lines =DB::table('so_lines_v')->where('header_id','=',$id)->get();
      $deliveryno = SoShipping::where('header_id','=',$id)->get();
      $deliveryno = $deliveryno->groupBy('deliveryno','tgl_kirim');
      $user_dist = User::where('customer_id','=',$header->distributor_id)->first();

      if ($user_dist->hasRole('Principal') )    {
        return view('shop.checkOrder1',compact('header','lines','deliveryno'));
      }else {
        $print=Input::get('print','');
        if ($print=="yes"){
          $pdf = PDF::loadView('shop.pdf_po',compact('header','lines','deliveryno'));
          $pdf->setPaper('a4','portrait');
          //return $pdf->stream();
          return $pdf->download($header->notrx.'.pdf');
              //return view('shop.pdf_po',compact('header','lines'));
        }
        return view('shop.checkOrder',compact('header','lines','deliveryno'));
      }
    }

    public function listOrder(Request $request)
    {
      $liststatus = DB::table('flexvalue')->where([['master','=','status_po'],['enabled_flag','=','Y']])->orderBy('id','asc')->get();
      //if(Auth::user()->hasRole('Distributor') || Auth::user()->hasRole('Outlet') || Auth::user()->hasRole('Apotik/Klinik'))
      if(Auth::user()->can('CheckStatusPO'))
      {
          $trx = DB::table('so_header_v as sh')->where('customer_id','=',Auth::user()->customer_id)->orderBy('tgl_order','desc');
      }else{
        abort(403, 'Unauthorized action.');
      }
      $request->jns=1;//listpo

      /*
      if($request->jns==1) //listpo
      {
        $trx = $trx->where('customer_id','=',Auth::user()->customer_id);
      }elseif($request->jns==0){//listso
        $trx = $trx->where('distributor_id','=',Auth::user()->customer_id);
      }*/

      if(is_null($request->search)) //$request->status=="" and $request->tgl_aw=="" and $request->tgl_ak=="" and $request->criteria=="" and
      {
        $request->status=0;
        $trx =$trx->where('status','=',0);
      }else{
        if(isset($request->status))
        {
          $trx =$trx->where('status','=',$request->status);
        }else{
          $request->status=-3;
        }
        if(isset($request->criteria))
        {
          $search = $request->criteria;
          $trx =$trx->Where(function ($query) use($search) {
            $query->orWhere('notrx','LIKE',"%$search%")
                  ->orWhere('customer_po','LIKE',"%$search%")
                  ->orWhere('distributor_name','LIKE',"%$search%")    ;
              });
        }
        if(isset($request->tglaw) )
        {
          $trx =$trx->where('tgl_order','>=',$request->tglaw);
        }
        if(isset($request->tglak) )
        {
          $trx =$trx->where('tgl_order','<=',$request->tglak);
        }
      }
      //var_dump($trx->toSql());
      $trx = $trx->get();
      return view('shop.listpo',compact('liststatus','trx','request'));
    }

    public function listSO(Request $request)
    {

      //var_dump($request->tglak."--".$request->tglaw);
      $liststatus = DB::table('flexvalue')->where([['master','=','status_po']
                                                ,['enabled_flag','=','Y']
                                                ,['id','!=',-99]  ])
                    ->orderBy('id','asc')->get();
      if(Auth::user()->can('CheckStatusSO'))
      {
          $trx = DB::table('so_header_v as sh')->where('distributor_id','=',Auth::user()->customer_id);
      }else{
        abort(403, 'Unauthorized action.');
      }
      $request->jns=2;//listso
      if ($request->method()=='GET')
      {
        $request->status=0;
        $trx =$trx->where('status','=',0);
        $request->tglak = date_format(Carbon::now(),'Y-m-d');
        $request->tglaw = date_format(Carbon::now()->addDay(-7),'Y-m-d');
      }
      if($request->excel == "Create Excel" and $request->status=="x")
      {
        $this->createExcel($request,null);
      }

      if($request->status=="" and $request->tglaw=="" and $request->tglak=="" and $request->criteria=="")
      {
        $request->status=0;
        $trx =$trx->where('status','=',0);
      }else{
        if(isset($request->status))
        {
          if($request->status=="x")
          {
              $trx =$trx->where([['status','=',0],['approve','=',1]]);
              //$this->createExcel($request,null);
          }elseif($request->status=="0"){
              $trx =$trx->where([['status','=',0],['approve','=',0]]);
          }else{
              $trx =$trx->where('status','=',$request->status);
          }

        }
        if(isset($request->criteria))
        {
          $search = $request->criteria;
          $trx =$trx->Where(function ($query) use($search) {
            $query->orwhere('customer_name','LIKE',"%$search%")
                  ->orWhere('notrx','LIKE',"%$search%")
                  ->orWhere('customer_po','LIKE',"%$search%")   ;
              });
        }
        if(isset($request->tglaw) )
        {
          $trx =$trx->where('tgl_order','>=',$request->tglaw);
        }
        if(isset($request->tglak) )
        {
          $trx =$trx->where('tgl_order','<=',date('Y-m-d H:i:s', strtotime($request->tglak." 23:59:59")));
        }
      }
      //var_dump($trx->toSql());
      $trx = $trx->orderBy('tgl_order','desc');
      $trx = $trx->orderBy('notrx','desc');
      $trx = $trx->get();
      return view('shop.listpo',compact('liststatus','trx','request'));
    }

    public function approvalSO(Request $request)
    {

      $header = SoHeader::where([
        ['id','=',$request->header_id],
        ['distributor_id','=',Auth::user()->customer_id]
      ])->first();
      //echo($header.','.Auth::user()->customer_id);
      if(!$header)
      {
        return view('errors.403');
      }
      //dd('test');
      if($request->approve=="approve")
      {
        if(!is_null($header->oracle_customer_id))
        {
          /*$connoracle = DB::connection('oracle');
          if($connoracle){
            $oraheader = $connoracle->table('oe_headers_iface_all')->insert([
              'order_source_id'=>config('constant.order_source_id')
              ,'orig_sys_document_ref'=>$header->notrx
              ,'org_id'=>$header->org_id
              ,'sold_from_org_id'=>$header->org_id
              ,'ship_from_org_id'=>$header->warehouse
              ,'ordered_date'=>$header->tgl_order
              ,'order_type_id'=>$header->order_type_id
              ,'sold_to_org_id'=>$header->oracle_customer_id
              ,'payment_term_id'=>$header->payment_term_id
              ,'operation_code'=>'INSERT'
              ,'created_by'=>-1
              ,'creation_date'=>Carbon::now()
              ,'last_updated_by'=>-1
              ,'last_update_date'=>Carbon::now()
              ,'customer_po_number'=>$header->customer_po
              ,'price_list_id'=>$header->price_list_id
              ,'ship_to_org_id'=>$header->oracle_ship_to
              ,'invoice_to_org_id'=>$header->oracle_bill_to
            ]);
            $header->interface_flag="Y";*/
            $header->status=0;
          //}
        }else{
          $header->status=1;
        }


        $solines = DB::table('so_lines')->where('header_id','=',$request->header_id)->get();
        $i=0;
        /*validation qty*/
        foreach($solines as $soline)
        {
          if($soline->qty_request_primary<$request->qtyshipping[$soline->line_id])
          {
            return redirect()->back()->withError("Gagal simpan! Qty melebihi order")->withInput();
          }
        }
        /*update qty*/
        foreach($solines as $soline)
        {
          $i+=1;
          if( $request->uom[$soline->line_id]==$soline->uom){
            $qty = $request->qtyshipping[$soline->line_id]*$soline->conversion_qty;
          }elseif( $request->uom[$soline->line_id]==$soline->uom_primary){
            $qty = $request->qtyshipping[$soline->line_id];
          }
          $update = DB::table('so_lines')
            ->where([
              ['header_id','=',$request->header_id],
              ['line_id','=',$soline->line_id]
            ])
            ->update(['qty_confirm' => $qty]);
            /*if(!is_null($header->oracle_customer_id))
          {
          if($oraheader){
              $oraline = $connoracle->table('oe_lines_iface_all')->insert([
                'order_source_id'=>config('constant.order_source_id')
                ,'orig_sys_document_ref' => $header->notrx
                ,'orig_sys_line_ref'=>$soline->line_id
                ,'line_number'=>$i
                ,'inventory_item_id'=>$soline->inventory_item_id
                ,'ordered_quantity'=>$request->qtyshipping[$soline->line_id]
                ,'order_quantity_uom'=>$soline->uom
                //,'ship_from_org_id'=>$soline->qty_shipping
                ,'org_id'=>$header->org_id
                //,'pricing_quantity'
                //,'unit_selling_price'
                //,'unit_list_price'
                //,'price_list_id'
                //,'payment_term_id'
                //,'schedule_ship_date'
                ,'request_date'=>$header->tgl_order
                ,'created_by'=>-1
                ,'creation_date'=>Carbon::now()
                ,'last_updated_by'=>-1
                ,'last_update_date'=>Carbon::now()
                //,'line_type_id'
                ,'calculate_price_flag'=>'Y'
              ]);
              $ordertype=config('constant.order_source_id');
              $orgid = $header->org_id;
              $notrx =$header->notrx;
            //}
          }*/
        }
        $header->approve=1;
      //  $header->status=1;
        $header->tgl_approve=Carbon::now();
        $header->save();

        //$this->createExcel(null,$h->id);
        return redirect()->route('order.listSO')->withMessage(trans('pesan.approveSO_msg',['notrx'=>$header->notrx]));
      }elseif($request->approve=="reject"){
        $update = DB::table('so_lines')
          ->where('header_id','=',$request->header_id)
          ->update(['qty_confirm' => 0]);
        $header->status=-2;
        $header->alasan_tolak=$request->alasan;
        $header->tgl_approve=Carbon::now();
        $header->save();
        $customer = Customer::where('id','=',$header->customer_id)->first();
        $content = "Mohon maaf, bersama ini kami informasikan bahwa PO anda No:".$header->customer_po." telah dibatalkan.";
        $content .="<br>Silahkan konfirmasi ke Distributor untuk penjelasan lebih detail.";
        $data=[
          'title' => 'Penolakan PO',
          'message' => 'Penolakan PO #'.$header->customer_po.'dari distributor',
          'id' => $header->id,
          'href' => route('order.notifnewpo'),
          'mail' => [
            'greeting'=>'Penolakan PO #'.$header->customer_po.'.',
            'content' =>$content,
          ]
        ];
        foreach($customer->users as $u)
        {
          $data['email'] = $u->email;
          //$u->notify(new RejectPoByDistributor($header,1, $request->alasan));
          //event(new PusherBroadcaster($data, $u->email));
          $u->notify(new PushNotif($data));
        }

        return redirect()->route('order.listSO')->withMessage(trans('pesan.rejectSO_msg',['notrx'=>$header->notrx]));
      }elseif($request->kirim=="kirim"){
        if($header->status<3 and $header->status>0 and Auth::user()->customer_id ==$header->distributor_id)
        {
          $this->validate($request, [
          'deliveryno' => 'required',
          ]);

          $solines = DB::table('so_lines')->where('header_id','=',$request->header_id)
                    ->whereIn('line_id',array_keys($request->qtyshipping))
                    ->get();
          /*validation qty*/
          foreach($solines as $soline)
          {
            if($soline->qty_request_primary<intval($soline->qty_shipping)+$request->qtyshipping[$soline->line_id])
            {
              return redirect()->back()->withError("Gagal simpan! Qty kirim melebihi order")->withInput();
            }
            $checkshipping= SoShipping::where(['deliveryno'=>$request->deliveryno
                            ,'header_id'=>$soline->header_id
                            ,'line_id'=>$soline->line_id])->first();
            if($checkshipping){
              return redirect()->back()->withError("Gagal simpan! Delivery No sudah ada")->withInput();
            }
          }
          /*update shipping*/
          foreach($solines as $soline)
          {
            if($request->qtyshipping[$soline->line_id]>0){
              $insertkirim = SoShipping::create([
                'deliveryno'=>$request->deliveryno
                ,'product_id'=>$soline->product_id
                ,'uom'=>$soline->uom
                ,'qty_request'=>$soline->qty_request
                ,'uom_primary'=>$soline->uom_primary
                ,'qty_request_primary'=>$soline->qty_request_primary
                ,'conversion_qty'=>$soline->conversion_qty
                ,'qty_shipping'=>$request->qtyshipping[$soline->line_id]
                ,'tgl_kirim'=>Carbon::now()
                ,'header_id'=>$soline->header_id
                ,'line_id'=>$soline->line_id
              ]);
              $update = DB::table('so_lines')
                ->where([
                  ['header_id','=',$request->header_id],
                  ['line_id','=',$soline->line_id]
                ])
                ->update(['qty_shipping' => intval($soline->qty_shipping)+$request->qtyshipping[$soline->line_id]]);
            }

          }
          $header->tgl_kirim =Carbon::now();
          $afterheader = DB::table('so_lines_sum_v')->where('header_id','=',$request->header_id)->first();

          if($afterheader->qty_shipping_primary==$afterheader->qty_confirm_primary
          or $afterheader->qty_shipping_primary==$afterheader->qty_request_primary)
          {
            $header->status=3; /*full shipping*/
          }else{
            $header->status=2; /*partial shipping*/
          }

          $customer = Customer::where('id','=',$header->customer_id)->first();
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
          foreach($customer->users as $u)
          {
            $data['email']= $u->email;
            //$u->notify(new ShippingOrderOracle($header,$customer->customer_name));
            //event(new PusherBroadcaster($data, $u->email));
            $u->notify(new PushNotif($data));
          }
          $header->save();
          return redirect()->route('order.listSO')->withMessage(trans('pesan.sendSO_msg',['notrx'=>$header->notrx]));
        }else{
          return view('errors.403');
        }

      }elseif($request->createExcel=="Create Excel"){
          $this->createExcel($request, $request->header_id);
        //  return redirect()->route('order.listPO');
      }
    }

    public function readnotifnewpo($id,$notifid)
    {
       Auth::User()->notifications()
                  ->where('id','=',$notifid)
                    ->update(['read_at' => Carbon::now()]);
       return redirect()->route('order.checkPO',$id);
    }

    public function batalPO(Request $request) /*untuk membatalkan PO atau receive item*/
    {

      if($request->batal=="batal"){
        $header = SoHeader::where([
          ['id','=',$request->header_id],
          ['customer_id','=',Auth::user()->customer_id]
        ])->first();
        if(!$header)
        {
          return view('errors.403');
        }
        if($header->status==0)
        {
          $header->status = -1;

          $customer = Customer::where('id','=',$header->distributor_id)->first();
          $content = "Bersama ini kami informaskikan bahwa PO dengan no.transaksi#".$header->notrx." telah dibatalkan oleh customer.";
          $content .="<br>Silahkan konfirmasi ke customer untuk penjelasan lebih detail.";
          $data=[
            'title' => 'Pembatalan PO',
            'message' => 'Pembatalan PO #'.$header->customer_po.'oleh '.$header->outlet->customer_name,
            'id' => $header->id,
            'href' => route('order.notifnewpo'),
            'mail' => [
              'greeting'=>'Pembatalan PO #'.$header->customer_po.'.',
              'content' =>$content,
            ]
          ];
          foreach($customer->users as $u)
          {
            $data['email']= $u->email;
            //$u->notify(new RejectPoByDistributor($header,0,""));
            //event(new PusherBroadcaster($data, $u->email));
            $u->notify(new PushNotif($data));
          }
          $header->save();
          return redirect()->route('order.listPO')->withMessage(trans('pesan.cancelPO_msg',['notrx'=>$header->notrx]));
        }else{
          return redirect()->route('order.checkPO',$request->header_id)->withMessage(trans('pesan.cantcancelPO',['notrx'=>$header->notrx]));
        }
      }elseif($request->terima=="terima")
      {
        $this->validate($request, [
        'deliveryno' => 'required',
        ]);
        //dd($request->qtyreceive);
        $header = SoHeader::where([
          ['id','=',$request->header_id],
          ['customer_id','=',Auth::user()->customer_id]
        ])->first();

        if(!$header)
        {
          return view('errors.403');
        }
        if($header->status>=0 and $header->status<4)
        {
          /*$soshipping = DB::table('so_shipping')->where([
            ['header_id','=',$request->header_id],
            ['deliveryno','=',$request->deliveryno]
          ])->get();*/
          $solines = SoLine::where('header_id','=',$request->header_id)
          /*$solines = DB::table('so_lines')
                    ->where('header_id','=',$request->header_id)*/
                    ->whereIn('line_id',array_keys($request->qtyreceive))
                    ->select('line_id',DB::raw("ifnull(qty_accept,0) as qty_accept")
                      ,DB::raw("ifnull(qty_confirm,qty_request_primary) as qty_confirm")
                      ,'product_id','uom','qty_request','qty_request_primary','uom_primary','conversion_qty')
                    ->get();
          foreach($solines as $soline)
          {
            $qtyterima =0;
            if(is_array($request->qtyreceive[$soline->line_id]))
            {
              foreach($request->qtyreceive[$soline->line_id] as $key => $qty)
              {
                  $insshipping =SoShipping::updateorCreate(
                    ['header_id'=>$request->header_id,'line_id'=>$soline->line_id,'deliveryno'=>$request->deliveryno,'id'=>$key]
                    ,['qty_accept'=>$qty,'product_id'=>$soline->product_id
                      ,'uom'=>$soline->uom,'qty_request'=>$soline->qty_request,'qty_request_primary'=>$soline->qty_request_primary
                      ,'uom_primary'=>$soline->uom_primary,'conversion_qty'=>$soline->conversion_qty
                      ,'tgl_terima'=>Carbon::now()
                     ]
                  )  ;

              }
            }else{
              $insshipping =SoShipping::Create(
                ['header_id'=>$request->header_id,'line_id'=>$soline->line_id,'deliveryno'=>$request->deliveryno
                  ,'qty_accept'=>$request->qtyreceive[$soline->line_id],'product_id'=>$soline->product_id
                  ,'uom'=>$soline->uom,'qty_request'=>$soline->qty_request,'qty_request_primary'=>$soline->qty_request_primary
                  ,'uom_primary'=>$soline->uom_primary,'conversion_qty'=>$soline->conversion_qty
                  ,'tgl_terima'=>Carbon::now()
                 ]
              )  ;
            }

              $soline->qty_accept = $soline->shippings->sum('qty_accept');
              $soline->save();
              //$qtyterima=$soline->qty_accept+$request->qtyreceive[$soline->line_id];
            /*$update = DB::table('so_lines')
              ->where([
                ['header_id','=',$request->header_id],
                ['line_id','=',$soline->line_id]
              ])
              ->update(['qty_accept' => $qtyterima]);*/
          }
          if(is_null($header->tgl_terima))
          {
            $header->tgl_terima= Carbon::now();
          }
          $afterheader = DB::table('so_lines_sum_v')->where('header_id','=',$request->header_id)->first();

          if($afterheader->qty_accept_primary==$afterheader->qty_confirm_primary
          or $afterheader->qty_accept_primary==$afterheader->qty_request_primary)
          {
              $header->status = 4;
          }elseif(($afterheader->qty_shipping_primary==$afterheader->qty_confirm_primary and $afterheader->qty_shipping_primary!=0)
          or $afterheader->qty_shipping_primary==$afterheader->qty_request_primary){
              $header->status = 3;
          }else{ $header->status = 2;}
          $header->save();
          $dist=Customer::where('id','=',$header->distributor_id)->first();
          $content = 'Pesanan Anda dengan PO nomor: <strong>'.$header->customer_po.'</strong> dan SJ: '.$request->deliveryno.' telah diterime customer.<br>';
          $content .='Terimakasih telah menggunakan aplikasi '.config('app.name', 'g-Order');
          $data=[
            'title' => 'PO diterima customer',
            'message' => 'SJ #'.$request->deliveryno.' telah diterima customer',
            'id' => $header->id,
            'href' => route('order.notifnewpo'),
            'mail' => [
              'greeting'=>'SJ: '.$request->deliveryno.' telah diterima customer',
              'content' =>$content,
            ]
          ];
          foreach($dist->users as $d)
          {
            $data['email']= $d->email;
            //$d->notify(new ReceiveItemsPo($header,$request->deliveryno));
            //event(new PusherBroadcaster($data, $d->email));
            $d->notify(new PushNotif($data));
          }

          return redirect()->route('order.listPO')->withMessage(trans('pesan.receivePO_msg',['notrx'=>$header->notrx]));
        }else{
          return redirect()->route('order.checkPO',$request->header_id)->withMessage(trans('pesan.cantreceivePO',['notrx'=>$header->notrx]));
        }
      }

    }

    public function createExcel(Request $request,$id)
    {
      $header = DB::table('so_headers as sh')
                ->join('customers as c','sh.customer_id','=','c.id')
                ->leftjoin('oe_transaction_types as ot','sh.order_type_id','=','ot.transaction_type_id')
                ->leftjoin('qp_list_headers as ql','ql.list_header_id','=','sh.price_list_id')
                ->where([
                        ['sh.status','=','0'],
                        ['sh.approve','=','1'],
                        ['sh.distributor_id','=',Auth::user()->customer_id],
                        //['sh.id','=',$id]
                      ]);
        if(isset($request->criteria))
        {
          $search = $request->criteria;
          $header =$header->Where(function ($query) use($search) {
            $query->orwhere('customer_name','LIKE',"%$search%")
                  ->orWhere('notrx','LIKE',"%$search%")
                  ->orWhere('customer_po','LIKE',"%$search%")   ;
              });
        }
        if(isset($request->tglaw) )
        {
          $header =$header->where('tgl_order','>=',$request->tglaw);
        }
        if(isset($request->tglak) )
        {
          $header =$header->where('tgl_order','<=',$request->tglak);
        }
        if(isset($id))
        {
          $header =$header->where('sh.id','=',$id);
        }
        $header =$header->select('c.customer_name','sh.customer_po', DB::raw('date_format(tgl_order,"%d-%b-%Y %H:%i:%s") as tgl_order')
                           , 'sh.oracle_ship_to','sh.currency', 'sh.oracle_bill_to',DB::raw('"ENT" as ent')
                           ,DB::raw('"*NB" as nb'),'sh.id', 'ot.name as transaction_name','ql.name as price_name', 'c.customer_number','sh.warehouse','sh.notrx'
                          )
                  ->get();
      //dd($header);
        //$header=$header->toArray();
        //$data= json_decode( json_encode($header), true);
        //$i=0;            //  dd($header->toArray());
      $create= Excel::create('template_SO_Oracle', function($excel) use ($header ) {
        $excel->setTitle('Interface sales order');
        $excel->setCreator('Shanty')
          ->setCompany('Solinda');
        $excel->sheet('order', function($sheet) use ($header)
        {

          foreach($header as $h)
          {
            //$i++;
            if (Auth::user()->name=='YASA MITRA PERDANA, PT')
            {
              //if($h->)
              $warehouse = config('constant.def_warehouse_YMP');
            }elseif (Auth::user()->name=='GALENIUM PHARMASHIA LABORATOEIS, PT'){
              $warehouse = config('constant.def_warehouse_GPL');
            }
            $connoracle = DB::connection('oracle');
            if($connoracle){
              $oraheader = $connoracle->table('oe_order_headers_all oha')
                          //->where(DB::raw('nvl(oha.attribute1,oha.orig_sys_document_ref)'),'=',$h->notrx)
                          ->whereExists(function ($query) use($h) {
                                $query->select(DB::raw(1))
                                      ->from('oe_order_lines_all ola')
                                      ->whereRaw("ola.header_id = oha.header_id and nvl(ola.attribute1,ola.orig_sys_document_ref) = '".$h->notrx."'");
                            })
                          ->where('oha.cancelled_flag','!=','Y')
                          ->get();
              //dd($oraheader);
              if($oraheader->isEmpty())
              {
                $oraheader=null;
              }
            }else{
              $oraheader=null;
            }
            if(is_null($oraheader)){
              //$data = json_decode( json_encode($h), true);
                //$sheet->fromArray($h, null, 'A1', false, false);
                //$sheet->row($i,json_decode( json_encode($h), true));
                //$sheet->appendRow(json_decode( json_encode($h), true));
                $sheet->appendRow(array($h->customer_name,$h->customer_number,$h->transaction_name,$h->customer_po,$h->tgl_order
                                  ,$h->price_name,$h->oracle_ship_to,config('constant.def_salesperson'),'','',$h->currency,$h->oracle_bill_to
                                  ,'','','ENT','','',$warehouse,'*NB'));
                $line = DB::table('so_lines as sl')
                      ->join('products as p','sl.product_id','=','p.id')
                      ->where([
                        ['sl.header_id','=',$h->id]
                      ])->select('p.itemcode','sl.qty_confirm/sl.conversion_qty as qty_confirm','sl.uom','sl.line_id')
                      ->get();
                foreach($line as $l)
                {
                  $sheet->appendRow(array($l->itemcode,'',$l->qty_confirm,$l->uom,'','',$h->tgl_order,'','','','','','','','',$h->notrx,$l->line_id,'ENT','*DN'));

                }
                $sheet->appendRow(array('*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*SAVE','*PB','*IR'));
            }


          }
          $sheet->protect('GPLJanganDiub4h');
        });
      })->export("xlsx");
      return redirect()->route('order.listPO');
      //return 1;
    }

    public function changeOrderUom(Request $request)
    {
      $line = DB::table('So_Lines_v')->where('line_id','=',$request->id)->first();
      $header = SoHeader::where('id','=',$line->header_id)->select('status','approve')->first();

      if ($request->satuan ==$line->uom)
      {
        $price = $line->unit_price;
        $qtyorder = $line->qty_request;
        if($header->status==0)
        {
            $qtyconfirm =$qtyorder;
            $qtykirim = $qtyorder;
            $qtyterima=$qtyorder;
        }elseif($header->status==1){
            $qtyconfirm =$line->qty_confirm;
            $qtykirim = $qtyconfirm;
            $qtyterima=$qtyconfirm;
        }


      }elseif ($request->satuan ==$line->uom_primary){
        $price = $line->unit_price/$line->conversion_qty;
        $qtyorder = $line->qty_request_primary;
        $qtyconfirm =$line->qty_confirm_primary;
        $qtykirim = $line->qty_shipping_primary;
        $qtyterima=$line->qty_accept_primary;
      }

      return response()->json([
                      'result' => 'success',
                      'price' => (float)$price,
                      'qtyorder' => (float)$qtyorder,
                      'qtyconfirm' => (float)$qtyconfirm,
                      'qtyshipping' =>$qtykirim,
                      'qtyaccept' =>$qtyterima
                    ],200);
    }

    public function shippingKurir(){
      return view('shop.kurir',['ship'=>null]);
    }

    public function searchShipping(Request $request){
      $this->validate($request, [
      'nosj' => 'required',
      ]);
      $sjnumber=$request->nosj;

      $ship = SoShipping::whereNotNull('source_header_id')
              //->join('so_header_v as sh','sh.id','=','so_shipping.header_id')
              //->join('so_lines_v as sl','sl.line_id','=','so_shipping.line_id')
              ->where(function($query) use ($sjnumber) {
                          $query->where('deliveryno','=',$sjnumber)
                          ->orWhere('waybill','=',$sjnumber);
                        })
            /*  ->select('sh.notrx','sh.customer_name','sh.distributor_name',
                    'sh.tgl_order','sh.ship_to_addr','sh.status', 'sl.title'
                    ,'so_shipping.qty_shipping'
                    ,'so_shipping.deliveryno'
                    ,'so_shipping.waybill'
                    ,'so_shipping.header_id'
                    ,'so_shipping.line_id'
                    ,'so_shipping.uom_primary'
                      )*/->get();
    $ship= $ship->groupBy('deliveryno');
      //dd($ship);
      return view('shop.kurir',compact('ship'));
    }

}
