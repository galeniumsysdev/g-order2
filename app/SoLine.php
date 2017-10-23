<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SoLine extends Model
{
  protected $primaryKey = 'line_id';

  protected $fillable = [
      'line_id','header_id','product_id','uom','qty_request','qty_shipping','qty_accept','list_price','unit_price','amount','tax_amount','tax_type','discount','bonus','oracle_line_id','conversion_qty'
      ,'inventory_item_id','uom_primary','qty_confirm','disc_product_amount','disc_product_percentage','disc_reg_amount','disc_reg_percentage','qty_request_primary','qty_confirm_primary'
  ];

  public function header()
  {
    return $this->belongsTo('App\SoHeader','header_id');
  }

}
