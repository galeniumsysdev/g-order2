<?php
namespace App\Observers;

use Illuminate\Support\Facades\Schema;
use Auth;

class UserActionsObserver
{
    public function creating($model)
    {
      if(Schema::hasColumn($model->getTable(), 'created_by') && Auth::check()) $model->created_by =Auth::user()->id;
    }

    public function saving($model)
    {
      if(Schema::hasColumn($model->getTable(), 'last_update_by') && Auth::check()) $model->last_update_by =Auth::user()->id;
    }

}
