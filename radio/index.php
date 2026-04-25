<?php
require_once 'conexao.php';
try {
    // Busca todas as configurações de uma vez
    $stmt = $pdo->query("SELECT * FROM configuracoes LIMIT 1");
    $config = $stmt->fetch();

    // Define os valores (se não existirem, usa um padrão)
    $modo = $config['modo_reproducao'] ?? 'fixo';
    $regraFixa = $config['musicas_por_propaganda'] ?? 3;
    $minMusicas = $config['min_musicas'] ?? 2;
    $maxMusicas = $config['max_musicas'] ?? 5;

    $musicas = $pdo->query("SELECT titulo, caminho_arquivo FROM audios WHERE tipo = 'musica'")->fetchAll();
    $propagandas = $pdo->query("SELECT titulo, caminho_arquivo FROM audios WHERE tipo = 'propaganda'")->fetchAll();
} catch (Exception $e) { 
    die("Erro de banco de dados no Player."); 
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Player - Rádio Alta Linha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700&display=swap" rel="stylesheet">
    
    <style>
        .fonte-logo { font-family: 'Montserrat', sans-serif; }
    </style>
</head>
<body class="bg-gray-800 h-screen flex flex-col items-center justify-center text-white">
    
    <div class="bg-gray-900 p-10 rounded-2xl shadow-2xl text-center w-full max-w-md border border-gray-700 border-t-4 border-t-red-800">
        
        <div class="flex flex-col items-center justify-center mb-10">
            <img src="assets/img/logo.png" alt="Alta Linha" class="h-20 object-contain">
            <span class="text-gray-300 text-lg font-bold mt-2 tracking-[0.4em] uppercase fonte-logo">Rádio</span>
        </div>
        
        <div class="h-24 flex items-center justify-center bg-gray-800 rounded-lg mb-8 border border-gray-700 p-4 shadow-inner">
            <div id="status-display"><p class="text-gray-500 italic">Pronto para iniciar...</p></div>
        </div>
        
        <button id="btn-play" class="w-full bg-red-800 hover:bg-red-900 text-white font-bold py-4 rounded-xl shadow-lg transition-all text-lg tracking-wide uppercase">
            Iniciar Transmissão
        </button>
    </div>

    <audio id="audio-1" preload="auto" style="display:none;"></audio>
    <audio id="audio-2" preload="auto" style="display:none;"></audio>

    <script>
        // Dados injetados pelo PHP para o JavaScript ler a nova lógica dinâmica!
        window.RADIO_DATA = {
            modo: "<?= $modo ?>",
            regraFixa: <?= $regraFixa ?>,
            minMusicas: <?= $minMusicas ?>,
            maxMusicas: <?= $maxMusicas ?>,
            musicas: <?= json_encode($musicas) ?>,
            propagandas: <?= json_encode($propagandas) ?>
        };
    </script>
    <script src="assets/js/player.js"></script>
</body>
</html>