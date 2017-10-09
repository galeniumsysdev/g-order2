<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Storage;
use App\FileCMO;
use App\Http\Requests;
use DB;
use Auth;
use File;

class FilesController extends Controller
{
  public function handleUpload(Request $request)
  {
      //if($request->hasFile('file')) {
          $file1 = $request->file('file1');
          $file2 = $request->file('file2');
          $distributorid=Auth::user()->customer_id;
          $allowedFileTypes = config('constant.allowedFileTypes');
          $maxFileSize = config('constant.maxFileSize');
          $version=0;
          $rules = [
              'file1' => 'required|mimes:pdf|max:10240',
              'file2' => 'required|mimes:xls,xlsx|max:10240',
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

      return redirect()->to('/uploadCMO')->withMessage('Data berhasil disimpan');
  }

  public function upload() {
      $directory = config('app.filesDestinationPath');
      $files = Storage::files('$directory');
      $period = date('M-Y', strtotime('+1 month'));

      $periodint = date('Ym', strtotime('+1 month'));

      //$files = UploadedFile::all();
      return view('files.upload')->with(array('files' => $files,'period'=>$period,'period_int'=>$periodint));
  }

  public function downfunc() {
        $downloads=DB::table('files_cmo')->where('distributor_id','=',Auth::user()->customer_id)->get();
        return view ('files.viewfile',compact('downloads'));
    }

}
