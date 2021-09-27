<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class lpo extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded=[];
    protected $primaryKey = 'id';
    protected $table = 'lpos';

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    public function lpo_details()
    {
        return $this->hasMany('App\Models\lpo_detail','lpo_id');
    }

    public function supplier()
    {
        return $this->belongsTo('App\Models\Supplier','customer_id','id');
    }

    public function project()
    {
        return $this->belongsTo('App\Models\Project','project_id','id');
    }
}
