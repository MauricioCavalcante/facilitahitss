<?php

namespace Modules\Ana\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Modules\Ana\Models\AnaUser;
use Modules\Ana\Models\AnaCoordenacao;
use Modules\Ana\Models\AnaEscopo;
use Modules\Ana\Models\AnaOrdemServico;
use Modules\Ana\Http\Helpers\RelatorioStatusHelper;

class AnaUserController extends Controller
{
    /**
     * Exibe a página inicial da ANA com informações sobre as Ordens de Serviço e status dos relatórios.
     *
     * @return \Illuminate\View\View Retorna a view da página inicial.
     */
    public function index()
    {

        $user = Auth::user();
        $prazoFinalFormatado = null;
        $prazoExpirado = false;
        $statusMensagem = null;
        $mensagemJustificativas = null;

        if ($user->role === 'user') {
            $ordens_servico = AnaOrdemServico::whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('status', 'Em andamento')->get();

            $osEmAndamento = $ordens_servico->first();
            if ($osEmAndamento) {
                $statusData = RelatorioStatusHelper::verificarStatus($user, $osEmAndamento);
                $prazoFinalFormatado = $statusData['prazoFinalFormatado'];
                $prazoExpirado = $statusData['prazoExpirado'];
                $statusMensagem = $statusData['statusMensagem'];
            }
        } else {
            $ordens_servico = AnaOrdemServico::where('status', 'Em andamento')->get();

            // Se o usuário for admin, buscar justificativas pendentes
            if ($user->role === 'admin') {
                $mensagemJustificativas = RelatorioStatusHelper::obterMensagemJustificativasPendentes();
            }
        }

        foreach ($ordens_servico as $os) {
            $usuariosComRelatorio = $os->relatorios->pluck('user_id')->toArray();

            $usuariosAssociados = $os->users ?? collect();

            $os->usuariosSemRelatorio = $usuariosAssociados->reject(function ($user) use ($usuariosComRelatorio) {
                return in_array($user->id, $usuariosComRelatorio);
            })->map(function ($user) {
                $user->nome = $user->name ?? 'Usuário não encontrado';
                return $user;
            })->values();
        }

        return view('ana::index', compact(
            'ordens_servico',
            'prazoFinalFormatado',
            'prazoExpirado',
            'statusMensagem',
            'mensagemJustificativas'
        ));
    }

    public function painel()
    {

        $moduleId = 1;
        $usuarios = User::whereHas('modules', function ($query) use ($moduleId) {
            $query->where('module_id', $moduleId);
        })
        ->where('role', 'admin')
        ->with(['anaUser.coordenacao'])->get();

        return view('ana::usuarios.painelUsuario', compact('usuarios'));
    }


    public function edit($id)
    {
        $user = User::findOrFail($id);
        $usuario = AnaUser::where('user_id', $id)->first();

        if (!$usuario) {
            $usuario = new AnaUser();
            $usuario->user_id = $id;
            $usuario->save();
        }

        $coordenacoes = AnaCoordenacao::all();
        $escopos = AnaEscopo::where('coordenacao_id', $usuario->coordenacao_id)->get();

        return view('ana::usuarios.editarUsuario', compact('user','usuario', 'coordenacoes', 'escopos'));
    }


    public function update(Request $request, $user_id)
    {
        $request->validate([
            'email_ana' => 'nullable|email',
            'coordenacao_id' => 'required|exists:ana_coordenacoes,id',
            'escopo_id' => 'required|exists:ana_escopos,id',
        ]);

        $usuario = AnaUser::where('user_id', $user_id)->firstOrFail();
        $usuario->update([
            'email_ana' => $request->email_ana,
            'coordenacao_id' => $request->coordenacao_id,
            'escopo_id' => $request->escopo_id,
        ]);

        return redirect()->route('ana::usuarios.painel', $user_id)->with('success', 'Usuário atualizado com sucesso!');
    }

    public function getEscoposByCoordenacao(Request $request)
    {
        $request->validate([
            'coordenacao_id' => 'required|exists:ana_coordenacoes,id',
        ]);

        $escopos = AnaEscopo::where('coordenacao_id', $request->coordenacao_id)->get();

        return response()->json(['escopos' => $escopos]);
    }
}
