<?php

namespace Modules\Ana\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use Modules\Ana\Models\AnaCoordenacao;
use Modules\Ana\Models\AnaEscopo;

class AnaUser extends Model
{
    use HasFactory;

    protected $table = 'ana_users';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'email_ana',
        'coordenacao_id',
        'escopo_id'
    ];

    /**
     * Relação com a tabela users (cada AnaUser pertence a um usuário do sistema).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relação com a tabela ana_coordenacoes (cada AnaUser pertence a uma coordenação).
     */
    public function coordenacao()
    {
        return $this->belongsTo(AnaCoordenacao::class, 'coordenacao_id');
    }

    /**
     * Relação com a tabela ana_escopos (cada AnaUser pertence a um escopo).
     */
    public function escopo()
    {
        return $this->belongsTo(AnaEscopo::class, 'escopo_id');
    }
}
