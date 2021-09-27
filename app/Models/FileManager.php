<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileManager extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded=[];
    protected $primaryKey = 'id';
    protected $table = 'file_managers';

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function report_type()
    {
        return $this->belongsTo('App\Models\ReportFileType','report_type_id','id');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id','id');
    }
}
