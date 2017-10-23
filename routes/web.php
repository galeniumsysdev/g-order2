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

Route::get('/ajax/getCity', 'UserController@getListCity');
Route::get('/ajax/getDistrict', 'UserController@getListDistrict');
Route::get('/ajax/getSubdistrict', 'UserController@getListSubDistrict');  

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
  Route::get('/add_contact', function () {
      return view('auth.profile.add_contact');
  });
  Route::delete('remove-address/{id}', 'ProfileController@removeaddress')->name('profile.removeaddress');
  Route::delete('remove-contact/{id}', 'ProfileController@removecontact')->name('profile.removecontact');

});

Route::group(['middleware'=>['permission:Create PO']],function(){
Route::get('/shopping-cart',[
    'uses' => 'ProductController@getCart'
    ,'as' => 'product.shoppingCart'
  ]);
  Route::get('/checkout',[
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

    Route::post('/checkout',[
      'uses' => 'ProductController@postOrder'
      ,'as' => 'product.postOrder'
    ]);
  });



Auth::routes();



Route::get('/home', 'HomeController@index')->name('home');
Route::post('/home', 'HomeController@search')->name('notif.search');

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

  Route::get('/showProduct', [
  'uses' => 'ProductController@index'
  ,'as' => 'product.show'
  ]);

  Route::get('masterProduct/{id}', [
  'uses' => 'ProductController@master'
  ,'as' => 'product.master'
  ]);

  Route::post('/updateProduct/{id}', [
    'uses' => 'ProductController@update'
    ,'as' => 'product.update'
  ]);

  Route::resource('CategoryOutlet',  'Cat_OutletController');
  Route::resource('users','UserController');
});

Route::get('/manageOutlet/{id}/{notif_id}', 'CustomerController@show')->name('customer.show');

Route::group(['middleware' => ['permission:Outlet_Distributor']], function () {
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
  /*Route::post('/approveOutlet',[
  'uses' => 'CustomerController@approve'
  ,'as' => 'customer.approve'
  ,'middleware' => ['permission:ApproveOutlet']
]);*/
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

Route::get('/oracle/getOrder', 'BackgroundController@getStatusOrderOracle')->name('order.getStatusOracle');

/*
Route::get('/test',function () {
  dd (DB::connection('oracle')->select('select name from hr_all_organization_units haou '));
});*/
Route::group(['middleware' => ['permission:UploadCMO']], function () {
Route::get('uploadCMO', 'FilesController@upload')->name('files.uploadcmo');
    Route::post('/handleUpload', 'FilesController@handleUpload');
    Route::get('viewAlldownloadfile', 'FilesController@downfunc')->name('files.viewfile');
    Route::get('/downloadCMO/{file}', function ($file='') {
        return response()->download(storage_path('app/uploads/'.$file));
  });
});