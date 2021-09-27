<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded=[];

    protected $casts = [
        'id'=>'integer'
    ];

    protected $primaryKey = 'id';
    protected $table = 'regions';

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    public function city()
    {
        return $this->belongsTo('App\Models\City','city_id','id');
    }

    public function companies()
    {
        return $this->hasMany('App\Models\Company');
    }

    public function customers()
    {
        return $this->hasMany('App\Models\Customer');
    }

    public function suppliers()
    {
        return $this->hasMany('App\Models\Supplier');
    }

    public function employees()
    {
        return $this->hasMany('App\Models\Employee');
    }
}
