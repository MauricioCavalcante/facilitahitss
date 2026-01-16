<?php

namespace Modules\Ana\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ana\Models\AnaRelatorioExecutivo;
use App\Models\User;

class AnaRelatorioExecutivoValidar extends Model
{
    protected $table = 'ana_relatorio_executivo_validar';

    // Permitir atribuição em massa para os campos listados
    protected $fillable = [
        'relatorio_id',
        'status',
        'comentario',
        'editado_por',
        'validado_por',
        'data_validacao'
    ];

    // Desabilitar timestamps padrão do Laravel, pois a tabela usa um campo específico para data
    public $timestamps = false;

    /**
     * Relacionamento com a tabela ana_relatorio_executivos
     */
    public function relatorio()
    {
        return $this->belongsTo(AnaRelatorioExecutivo::class, 'relatorio_id');
    }

    /**
     * Relacionamento com a tabela users (quem validou o relatório)
     */
    public function validador()
    {
        return $this->belongsTo(User::class, 'validado_por');
    }
}
