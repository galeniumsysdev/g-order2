<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Category;
use App\Customer;
use Auth;
use Session;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
      view()->composer('layouts.navbar_product', function($view)
     {
      $product_flexfields = Category::where([//ProductFlexfield::where([
        ['enabled_flag','=','Y']
        ,['summary_flag','=','N']
        //,['flex_value_set_id','=',config('constant.flex_value_set_id')]
        ]);
        if(isset(Auth::user()->customer_id)){
          $oldDisttributor = Session::has('distributor_to')?Session::get('distributor_to'):null;
          $customer = Customer::find(Auth::user()->customer_id);
          if(is_null($oldDisttributor))
          {
            if($customer->psc_flag!="1" )
            {
              $product_flexfields =$product_flexfields->where('flex_value','not like','1%');
            }
            if($customer->pharma_flag!="1")
            {
              $product_flexfields =$product_flexfields->where('flex_value','not like','2%');
            }
            if($customer->export_flag!="1" )
            {
              $product_flexfields =$product_flexfields->where('flex_value','not like','3%');
            }
          }else{
            if($customer->psc_flag!="1" or $oldDisttributor['psc_flag']!="1")
            {
              $product_flexfields =$product_flexfields->where('flex_value','not like','1%');
            }
            if($customer->pharma_flag!="1" or $oldDisttributor['pharma_flag']!="1")
            {
              $product_flexfields =$product_flexfields->where('flex_value','not like','2%');
            }
            if($customer->export_flag!="1" or $oldDisttributor['export_flag']!="1")
            {
              $product_flexfields =$product_flexfields->where('flex_value','not like','3%');
            }

          }

        }
        $product_flexfields =$product_flexfields->orderBy('flex_value')->get() ;
        //View::share('product_flexfields', $product_flexfields);
        $view->with('product_flexfields', $product_flexfields);
      });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
