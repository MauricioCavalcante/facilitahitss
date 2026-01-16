<?php

namespace Modules\Aneel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Aneel\Database\Factories\AneelReportAttachmentFactory;

class AneelReportAttachment extends Model
{
    use HasFactory;

    protected $table = 'aneel_report_attachments';

    protected $fillable = [
        'report_id',
        'name',
        'label',
        'mime_type',
        'size',
        'attachment'
    ];

    // Relacionamento com o relatório ao qual o anexo pertence
    public function report()
    {
        return $this->belongsTo(AneelReport::class, 'report_id');
    }
}
