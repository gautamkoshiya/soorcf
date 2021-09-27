<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded=[];
    protected $primaryKey = 'id';
    protected $table = 'quotations';

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    public function quotation_details()
    {
        return $this->hasMany('App\Models\QuotationDetail','quotation_id');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer','customer_id','id');
    }

    public function project()
    {
        return $this->belongsTo('App\Models\Project','project_id','id');
    }
}
