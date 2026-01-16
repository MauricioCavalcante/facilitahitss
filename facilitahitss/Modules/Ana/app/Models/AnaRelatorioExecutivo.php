<?php

namespace Modules\Ana\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class AnaRelatorioExecutivo extends Model
{
    use HasFactory;

    protected $table = 'ana_relatorio_executivos';

    protected $fillable = [
        'user_id',
        'ordem_servico_id',
        'nome',
        'tipo',
        'tamanho',
    ];

    // Define o relacionamento "belongsTo" com a tabela de Ordens de Serviço
    public function ordemServico()
    {
        return $this->belongsTo(AnaOrdemServico::class, 'ordem_servico_id');
    }

    // Define o relacionamento "belongsTo" com a tabela de Usuários
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalhes()
    {
        return $this->hasOne(AnaRelatorioExecutivoDetalhe::class, 'relatorio_id');
    }

    public function validacao()
    {
        return $this->hasOne(AnaRelatorioExecutivoValidar::class, 'relatorio_id');
    }
}
