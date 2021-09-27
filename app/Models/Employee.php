<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded=[];
    protected $primaryKey = 'id';
    protected $table = 'employees';

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function api_user()
    {
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    public function region()
    {
        return $this->belongsTo('App\Models\Region','region_id','id');
    }

    public function loans()
    {
        return $this->hasMany('App\Models\Loan');
    }

    public function account_transaction()
    {
        return $this->hasOne('App\Models\AccountTransaction');
    }

    public function designation()
    {
        return $this->belongsTo('App\Models\Designation','designation_id','id');
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Department','department_id','id');
    }

    public function gender()
    {
        return $this->belongsTo('App\Models\Gender','gender_id','id');
    }

    public function nationality()
    {
        return $this->belongsTo('App\Models\Nationality','nationality_id','id');
    }

    public function project()
    {
        return $this->belongsTo('App\Models\Project','UpdateDescription','id');
    }


}
