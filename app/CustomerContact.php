<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerContact extends Model
{
  protected $fillable = [
      'contact_name','contact_type','contact','customer_id'
  ];
  protected $hidden = [
      'oracle_customer_id', 'account_number',
  ];

  public function customer()
  {
    return $this->belongsTo('App\Customer');
  }
}
