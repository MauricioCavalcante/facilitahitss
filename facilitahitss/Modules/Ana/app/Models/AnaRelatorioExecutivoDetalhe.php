<?php

namespace Modules\Ana\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnaRelatorioExecutivoDetalhe extends Model
{
    use HasFactory;

    protected $table = 'ana_relatorio_executivo_detalhes';

    // Especifica os campos que podem ser atribuídos em massa
    protected $fillable = [
        'relatorio_id',
        'titulo',
        'referencias',
        'atividades',
        'tarefas',
        'evidencias',
        'sei',
    ];

    // Relacionamento com a tabela `ana_relatorio_executivos`
    public function relatorio()
    {
        return $this->belongsTo(AnaRelatorioExecutivo::class, 'relatorio_id');
    }
}
