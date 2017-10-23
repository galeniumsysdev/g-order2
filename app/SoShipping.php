<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SoShipping extends Model
{
  protected $fillable = [
      'line_id','header_id','product_id','uom','qty_request','qty_shipping','qty_accept','list_price','unit_price','amount','tax_amount','tax_type','discount','bonus','oracle_line_id','conversion_qty'
      ,'inventory_item_id','qty_confirm','disc_product_amount','disc_product_percentage','disc_reg_amount','disc_reg_percentage'
  ];
  public function lines()
  {
    return $this->belongsTo('App\SoLine','line_id');
  }
}
