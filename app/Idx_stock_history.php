<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\softDeletes;
class Idx_stock_history extends Model
{
  //
  use SoftDeletes;
  protected $fillable = ['emiten_code','date','high','low','close','open','volume','frequency','status','deleted_at'];
}
