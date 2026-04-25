<?php
session_start();
require_once '../conexao.php'; // Caminho para sua conexão

// Verifica se está logado (descomente ou ajuste conforme a sua segurança)
// if (!isset($_SESSION['admin_logado'])) { die("Acesso negado."); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ids'])) {
    $ids = $_POST['ids'];
    
    // Cria os placeholders dinâmicos (ex: ?, ?, ?)
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    // Passo 1: Buscar os caminhos dos arquivos para apagar do disco
    $stmt = $pdo->prepare("SELECT caminho_arquivo FROM audios WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $arquivos = $stmt->fetchAll();
    
    foreach ($arquivos as $arq) {
        $caminhoFisico = '../' . $arq['caminho_arquivo'];
        // Se o arquivo existir na pasta uploads, nós o apagamos
        if (file_exists($caminhoFisico)) {
            unlink($caminhoFisico);
        }
    }
    
    // Passo 2: Apagar os registros do banco de dados
    $stmtDelete = $pdo->prepare("DELETE FROM audios WHERE id IN ($placeholders)");
    $stmtDelete->execute($ids);
}

// Redireciona de volta para o painel
header("Location: index.php?msg=deletado");
exit;