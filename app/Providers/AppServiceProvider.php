<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Category;
use App\Customer;
use Auth;
use Session;
use App\Banner;
use DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
      view()->composer(['layouts.navbar_product','shop.product'], function($view)
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
              $product_flexfields =$product_flexfields->where('parent','not like','PSC');
            }
            if($customer->pharma_flag!="1")
            {
              $product_flexfields =$product_flexfields->where('parent','not like','PHARMA');
            }
            if($customer->export_flag!="1" )
            {
              $product_flexfields =$product_flexfields->where('parent','not like','INTERNATIONAL');
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
          if($customer->tollin_flag==1)
          {
            $product_flexfields =$product_flexfields->whereRaw("parent='TollIn'
                                  and exists (select 1 from category_products as cp, products as p, qp_list_lines_v as qll
                                              where cp.flex_value=categories.flex_Value
                                              and cp.product_id = p.id
                                              and p.inventory_item_id = qll.product_attr_value
                                              and qll.list_header_id = '".$customer->price_list_id."')");

          }
          if(Auth::user()->hasRole('Apotik/Klinik') or Auth::user()->hasRole('Outlet'))
          {
            $product_flexfields = $product_flexfields->where('description','<>','BPJS');
          }

        }
        //dd($product_flexfields->toSQL());
        $product_flexfields =$product_flexfields->orderBy('flex_value')->get() ;
        //dd(DB::getQueryLog());
        //View::share('product_flexfields', $product_flexfields);
        $view->with('product_flexfields', $product_flexfields);
      });
     view()->composer('shop.carausel', function($view)
     {
       $banners = Banner::where('publish_flag','=','Y')->orderBy('id')->get();
       $view->with('banners', $banners);
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
