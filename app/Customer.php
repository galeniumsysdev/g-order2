<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
  use Uuids;

  public $incrementing = false;
  protected $fillable = [
      'id','customer_number', 'customer_name','oracle_customer_id', 'status', 'customer_category_code','customer_class_code','primary_salesrep_id','tax_reference','tax_code','price_list_id','order_type_id','customer_name_phonetic','pharma_flag','psc_flag'
      ,'outlet_type_id','subgroup_dc_id','longitude','langitude','id_approval_psc','id_approval_pharma','date_approval_psc','date_approval_pharma','keterangan','export_flag'
  ];

  public function users()
  {
      return $this->hasMany('App\User','customer_id');
  }

  public function sites()
  {
      return $this->hasMany('App\CustomerSite');
  }

  public function contact()
  {
      return $this->hasMany('App\CustomerContact');
  }

  public function hasDistributor()
  {
    return $this->belongsToMany('App\Customer','outlet_distributor','outlet_id','distributor_id')->withTimestamps();;
  }

  public function subgroupdc()
  {
    return $this->belongsTo('App\SubgroupDatacenter','subgroup_dc_id');
  }

  public function categoryOutlet()
  {
    return $this->belongsTo('App\CategoryOutlet','outlet_type_id');
  }

  public function cmo()
  {
      return $this->hasMany('App\FileCMO');
  }



}
