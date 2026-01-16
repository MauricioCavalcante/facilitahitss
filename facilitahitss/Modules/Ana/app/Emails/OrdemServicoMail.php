<?php

namespace Modules\Ana\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrdemServicoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $ordemServico;
    public $usuario;
    public $escopos;

    /**
     * Create a new message instance.
     */
    public function __construct($ordemServico, $usuario, $escopos)
    {
        $this->ordemServico = $ordemServico;
        $this->usuario = $usuario;
        $this->escopos = $escopos;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject('Nova Ordem de Serviço Atribuída!')
        ->attach(public_path('img/logo_hitssbr.jpg'), [
            'as' => 'logo.jpg',
            'mime' => 'image/jpeg',
        ])
        ->view('ana::emails.ordem_servico')
        ->with([
            'ordemServico' => $this->ordemServico,
            'usuario' => $this->usuario,
            'escopos' => $this->escopos
        ]);
    }
}
