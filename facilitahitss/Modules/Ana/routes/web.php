<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckModuleAccess;
use Modules\Ana\Http\Controllers\AnaUserController;
use Modules\Ana\Http\Controllers\CoordenacaoController;
use Modules\Ana\Http\Controllers\OrdemServicoController;
use Modules\Ana\Http\Controllers\RelatorioExecutivoController;
use Modules\Ana\Http\Controllers\RelatorioFaturamentoController;
use Modules\Ana\Http\Controllers\RelatorioExecutivoJustificativaController;
use Modules\Ana\Http\Controllers\AnaProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth', CheckModuleAccess::class])->group(function () {
    Route::prefix('ana')->name('ana::')->group(function () {
        Route::get('/', [AnaUserController::class, 'index'])->name('index');

        // Ordens de Serviço
        Route::prefix('ordens_servico')->name('ordens_servico.')->group(function () {
            Route::get('/', [OrdemServicoController::class, 'index'])->name('index');
            Route::get('/criar', [OrdemServicoController::class, 'criarOrdemServico'])->name('criar');
            Route::post('', [OrdemServicoController::class, 'salvarOrdemServico'])->name('salvar');
            Route::get('/{ordensServico}/editar', [OrdemServicoController::class, 'editarOrdemServico'])->name('editar');
            Route::put('/{ordensServico}', [OrdemServicoController::class, 'atualizarOrdemServico'])->name('atualizar');
            Route::delete('/{ordensServico}', [OrdemServicoController::class, 'excluirOrdemServico'])->name('excluir');
            Route::get('/escopos/{coordenacao_id}', [OrdemServicoController::class, 'getEscoposByCoordenacao']);
            Route::post('/{ordemServico}/atualizar-status', [OrdemServicoController::class, 'atualizarStatus'])->name('atualizarStatus');
            Route::post('/{ordensServico}/duplicar', [OrdemServicoController::class, 'duplicar'])->name('duplicar');
        });

        // Relatório Executivo
        Route::prefix('relatorio_executivo')->name('relatorio_executivo.')->group(function () {
            Route::get('/', [RelatorioExecutivoController::class, 'index'])->name('index');
            Route::get('/{id}/download', [RelatorioExecutivoController::class, 'baixarRelatorio'])->name('baixar');
            Route::get('/baixar-atualizado/{id}', [RelatorioExecutivoController::class, 'baixarRelatorioAtualizado'])->name('baixarAtualizado');
            Route::get('/baixar-todos', [RelatorioExecutivoController::class, 'baixarRelatoriosValidados'])->name('baixarValidados');
            Route::get('/criar', [RelatorioExecutivoController::class, 'criarRelatorio'])->name('criar');
            Route::post('', [RelatorioExecutivoController::class, 'salvarRelatorio'])->name('salvar');
            Route::get('/{id}/editar', [RelatorioExecutivoController::class, 'editarRelatorio'])->name('editar');
            Route::put('/{id}', [RelatorioExecutivoController::class, 'atualizarRelatorio'])->name('atualizar');
            Route::delete('/{id}', [RelatorioExecutivoController::class, 'excluirRelatorio'])->name('excluir');
            Route::get('/{id}/validar', [RelatorioExecutivoController::class, 'validarRelatorio'])->name('validarRelatorio');
            Route::post('/{id}/validar', [RelatorioExecutivoController::class, 'salvarValidacao'])->name('salvarValidacao');
        });

        // Justificativas
        Route::prefix('justificativas')->name('justificativas.')->group(function () {
            Route::get('/', [RelatorioExecutivoJustificativaController::class, 'index'])->name('index');
            Route::get('/{id}/visualizar', [RelatorioExecutivoJustificativaController::class, 'visualizar'])->name('visualizar');
            Route::get('/criar/{ordens_servico_id}', [RelatorioExecutivoJustificativaController::class, 'criar'])->name('criar');
            Route::post('/salvar', [RelatorioExecutivoJustificativaController::class, 'salvar'])->name('salvar');
        });

        // Atualizar perfil
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/{id}', [AnaProfileController::class, 'index'])->name('index');
            Route::put('/{id}', [AnaProfileController::class, 'atualizarPerfil'])->name('atualizarPerfil');
        });

        // Rotas admin (somente com CheckRole)
        Route::middleware([CheckRole::class])->group(function () {
            // Usuários
            Route::prefix('usuarios')->name('usuarios.')->group(function () {
                Route::get('/', [AnaUserController::class, 'painel'])->name('painel');
                Route::get('/{id}/edit', [AnaUserController::class, 'edit'])->name('edit');
                Route::post('/{id}', [AnaUserController::class, 'update'])->name('update');
                Route::get('/{id}', [AnaUserController::class, 'create'])->name('create');
                Route::post('/escopos/by-coordenacao', [AnaUserController::class, 'getEscoposByCoordenacao'])->name('getEscoposByCoordenacao');
                Route::post('/store', [AnaUserController::class, 'store'])->name('store');
            });

            // Coordenação
            Route::prefix('coordenacoes')->name('coordenacoes.')->group(function () {
                Route::get('/', [CoordenacaoController::class, 'index'])->name('index');
                Route::get('/create', [CoordenacaoController::class, 'criarCoordenacao'])->name('criar');
                Route::post('', [CoordenacaoController::class, 'salvarCoordenacao'])->name('salvar');
                Route::get('/{coordenacao}/edit', [CoordenacaoController::class, 'editarCoordenacao'])->name('editar');
                Route::put('/{coordenacao}', [CoordenacaoController::class, 'atualizarCoordenacao'])->name('atualizar');
                Route::delete('/{coordenacao}', [CoordenacaoController::class, 'excluirCoordenacao'])->name('excluir');
            });

            // Relatórios de Faturamento
            Route::prefix('relatorio_faturamento')->name('relatorio_faturamento.')->group(function () {
                Route::get('/', [RelatorioFaturamentoController::class, 'index'])->name('index');
                Route::get('/criar', [RelatorioFaturamentoController::class, 'criarRelatorioFaturamento'])->name('criar');
                Route::post('', [RelatorioFaturamentoController::class, 'salvarRelatorioFaturamento'])->name('salvar');
                Route::get('/{id}/editar', [RelatorioFaturamentoController::class, 'editarRelatorioFaturamento'])->name('editar');
                Route::put('/{id}', [RelatorioFaturamentoController::class, 'atualizarRelatorioFaturamento'])->name('atualizar');
                Route::get('/{id}/download', [RelatorioFaturamentoController::class, 'baixarRelatorio'])->name('baixar');
                Route::delete('/{id}', [RelatorioFaturamentoController::class, 'excluirRelatorio'])->name('excluir');
            });

            // Justificativas Admin
            Route::prefix('justificativas')->name('justificativas.')->group(function () {
                Route::post('/{id}/validar', [RelatorioExecutivoJustificativaController::class, 'validar'])->name('validar');
            });

            // Recriação de Relatórios
            Route::prefix('relatorio_executivo')->name('relatorio_executivo.')->group(function () {
                Route::get('/recriar', [RelatorioExecutivoController::class, 'recriarTodosRelatorios'])->name('recriar');
            });
        });
    });
});
