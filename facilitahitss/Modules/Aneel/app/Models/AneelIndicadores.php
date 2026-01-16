<?php

namespace Modules\Aneel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AneelIndicadores extends Model
{
    use HasFactory;

    protected $table; // A tabela será definida dinamicamente

    protected $fillable = [
        'chamadas_abandonadas', 'chamadas_total', 'chamadas_espera_60s',
        'qt10', 'qtotal', 'qtrci', 'qtr', 'qtaa', 'qta', 'qts_sbc', 'qts',
        'qtre', 'qc1', 'qc2', 'qc3', 'qc4', 'qus', 'qunr', 'qti', 'qtie',
        'qir', 'qrr', 'qtr', 'qti', 'qtd', 'qtga', 'qtgd', 'qtf', 'qted', 'qtsr',
        'qtpr', 'qtaap', 'qtos', 'resultado', 'relatorio_id',
    ];

    /**
     * Define a tabela do indicador dinamicamente.
     *
     * @param string $indicador Nome do indicador.
     * @return void
     */
    public function setTableForIndicator($indicador)
    {
        $this->table = 'aneel_indicador_' . strtolower($indicador);
    }

    /**
     * Retorna uma instância do modelo configurada para uma tabela específica.
     *
     * @param string $tableName Nome do indicador que será usado para definir a tabela.
     * @return self Nova instância do modelo com a tabela definida.
     */
    public static function tableForIndicator($tableName)
    {
        $instance = new self();
        $instance->setTable("aneel_indicador_{$tableName}");
        return $instance;
    }

    /**
     * Relacionamento com o modelo AneelRelatorioRTA.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function relatorio()
    {
        return $this->belongsTo(AneelRelatorioRTA::class, 'relatorio_id');
    }
}
