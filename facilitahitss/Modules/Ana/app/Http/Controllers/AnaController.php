<?php

namespace Modules\Ana\Http\Controllers;

use ZipArchive;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Modules\Ana\Models\AnaOrdemServico;
use Modules\Ana\Models\AnaRelatorioExecutivo;
use Modules\Ana\Models\AnaRelatorioExecutivoValidar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('ana::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('ana::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('ana::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('ana::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    public function downloadAllReports()
    {
        $ordensServico = AnaOrdemServico::where('status', 'Em andamento')->get();
    
        if ($ordensServico->isEmpty()) {
            return back()->with('error', 'Nenhuma Ordem de Serviço em andamento.');
        }
    
        $zip = new ZipArchive;
        $nomeZip = 'relatorios_validados.zip';
        $caminhoZip = public_path("temp/{$nomeZip}");
    
        if (!File::exists(public_path('temp'))) {
            File::makeDirectory(public_path('temp'), 0777, true);
        }
    
        if ($zip->open($caminhoZip, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
    
            foreach ($ordensServico as $os) {
                // Buscar relatórios validados para essa OS
                $relatorios = AnaRelatorioExecutivo::where('ordem_servico_id', $os->id)
                    ->whereHas('validacao', function ($query) {
                        $query->where('status', 'Validado');
                    })
                    ->with('user') // para evitar N+1
                    ->get();
    
                if ($relatorios->isEmpty()) {
                    continue; // Não cria a pasta se não houver relatório validado
                }
    
                // Formatar datas
                $dataInicio = \Carbon\Carbon::parse($os->data_inicio)->format('d-m-Y');
                $dataFim = \Carbon\Carbon::parse($os->data_fim)->format('d-m-Y');
    
                $nomePasta = "OS_{$os->numero} - {$dataInicio} até {$dataFim}";
                $zip->addEmptyDir($nomePasta);
    
                foreach ($relatorios as $relatorio) {
                    $nomeArquivo = $relatorio->nome ?? "relatorio_{$relatorio->id}.{$relatorio->tipo}";
                    $caminhoTemp = public_path("temp/{$nomeArquivo}");
    
                    // Grava o conteúdo do BLOB em um arquivo temporário
                    file_put_contents($caminhoTemp, $relatorio->arquivo);
    
                    if (file_exists($caminhoTemp)) {
                        $zip->addFile($caminhoTemp, "{$nomePasta}/{$nomeArquivo}");
                    }
                }
            }
    
            $zip->close();
        } else {
            return back()->with('error', 'Erro ao gerar o arquivo ZIP.');
        }
    
        // Após o envio do ZIP, apagar arquivos temporários .docx
        register_shutdown_function(function () {
            $tempFiles = File::files(public_path('temp'));
            foreach ($tempFiles as $file) {
                if (Str::endsWith($file->getFilename(), '.docx')) {
                    @unlink($file->getPathname());
                }
            }
        });
    
        return response()->download($caminhoZip)->deleteFileAfterSend(true);
    }
    
    
}
