<!DOCTYPE html>
<html>

<head>
    <title>Nova Ordem de Serviço!</title>
</head>

<body>

    <p>Olá, {{ $usuario->name }}.</p>
    <p>Uma nova <strong>Ordem de Serviço</strong> foi criada e atribuída a você.</p>
    <p><strong>📌 Detalhes:</strong></p>
    <ul >
        <li><strong>Número da OS:</strong> {{ $ordemServico->numero }}</li>
        <li><strong>Data Fim:</strong> {{ $ordemServico->data_fim }}</li>
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
    <p>Por favor, verifique a OS no sistema e inicie os procedimentos necessários.</p>
    <p>Atenciosamente, <br><br> Facilta</p><br>
    <div>
        <img src="cid:logo.jpg" alt="Logo" style="height: 50px;">
    </div>
</body>

</html>
