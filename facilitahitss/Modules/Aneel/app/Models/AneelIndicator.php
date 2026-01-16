<?php

namespace Modules\Aneel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Aneel\Database\Factories\AneelIndicatorFactory;

class AneelIndicator extends Model
{
    use HasFactory;

    protected $table = 'aneel_indicators';

    protected $fillable = [
        'code',
        'name',
        'description',
        'service_level',
        'inputs'
    ];

    protected $casts = [
        'inputs' => 'array'
    ];

    public function reports()
    {
        return $this->hasMany(AneelReportIndicator::class, 'indicator_id');
    }

    public function checkStatus($value)
    {
        return self::evaluateCondition($value, $this->service_level);
    }

    private static function evaluateCondition($value, $serviceLevel)
    {
        if (preg_match('/(<=|>=|<|>|=)\s*([\d\.]+)/', $serviceLevel, $matches)) {
            $operator = $matches[1]; 
            $threshold = floatval($matches[2]); 

            switch ($operator) {
                case '>=': return $value >= $threshold ? 'Atingiu' : 'Não Atingiu';
                case '<=': return $value <= $threshold ? 'Atingiu' : 'Não Atingiu';
                case '>':  return $value > $threshold ? 'Atingiu' : 'Não Atingiu';
                case '<':  return $value < $threshold ? 'Atingiu' : 'Não Atingiu';
                case '=':  return $value == $threshold ? 'Atingiu' : 'Não Atingiu';
                default:   return 'Indefinido';
            }
        }
        return 'Indefinido';
    }
}
