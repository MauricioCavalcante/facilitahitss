<?php

namespace Modules\Ana\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnaOrdemServicoEscopo extends Model
{
    use HasFactory;

    protected $table = 'ana_ordem_servicos_escopos';

    protected $fillable = [
        'ordem_servico_id',
        'coordenacao_id',
        'escopo_id',
    ];

    /**
     * Relação com a ordem de serviço
     */
    public function ordemServico()
    {
        return $this->belongsTo(AnaOrdemServico::class, 'ordem_servico_id');
    }

    /**
     * Relação com a coordenação
     */
    public function coordenacao()
    {
        return $this->belongsTo(AnaCoordenacao::class, 'coordenacao_id');
    }

    /**
     * Relação com o escopo
     */
    public function escopo()
    {
        return $this->belongsTo(AnaEscopo::class, 'escopo_id');
    }
}
