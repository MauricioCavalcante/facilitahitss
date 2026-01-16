<?php

namespace Modules\Ana\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Modules\Ana\Models\AnaOrdemServico;
use Modules\Ana\Models\AnaCoordenacao;
use Modules\Ana\Models\AnaUser;
use Modules\Ana\Models\AnaEscopo;
use Modules\Ana\Models\AnaOrdemServicoEscopo;
use Modules\Ana\Emails\OrdemServicoMail;

class OrdemServicoController extends Controller
{
    public function index()
    {

        $user = auth()->user();

        if ($user->role === 'admin') {

            $ordensServico = AnaOrdemServico::with([
                'coordenacoes',
                'ordemservicoEscopo.escopo',
                'users'
            ])
            ->orderBy('id', 'desc')
            ->paginate(10);
        } else {
            
            $ordensServico = AnaOrdemServico::with([
                'coordenacoes',
                'ordemservicoEscopo.escopo',
                'users'
            ])
            ->whereHas('users', function ($query) use ($user) {
                $query->where('ana_ordem_servicos_users.user_id', $user->id);
            })
            ->orderBy('id', 'desc')
            ->paginate(10);
        }

        return view('ana::ordens_servico.indexOS', compact('ordensServico'));
    }


    public function criarOrdemServico()
    {
        
        $coordenacoes = AnaCoordenacao::with('escopos')->get();

        
        $users = AnaUser::all();

        return view('ana::ordens_servico.criarOS', compact('coordenacoes', 'users'));
    }

    public function salvarOrdemServico(Request $request)
    {
        
        $request->validate([
            'numero' => 'required|string|max:50',
            'documento' => 'required|string|max:50',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'prazo' => 'required|integer|min:1|max:10',
            'horas' => 'required|integer',
            'endereco' => 'required|string',
            'coordenacao_id' => 'required|array',
            'coordenacao_id.*' => 'exists:ana_coordenacoes,id',
            'escopos' => 'required|array',
            'escopos.*' => 'required|array',
            'users' => 'required|array',
            'users.*' => 'exists:ana_users,user_id',
        ]);

        
        $ordemServico = AnaOrdemServico::create($request->only([
            'numero', 'documento', 'status', 'data_inicio', 'data_fim', 'prazo', 'horas', 'endereco'
        ]));

        
        $ordemServico->coordenacoes()->sync($request->coordenacao_id);

        
        foreach ($request->coordenacao_id as $coordenacao_id) {
            if (isset($request->escopos[$coordenacao_id]) && is_array($request->escopos[$coordenacao_id])) {
                foreach ($request->escopos[$coordenacao_id] as $escopo_id) {
                    AnaOrdemServicoEscopo::create([
                        'ordem_servico_id' => $ordemServico->id,
                        'coordenacao_id' => $coordenacao_id,
                        'escopo_id' => $escopo_id
                    ]);
                }
            }
        }

        
        $ordemServico->users()->sync($request->users);

        $usuarios = User::whereIn('id', $request->users)->get();

        $escopos = AnaOrdemServicoEscopo::where('ordem_servico_id', $ordemServico->id)->get();

        foreach ($usuarios as $usuario) {
            $anaUser = AnaUser::where('user_id', $usuario->id)->first();
    
            $emails = [$usuario->email];
    
            if ($anaUser && $anaUser->email_ana) {
                $emails[] = $anaUser->email_ana;
            }
    
            Mail::to($emails)->send(new OrdemServicoMail($ordemServico, $usuario, $escopos));
        }

        return redirect()->route('ana::ordens_servico.index')->with('success', 'Ordem de Serviço criada com sucesso.');
    }

    public function editarOrdemServico(AnaOrdemServico $ordensServico)
    {
        $coordenacoes = AnaCoordenacao::with('escopos')->get();
        $users = AnaUser::with('user')->get();

        $coordenacoesSelecionadas = $ordensServico->coordenacoes()->pluck('ana_coordenacoes.id')->toArray();

        $escoposSelecionados = [];
        $ordensServicoEscopos = AnaOrdemServicoEscopo::where('ordem_servico_id', $ordensServico->id)->get();

        foreach ($ordensServicoEscopos as $escopo) {
            $escoposSelecionados[$escopo->coordenacao_id][] = $escopo->escopo_id;
        }


        $usuariosSelecionados = DB::table('ana_ordem_servicos_users')
            ->where('ordem_servico_id', $ordensServico->id)
            ->pluck('user_id')
            ->toArray();

        return view('ana::ordens_servico.editarOS', compact(
            'ordensServico', 'coordenacoes', 'users', 'coordenacoesSelecionadas', 'escoposSelecionados', 'usuariosSelecionados'
        ));
    }

    public function atualizarOrdemServico(Request $request, AnaOrdemServico $ordensServico)
    {
        $request->validate([
            'numero' => 'required|string|max:50' . $ordensServico->id,
            'documento' => 'required|string|max:50' . $ordensServico->id,
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'prazo' => 'required|integer|min:1|max:10',
            'horas' => 'required|integer',
            'endereco' => 'required|string',
            'coordenacao_id' => 'required|array',
            'coordenacao_id.*' => 'exists:ana_coordenacoes,id',
            'escopos' => 'required|array',
            'escopos.*' => 'required|array',
            'users' => 'required|array',
            'users.*' => 'exists:users,id'
        ]);

        $ordensServico->update($request->only([
            'numero', 'documento', 'status', 'data_inicio', 'data_fim', 'prazo', 'horas', 'endereco'
        ]));

        $ordensServico->coordenacoes()->sync($request->coordenacao_id);

        AnaOrdemServicoEscopo::where('ordem_servico_id', $ordensServico->id)->delete();

        foreach ($request->coordenacao_id as $coordenacao_id) {
            if (isset($request->escopos[$coordenacao_id]) && is_array($request->escopos[$coordenacao_id])) {
                foreach ($request->escopos[$coordenacao_id] as $escopo_id) {
                    AnaOrdemServicoEscopo::create([
                        'ordem_servico_id' => $ordensServico->id,
                        'coordenacao_id' => $coordenacao_id,
                        'escopo_id' => $escopo_id
                    ]);
                }
            }
        }

        $ordensServico->users()->sync($request->users);

        $usuarios = User::whereIn('id', $request->users)->get();

        $escopos = AnaOrdemServicoEscopo::where('ordem_servico_id', $ordensServico->id)->get();

        foreach ($usuarios as $usuario) {
            $anaUser = AnaUser::where('user_id', $usuario->id)->first();
    
            $emails = [$usuario->email];
    
            if ($anaUser && $anaUser->email_ana) {
                $emails[] = $anaUser->email_ana;
            }
    
            Mail::to($emails)->send(new OrdemServicoMail($ordensServico, $usuario, $escopos));
        }

        return redirect()->route('ana::ordens_servico.index')->with('success', 'Ordem de Serviço atualizada com sucesso.');
    }

    public function excluirOrdemServico(AnaOrdemServico $ordensServico)
    {
        $ordensServico->delete();
        return redirect()->route('ana::ordens_servico.index')->with('success', 'Ordem de Serviço excluída com sucesso.');
    }

    public function getEscoposByCoordenacao(Request $request)
    {
        $escopos = AnaEscopo::where('coordenacao_id', $request->coordenacao_id)->get();
        return response()->json($escopos);
    }

    public function atualizarStatus(Request $request, AnaOrdemServico $ordemServico)
    {
        $request->validate([
            'status' => 'required|string|in:Nova,Em andamento,Encerrada'
        ]);

        // Atualiza o status da OS
        $ordemServico->update(['status' => $request->status]);

        return response()->json(['success' => true]);
    }

    public function duplicar(Request $request, AnaOrdemServico $ordensServico)
    {
        // Validação das novas datas de início, fim, endereço e prazo
        $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'prazo' => 'required|integer|min:1|max:10', // Adicionando validação do prazo
            'endereco' => 'required|string|max:255',
        ]);

        // Duplica os dados da OS existente e aplica os novos valores
        $novaOS = $ordensServico->replicate();
        $novaOS->status = 'Nova';
        $novaOS->data_inicio = $request->data_inicio;
        $novaOS->data_fim = $request->data_fim;
        $novaOS->prazo = $request->prazo; // Incluindo o novo prazo
        $novaOS->endereco = $request->endereco;
        $novaOS->save();

        // Duplica as coordenações associadas
        if ($ordensServico->coordenacoes()->exists()) {
            $novaOS->coordenacoes()->sync($ordensServico->coordenacoes->pluck('id'));
        }

        // Certifica-se de que o relacionamento está carregado
        $ordensServico->load('ordemservicoEscopo');

        // Duplica os escopos associados à nova OS na tabela ana_ordem_servicos_escopos
        if ($ordensServico->ordemservicoEscopo->isNotEmpty()) {
            foreach ($ordensServico->ordemservicoEscopo as $coordenacaoEscopo) {
                AnaOrdemServicoEscopo::create([
                    'ordem_servico_id' => $novaOS->id,
                    'coordenacao_id' => $coordenacaoEscopo->coordenacao_id,
                    'escopo_id' => $coordenacaoEscopo->escopo_id,
                ]);
            }
        }

        // Duplica os usuários associados
        if ($ordensServico->users()->exists()) {
            $novaOS->users()->sync($ordensServico->users->pluck('id'));
        }

        return redirect()->route('ana::ordens_servico.index')->with('success', 'Ordem de Serviço duplicada com sucesso.');
    }

}
