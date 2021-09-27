<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxInvoicePayments extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded=[];
    protected $primaryKey = 'id';
    protected $table = 'tax_invoice_payments';

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    public function invoice()
    {
        return $this->belongsTo('App\Models\TaxInvoice','tax_invoice_id','id');
    }
}
