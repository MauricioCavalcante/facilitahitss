<?php

namespace Modules\Aneel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AneelRelatorioRTAAnexo extends Model
{
    use HasFactory;

    protected $table = 'aneel_relatorio_rta_anexos';

    protected $fillable = [
        'relatorio_id',
        'nome_arquivo',
        'tipo',
        'arquivo',
    ];

    public function relatorio()
    {
        return $this->belongsTo(AneelRelatorioRTA::class, 'relatorio_id');
    }
}
