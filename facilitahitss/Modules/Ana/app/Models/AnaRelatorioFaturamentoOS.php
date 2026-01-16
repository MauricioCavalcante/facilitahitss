<?php

namespace Modules\Ana\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnaRelatorioFaturamentoOS extends Model
{
    use HasFactory;

    protected $table = 'ana_relatorio_faturamento_os';

    // Definindo os campos que podem ser preenchidos em massa
    protected $fillable = [
        'relatorio_id',
        'os_id',
        'valor'
    ];

    /**
     * Relacionamento com o modelo AnaRelatorioFaturamento
     * Cada entrada pertence a um relatório de faturamento
     */
    public function relatorio()
    {
        return $this->belongsTo(AnaRelatorioFaturamento::class, 'relatorio_id');
    }

    /**
     * Relacionamento com o modelo AnaOrdemServico
     * Cada entrada pertence a uma ordem de serviço
     */
    public function ordemServico()
    {
        return $this->belongsTo(AnaOrdemServico::class, 'os_id');
    }
}
