<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseInvoice extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded=[];
    protected $primaryKey = 'id';
    protected $table = 'purchase_invoices';

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    public function purchase_invoice_details()
    {
        return $this->hasMany('App\Models\PurchaseInvoiceDetail','purchase_invoice_id');
    }

    public function supplier()
    {
        return $this->belongsTo('App\Models\Supplier','supplier_id','id');
    }

    public function project()
    {
        return $this->belongsTo('App\Models\Project','project_id','id');
    }
}
