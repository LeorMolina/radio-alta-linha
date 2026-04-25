<?php
session_start();
require_once '../conexao.php';

if (!isset($pdo)) {
    die("Erro fatal: A variável de ligação à base de dados não foi inicializada.");
}

// --- AUTO-MIGRAÇÃO DE BANCO DE DADOS ---
// Cria as novas colunas para o modo dinâmico automaticamente se elas não existirem
try {
    $pdo->query("SELECT modo_reproducao FROM configuracoes LIMIT 1");
} catch (PDOException $e) {
    $pdo->exec("ALTER TABLE configuracoes ADD COLUMN modo_reproducao ENUM('fixo', 'dinamico') DEFAULT 'fixo'");
    $pdo->exec("ALTER TABLE configuracoes ADD COLUMN min_musicas INT DEFAULT 2");
    $pdo->exec("ALTER TABLE configuracoes ADD COLUMN max_musicas INT DEFAULT 5");
}

$erro = '';
$sucesso = isset($_GET['sucesso']) ? true : false;
$deletado = isset($_GET['msg']) && $_GET['msg'] === 'deletado';
$regra_atualizada = isset($_GET['regra_ok']) ? true : false;

// Lógica de Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['senha'])) {
    $stmt = $pdo->query("SELECT senha_admin FROM configuracoes LIMIT 1");
    $config = $stmt->fetch();
    if ($config && password_verify($_POST['senha'], $config['senha_admin'])) {
        $_SESSION['admin_logado'] = true;
        header("Location: index.php"); exit;
    } else { $erro = "Palavra-passe incorreta."; }
}

// Lógica de Logout
if (isset($_GET['sair'])) { session_destroy(); header("Location: index.php"); exit; }

// --- LÓGICA: ATUALIZAR REGRA DA GRADE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_regra']) && isset($_SESSION['admin_logado'])) {
    $modo = $_POST['modo_reproducao'];
    $fixo = (int)$_POST['musicas_por_propaganda'];
    $min = (int)$_POST['min_musicas'];
    $max = (int)$_POST['max_musicas'];
    
    $stmt = $pdo->prepare("UPDATE configuracoes SET modo_reproducao = ?, musicas_por_propaganda = ?, min_musicas = ?, max_musicas = ?");
    $stmt->execute([$modo, $fixo, $min, $max]);
    header("Location: index.php?regra_ok=1"); exit;
}

// Buscar dados se logado
$audios = [];
$config_regra = null;
if (isset($_SESSION['admin_logado'])) {
    $stmt = $pdo->query("SELECT * FROM audios ORDER BY id DESC");
    $audios = $stmt->fetchAll();
    
    $stmt_regra = $pdo->query("SELECT * FROM configuracoes LIMIT 1");
    $config_regra = $stmt_regra->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Rádio Alta Linha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700&display=swap" rel="stylesheet">
    <style> .fonte-logo { font-family: 'Montserrat', sans-serif; } </style>
</head>
<body class="bg-gray-800 h-screen flex items-center justify-center p-4">

    <?php if (!isset($_SESSION['admin_logado'])): ?>
        <div class="bg-gray-900 p-8 rounded-lg shadow-2xl w-full max-w-sm border-t-4 border-red-800">
            <div class="flex flex-col items-center justify-center mb-8">
                <img src="../assets/img/logo.png" alt="Alta Linha" class="h-20 object-contain">
                <span class="text-gray-300 text-lg font-bold mt-2 tracking-[0.4em] uppercase fonte-logo">Rádio</span>
            </div>
            
            <?php if ($erro): ?>
                <div class="bg-red-900/50 border-l-4 border-red-500 text-red-200 p-3 mb-4 text-sm font-medium">
                    <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <input type="password" name="senha" placeholder="Palavra-passe de Acesso" required class="w-full bg-gray-800 text-white border border-gray-700 p-3 rounded focus:outline-none focus:border-red-800">
                <button type="submit" class="w-full bg-red-800 text-white font-bold py-3 rounded hover:bg-red-900 transition">Entrar</button>
            </form>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-6xl h-[85vh] flex flex-col overflow-hidden border-t-4 border-red-800">
            <header class="flex justify-between items-center p-4 border-b border-gray-700 bg-gray-900">
                <div class="flex items-center">
                    <div class="flex flex-col items-center mr-4">
                        <img src="../assets/img/logo.png" alt="Alta Linha" class="h-8 object-contain">
                        <span class="text-gray-300 text-[10px] font-bold mt-1 tracking-[0.3em] uppercase fonte-logo">Rádio</span>
                    </div>
                    <span class="text-gray-400 text-lg font-normal border-l border-gray-600 pl-4 hidden sm:block">Gerenciador</span>
                </div>
                <a href="?sair=1" class="text-gray-400 hover:text-red-500 font-semibold transition">Sair (Logout)</a>
            </header>
            
            <main class="flex-1 flex overflow-hidden">
                <div class="w-1/3 p-6 border-r border-gray-200 bg-gray-50 flex flex-col overflow-y-auto">
                    
                    <h2 class="text-lg font-bold mb-4 text-gray-800">Enviar Novo Áudio</h2>
                    <form action="upload.php" method="POST" enctype="multipart/form-data" class="space-y-4 mb-8">
                        <input type="file" name="audio" accept=".mp3" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:bg-gray-800 file:text-white cursor-pointer">
                        <select name="tipo" required class="block w-full border-gray-300 rounded-md p-2 border">
                            <option value="musica">Música (Programação)</option>
                            <option value="propaganda">Propaganda / Jingle</option>
                        </select>
                        <button type="submit" class="w-full bg-red-800 text-white font-bold py-2 rounded hover:bg-red-900 transition shadow">Fazer Upload</button>
                    </form>

                    <div class="pt-6 border-t border-gray-200">
                        <h2 class="text-lg font-bold mb-4 text-gray-800">Configurações da Grade</h2>
                        <?php if ($regra_atualizada): ?>
                            <p class="text-xs text-green-600 font-bold mb-2">✓ Configuração guardada com sucesso!</p>
                        <?php endif; ?>
                        
                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Modo de Reprodução</label>
                                <select name="modo_reproducao" id="modo_reproducao" onchange="toggleModoGrade()" class="block w-full border-gray-300 rounded-md p-2 border focus:border-red-800 bg-white">
                                    <option value="fixo" <?= ($config_regra['modo_reproducao'] ?? 'fixo') === 'fixo' ? 'selected' : '' ?>>Fixo (Exato)</option>
                                    <option value="dinamico" <?= ($config_regra['modo_reproducao'] ?? '') === 'dinamico' ? 'selected' : '' ?>>Dinâmico (Aleatório)</option>
                                </select>
                            </div>

                            <div id="div_fixo" style="display: <?= ($config_regra['modo_reproducao'] ?? 'fixo') === 'fixo' ? 'block' : 'none' ?>;">
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Músicas entre Propagandas</label>
                                <input type="number" name="musicas_por_propaganda" value="<?= $config_regra['musicas_por_propaganda'] ?? 3 ?>" min="1" max="20"
                                       class="block w-full border-gray-300 rounded-md p-2 border focus:border-red-800 outline-none">
                            </div>

                            <div id="div_dinamico" class="space-x-2" style="display: <?= ($config_regra['modo_reproducao'] ?? 'fixo') === 'dinamico' ? 'flex' : 'none' ?>;">
                                <div class="w-1/2">
                                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Mínimo</label>
                                    <input type="number" name="min_musicas" value="<?= $config_regra['min_musicas'] ?? 2 ?>" min="1" max="20"
                                           class="block w-full border-gray-300 rounded-md p-2 border focus:border-red-800 outline-none">
                                </div>
                                <div class="w-1/2">
                                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Máximo</label>
                                    <input type="number" name="max_musicas" value="<?= $config_regra['max_musicas'] ?? 5 ?>" min="1" max="20"
                                           class="block w-full border-gray-300 rounded-md p-2 border focus:border-red-800 outline-none">
                                </div>
                            </div>

                            <button type="submit" name="atualizar_regra" class="w-full bg-gray-800 text-white font-bold py-2 rounded hover:bg-gray-900 transition">
                                Salvar Nova Regra
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="w-2/3 p-6 overflow-y-auto bg-white">
                    <h2 class="text-lg font-bold mb-4 text-gray-800">Biblioteca de Áudios</h2>
                    <form action="delete.php" method="POST" class="space-y-3">
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-sm text-gray-500">Gerenciar ficheiros:</span>
                            <button type="submit" onclick="return confirm('Tem a certeza que deseja apagar?');" class="bg-gray-200 hover:bg-red-100 hover:text-red-800 text-gray-600 font-bold py-1 px-3 rounded text-xs transition">Apagar Selecionados</button>
                        </div>
                        <?php foreach ($audios as $audio): ?>
                            <label class="flex items-center justify-between p-3 border rounded cursor-pointer transition hover:bg-gray-50">
                                <div class="flex items-center space-x-3">
                                    <input type="checkbox" name="ids[]" value="<?= $audio['id'] ?>" class="w-4 h-4 text-red-800">
                                    <span class="text-xs font-bold uppercase px-2 py-1 rounded <?= $audio['tipo'] === 'musica' ? 'bg-gray-200' : 'bg-red-200 text-red-900' ?>">
                                        <?= $audio['tipo'] ?>
                                    </span>
                                    <span class="text-gray-700 text-sm truncate w-64"><?= htmlspecialchars($audio['titulo']) ?></span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </form>
                </div>
            </main>
        </div>
    <?php endif; ?>

    <script>
        function toggleModoGrade() {
            const modo = document.getElementById('modo_reproducao').value;
            const divFixo = document.getElementById('div_fixo');
            const divDinamico = document.getElementById('div_dinamico');
            
            if(modo === 'fixo') {
                divFixo.style.display = 'block';
                divDinamico.style.display = 'none';
            } else {
                divFixo.style.display = 'none';
                divDinamico.style.display = 'flex';
            }
        }
    </script>
</body>
</html>