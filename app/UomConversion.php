<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UomConversion extends Model
{
  protected $table = 'mtl_uom_conversions_v';

  protected $fillable = [
    'product_id','uom_code','rate'
  ];


}
