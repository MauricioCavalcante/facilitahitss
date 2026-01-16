<?php

namespace Modules\Ana\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ana\Models\AnaCoordenacao;
use Modules\Ana\Models\AnaEscopo;

class CoordenacaoController extends Controller
{
    public function index()
    {
        // Obtém todas as coordenações e seus escopos
        $coordenacoes = AnaCoordenacao::with('escopos')->get();
        return view('ana::coordenacoes.indexCoordenacao', compact('coordenacoes'));
    }

    public function criarCoordenacao()
    {
        // Retorna a view para criar uma nova coordenação
        return view('ana::coordenacoes.criarCoordenacao');
    }

    public function salvarCoordenacao(Request $request)
    {
        // Valida os dados do formulário de criação de coordenação
        $request->validate([
            'codigo' => 'required|string|max:100',
            'nome' => 'required|string|max:100',
            'escopos' => 'nullable|array', // Escopos são opcionais e enviados como array
            'escopos.*' => 'required|string', // Cada escopo deve ser uma string
        ]);

        // Cria uma nova coordenação
        $coordenacao = AnaCoordenacao::create($request->only(['codigo', 'nome']));

        // Salva os escopos se forem enviados
        if ($request->has('escopos')) {
            foreach ($request->escopos as $escopo) {
                AnaEscopo::create([
                    'escopo' => $escopo,
                    'coordenacao_id' => $coordenacao->id
                ]);
            }
        }

        return redirect()->route('ana::coordenacoes.index')->with('success', 'Coordenação e escopos criados com sucesso.');
    }

    public function editarCoordenacao(AnaCoordenacao $coordenacao)
    {
        // Retorna a view para editar uma coordenação existente junto com seus escopos
        $coordenacao->load('escopos');
        return view('ana::coordenacoes.editarCoordenacao', compact('coordenacao'));
    }

    public function atualizarCoordenacao(Request $request, AnaCoordenacao $coordenacao)
    {
        // Valida os dados do formulário de edição de coordenação
        $request->validate([
            'codigo' => 'required|string|max:100',
            'nome' => 'required|string|max:100',
            'escopos' => 'nullable|array', // Escopos opcionais
            'escopos.*.id' => 'nullable|exists:ana_escopos,id', // ID opcional para identificar escopos existentes
            'escopos.*.escopo' => 'required|string', // Validação de cada escopo
        ]);

        // Atualiza a coordenação
        $coordenacao->update($request->only(['codigo', 'nome']));

        $escoposIds = [];

        if ($request->has('escopos')) {
            foreach ($request->escopos as $escopoData) {
                if (isset($escopoData['id'])) {
                    // Atualiza o escopo existente
                    $escopo = AnaEscopo::find($escopoData['id']);
                    if ($escopo) {
                        $escopo->update(['escopo' => $escopoData['escopo']]);
                    }
                    $escoposIds[] = $escopoData['id'];
                } else {
                    // Cria um novo escopo, se não houver ID
                    $novoEscopo = AnaEscopo::create([
                        'escopo' => $escopoData['escopo'],
                        'coordenacao_id' => $coordenacao->id
                    ]);
                    $escoposIds[] = $novoEscopo->id;
                }
            }
        }

        // Remove escopos que não estão mais presentes na requisição
        $coordenacao->escopos()->whereNotIn('id', $escoposIds)->delete();

        return redirect()->route('ana::coordenacoes.index')->with('success', 'Coordenação e escopos atualizados com sucesso.');
    }

    public function excluirCoordenacao($id)
    {
        // Encontra a coordenação pelo ID
        $coordenacao = AnaCoordenacao::findOrFail($id);

        // Exclui os escopos relacionados
        $coordenacao->escopos()->delete();

        // Exclui a coordenação
        $coordenacao->delete();

        return redirect()->route('ana::coordenacoes.index')->with('success', 'Coordenação e escopos excluídos com sucesso.');
    }
}
