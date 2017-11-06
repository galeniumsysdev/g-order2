<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubgroupDatacenter extends Model
{
    protected $fillable=['name','display_name','enabled_flag','group_id'];

    public function groupdatacenter()
    {
      return $this->belongsTo('App\GroupDatacenter','group_id');
    }

    public function customer(){
      return $this->hasMany('App\Customer','subgroup_dc_id');
    }
}
