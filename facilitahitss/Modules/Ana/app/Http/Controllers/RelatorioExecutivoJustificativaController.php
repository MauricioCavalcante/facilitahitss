<?php

namespace Modules\Ana\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ana\Models\AnaRelatorioExecutivoJustificativa;
use Modules\Ana\Models\AnaOrdemServico;
use Modules\Ana\Http\Helpers\PrazoStatusHelper;
use Modules\Ana\Http\Helpers\RelatorioStatusHelper;
use Illuminate\Support\Facades\Auth;

class RelatorioExecutivoJustificativaController extends Controller
{
    public function index()
    {
        $justificativasPendentes = [];
        $justificativasAprovadas = [];
        $justificativasSancionadas = [];
        $meusJustificativas = [];
        $mensagemPrazo = null;

        $user = Auth::user();

        $justificativasPendentes = AnaRelatorioExecutivoJustificativa::with(['user', 'os'])
            ->where('status', 'Pendente')->get();
        $justificativasAprovadas = AnaRelatorioExecutivoJustificativa::with(['user', 'os'])
            ->where('status', 'Aprovada')->get();
        $justificativasSancionadas = AnaRelatorioExecutivoJustificativa::with(['user', 'os'])
            ->where('status', 'Sancionada')->get();
        $meusJustificativas = AnaRelatorioExecutivoJustificativa::with('os')
            ->where('user_id', $user->id)
            ->get();

        if ($user->role('user')) {
            // Usuários comuns só podem visualizar suas próprias justificativas
            $meusJustificativas = AnaRelatorioExecutivoJustificativa::with('os')
                ->where('user_id', $user->id)
                ->get();
        }

        // Verificar se o usuário tem alguma OS com prazo expirado sem justificativa aprovada
        $ordensExpiradas = AnaOrdemServico::whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('status', 'Em andamento')
        ->get();

        foreach ($ordensExpiradas as $ordem) {
            $prazoFinal = PrazoStatusHelper::calcularPrazoFinal($ordem->data_fim, $ordem->prazo);
            if (PrazoStatusHelper::prazoExpirado($prazoFinal)) {
                $justificativaValida = AnaRelatorioExecutivoJustificativa::where('user_id', $user->id)
                    ->where('os_id', $ordem->id)
                    ->whereIn('status', ['Aprovada', 'Sancionada'])
                    ->exists();

                if (!$justificativaValida) {
                    $mensagemPrazo = 'Você perdeu o prazo para criar o relatório. ' .
                        '<a href="' . route('ana::justificativas.criar', $ordem->id) . '">Clique aqui</a> para justificar o atraso.';
                    break;
                }
            }
        }

        return view('ana::justificativas.index', compact(
            'justificativasPendentes',
            'justificativasAprovadas',
            'justificativasSancionadas',
            'meusJustificativas',
            'mensagemPrazo'
        ));
    }

    public function criar($os_id)
    {
        // Verifica se a Ordem de Serviço existe
        $ordemServico = AnaOrdemServico::findOrFail($os_id);

        return view('ana::justificativas.criar', compact('ordemServico'));
    }

    public function salvar(Request $request)
    {
        $request->validate([
            'os_id' => 'required|exists:ana_ordem_servicos,id',
            'justificativa' => 'required|string|max:2000',
        ]);

        // Verifica se o usuário já enviou uma justificativa para esta OS
        $existeJustificativa = AnaRelatorioExecutivoJustificativa::where('user_id', Auth::id())
            ->where('os_id', $request->os_id)
            ->exists();

        if ($existeJustificativa) {
            return redirect()->back()->with(
                'error',
                'Você já enviou uma justificativa para esta Ordem de Serviço.'
            );
        }

        AnaRelatorioExecutivoJustificativa::create([
            'user_id' => Auth::id(),
            'os_id' => $request->os_id,
            'justificativa' => $request->justificativa,
            'status' => 'Pendente',
        ]);

        return redirect()->route('ana::index')->with('success', 'Justificativa enviada com sucesso.');
    }

    public function validar(Request $request, $id)
    {
        $justificativa = AnaRelatorioExecutivoJustificativa::findOrFail($id);

        $acao = $request->input('acao');
        if ($acao === 'Aprovar') {
            $justificativa->status = 'Aprovada';
        } elseif ($acao === 'Sancionar') {
            $justificativa->status = 'Sancionada';
        }

        $justificativa->save();

        return redirect()->route('ana::justificativas.index')->with('success', 'Justificativa atualizada com sucesso.');
    }

    public function visualizar($id)
    {
        $justificativa = AnaRelatorioExecutivoJustificativa::with(['user', 'os'])->findOrFail($id);

        return view('ana::justificativas.visualizar', compact('justificativa'));
    }
}
