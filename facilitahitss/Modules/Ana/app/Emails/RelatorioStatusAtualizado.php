<?php

namespace Modules\Ana\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Ana\Models\AnaRelatorioExecutivo;
use Modules\Ana\Models\AnaOrdemServico;

class RelatorioStatusAtualizado extends Mailable
{
    use Queueable, SerializesModels;

    public $relatorio;
    public $usuario;
    public $escopos;
    
    public $status;
    public $numeroOrdemServico;

    /**
     * Cria uma nova instância do Mailable.
     *
     * @param \Modules\Ana\Models\AnaRelatorioExecutivo $relatorio
     * @param string $status
     */

    public function __construct(AnaRelatorioExecutivo $relatorio, $status, $escopos, $usuario)
    {
        $this->relatorio = $relatorio;
        $this->status = $status;
        $this->usuario = $usuario;
        $this->escopos = $escopos;
        $ordemServico = AnaOrdemServico::find($relatorio->ordem_servico_id);

        $this->numeroOrdemServico = $ordemServico ? $ordemServico->numero : 'Desconhecido';
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Status do Relatório Atualizado')
        ->attach(public_path('img/logo_hitssbr.jpg'), [
            'as' => 'logo.jpg',
            'mime' => 'image/jpeg',
        ])
            ->view('ana::emails.relatorio_status_atualizado')
            ->with([
                'relatorio' => $this->relatorio,
                'usuario' => $this->usuario,
                'status' => $this->status,
                'numeroOrdemServico' => $this->numeroOrdemServico,
            ]);
    }
}
