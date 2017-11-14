<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SoShipping extends Model
{
  protected $table = 'so_shipping';
  protected $fillable = [
      'deliveryno','source_header_id','source_line_id','delivery_detail_id','product_id','uom','qty_request'
      ,'uom_primary','qty_request_primary','conversion_qty','qty_shipping','qty_accept','batchno','split_source_id','tgl_kirim'
      ,'tgl_terima','tgl_terima_kurir','userid_kurir','header_id', 'line_id'
  ];

  public function lines()
  {
    return $this->belongsTo('App\SoLine','line_id','line_id');
  }
  public function product()
  {
     return $this->belongsTo('App\Product');
  }

}
