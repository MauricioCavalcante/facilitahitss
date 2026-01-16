<?php

namespace Modules\Ana\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\User;
use Modules\Ana\Emails\RelatorioStatusAtualizado;
use Modules\Ana\Models\AnaOrdemServico;
use Modules\Ana\Models\AnaOrdemServicoEscopo;
use Modules\Ana\Models\AnaRelatorioExecutivo;
use Modules\Ana\Models\AnaRelatorioExecutivoDetalhe;
use Modules\Ana\Models\AnaRelatorioExecutivoValidar;
use Modules\Ana\Models\AnaRelatorioExecutivoJustificativa;
use Modules\Ana\Models\AnaUser;
use Modules\Ana\Http\Helpers\TemplateHelper;
use Modules\Ana\Http\Helpers\PrazoStatusHelper;

use ZipArchive;
use Illuminate\Support\Facades\Storage;

class RelatorioExecutivoController extends Controller
{
    public function index()
    {
        try {

            $relatorios = AnaRelatorioExecutivo::where('user_id', Auth::user()->id)
                ->with(['ordemServico', 'user.anaUser.coordenacao', 'validacao'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return view('ana::relatorio_executivo.indexRelatorio', compact('relatorios', 'possuiOrdemServico'));
        } catch (\Exception $e) {
            Log::error('Erro ao listar relatórios: ' . $e->getMessage());
            return redirect()->route('ana::index')->with('error', 'Erro ao carregar os relatórios.');
        }
    }

    public function criarRelatorio()
    {
        try {
            $user = Auth::user();

            $ordens_servico = AnaOrdemServico::where('status', 'Em andamento')
                ->whereHas('users', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->get();

            if ($ordens_servico->isEmpty()) {
                return redirect()->back()->with('error', 'Você não está associado a nenhuma Ordem de Serviço em andamento.');
            }

            $ordemServicoAtual = $ordens_servico->first();
            $prazoFinal = PrazoStatusHelper::calcularPrazoFinal($ordemServicoAtual->data_fim, $ordemServicoAtual->prazo);

            if (PrazoStatusHelper::prazoExpirado($prazoFinal)) {
                $justificativaValida = AnaRelatorioExecutivoJustificativa::where('user_id', $user->id)
                    ->where('os_id', $ordemServicoAtual->id)
                    ->whereIn('status', ['Aprovada', 'Sancionada'])
                    ->exists();

                if (!$justificativaValida) {
                    return redirect()->route('ana::relatorio_executivo.index')->with(
                        'error',
                        'Você perdeu o prazo para criar o relatório da OS ' . $ordemServicoAtual->numero .
                        '. O prazo era até ' . $prazoFinal->format('d/m/Y') . ' às 23:59. ' .
                        '<a href="' . route('ana::justificativas.criar', $ordemServicoAtual->id) . '">Clique aqui</a> para justificar o atraso.'
                    );
                }
            }

            $relatorioExistente = AnaRelatorioExecutivo::where('user_id', $user->id)
                ->where('ordem_servico_id', $ordemServicoAtual->id)
                ->exists();

            if ($relatorioExistente) {
                return redirect()->back()->with('error', 'Você já criou um relatório para a Ordem de Serviço ' . $ordemServicoAtual->numero . '.');
            }

            $relatorio = null;

            return view('ana::relatorio_executivo.criarRelatorio', compact('ordens_servico', 'ordemServicoAtual', 'relatorio'));
        } catch (\Exception $e) {
            Log::error('Erro ao acessar a tela de criação de relatório: ' . $e->getMessage());
            return redirect()->route('ana::relatorio_executivo.index')->with('error', 'Erro ao acessar a criação do relatório.');
        }
    }

    public function salvarRelatorio(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'ordem_servico_id' => 'required|exists:ana_ordem_servicos,id',
                'titulo' => 'required|string',
                'referencias' => 'required|string',
                'atividades' => 'required|string',
                'tarefas' => 'required|array|min:1',
                'tarefas.*' => 'required|string',
                'evidencias' => 'required|array|min:1',
                'evidencias.*' => 'required|string',
                'sei' => 'required|array|min:1',
                'sei.*' => 'required|string',
            ]);

            $user = Auth::user();
            $ordemServico = AnaOrdemServico::with('coordenacoes')->findOrFail($validatedData['ordem_servico_id']);

            $relatoriosPath = public_path('relatorios');
            $modeloPath = public_path('modelos' . DIRECTORY_SEPARATOR . 'Modelo_Relatorio_Executivo.docx');



            if (!File::exists($modeloPath)) {
                Log::error("Modelo do relatório não encontrado em: {$modeloPath}");
                return back()->with('error', 'Modelo de relatório não encontrado.');
            }

            File::ensureDirectoryExists($relatoriosPath, 0777, true);

            $mesAno = now()->format('m_Y');
            $filename = 'Relatorio_OS_' . $ordemServico->numero . '_' . $mesAno . '_' . $user->name . '.docx';

            $filepath = $relatoriosPath . '/' . $filename;

            $templateProcessor = TemplateHelper::templateRelatorioExecutivo($modeloPath, [
                'ordem_servico' => $ordemServico,
                'user' => $user,
                'escopo' => optional($user->anaUser->escopo)->escopo ?? 'Escopo não definido',
                'titulo' => $validatedData['titulo'],
                'referencias' => $validatedData['referencias'],
                'atividades' => $validatedData['atividades'],
                'tarefas' => $validatedData['tarefas'],
                'evidencias' => $validatedData['evidencias'],
                'sei' => $validatedData['sei'],
            ]);

            DB::transaction(function () use ($templateProcessor, $filepath, $filename, $validatedData, $user) {
                $templateProcessor->saveAs($filepath);

                $relatorio = AnaRelatorioExecutivo::create([
                    'user_id' => $user->id,
                    'ordem_servico_id' => $validatedData['ordem_servico_id'],
                    'nome' => $filename,
                    'tipo' => 'docx',
                    'tamanho' => File::size($filepath),
                ]);

                AnaRelatorioExecutivoDetalhe::create([
                    'relatorio_id' => $relatorio->id,
                    'titulo' => $validatedData['titulo'],
                    'referencias' => $validatedData['referencias'],
                    'atividades' => $validatedData['atividades'],
                    'tarefas' => json_encode($validatedData['tarefas']),
                    'evidencias' => json_encode($validatedData['evidencias']),
                    'sei' => json_encode($validatedData['sei']),
                ]);

                AnaRelatorioExecutivoValidar::create([
                    'relatorio_id' => $relatorio->id,
                    'status' => request()->has('salvar_como_rascunho') ? 'Rascunho' : 'Novo',
                ]);
            });

            return redirect()->route('ana::relatorio_executivo.index')->with(
                'success',
                $request->has('salvar_como_rascunho') ? 'Relatório salvo como rascunho com sucesso.' : 'Relatório criado com sucesso!'
            );
        } catch (\Throwable $e) {
            Log::error('Erro ao salvar relatório: ' . $e->getMessage());
            return back()->with('error', 'Erro ao salvar o relatório.');
        }
    }


    public function editarRelatorio($id)
    {
        try {
            $relatorio = AnaRelatorioExecutivo::with('detalhes')->findOrFail($id);

            if (!$relatorio->detalhes) {
                return redirect()->route('ana::relatorio_executivo.index')->with('error', 'Detalhes do relatório não encontrados.');
            }

            $ordens_servico = AnaOrdemServico::all();
            return view('ana::relatorio_executivo.editarRelatorio', compact('relatorio', 'ordens_servico'));
        } catch (\Exception $e) {
            Log::error('Erro ao acessar a edição do relatório: ' . $e->getMessage());
            return redirect()->route('ana::relatorio_executivo.index')->with('error', 'Erro ao acessar a edição do relatório.');
        }
    }

    public function atualizarRelatorio(Request $request, $id)
    {
        try {
            $relatorio = AnaRelatorioExecutivo::with('detalhes')->findOrFail($id);
            $detalhes = $relatorio->detalhes;
            $user = User::where('id', $relatorio->user_id)->first();
            $anaUser = AnaUser::where('user_id', $relatorio->user_id)->first();

            $emails = [$user->email];

            if ($anaUser && $anaUser->email_ana) {
                $emails[] = $anaUser->email_ana;
            }

            $validatedData = $request->validate([
                'ordem_servico_id' => 'required|exists:ana_ordem_servicos,id',
                'titulo' => 'required|string',
                'referencias' => 'required|string',
                'atividades' => 'required|string',
                'tarefas' => 'required|array|min:1',
                'tarefas.*' => 'required|string',
                'evidencias' => 'required|array|min:1',
                'evidencias.*' => 'required|string',
                'sei' => 'required|array|min:1',
                'sei.*' => 'required|string',
            ]);

            $ordemServico = AnaOrdemServico::findOrFail($validatedData['ordem_servico_id']);
            $escopos = AnaOrdemServicoEscopo::where('ordem_servico_id', $ordemServico->id)->get();
            $user = Auth::user();

            $relatoriosPath = public_path('relatorios');

            if (!file_exists($relatoriosPath)) {
                mkdir($relatoriosPath, 0777, true);
            }

            $mesAno = now()->format('m_Y');
            $filename = 'Relatorio_OS_' . $ordemServico->numero . '_' . $mesAno . '_' . $user->name . '.docx';
            $filepath = "{$relatoriosPath}/{$filename}";

            $templateProcessor = TemplateHelper::templateRelatorioExecutivo(
                public_path('modelos' . DIRECTORY_SEPARATOR . 'Modelo_Relatorio_Executivo.docx'),
                [
                    'ordem_servico' => $ordemServico,
                    'user' => $user,
                    'escopo' => optional($user->anaUser->escopo)->escopo ?? 'Escopo não definido',
                    'titulo' => $validatedData['titulo'],
                    'referencias' => $validatedData['referencias'],
                    'atividades' => $validatedData['atividades'],
                    'tarefas' => $validatedData['tarefas'],
                    'evidencias' => $validatedData['evidencias'],
                    'sei' => $validatedData['sei'],
                ],
                true
            );

            $templateProcessor->saveAs($filepath);

            if (!file_exists($filepath)) {
                return redirect()->back()->with('error', 'Erro ao salvar o relatório atualizado.');
            }
            $relatorio->update([
                'ordem_servico_id' => $validatedData['ordem_servico_id'],
                'nome' => $filename,
                'tipo' => 'docx',
                'tamanho' => filesize($filepath),
                'updated_at' => now(),
            ]);

            $detalhes->update([
                'titulo' => $validatedData['titulo'],
                'referencias' => $validatedData['referencias'],
                'atividades' => $validatedData['atividades'],
                'tarefas' => json_encode($validatedData['tarefas']),
                'evidencias' => json_encode($validatedData['evidencias']),
                'sei' => json_encode($validatedData['sei']),
            ]);

            $relatorioValidar = AnaRelatorioExecutivoValidar::where('relatorio_id', $relatorio->id)->first();

            if ($request->has('salvar_como_rascunho')) {
                $status = 'Rascunho';
            } elseif ($relatorioValidar && $relatorioValidar->status === 'Para Corrigir') {
                $status = 'Corrigido';
            } else {
                $status = 'Novo';
            }

            AnaRelatorioExecutivoValidar::updateOrCreate(
                ['relatorio_id' => $relatorio->id],
                ['status' => $status]
            );

            if ($status === 'Rascunho' || $status === 'Corrigido' || $status === 'Novo') {
                Mail::to($emails)->send(new RelatorioStatusAtualizado($relatorio, $status, $escopos, $user));
            }

            if ($status === 'Rascunho') {
                return redirect()->route('ana::relatorio_executivo.index')->with('success', 'Relatório salvo como rascunho com sucesso.');
            } elseif ($status === 'Corrigido') {
                return redirect()->route('ana::relatorio_executivo.validarRelatorio', ['id' => $id])->with('success', 'Relatório corrigido com sucesso.');
            }

            return redirect()->route('ana::relatorio_executivo.index')->with('success', 'Relatório atualizado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar relatório: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao atualizar o relatório.');
        }
    }

    public function baixarRelatorioAtualizado($id)
    {
        try {
            $relatorio = AnaRelatorioExecutivo::with('detalhes')->findOrFail($id);
            $detalhes = $relatorio->detalhes;

            $userDono = User::findOrFail($relatorio->user_id);
            $anaUser = AnaUser::where('user_id', $relatorio->user_id)->first();

            $ordemServico = AnaOrdemServico::findOrFail($relatorio->ordem_servico_id);
            $escopo = optional($anaUser->escopo)->escopo ?? 'Escopo não definido';

            $dados = [
                'ordem_servico' => $ordemServico,
                'user' => $userDono,
                'escopo' => $escopo,
                'titulo' => $detalhes->titulo,
                'referencias' => $detalhes->referencias,
                'atividades' => $detalhes->atividades,
                'tarefas' => json_decode($detalhes->tarefas, true),
                'evidencias' => json_decode($detalhes->evidencias, true),
                'sei' => json_decode($detalhes->sei, true),
            ];


            $templateProcessor = TemplateHelper::templateRelatorioExecutivo(
                public_path('modelos/Modelo_Relatorio_Executivo.docx'),
                $dados,
                false
            );

            $mesAno = now()->format('m_Y');
            $filename = "Relatorio_OS_{$ordemServico->numero}_{$mesAno}_{$userDono->name}.docx";

            $tempFile = tempnam(sys_get_temp_dir(), 'relatorio_');
            $templateProcessor->saveAs($tempFile);

            DB::transaction(function () use ($relatorio, $filename, $tempFile) {
                $relatorio->update([
                    'nome' => $filename,
                    'tipo' => 'docx',
                    'tamanho' => filesize($tempFile),
                ]);
            });

            return response()->download($tempFile, $filename);
        } catch (\Exception $e) {
            Log::error('Erro ao baixar relatório atualizado: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao baixar o relatório atualizado.');
        }
    }

    public function baixarRelatoriosValidados()
    {
        try {
            $ordensServico = AnaOrdemServico::where('status', 'Em andamento')->get();

            if ($ordensServico->isEmpty()) {
                return redirect()->back()->with('error', 'Não há ordens de serviço "Em Andamento".');
            }

            $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'relatorios' . DIRECTORY_SEPARATOR;

            if (!File::exists($tempDir)) {
                File::makeDirectory($tempDir, 0777, true);
                Log::info('Diretório temporário criado: ' . $tempDir);
            }

            $relatoriosValidos = false;
            $zipFile = $tempDir . 'Relatorio_RTA_' . now()->format('m_Y') . '.zip';
            $zip = new ZipArchive;

            if ($zip->open($zipFile, ZipArchive::CREATE) !== TRUE) {
                return redirect()->back()->with('error', 'Erro ao criar o arquivo ZIP.');
            }

            foreach ($ordensServico as $os) {
                $osDir = $tempDir . 'OS_' . $os->numero . DIRECTORY_SEPARATOR;

                if (!File::exists($osDir)) {
                    File::makeDirectory($osDir, 0777, true);
                    Log::info('Pasta criada para a OS: ' . $osDir);
                }

                $relatorios = $os->relatorios()->whereHas('validacao', function ($query) {
                    $query->where('status', 'Validado');
                })->get();

                if ($relatorios->isNotEmpty()) {
                    $relatoriosValidos = true;
                }

                foreach ($relatorios as $relatorio) {
                    $filename = $relatorio->nome ?: "Relatorio_OS_{$os->numero}_{$relatorio->user->name}.docx";
                    $filePath = $osDir . $filename;

                    // Grava o conteúdo do BLOB no arquivo temporário
                    File::put($filePath, $relatorio->arquivo); // <- Aqui você grava o BLOB no disco

                    $relativePath = 'relatorios/OS_' . $os->numero . '/' . $filename;

                    if (!$zip->locateName($relativePath)) {
                        $zip->addFile($filePath, $relativePath);
                        Log::info("Arquivo adicionado ao ZIP: " . $relativePath);
                    }
                }
            }

            if (!$relatoriosValidos) {
                return redirect()->back()->with('error', 'Não há relatórios validados para as ordens de serviço em andamento.');
            }

            $zip->close();
            Log::info("Arquivo ZIP criado: " . $zipFile);

            if (!File::exists($zipFile)) {
                Log::error("Arquivo ZIP não encontrado: " . $zipFile);
                return redirect()->back()->with('error', 'Erro ao gerar o arquivo ZIP.');
            }

            return response()->download($zipFile)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Erro ao baixar relatórios validados: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao baixar os relatórios validados.');
        }
    }


    public function baixarRelatorio($id)
    {
        try {
            $relatorio = AnaRelatorioExecutivo::findOrFail($id);

            $filepath = public_path('relatorios/' . $relatorio->nome);

            if (file_exists($filepath)) {
                return response()->download($filepath);
            }

            if ($relatorio->arquivo) {
                return response($relatorio->arquivo)
                    ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
                    ->header("Content-Disposition", "attachment; filename={$relatorio->nome}");
            }

            Log::error("Arquivo {$relatorio->nome} não encontrado no sistema nem no banco de dados.");
            return redirect()->route('ana::relatorio_executivo.index')->with('error', 'Arquivo não encontrado.');
        } catch (\Exception $e) {
            Log::error('Erro ao baixar relatório: ' . $e->getMessage());
            return redirect()->route('ana::relatorio_executivo.index')->with('error', 'Erro ao baixar o relatório.');
        }
    }

    public function excluirRelatorio($id)
    {
        try {
            $relatorio = AnaRelatorioExecutivo::findOrFail($id);

            $validacao = AnaRelatorioExecutivoValidar::where('relatorio_id', $relatorio->id)->first();

            if ($validacao && $validacao->status === 'Validado') {
                return redirect()->route('ana::relatorio_executivo.index')->with('error', 'Não é possível excluir um relatório validado.');
            }

            $filepath = public_path('relatorios/' . $relatorio->nome);

            if (file_exists($filepath)) {
                @unlink($filepath);
            }

            $relatorio->delete();

            return redirect()->route('ana::relatorio_executivo.index')->with('success', 'Relatório excluído com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao excluir relatório: ' . $e->getMessage());
            return redirect()->route('ana::relatorio_executivo.index')->with('error', 'Erro ao excluir o relatório.');
        }
    }

    public function validarRelatorio($id)
    {
        try {

            $relatorio = AnaRelatorioExecutivo::where('id', $id)
                ->whereHas('validacao', function ($query) {
                    $query->where('status', '!=', 'Rascunho');
                })
                ->with('user.anaUser.coordenacao')
                ->firstOrFail();

            $validacao = AnaRelatorioExecutivoValidar::where('relatorio_id', $id)->first();

            return view('ana::relatorio_executivo.validarRelatorio', compact('relatorio', 'validacao'));
        } catch (\Exception $e) {
            Log::error('Erro ao acessar a validação do relatório: ' . $e->getMessage());
            return redirect()->route('ana::relatorio_executivo.index')->with('error', 'Erro ao acessar a validação do relatório.');
        }
    }

    public function salvarValidacao(Request $request, $id)
    {
        try {
            $relatorio = AnaRelatorioExecutivo::with('ordemServico')->findOrFail($id);
            $ordemServico = $relatorio->ordemServico;
            $user = User::where('id', $relatorio->user_id)->first();
            $anaUser = AnaUser::where('user_id', $relatorio->user_id)->first();
            $escopos = AnaOrdemServicoEscopo::where('ordem_servico_id', $ordemServico->id)->get();

            $emails = [$user->email];

            if ($anaUser && $anaUser->email_ana) {
                $emails[] = $anaUser->email_ana;
            }

            if (!$ordemServico) {
                return redirect()->back()->with('error', 'Ordem de Serviço não encontrada.');
            }

            $validacao = AnaRelatorioExecutivoValidar::where('relatorio_id', $id)->first();

            if (!$validacao) {
                $validacao = new AnaRelatorioExecutivoValidar();
                $validacao->relatorio_id = $relatorio->id;
                $validacao->validado_por = auth()->user()->id;
                $validacao->data_validacao = now();
            }
            $successMessage = 'Operação concluída com sucesso.';


            if ($request->input('action') === 'inserir_comentario') {
                $validacao->status = 'Para Corrigir';
                $validacao->comentario = $request->input('comentario');
                $successMessage = 'Comentário inserido com sucesso.';

                Mail::to($emails)->send(new RelatorioStatusAtualizado($relatorio, 'Para Corrigir', $escopos, $user));
            }

            if ($request->input('action') === 'validar') {
                $validacao->validado_por = auth()->user()->id;
                $validacao->status = 'Validado';

                if ($request->has('comentario') && !empty($request->input('comentario'))) {
                    $validacao->comentario = $request->input('comentario');
                }
                $filepath = public_path('relatorios/' . $relatorio->nome);

                if (file_exists($filepath)) {
                    $fileContent = file_get_contents($filepath);

                    if ($fileContent === false) {
                        return redirect()->route('ana::index')->with('error', 'Erro ao ler o arquivo.');
                    }

                    $relatorio->arquivo = $fileContent;
                    if ($relatorio->save()) {
                        @unlink($filepath);
                    } else {
                        return redirect()->route('ana::index')->with('error', 'Erro ao salvar o arquivo no banco de dados.');
                    }
                } else {
                    return redirect()->route('ana::index')->with('error', 'Arquivo não encontrado.');
                }

                $successMessage = 'Relatório validado com sucesso.';

                Mail::to($emails)->send(new RelatorioStatusAtualizado($relatorio, 'Validado', $escopos, $user));
            }

            $validacao->save();

            return redirect()->route('ana::index')->with('success', $successMessage);
        } catch (\Exception $e) {
            Log::error('Erro ao salvar validação do relatório: ' . $e->getMessage());
            return redirect()->route('ana::index')->with('error', 'Erro ao salvar a validação do relatório.');
        }
    }

    public function recriarTodosRelatorios()
    {
        try {

            $relatorios = AnaRelatorioExecutivo::with('detalhes', 'user', 'ordemServico')
                ->whereNull('arquivo')
                ->get();

            if ($relatorios->isEmpty()) {
                return redirect()->route('ana::relatorio_executivo.index')
                    ->with('info', 'Nenhum relatório precisa ser recriado. Todos já possuem arquivos.');
            }

            $relatoriosCriados = 0;

            foreach ($relatorios as $relatorio) {
                if (!$relatorio->detalhes || !$relatorio->ordemServico) {
                    Log::warning("Relatório ID {$relatorio->id} ignorado por falta de detalhes ou OS.");
                    continue;
                }

                $user = $relatorio->user;
                $ordemServico = $relatorio->ordemServico;
                $detalhes = $relatorio->detalhes;

                $mesAno = now()->format('m_Y');
                $filename = "Relatorio_OS_{$ordemServico->numero}_{$mesAno}_{$user->name}.docx";

                $templateProcessor = TemplateHelper::templateRelatorioExecutivo(
                    public_path('modelos/Modelo_Relatorio_Executivo.docx'),
                    [
                        'ordem_servico' => $ordemServico,
                        'user' => $user,
                        'escopo' => optional($user->anaUser->escopo)->escopo ?? 'Escopo não definido',
                        'titulo' => $detalhes->titulo,
                        'referencias' => $detalhes->referencias,
                        'atividades' => $detalhes->atividades,
                        'tarefas' => json_decode($detalhes->tarefas, true),
                        'evidencias' => json_decode($detalhes->evidencias, true),
                        'sei' => json_decode($detalhes->sei, true),
                    ]
                );

                $tempFile = tmpfile();
                $metaData = stream_get_meta_data($tempFile);
                $tempPath = $metaData['uri'];
                $templateProcessor->saveAs($tempPath);

                if (!file_exists($tempPath)) {
                    Log::error("Erro ao salvar o relatório ID {$relatorio->id}.");
                    fclose($tempFile);
                    continue;
                }

                $arquivoBlob = file_get_contents($tempPath);

                DB::table('ana_relatorio_executivos')->where('id', $relatorio->id)->update([
                    'nome' => $filename,
                    'tamanho' => filesize($tempPath),
                    'arquivo' => $arquivoBlob,
                    'updated_at' => now(),
                ]);

                $relatoriosCriados++;
                fclose($tempFile);
            }

            return redirect()->route('ana::relatorio_executivo.index')
                ->with('success', "{$relatoriosCriados} relatórios foram recriados com sucesso!");
        } catch (\Exception $e) {
            Log::error('Erro ao recriar todos os relatórios: ' . $e->getMessage());
            return redirect()->route('ana::relatorio_executivo.index')
                ->with('error', 'Erro ao recriar os relatórios.');
        }
    }

    public function downloadRelatorioExecutivo($id)
    {
        try {
            $relatorio = AnaRelatorioExecutivo::findOrFail($id);

            if ($relatorio->arquivo) {
                return response($relatorio->arquivo)
                    ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
                    ->header("Content-Disposition", "attachment; filename={$relatorio->nome}");
            }

            return redirect()->back()->with('error', 'Arquivo não encontrado.');
        } catch (\Exception $e) {
            Log::error('Erro ao baixar relatório: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao baixar o relatório.');
        }
    }
}
