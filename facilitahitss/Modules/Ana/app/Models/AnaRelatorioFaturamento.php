<?php

namespace Modules\Ana\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnaRelatorioFaturamento extends Model
{
    use HasFactory;

    protected $table = 'ana_relatorio_faturamentos';

    // Definindo os campos que podem ser preenchidos em massa
    protected $fillable = [
        'nome',
        'arquivo',
        'tamanho',
        'numero_nota_fiscal',
        'data_vencimento',
        'data_inicio',
        'data_fim',
        'desconto',
        'valor_final'
    ];

    /**
     * Relacionamento com a tabela ana_relatorio_faturamento_os
     * Um relatório de faturamento pode ter várias ordens de serviço associadas
     */
    public function ordensServico()
    {
        return $this->hasMany(AnaRelatorioFaturamentoOS::class, 'relatorio_id');
    }
}
