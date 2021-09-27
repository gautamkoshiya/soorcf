<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerAdvanceDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded=[];
    protected $primaryKey = 'id';
    protected $table = 'customer_advance_details';

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    public function customer_advance()
    {
        return $this->belongsTo('App\Models\CustomerAdvance','customer_advances_id','id');
    }

    public function sale()
    {
        return $this->belongsTo('App\Models\Sale','sale_id','id');
    }
}
