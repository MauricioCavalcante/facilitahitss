<?php

namespace Modules\Ana\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Modules\Ana\Models\AnaUser;
use Modules\Ana\Models\AnaCoordenacao;
use Modules\Ana\Models\AnaEscopo;

class AnaProfileController extends Controller
{
    /**
     * Exibe a página de edição do perfil do usuário ANA.
     */
    public function index($id)
    {
        $user = User::find($id);


        if (!$user) {
            return redirect()->route('profile.index', ['id' => Auth::user()->id])->with('error', 'Usuário não encontrado');
        }

        $anaUser = AnaUser::where('user_id', $user->id)->first();

        if (!$anaUser) {
            return redirect()->route('profile.index', ['id' => Auth::user()->id])->with('error', 'Perfil não encontrado no módulo ANA');
        }
        $coordenacoes = AnaCoordenacao::all();

        $escopos = $anaUser->coordenacao_id ? AnaEscopo::where('coordenacao_id', $anaUser->coordenacao_id)->get() : collect([]);
        $email_ana = $anaUser->email_ana ?? 'Não definido';

        return view('ana::profile.editar', compact('user', 'anaUser', 'coordenacoes', 'escopos', 'email_ana'));
    }

    /**
     * Atualiza o perfil do usuário.
     */
    public function atualizarPerfil(Request $request, $user_id)
    {
        $request->validate([
            'email_ana' => 'nullable|email',
        ]);

        $usuario = AnaUser::where('user_id', $user_id)->firstOrFail();
        $usuario->update([
            'email_ana' => $request->email_ana,
        ]);

        return redirect()->route('ana::profile.index', ['id' => $user_id])->with('success', 'Perfil atualizado com sucesso.')->withInput();

    }
}
