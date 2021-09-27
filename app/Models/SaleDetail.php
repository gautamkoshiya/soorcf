<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded=[];
    protected $primaryKey = 'id';
    protected $table = 'sale_details';

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer','customer_id','id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    public function sale()
    {
        return $this->belongsTo('App\Models\Sale','sale_id','id');
    }

    public function vehicle()
    {
        return $this->belongsTo('App\Models\Vehicle','vehicle_id','id');
    }

    public function api_vehicle()
    {
        return $this->belongsTo('App\Models\Vehicle','vehicle_id','id')->withTrashed();
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Product','product_id','id');
    }

    public function api_product()
    {
        return $this->belongsTo('App\Models\Product','product_id','id')->withTrashed();
    }

    public function unit()
    {
        return $this->belongsTo('App\Models\Unit','unit_id','id');
    }
}
