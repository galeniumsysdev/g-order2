<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
  use Uuids;

    public $incrementing = false;
    protected $fillable = ['imagePath','title','description','description_en','price','satuan_primary','satuan_secondary','inventory_item_id','itemcode','Enabled_Flag'];

    public function categories()
    {
       return $this->belongsToMany('App\Category','category_products','product_id','flex_value')->withTimestamps();
      /*return $this->hasManyThrough(
            'App\Category',
            'App\CategoryProduct',
            'product_id', // Foreign key on users table...
            'flex_value', // Foreign key on posts table...
            'id', // Local key on countries table...
            'flex_Value' // Local key on users table...
        );*/
    }



}
