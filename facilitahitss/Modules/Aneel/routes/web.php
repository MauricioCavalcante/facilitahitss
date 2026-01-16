<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckModuleAccess;
use Modules\Aneel\Http\Middleware\RoleMiddleware;
use Modules\Aneel\Http\Middleware\CheckDeletePermission;
use Modules\Aneel\Http\Controllers\AneelController;
use Modules\Aneel\Http\Controllers\AneelReportController;
use Modules\Aneel\Http\Controllers\AneelIndicadoresController;
use Modules\Aneel\Http\Controllers\AneelRelatorioRTAController;

use Livewire\Livewire;

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

Route::middleware(['web', 'auth', CheckModuleAccess::class])->group(function () {
    Route::prefix('aneel')->name('aneel::')->group(function () {
        Route::get('/', [AneelController::class, 'index'])->name('index');
        Route::get('/download-all/{report_id}', [AneelReportController::class, 'downloadImages'])->name('downloadImages');
        Route::get('/image/{id}/download', [AneelReportController::class, 'downloadImagesById'])->name('downloadImagesById');
        Route::get('/indicadores/anexo/{id}/download', [AneelReportController::class, 'downloadIndicatorAttachment'])->name('downloadIndicatorAttachment');
        Route::get('/reports/download/{id}', [AneelController::class, 'downloadAneelReport'])->name('archivesRTA');
        Route::get('/download/{id}', [AneelController::class, 'downloadReport'])->name('downloadReport');
        Route::get('/download-xlsx/{id}', [AneelController::class, 'downloadXlsx'])->name('downloadAneelXlsx');



        Route::prefix('reports')->name('reportsRTA.')->group(function () {
            Route::get('/', [AneelReportController::class, 'index'])->name('index');
            Route::get('/{id}/details', [AneelReportController::class, 'show'])->name('show');
        });

        Route::middleware([RoleMiddleware::class])->group(function () {
            Route::prefix('reports')->name('reportsRTA.')->group(function () {
                Route::get('/new_report', [AneelReportController::class, 'create'])->name('create');
                Route::post('/store', [AneelReportController::class, 'store'])->name('store');
                Route::get('/{id}/edit', [AneelReportController::class, 'edit'])->name('edit');
                Route::put('/{id}/update', [AneelReportController::class, 'update'])->name('update');
                Route::put('/{id}/updateReport', [AneelReportController::class, 'updateReport'])->name('updateReport');
                Route::put('/{id}/updateIndicator', [AneelReportController::class, 'updateIndicator'])->name('updateIndicator');
                Route::post('/update-xlsx/{id}', [AneelReportController::class, 'updateXlsx'])->name('updateXlsx');
                Route::get('/{id}/gerar', [AneelReportController::class, 'generateRTAReport'])->name('generateRTAReport');
                Route::get('/{id}', [AneelReportController::class, 'finalizeReport'])->name('finalizeReport');
                Route::get('/{id}/finalize', [AneelReportController::class, 'finalizeGenerateReport'])->name('finalizeGenerateReport');

                Route::middleware([CheckDeletePermission::class])->group(function () {
                    Route::delete('/{id}/destroy', [AneelReportController::class, 'destroy'])->name('destroy');
                    Route::delete('/indicator/{id}/remove', [AneelReportController::class, 'deleteAttachment'])->name('removeIndicatorAttachment');
                    Route::delete('/image/{id}/remove', [AneelReportController::class, 'deleteImageAttachment'])->name('removeImageAttachment');
                    Route::delete('/attachment/{id}/remove', [AneelController::class, 'deleteAttachmentRTA'])->name('deleteAttachmentRTA');
                    Route::delete('/attachmentXlsx/{id}/remove', [AneelController::class, 'deleteAttachmentXlsx'])->name('deleteAttachmentXlsx');
                });
            });
        });
    });
});

