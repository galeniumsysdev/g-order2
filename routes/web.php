<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/',  [
  'uses' => 'ProductController@getIndex',
  'as' => 'product.index'
]);
Route::get('/getPrice',[
    'uses' => 'ProductController@getPrice'
    ,'as' => 'product.getPrice'
  ]);
Route::post('/ajax/changeOrderUom', 'OrderController@changeOrderUom');

Route::get('/ajax/getCity', 'UserController@getListCity');
Route::get('/ajax/getDistrict', 'UserController@getListDistrict');
Route::get('/ajax/getSubdistrict', 'UserController@getListSubDistrict');
Route::get('/ajax/typeaheadProvince', 'ProfileController@getListProvince');
Route::get('/ajax/typeaheadCity', 'ProfileController@getListCity');


Route::get('/ajax/shiptoaddr', 'CustomerController@ajaxSearchAlamat');

Route::get('detail/{id}', [
  'uses' => 'ProductController@show'
  ,'as' => 'product.detail'
]);
Route::match(['get', 'post'],'search_product', [
  'uses' => 'ProductController@search'
  ,'as' => 'product.search'
]);


Route::get('product/category/{id}', [
  'uses' => 'ProductController@category'
  ,'as' => 'product.category'
]);

Route::group(['middleware'=>'auth'],function(){
  Route::get('profile', 'ProfileController@profile')->name('profile.index');
  Route::post('profile', 'ProfileController@update_avatar')->name('profile.update');
  Route::post('add-address', 'ProfileController@addaddress')->name('profile.address');
  Route::post('add-contact', 'ProfileController@addcontact')->name('profile.contact');
  Route::get('/add_address', function () {
    return view('auth.profile.add_address');
  });
  Route::match(['patch','get'],'/edit_address/{id}', 'ProfileController@editaddress')->name('profile.edit_address');;
  //Route::get('/edit_address/{id}', 'ProfileController@editaddress')->name('profile.edit_address');;
  Route::get('/add_contact', function () {
      return view('auth.profile.add_contact');
  });
  Route::delete('remove-address/{id}', 'ProfileController@removeaddress')->name('profile.removeaddress');
  Route::delete('remove-contact/{id}', 'ProfileController@removecontact')->name('profile.removecontact');
  Route::get('/home', 'HomeController@index')->name('home');
  Route::post('/home', 'HomeController@search')->name('notif.search');
});

Route::group(['middleware'=>['permission:Create PO']],function(){
Route::get('/product/buy', 'ProductController@displayProduct')->name('product.buy');
Route::get('/shopping-cart',[
    'uses' => 'ProductController@getCart'
    ,'as' => 'product.shoppingCart'
  ]);
  Route::post('/checkout',[
      'uses' => 'ProductController@checkOut'
      ,'as' => 'product.checkOut'
    ]);

 Route::post('/add-to-cart/{id}',[
     'uses' => 'ProductController@getAddToCart'
     ,'as' => 'product.addToCart'
   ]);

   Route::post('/edit-item-cart/{id}',[
       'uses' => 'ProductController@getEditToCart'
       ,'as' => 'product.editToCart'
     ]);

    Route::get('/removeItem/{id}',[
      'uses' => 'ProductController@getRemoveItem'
      ,'as' => 'product.removeItem'
    ]);

    Route::post('/postcheckout',[
      'uses' => 'ProductController@postOrder'
      ,'as' => 'product.postOrder'
    ]);
  });



Auth::routes();

Route::group(['middleware' => ['web']], function () {
  Route::post('language-chooser','LanguageController@changeLanguage');
  Route::post('/language/',array(
    'before'=>'csrf',
    'as'=>'language-chooser',
    'uses'=>'LanguageController@changeLanguage',
    )
  );

});

Route::get('/users/confirmation/{token}','Auth\RegisterController@confirmation')->name('confirmation');
Route::get('/users/verification/{token}','Auth\RegisterController@verification_email')->name('verification');

Route::post('/register2','Auth\RegisterController@register2')->name('register2');

Route::group(['middleware' => ['role:IT Galenium']], function () {
  Route::resource('role',  'RoleController');
  Route::get('/rolepdf', [
    'uses' => 'RoleController@makePDF'
    ,'as' => 'role.rptpdf'
  ]);
  Route::resource('permission',  'PermissionController');
  Route::get('/admin',  [
    'uses' => function () {
        return view('admin.index',['menu'=>'blank']);
        //
    },
    'middleware' => ['role:IT Galenium'],
    'as' => 'admin.index'
  ]);
  Route::get('/admin/banner','BannerController@listBanner')->name('admin.banner');
  Route::get('/banner/publish/{publish}/{id}','BannerController@publish')->name('banner.publish');
  Route::delete('/banner/destroy/{id}','BannerController@destroy')->name('banner.destroy');
  Route::get('/banner/create','BannerController@create')->name('banner.create');
  Route::post('/banner/create','BannerController@store')->name('banner.store');
  Route::get('/banner/{id}/edit','BannerController@edit')->name('banner.edit');
  Route::patch('/banner/{id}','BannerController@update')->name('banner.update');

  Route::get('/showProduct', [
  'uses' => 'ProductController@index'
  ,'as' => 'product.show'
  ]);


  Route::get('/product/listparetoProduct','ProductController@listParetoProduct')->name('product.pareto');
  Route::get('/product/getParetoProduct','ProductController@getAjaxProduct')->name('product.getAjaxProduct');
  Route::post('/product/updatePareto','ProductController@updatePareto')->name('product.updatePareto');
  Route::delete('/product/updatePareto/{id}','ProductController@destroyPareto')->name('product.destroyPareto');

  Route::get('masterProduct/{id}', [
  'uses' => 'ProductController@master'
  ,'as' => 'product.master'
  ]);

  Route::post('/updateProduct/{id}', [
    'uses' => 'ProductController@update'
    ,'as' => 'product.update'
  ]);
  Route::get('/users/oracle','UserController@oracleIndex')->name('useroracle.index');
  Route::get('/users/oracle/{id}','UserController@oracleShow')->name('useroracle.show');
  Route::patch('/users/oracle/{id}','UserController@oracleUpdate')->name('useroracle.update');
  Route::get('/users/cabang/{parent_id}','UserController@cabangCreate')->name('usercabang.create');
  Route::post('/users/cabang/{parent_id}','UserController@cabangStore')->name('usercabang.store');
  Route::get('/users/cabang/edit/{id}','UserController@cabangEdit')->name('usercabang.edit');
  Route::patch('/users/cabang/edit/{id}','UserController@cabangUpdate')->name('usercabang.update');

  Route::resource('CategoryOutlet',  'Cat_OutletController');
  Route::resource('CategoryProduct',  'CategoryProductController');
  Route::resource('users','UserController');
  Route::resource('GroupDataCenter','DataCenterController');
  Route::resource('SubgroupDatacenter','SubgroupDCController');



});

Route::get('/manageOutlet/{id}/{notif_id}', 'CustomerController@show')->name('customer.show');
//Route::get('/searchNoo', 'CustomerController@searchNoo')->name('customer.searchNoo');
Route::get('customer/searchOutlet', 'CustomerController@searchOutlet')->name('customer.searchoutlet');


Route::group(['middleware' => ['permission:Outlet_Distributor']], function () {
  Route::match(['get','post'],'/searchNoo', 'CustomerController@listNoo')->name('customer.listNoo');
  Route::get('/searchCustomer/{search}/{id}','CustomerController@search');
  Route::get('/tambahDistributor/{id}/{outletid}','CustomerController@addlist');
  Route::patch('/saveOutlet/{customer}', 'CustomerController@update')->name('customer.update');
  Route::post('/rejectbyGPL','CustomerController@rejectGPL')->name('customer.rejectGPL');
  Route::get('/ajax-subcat',function () {
      $cat_id = Input::get('cat_id');
      $subcategories = App\SubgroupDatacenter::where('group_id','=',$cat_id)->get();
      return Response::json($subcategories);
  });
});

/*distributor or principal*/
Route::group(['middleware' => ['permission:ApproveOutlet']], function () {
  Route::post('/approveOutlet','CustomerController@approve')->name('customer.approve');
  Route::post('/rejectOutlet','CustomerController@reject')->name('customer.reject');
});
/*
Route::get('/csv/user', function()
{
    if (($handle = fopen(public_path() . '/uploads/user2.csv','r')) !== FALSE)
    {
        while (($data = fgetcsv($handle, 1000, ',')) !==FALSE)
        {
                $user = new \App\User();
                $user->id = Webpatser\Uuid\Uuid::generate();
                $user->name = $data[0];
                $user->email = $data[1];
                $user->password = bcrypt('123456');
                $user->customer_id = $data[2];
                $user->validate_flag = 1;
                $user->register_flag = 1;
                $user->avatar = 'default.jpg';
                $user->save();
        }
        fclose($handle);
    }

    return \App\User::all();
});

Route::get('/test', function () {
    return view('testtable');
});*/
/*check PO from Outlet/Distributor*/
Route::group(['middleware' => ['auth']], function () {
  Route::get('/checkPO/{id}','OrderController@checkOrder')->name('order.checkPO');
  Route::match(['get', 'post'],'/listpo','OrderController@listOrder')->name('order.listPO');
  Route::match(['get', 'post'],'/listso','OrderController@listSO')->name('order.listSO');
  Route::post('/SO/approval','OrderController@approvalSO')->name('order.approvalSO');
  Route::get('/notif/newpo/{notifid}/{id}','OrderController@readnotifnewpo')->name('order.notifnewpo');
  Route::post('/PO/batal','OrderController@batalPO')->name('order.cancelPO');
  //Route::post('/PO/Receive','OrderController@receivePO')->name('order.receivePO');
  Route::get('/download/PO/{file}', function ($file='') {
      return response()->download(storage_path('app/PO/'.$file));
  });
});
Route::get('/oracle/getOrder', 'BackgroundController@getStatusOrderOracle')->name('order.getStatusOracle');
Route::get('/oracle/exportexcel/{id}', 'OrderController@createExcel')->name('order.createExcel');
Route::get('/oracle/synchronize', 'BackgroundController@synchronize_oracle')->name('order.synchronizeOracle');
Route::get('/oracle/synchronizemodifier', 'BackgroundController@getModifierSummary');

Route::get('/oracle/getdiskon', 'BackgroundController@updateDiskonTable');
/*
Route::get('/test',function () {
  dd (DB::connection('oracle')->select('select name from hr_all_organization_units haou '));
});*/


Route::group(['middleware' => ['permission:UploadCMO']], function () {
    Route::get('uploadCMO', 'FilesController@upload')->name('files.uploadcmo');
    Route::post('/handleUpload', 'FilesController@handleUpload');
    Route::get('/downloadCMO/{file}', function ($file='') {
        return response()->download(storage_path('app/uploads/'.$file));
    });
});
Route::group(['middleware' => ['permission:DownloadCMO']], function () {
  Route::get('viewAlldownloadfile', 'FilesController@downfunc')->name('files.viewfile');
  Route::get('/downloadCMO/{file}', function ($file='') {
      return response()->download(storage_path('app/uploads/'.$file));
  });
  Route::post('/viewAlldownloadfile', 'FilesController@search')->name('files.postviewfile');
  route::get('notifrejectcmo/{notifid}/{id}','FilesController@readNotif')->name('files.readnotif');
});
Route::group(['middleware' => ['role:Principal']], function () {
  Route::put('/approvecmo/{id}', 'FilesController@approvecmo')->name('files.approvecmo');
});

/**
* created by WK Productions
*/
Route::get('/dpl/list/','DPLController@dplList')->name('dpl.list');
Route::get('/dpl/suggestno/form','DPLController@generateSuggestNoForm')->name('dpl.generateForm');
Route::post('/dpl/suggestno/generate','DPLController@generateExec')->name('dpl.generateExec');
Route::get('/dpl/suggestno/success','DPLController@generateSuccess')->name('dpl.generateSuccess');
Route::get('/dpl/suggestno/validation/{outlet_id}/{suggest_no}','DPLController@suggestNoValidation')->name('dpl.suggestNoValidation');
Route::get('/dpl/distlist/{outlet_id}','DPLController@getDistributorList')->name('dpl.distributorList');

Route::get('/dpl/discount/form/{suggest_no?}','DPLController@inputDiscount')->name('dpl.discountForm');
Route::post('/dpl/discount/set','DPLController@discountSet')->name('dpl.discountSet');
Route::get('/dpl/discount/approval/{suggest_no}','DPLController@discountApprovalForm')->name('dpl.discountApproval');
Route::post('/dpl/discount/approval','DPLController@discountApprovalSet')->name('dpl.discountApprovalSet');

Route::get('/dpl/discount/{suggest_no?}/{notifid?}','DPLController@readNotifDiscount')->name('dpl.readNotifDiscount');
Route::get('/dpl/approval/{suggest_no?}/{notifid?}','DPLController@readNotifApproval')->name('dpl.readNotifApproval');

Route::get('/dpl/history/{suggest_no}','DPLController@dplLogHistory')->name('dpl.dplHistory');

Route::get('/dpl/input/form/{suggest_no}','DPLController@dplNoInputForm')->name('dpl.dplNoForm');
Route::post('/dpl/input/set','DPLController@dplNoInputSet')->name('dpl.dplNoSet');

//Organization Structure
Route::get('/Organization','OrgStructureController@index')->name('org.list');
Route::get('/Organization/{user_id}/setting','OrgStructureController@setting')->name('org.setting');
Route::post('/Organization/{user_id}/setting/save','OrgStructureController@saveSetting')->name('org.saveSetting');

//Outlet Product and Stock
Route::get('/outlet/product/download/template/product','OutletProductController@downloadTemplateProduct')->name('outlet.downloadTemplateProduct');
Route::get('/outlet/product/import','OutletProductController@importProduct')->name('outlet.importProduct');
Route::post('/outlet/product/import/view','OutletProductController@importProductView')->name('outlet.importProductView');
Route::post('/outlet/product/import/process','OutletProductController@importProductProcess')->name('outlet.importProductProcess');

Route::get('/outlet/product/download/template/stock','OutletProductController@downloadTemplateStock')->name('outlet.downloadTemplateStock');
Route::get('/outlet/product/import/stock','OutletProductController@importProductStock')->name('outlet.importProductStock');
Route::post('/outlet/product/import/stock/view','OutletProductController@importProductStockView')->name('outlet.importProductStockView');
Route::post('/outlet/product/import/stock/process','OutletProductController@importProductStockProcess')->name('outlet.importProductStockProcess');

Route::get('/outlet/product/list','OutletProductController@listProductStock')->name('outlet.listProductStock');
Route::get('/outlet/product/form/{id?}','OutletProductController@formProduct')->name('outlet.formProduct');
Route::post('/outlet/product/submit','OutletProductController@submitProduct')->name('outlet.submitProduct');
Route::get('/outlet/product/delete/{id}','OutletProductController@deleteProduct')->name('outlet.deleteProduct');
Route::get('/outlet/product/detail/{product_id}','OutletProductController@detailProductStock')->name('outlet.detailProductStock');
Route::get('/outlet/product/getList','OutletProductController@getListProductStock')->name('outlet.getListProductStock');

Route::get('/outlet/transaction','OutletProductController@outletTrx')->name('outlet.trx');
Route::get('/outlet/transaction/list','OutletProductController@outletTrxList')->name('outlet.trxList');
Route::post('/outlet/transaction/in/process','OutletProductController@outletTrxInProcess')->name('outlet.trxInProcess');
Route::post('/outlet/transaction/out/process','OutletProductController@outletTrxOutProcess')->name('outlet.trxOutProcess');
/*
*
*/

Route::get('/displayproduct', function () {
    return view('shop.welcome');
});
