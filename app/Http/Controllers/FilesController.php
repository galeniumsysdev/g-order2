<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Storage;
use App\FileCMO;
use App\Http\Requests;
use App\User;
use DB;
use Auth;
use File;
use Carbon\Carbon;
//use App\Notifications\RejectCmo;
use App\Events\PusherBroadcaster;
use App\Notifications\PushNotif;

class FilesController extends Controller
{
  public function handleUpload(Request $request)
  {
      //if($request->hasFile('file')) {
          $file1 = $request->file('filepdf');
          $file2 = $request->file('fileexcel');
          $distributorid=Auth::user()->customer_id;
          $allowedFileTypes = config('constant.allowedFileTypes');
          $maxFileSize = config('constant.maxFileSize');
          $version=0;
          $rules = [
              'filepdf' => 'required|mimes:pdf',
              'fileexcel' => 'required|mimes:xls,xlsx',
          ];
          $version =DB::table('files_cmo')->where([
            ['distributor_id','=',Auth::user()->customer_id],
            ['period','=',$request->period]
            ])->max('version');
            if(is_null($version))
            {
              $version=0;
            }else{
              $version=$version+1;
            }

          $this->validate($request, $rules);
          $fileName1 = $request->period.'_'.$distributorid.'_'.$version.'.'.$file1->getClientOriginalExtension();
          $fileName2 = $request->period."_".$distributorid."_".$version.".".$file2->getClientOriginalExtension();
          //$fileName1 = $file1->getClientOriginalName()."_".$version;
          //$fileName2 = $file2->getClientOriginalName()."_".$version;
          //dd($fileName1."-".$fileName2);
          $destinationPath = config('constant.fileDestinationPath');
          $uploaded = Storage::put($destinationPath.'/'.$fileName1, file_get_contents($file1->getRealPath()));
          $uploaded2 = Storage::put($destinationPath.'/'.$fileName2, file_get_contents($file2->getRealPath()));

          $bln=substr($request->period,-2,2);
          $thn=substr($request->period,0,4);
        //dd($bln.'-'.$thn."-".$version);
          if($uploaded) {
              $filecmo = FileCMO::create([
                'distributor_id' =>Auth::user()->customer_id,
                'version'=>$version,
                'period'=>$request->period,
                'bulan'=>$bln,
                'tahun'=>$thn,
                'file_pdf' => $fileName1,
                'file_excel' => $fileName2
              ]);
          }
      //}
      $content = 'Distributor '.Auth::user()->name.' telah mengupload file CMO period:'. $request->period;
      if($version!=0)
      {
        $content .=  'versi ke '.$version.'<br>';
      }
      $content .='Silahkan buka aplikasi eOrder untuk memdownload file.<br>' ;
      $data=[
        'title' => 'Upload CMO',
        'message' => 'File CMO period #'.$request->period.' versi '.$version.' telah diupload oleh '.Auth::user()->name,
        'id' => $filecmo->id,
        'href' => route('files.readnotif'),
        'mail' => [
          'greeting'=>'File CMO period #'.$request->period.' oleh '.Auth::user()->name,
          'content' =>$content,
        ]
      ];
      $cust_Yasa=config('constant.customer_yasa', 'GPL1000001');
      $userYasa = User::whereExists(function ($query) use($cust_Yasa) {
            $query->select(DB::raw(1))
                  ->from('customers as c')
                  ->whereRaw("users.customer_id = c.id and c.customer_number = '".$cust_Yasa."'");
        })->get();
      if($userYasa)
      {
        foreach($userYasa as $yasa){
          $data['email'] = $yasa->email;
          $yasa->notify(new PushNotif($data));
        }
      }
      return redirect()->to('/uploadCMO')->withMessage(trans('pesan.successupload'));
  }

  public function upload() {
      /*$directory = config('constant.fileDestinationPath');
      $files = Storage::files('$directory');
      var_dump($files);*/
      $period = date('M-Y', strtotime('+1 month'));

      $periodint = date('Ym', strtotime('+1 month'));
      //var_dump($periodint);
      $files = DB::table('files_cmo')
              ->where([
                        ['distributor_id','=',Auth::user()->customer_id],
                        ['period','=',$periodint]
                      ])
              ->where(function ($query) {
                $query->whereNull('approve')
                      ->orwhere('approve', '=', 1);
              })->latest()->first();
      $filereject = DB::table('files_cmo')
              ->where([
                        ['distributor_id','=',Auth::user()->customer_id],
                        ['period','=',$periodint]
                      ])
              ->where('approve', '=', 0)
              ->latest()->first();
      return view('files.upload')->with(array('files' => $files,'period'=>$period,'periodint'=>$periodint,'filereject'=>$filereject));
  }

  public function downfunc($id = '') {
       if($id){
         $downloads=DB::table('files_cmo')->join('customers','files_cmo.distributor_id','=','customers.id')
                   ->where('files_cmo.id','=',$id)
                   ->select('file_excel','file_pdf','distributor_id','files_cmo.id','files_cmo.created_at','version','customer_name','period','approve','bulan','tahun','files_cmo.keterangan')
                   ->orderBy('Period','desc')
                   ->get();
         $distributor = $downloads->first()->distributor_id;
         $bulan = $downloads->first()->bulan;
         $tahun = $downloads->first()->tahun;
         $status= $downloads->first()->approve;
         //dd($downloads);
       }else{
         $bulan=date('m')+1;
         $tahun=date('Y');
         $distributor=null;
         $status="";
         $id=null;
          $downloads=DB::table('files_cmo')->join('customers','files_cmo.distributor_id','=','customers.id')
                    ->where('distributor_id','=',Auth::user()->customer_id)
                    ->where('tahun','=',$tahun)
                    ->where('bulan','=',$bulan)
                    ->select('file_excel','file_pdf','distributor_id','files_cmo.id','files_cmo.created_at','version','customer_name','period','approve','files_cmo.keterangan')
                    ->orderBy('Period','desc')
                    ->get();
        }
        return view ('files.viewfile',compact('downloads','bulan','tahun','distributor','status','id'));
    }

  public function search(Request $request){
      $bulan = $request->bulan;
      $tahun = $request->tahun;
      $distributor = $request->distributor;
      $status =$request->status;
      $id=null;

      $downloads=DB::table('files_cmo')->join('customers','files_cmo.distributor_id','=','customers.id');
      if(Auth::user()->hasRole('Principal'))
      {
        if($request->distributor!="")
        {
            $downloads=$downloads->where('customer_name','like',$request->distributor.'%');
        }
        if($request->status!="%")
        {
          if($request->status=="")
          {
            $downloads=$downloads->whereNull('files_cmo.approve');
          }else{
            $downloads=$downloads->where('files_cmo.approve','=',$request->status);
          }
        }
      }else{
        $downloads=$downloads->where('distributor_id','=',Auth::user()->customer_id);
      }
      if($request->tahun!="")
      {
          $downloads=$downloads->where('tahun','=',$request->tahun);
      }
      if($request->bulan!="")
      {
          $downloads=$downloads->where('bulan','=',$request->bulan);
      }
      $downloads=$downloads->select('file_excel','file_pdf','distributor_id','files_cmo.id','files_cmo.created_at','version','customer_name','period','approve','files_cmo.keterangan');
      //var_dump($downloads->toSql());
      $downloads=$downloads->orderBy('Period','desc')->orderBy('version', 'desc');
      $downloads=$downloads->get();
      return view ('files.viewfile',compact('downloads','bulan','tahun','distributor','status','id'));
  }

  public function approvecmo(Request $request, $id){
    DB::beginTransaction();
    try{
      $cmo_distributor = FileCMO::find($id);
      if($cmo_distributor){
        $oldstatus =$cmo_distributor->approve;
        if($request->approve=="approve")
        {
          $cmo_distributor->approve=1;
          $cmo_distributor->first_download=Carbon::now();
          $cmo_distributor->save();
          /*DB::table('files_cmo')->where('id','=',$id)->wherenull('approve')
          ->update(['approve' => 1, 'first_download'=>Carbon::now(),'updated_at'=>Carbon::now()]);*/
          //$cmo_distributor =FileCMO::find($id);
          $userdistributor =User::where('customer_id','=',$cmo_distributor->getDistributor->id)->first();
          $content = 'File CMO Anda untuk period:'.$cmo_distributor->period.' telah diperiksa dan diterima oleh Yasa Mitra Perdana.';
          $content .='Terimakasih telah menggupload menggunakan '.config('app.name').'.<br>' ;
          $data=[
            'title' => 'Konfirmasi CMO Oleh Yasa',
            'message' => 'File CMO period #'.$cmo_distributor->period.' telah diterima',
            'id' => $cmo_distributor->id,
            'href' => route('files.readnotif'),
            'mail' => [
              'greeting'=>'File CMO period #'.$cmo_distributor->period.' telah diterima',
              'content' =>$content,
            ]
          ];
        }elseif($request->approve=="reject"){
          $this->validate($request, [
          'reason_reject' => 'required',
          ]);
          $cmo_distributor->approve=0;
          $cmo_distributor->first_download=Carbon::now();
          $cmo_distributor->keterangan=$request->reason_reject;
          $cmo_distributor->save();
          $userdistributor =User::where('customer_id','=',$cmo_distributor->getDistributor->id)->first();
          $content = 'Mohon maaf, Harap upload kembali file CMO Anda untuk period:'.$cmo_distributor->period.'.';
          $content .='Silahkan konfirmasi ke Yasa Mitra Perdana untuk penjelasan lebih detail.<br>' ;
          $message = 'File CMO period #'.$cmo_distributor->period.' ditolak';
          if (isset($request->reason_reject))
          {
            $message .= " dengan alasan: ". $request->reason_reject;
          }
          $data=[
            'title' => 'Penolakan CMO Oleh Yasa',
            'message' => $message,
            'id' => $cmo_distributor->id,
            'href' => route('files.readnotif'),
            'mail' => [
              'greeting'=>'File CMO period #'.$cmo_distributor->period.' ditolak',
              'content' =>$content,
            ]
          ];
        }
        if($userdistributor and is_null($oldstatus))
        {
          $data['email'] = $userdistributor->email;
          $userdistributor->notify(new PushNotif($data));
        }
      }
      DB::commit();
      //return $this->search($request);
      return redirect()->route('files.viewfile',['id'=>$id]);
    }catch (\Exception $e) {
      DB::rollback();
      throw $e;
    }
    //return redirect()->route('files.postviewfile', ['request'=>$request]);
  }

  public function readNotif($id,$notifid)
  {
    $notif = Auth::User()->notifications()
               ->where('id','=',$notifid)->first();
               //->update(['read_at' => Carbon::now()])
    if($notif){
      $notif->read_at = Carbon::now();
      $notif->save();
      if($notif->data['tipe']=="Upload CMO") return redirect()->route('files.viewfile',['id'=>$notif->data['id']]);
      else return redirect()->route('files.uploadcmo');
    }

  }



}
