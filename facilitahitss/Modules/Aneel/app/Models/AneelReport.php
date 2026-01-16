<?php

namespace Modules\Aneel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Aneel\Database\Factories\AneelReportFactory;

class AneelReport extends Model
{
    use HasFactory;

    protected $table = 'aneel_reports';

    protected $fillable = [
        'name',
        'attachment',
        'attachment_size',
        'period_start',
        'period_end',
        'justification1',
        'justification2',
        'xlsx_name', 
        'xlsx_attachment', 
        'xlsx_attachment_size', 
        'xlsx_mime_type',
        'status'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    // Relacionamento com os anexos
    public function attachments()
    {
        return $this->hasMany(AneelReportAttachment::class, 'report_id');
    }

    // Relacionamento com os indicadores calculados
    public function indicators()
    {
        return $this->hasMany(AneelReportIndicator::class, 'report_id');
    }
    
}