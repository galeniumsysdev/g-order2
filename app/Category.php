<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
  protected $table = 'categories';
  protected $fillable=['flex_value','description','enabled_flag','summary_flag','parent'];
  protected $primaryKey = 'flex_value';
  public $incrementing = false;

  public function products()
  {
      return $this->belongsToMany('App\Product','category_products','flex_value','product_id')->withTimestamps();
  }
}
