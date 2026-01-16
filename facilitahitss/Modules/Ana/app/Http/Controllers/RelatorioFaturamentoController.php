<?php

namespace Modules\Ana\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Modules\Ana\Models\AnaOrdemServico;
use Modules\Ana\Models\AnaRelatorioFaturamento;
use Modules\Ana\Models\AnaRelatorioFaturamentoOS;
use Modules\Ana\Http\Helpers\TemplateHelper;

class RelatorioFaturamentoController extends Controller
{
    public function index()
    {
        try {
            $relatorios = AnaRelatorioFaturamento::orderBy('created_at', 'desc')->paginate(10);

            return view('ana::relatorio_faturamento.indexFaturamento', compact('relatorios'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao carregar os relatórios: ' . $e->getMessage());
        }
    }

    public function criarRelatorioFaturamento()
    {
        try {
            $mesesComOs = AnaOrdemServico::where('status', 'Encerrada')
                ->selectRaw('YEAR(data_fim) as ano, MONTH(data_fim) as mes')
                ->groupByRaw('YEAR(data_fim), MONTH(data_fim)')
                ->orderByRaw('YEAR(data_fim) DESC, MONTH(data_fim) DESC')
                ->take(2)
                ->get();

            $ordens_servico = collect();

            foreach ($mesesComOs as $mes) {
                $osMes = AnaOrdemServico::where('status', 'Encerrada')
                    ->whereYear('data_fim', $mes->ano)
                    ->whereMonth('data_fim', $mes->mes)
                    ->orderBy('data_fim', 'desc')
                    ->take(5)
                    ->get();

                $ordens_servico = $ordens_servico->merge($osMes);
            }

            return view('ana::relatorio_faturamento.criarRelatorioFaturamento', compact('ordens_servico'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao carregar a página de criação: ' . $e->getMessage());
        }
    }

    public function salvarRelatorioFaturamento(Request $request)
    {
        try {
            // Validação dos dados
            $validatedData = $request->validate([
                'ordens_servico' => 'required|array|min:1',
                'ordens_servico.*' => 'exists:ana_ordem_servicos,id',
                'numero_nota_fiscal' => 'required|string',
                'data_vencimento' => 'required|date',
                'valores' => 'required|array|min:1',
                'valores.*' => 'numeric|min:0',
                'desconto' => 'nullable|numeric|min:0',
            ]);

            // Obtém as ordens de serviço com seus escopos
            $ordensServico = AnaOrdemServico::with('escopos')->whereIn('id', $validatedData['ordens_servico'])->get();

            if ($ordensServico->isEmpty()) {
                return redirect()->back()->with('error', 'Nenhuma Ordem de Serviço encontrada.');
            }

            // Define o nome do arquivo no formato correto
            $mesAno = now()->format('m_Y');
            $filename = "Relatorio_Faturamento_{$validatedData['numero_nota_fiscal']}_{$mesAno}.docx";

            // Apenas envia os dados crus para o helper
            $ordensServicoDados = $ordensServico->values()->map(function ($os, $index) use ($validatedData) {
                return [
                    'modelo' => $os,
                    'valor' => $validatedData['valores'][$index],
                ];
            })->toArray();

            // Calcula valores finais
            $desconto = $validatedData['desconto'] ?? 0;
            $valorFinal = array_sum($validatedData['valores']) - $desconto;

            // Gera o documento DOCX preenchido
            $templateProcessor = TemplateHelper::templateRelatorioFaturamento(
                public_path('modelos/Modelo_Relatorio_Faturamento.docx'),
                [
                    'numero_nota_fiscal' => $validatedData['numero_nota_fiscal'],
                    'data_vencimento' => $validatedData['data_vencimento'],
                    'data_inicio' => $ordensServico->min('data_inicio'),
                    'data_fim' => $ordensServico->max('data_fim'),
                    'desconto' => $desconto,
                    'valor_final' => $valorFinal,
                    'ordens_servico' => $ordensServicoDados,
                ]
            );

            // Salva o documento temporário e converte para binário
            $tempFilePath = tempnam(sys_get_temp_dir(), 'relatorio_faturamento');
            $templateProcessor->saveAs($tempFilePath);
            $fileContent = file_get_contents($tempFilePath);
            unlink($tempFilePath);

            // Salva os dados no banco de dados
            $relatorio = AnaRelatorioFaturamento::create([
                'nome' => $filename,
                'arquivo' => $fileContent,
                'tamanho' => strlen($fileContent),
                'numero_nota_fiscal' => $validatedData['numero_nota_fiscal'],
                'data_vencimento' => $validatedData['data_vencimento'],
                'data_inicio' => $ordensServico->min('data_inicio'),
                'data_fim' => $ordensServico->max('data_fim'),
                'desconto' => $desconto,
                'valor_final' => $valorFinal,
            ]);

            // Salva os detalhes das OSs associadas ao relatório
            foreach ($ordensServico as $index => $os) {
                AnaRelatorioFaturamentoOS::create([
                    'relatorio_id' => $relatorio->id,
                    'os_id' => $os->id,
                    'valor' => $validatedData['valores'][$index],
                ]);
            }

            // Retorna mensagem de sucesso
            return redirect()->route('ana::relatorio_faturamento.index')->with('success', 'Relatório criado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao salvar relatório de faturamento: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao salvar o relatório.');
        }
    }

    public function editarRelatorioFaturamento($id)
    {
        try {
            // Busca o relatório de faturamento a ser editado
            $relatorio = AnaRelatorioFaturamento::findOrFail($id);

            // Obtém as OSs associadas ao relatório
            $ordens_servico_selecionadas = AnaRelatorioFaturamentoOS::where('relatorio_id', $relatorio->id)
                ->pluck('os_id')
                ->toArray();

            // Obtém as OSs encerradas nos últimos 2 meses
            $mesesComOs = AnaOrdemServico::where('status', 'Encerrada')
                ->selectRaw('YEAR(data_fim) as ano, MONTH(data_fim) as mes')
                ->groupByRaw('YEAR(data_fim), MONTH(data_fim)')
                ->orderByRaw('YEAR(data_fim) DESC, MONTH(data_fim) DESC')
                ->take(2)
                ->get();

            $ordens_servico = collect();
            foreach ($mesesComOs as $mes) {
                $osMes = AnaOrdemServico::where('status', 'Encerrada')
                    ->whereYear('data_fim', $mes->ano)
                    ->whereMonth('data_fim', $mes->mes)
                    ->orderBy('data_fim', 'desc')
                    ->take(5)
                    ->get();

                $ordens_servico = $ordens_servico->merge($osMes);
            }

            // Obtém os valores das OS associadas ao relatório
            $valores_os = AnaRelatorioFaturamentoOS::where('relatorio_id', $relatorio->id)
                ->pluck('valor', 'os_id')
                ->toArray();

            return view('ana::relatorio_faturamento.editarRelatorioFaturamento', compact(
                'relatorio', 'ordens_servico', 'ordens_servico_selecionadas', 'valores_os'
            ));
        } catch (\Exception $e) {
            \Log::error('Erro ao carregar edição do relatório de faturamento: ' . $e->getMessage());
            return redirect()->route('ana::relatorio_faturamento.index')->with('error', 'Erro ao carregar a edição do relatório.');
        }
    }

    public function atualizarRelatorioFaturamento(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'numero_nota_fiscal' => 'required|string',
                'data_vencimento' => 'required|date',
                'ordens_servico' => 'required|array|min:1',
                'ordens_servico.*' => 'exists:ana_ordem_servicos,id',
                'valores' => 'required|array|min:1',
                'valores.*' => 'numeric|min:0',
                'desconto' => 'nullable|numeric|min:0',
            ]);

            // Obtém o relatório existente
            $relatorio = AnaRelatorioFaturamento::findOrFail($id);

            // Carrega as OSs e reordena de acordo com a ordem do request
            $ordensServico = AnaOrdemServico::with('escopos')
                ->whereIn('id', $validatedData['ordens_servico'])
                ->get()
                ->sortBy(function ($os) use ($validatedData) {
                    return array_search($os->id, $validatedData['ordens_servico']);
                })
                ->values();

            if ($ordensServico->isEmpty()) {
                return redirect()->back()->with('error', 'Nenhuma Ordem de Serviço encontrada.');
            }

            // Nome do arquivo
            $mesAno = now()->format('m_Y');
            $filename = "Relatorio_Faturamento_{$validatedData['numero_nota_fiscal']}_{$mesAno}.docx";

            // Prepara os dados para o helper
            $ordensServicoDados = $ordensServico->map(function ($os, $index) use ($validatedData) {
                return [
                    'modelo' => $os,
                    'valor' => $validatedData['valores'][$index],
                ];
            })->toArray();

            $desconto = $validatedData['desconto'] ?? 0;
            $valorFinal = array_sum($validatedData['valores']) - $desconto;

            // Gera documento
            $templateProcessor = TemplateHelper::templateRelatorioFaturamento(
                public_path('modelos/Modelo_Relatorio_Faturamento.docx'),
                [
                    'numero_nota_fiscal' => $validatedData['numero_nota_fiscal'],
                    'data_vencimento' => $validatedData['data_vencimento'],
                    'data_inicio' => $ordensServico->min('data_inicio'),
                    'data_fim' => $ordensServico->max('data_fim'),
                    'desconto' => $desconto,
                    'valor_final' => $valorFinal,
                    'ordens_servico' => $ordensServicoDados,
                ]
            );

            // Salva o documento temporário e converte para binário
            $tempFilePath = tempnam(sys_get_temp_dir(), 'relatorio_faturamento');
            $templateProcessor->saveAs($tempFilePath);
            $fileContent = file_get_contents($tempFilePath);
            unlink($tempFilePath);

            // Atualiza relatório
            $relatorio->update([
                'nome' => $filename,
                'arquivo' => $fileContent,
                'tamanho' => strlen($fileContent),
                'numero_nota_fiscal' => $validatedData['numero_nota_fiscal'],
                'data_vencimento' => $validatedData['data_vencimento'],
                'data_inicio' => $ordensServico->min('data_inicio'),
                'data_fim' => $ordensServico->max('data_fim'),
                'desconto' => $desconto,
                'valor_final' => $valorFinal,
            ]);

            // Atualiza os registros vinculados
            AnaRelatorioFaturamentoOS::where('relatorio_id', $relatorio->id)->delete();
            foreach ($ordensServico as $index => $os) {
                AnaRelatorioFaturamentoOS::create([
                    'relatorio_id' => $relatorio->id,
                    'os_id' => $os->id,
                    'valor' => $validatedData['valores'][$index],
                ]);
            }

            return redirect()->route('ana::relatorio_faturamento.index')->with('success', 'Relatório atualizado com sucesso!');
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar relatório de faturamento: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao atualizar o relatório.');
        }
    }

    public function baixarRelatorio($id)
    {
        try {
            $relatorio = AnaRelatorioFaturamento::findOrFail($id);

            if (!$relatorio->arquivo) {
                return redirect()->route('ana::relatorio_faturamento.index')->with('error', 'Arquivo não encontrado no banco de dados.');
            }

            return response()->streamDownload(function () use ($relatorio) {
                echo $relatorio->arquivo;
            }, $relatorio->nome, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Length' => $relatorio->tamanho,
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao baixar relatório de faturamento: ' . $e->getMessage());
            return redirect()->route('ana::relatorio_faturamento.index')->with('error', 'Erro ao baixar o relatório.');
        }
    }

    public function excluirRelatorio($id)
    {
        try {
            $relatorio = AnaRelatorioFaturamento::findOrFail($id);
            $filepath = public_path("relatorios/{$relatorio->nome}");

            // Remove o arquivo do sistema de arquivos se existir
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            // Remove as entradas relacionadas ao relatório
            AnaRelatorioFaturamentoOS::where('relatorio_id', $relatorio->id)->delete();

            // Exclui o registro do relatório
            $relatorio->delete();

            return redirect()->route('ana::relatorio_faturamento.index')->with('success', 'Relatório excluído com sucesso!');
        } catch (\Exception $e) {
            \Log::error('Erro ao excluir relatório de faturamento: ' . $e->getMessage());
            return redirect()->route('ana::relatorio_faturamento.index')->with('error', 'Erro ao excluir o relatório.');
        }
    }

}
