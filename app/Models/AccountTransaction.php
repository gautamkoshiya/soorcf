<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountTransaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded=[];
    protected $primaryKey = 'id';
    protected $table = 'account_transactions';

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer','customer_id','id');
    }

    public function investor()
    {
        return $this->belongsTo('App\Models\Investor','investor_id','id');
    }

    public function employee()
    {
        return $this->belongsTo('App\Models\Employee','employee_id','id');
    }

    public function supplier()
    {
        return $this->belongsTo('App\Models\Supplier','supplier_id','id');
    }

}
