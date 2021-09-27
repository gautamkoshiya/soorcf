<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerAdvance extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded=[];
    protected $primaryKey = 'id';
    protected $table = 'customer_advances';

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer','customer_id','id');
    }

    public function api_customer()
    {
        return $this->belongsTo('App\Models\Customer','customer_id','id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    public function bank()
    {
        return $this->belongsTo('App\Models\Bank','bank_id','id');
    }

    public function customer_advance_details()
    {
        return $this->hasMany('App\Models\CustomerAdvanceDetail','customer_advances_id');
    }
}
