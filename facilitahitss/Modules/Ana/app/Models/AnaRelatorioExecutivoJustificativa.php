<?php

namespace Modules\Ana\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use Modules\Ana\Models\AnaOrdemServico;

class AnaRelatorioExecutivoJustificativa extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'os_id',
        'justificativa',
        'status',
    ];

    // Relacionamento com User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relacionamento com OrdemServico
    public function os()
    {
        return $this->belongsTo(AnaOrdemServico::class, 'os_id');
    }
}
