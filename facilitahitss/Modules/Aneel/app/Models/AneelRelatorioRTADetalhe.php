<?php

namespace Modules\Aneel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AneelRelatorioRTADetalhe extends Model
{
    use HasFactory;

    protected $table = 'aneel_relatorio_rta_detalhes';

    protected $fillable = [
        'relatorio_id',
        'justificativa1',
        'justificativa2',
    ];

    public function relatorio()
    {
        return $this->belongsTo(AneelRelatorioRTA::class, 'relatorio_id');
    }
}
