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
use App\Notifications\RejectCmo;

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
              FileCMO::create([
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
      return view('files.upload')->with(array('files' => $files,'period'=>$period,'periodint'=>$periodint));
  }

  public function downfunc() {
       $bulan=date('m')+1;
       $tahun=date('Y');
       $distributor="";
       $status="";
        $downloads=DB::table('files_cmo')->join('customers','files_cmo.distributor_id','=','customers.id')
                  ->where('distributor_id','=',Auth::user()->customer_id)
                  ->where('tahun','=',$tahun)
                  ->where('bulan','=',$bulan)
                  ->select('file_excel','file_pdf','distributor_id','files_cmo.id','files_cmo.created_at','version','customer_name','period','approve')
                  ->orderBy('Period','desc')
                  ->get();
        return view ('files.viewfile',compact('downloads','bulan','tahun','distributor','status'));
    }
  public function search(Request $request){
      $bulan = $request->bulan;
      $tahun = $request->tahun;
      $distributor = $request->distributor;
      $status =$request->status;
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
      $downloads=$downloads->select('file_excel','file_pdf','distributor_id','files_cmo.id','files_cmo.created_at','version','customer_name','period','approve');
      //var_dump($downloads->toSql());
      $downloads=$downloads->orderBy('Period','desc')->orderBy('version', 'desc');
      $downloads=$downloads->get();
      return view ('files.viewfile',compact('downloads','bulan','tahun','distributor','status'));
  }

  public function approvecmo(Request $request, $id){
    var_dump($request->approve);
    if($request->approve=="approve")
    {
      DB::table('files_cmo')->where('id','=',$id)->wherenull('approve')
      ->update(['approve' => 1, 'first_download'=>Carbon::now(),'updated_at'=>Carbon::now()]);
    }elseif($request->approve=="reject"){
       DB::table('files_cmo')->where('id','=',$id)->wherenull('approve')
        ->update(['approve' => 0, 'first_download'=>Carbon::now(),'updated_at'=>Carbon::now()]);
        $cmo_distributor =FileCMO::find($id);
      $userdistributor =User::where('customer_id','=',$cmo_distributor->getDistributor->id)->first();
      if($userdistributor)
      {
          $userdistributor->notify(new RejectCmo($cmo_distributor));
      }
    }

    return $this->search($request);
    //return redirect()->route('files.postviewfile', ['request'=>$request]);
  }

  public function readNotif($notifid,$id)
  {
    Auth::User()->notifications()
               ->where('id','=',$notifid)
                 ->update(['read_at' => Carbon::now()]);
    return redirect()->route('files.uploadcmo');
  }



}
