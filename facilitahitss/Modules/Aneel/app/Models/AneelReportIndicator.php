<?php

namespace Modules\Aneel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Aneel\Database\Factories\AneelReportIndicatorFactory;

class AneelReportIndicator extends Model
{
    use HasFactory;

    protected $table = 'aneel_report_indicators';

    protected $fillable = [
        'report_id',
        'indicator_id',
        'inputs',
        'value',
        'name_attachment',
        'attachment',
        'mime',
        'status'
    ];

    protected $casts = [
        'inputs' => 'array',
        'value' => 'decimal:4'
    ];

    protected $attributes = [
        'attachment' => null,
        'name_attachment' => null,
        'mime' => null,
    ];
    
    public function report()
    {
        return $this->belongsTo(AneelReport::class, 'report_id');
    }

    public function indicator()
    {
        return $this->belongsTo(AneelIndicator::class, 'indicator_id');
    }

    public static function checkIndicatorStatus($value, $serviceLevel)
    {
        if (strcasecmp(trim($serviceLevel), 'Informativo') === 0) {
            return 'Calculado';
        }

        if (preg_match('/(<=|>=|<|>|=)\s*([\d,\.]+)%?/', $serviceLevel, $matches)) {
            $operator = $matches[1];
            $threshold = floatval(str_replace(',', '.', $matches[2]));
    
            return match ($operator) {
                '>=' => $value >= $threshold ? 'Atingiu' : 'Não Atingiu',
                '<=' => $value <= $threshold ? 'Atingiu' : 'Não Atingiu',
                '>'  => $value > $threshold  ? 'Atingiu' : 'Não Atingiu',
                '<'  => $value < $threshold  ? 'Atingiu' : 'Não Atingiu',
                '='  => $value == $threshold ? 'Atingiu' : 'Não Atingiu',
                default => 'Indefinido',
            };
        }
    
        return 'Indefinido';
    }
    
}
