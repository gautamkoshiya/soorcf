<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProformaInvoiceDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded=[];
    protected $primaryKey = 'id';
    protected $table = 'proforma_invoice_details';

    public function proforma_invoice()
    {
        return $this->belongsTo('App\Models\ProformaInvoice','proforma_invoice_id','id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Product','product_id','id');
    }

    public function unit()
    {
        return $this->belongsTo('App\Models\Unit','unit_id','id');
    }
}
