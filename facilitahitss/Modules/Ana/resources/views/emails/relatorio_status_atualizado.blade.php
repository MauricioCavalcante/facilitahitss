<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status do Relatório Atualizado</title>
</head>
<body>
    <div>
        <img src="cid:logo.jpg" alt="Logo" style="height: 50px;">
    </div>
    <p>Olá, {{ $usuario->name }}.</p>
    <p>A Ordem de Serviço <strong>{{ $numeroOrdemServico }}</strong> teve seu status atualizado para <strong>{{ $status }}</strong>.</p>
    <p><strong>📌 Detalhes da Atualização:</strong></p>
    <ul>
        <li style="margin-bottom: 15px;"><strong>Descrição:</strong></li>
        <ul>
            @foreach ($escopos as $escopo)
                <?php
                $data = json_decode($escopo->escopo);
                ?>
                <li style="margin-bottom: 15px;">{{ htmlspecialchars_decode($data->escopo) }}</li> 
            @endforeach
        </ul>
    </ul>
    <p>Para mais detalhes, acesse o sistema.</p><br>
    <p>Atenciosamente, <br><br> Facilta</p><br>
    <div>
        <img src="cid:logo.jpg" alt="Logo" style="height: 50px;">
    </div>
</body>
</html>
