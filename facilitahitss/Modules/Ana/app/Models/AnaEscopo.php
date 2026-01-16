<?php

namespace Modules\Ana\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnaEscopo extends Model
{
    use HasFactory;

    protected $table = 'ana_escopos';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'escopo',
        'coordenacao_id',
    ];

    /**
     * Relação com a coordenação (cada escopo pertence a uma coordenação).
     */
    public function coordenacao()
    {
        return $this->belongsTo(AnaCoordenacao::class, 'coordenacao_id');
    }

    /**
     * Relação com a tabela pivot ana_ordem_servicos_escopos.
     */
    public function ordensServicoEscopos()
    {
        return $this->hasMany(AnaOrdemServicoEscopo::class, 'escopo_id');
    }

    /**
     * Relação com usuários (um escopo pode ter muitos usuários).
     */
    public function users()
    {
        return $this->hasMany(AnaUser::class, 'escopo_id');
    }
}
