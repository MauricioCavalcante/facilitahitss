<?php

namespace Modules\Ana\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class AnaOrdemServico extends Model
{
    use HasFactory;

    protected $table = 'ana_ordem_servicos';

    protected $fillable = [
        'numero',
        'documento',
        'status',
        'data_inicio',
        'data_fim',
        'prazo',
        'horas',
        'endereco'
    ];

    /**
     * Relação com as coordenações (muitos para muitos através da tabela pivot)
     */
    public function coordenacoes()
    {
        return $this->belongsToMany(AnaCoordenacao::class, 'ana_ordem_servicos_coordenacoes', 'ordem_servico_id', 'coordenacao_id');
    }

    /**
     * Relação com usuários (muitos para muitos através da tabela pivot)
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'ana_ordem_servicos_users', 'ordem_servico_id', 'user_id');
    }

    /**
     * Relação com os escopos através da tabela pivot ana_ordem_servicos_escopos
     */
    public function escopos()
    {
        return $this->belongsToMany(AnaEscopo::class, 'ana_ordem_servicos_escopos', 'ordem_servico_id', 'escopo_id');
    }

    /**
     * Relação com a tabela auxiliar ana_ordem_servicos_escopos
     */
    public function ordemservicoEscopo()
    {
        return $this->hasMany(AnaOrdemServicoEscopo::class, 'ordem_servico_id');
    }

    /**
     * Relação com relatórios vinculados à Ordem de Serviço
     */
    public function relatorios()
    {
        return $this->hasMany(AnaRelatorioExecutivo::class, 'ordem_servico_id');
    }
}
