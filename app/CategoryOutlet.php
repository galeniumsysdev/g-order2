<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoryOutlet extends Model
{
    protected $table = 'category_outlets';
    protected $fillable=['name','enable_flag'];

    public function customer(){
      return $this->hasMany('App\Customer');
    }
}
