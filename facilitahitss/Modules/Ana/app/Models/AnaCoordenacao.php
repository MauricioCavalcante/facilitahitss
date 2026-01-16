<?php

namespace Modules\Ana\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnaCoordenacao extends Model
{
    use HasFactory;

    protected $table = 'ana_coordenacoes';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'codigo',
        'nome',
    ];

    /**
     * Relação com os escopos da coordenação.
     */
    public function escopos()
    {
        return $this->hasMany(AnaEscopo::class, 'coordenacao_id');
    }

    /**
     * Relação com a tabela pivot ana_ordem_servicos_escopos.
     */
    public function escoposRelacionados()
    {
        return $this->hasMany(AnaOrdemServicoEscopo::class, 'coordenacao_id');
    }

    /**
     * Relação com as ordens de serviço (muitos para muitos).
     */
    public function ordensServico()
    {
        return $this->belongsToMany(AnaOrdemServico::class, 'ana_ordem_servicos_coordenacoes', 'coordenacao_id', 'ordem_servico_id');
    }
}
