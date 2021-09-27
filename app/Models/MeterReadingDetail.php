<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeterReadingDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded=[];
    protected $primaryKey = 'id';
    protected $table = 'meter_reading_details';

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }

    public function meter_reading()
    {
        return $this->belongsTo('App\Models\MeterReading','meter_reading_id','id');
    }

    public function meter_reader()
    {
        return $this->belongsTo('App\Models\MeterReader','meter_reader_id','id');
    }
}
