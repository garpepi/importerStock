<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class idxstockshistory extends Model
{
    //
	use SoftDeletes;
	protected $fillable = ['emiten_code','date','high','low','close','open','volume','frequency','status'];
	
}
