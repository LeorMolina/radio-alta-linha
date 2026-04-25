<?php
session_start();
require_once '../conexao.php';

if (!isset($_SESSION['admin_logado']) || !isset($pdo)) {
    http_response_code(403);
    die("Acesso negado ou banco indisponível.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['audio'])) {
    $tipo = $_POST['tipo'] ?? '';
    
    if (!in_array($tipo, ['musica', 'propaganda'])) {
        die("Tipo de áudio inválido.");
    }

    $arquivo = $_FILES['audio'];
    
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        die("Erro na transmissão do arquivo. Código: " . $arquivo['error']);
    }

    // Validação de MIME Type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $arquivo['tmp_name']);
    finfo_close($finfo);

    $mimesPermitidos = ['audio/mpeg', 'audio/mp3'];
    if (!in_array($mime, $mimesPermitidos)) {
        die("Arquivo não suportado. Envie apenas MP3.");
    }

    // Limpeza de nomenclatura para evitar quebra de URL no Player JS
    $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
    $nomeOriginal = pathinfo($arquivo['name'], PATHINFO_FILENAME);
    $nomeLimpo = preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '_', $nomeOriginal));
    $novoNome = $nomeLimpo . '_' . time() . '.' . $extensao;

    $pastaDestino = '../uploads/' . $tipo . 's/';
    
    if (!is_dir($pastaDestino)) {
        mkdir($pastaDestino, 0755, true);
    }

    $caminhoCompleto = $pastaDestino . $novoNome;
    $caminhoRelativo = 'uploads/' . $tipo . 's/' . $novoNome;

    // Persistência em disco e banco
    if (move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
        $stmt = $pdo->prepare("INSERT INTO audios (titulo, caminho_arquivo, tipo) VALUES (?, ?, ?)");
        $stmt->execute([$arquivo['name'], $caminhoRelativo, $tipo]);
        
        header("Location: index.php?sucesso=1");
        exit;
    } else {
        die("Falha de permissão no servidor ao mover o arquivo.");
    }
} else {
    header("Location: index.php");
    exit;
}