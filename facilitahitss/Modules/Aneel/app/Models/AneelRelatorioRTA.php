<?php

namespace Modules\Aneel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AneelRelatorioRTA extends Model
{
    use HasFactory;

    protected $table = 'aneel_relatorio_rta';

    protected $fillable = [
        'nome',
        'arquivo',
        'tamanho',
        'status',
        'periodo_inicio',
        'periodo_fim',
    ];

    public function indicadores()
    {
        return $this->hasMany(AneelIndicadores::class, 'relatorio_id');
    }

    public function anexos()
    {
        return $this->hasMany(AneelRelatorioRTAAnexo::class, 'relatorio_id');
    }

    public function detalhes()
    {
        return $this->hasOne(AneelRelatorioRTADetalhe::class, 'relatorio_id');
    }
}
