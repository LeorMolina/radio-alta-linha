document.addEventListener('DOMContentLoaded', () => {
    const state = {
        musicas: [...window.RADIO_DATA.musicas].sort(() => Math.random() - 0.5),
        propagandas: [...window.RADIO_DATA.propagandas].sort(() => Math.random() - 0.5),
        
        // --- NOVA LÓGICA DINÂMICA ---
        minMusicas: 1, // Mínimo de músicas entre anúncios
        maxMusicas: 3, // Máximo de músicas entre anúncios
        regraAtual: 0, // Será definida no início e a cada anúncio
        // ----------------------------
        
        musicasTocadas: 0,
        isFading: false
    };

    // Função para sortear quantas músicas tocarão no próximo bloco
    function sortearProximoBloco() {
        state.regraAtual = Math.floor(Math.random() * (state.maxMusicas - state.minMusicas + 1)) + state.minMusicas;
        console.log(`>>> Sorteio realizado: O próximo bloco terá ${state.regraAtual} músicas.`);
    }

    // Inicializa o primeiro sorteio
    sortearProximoBloco();

    const statusDisplay = document.getElementById('status-display');
    const player1 = document.getElementById('audio-1');
    const player2 = document.getElementById('audio-2');
    const btnPlay = document.getElementById('btn-play');
    
    let currentPlayer = player1;
    const FADE_TIME = 2.5;

    function getNextTrack() {
        // Verifica se atingiu a regra sorteada para este bloco
        if (state.musicasTocadas >= state.regraAtual && state.propagandas.length > 0) {
            state.musicasTocadas = 0;
            
            // Após escolher a propaganda, sorteamos o tamanho do PRÓXIMO bloco de músicas
            sortearProximoBloco(); 
            
            let ad = state.propagandas.shift();
            state.propagandas.push(ad);
            ad.categoria = 'PROPAGANDA';
            return ad;
        }
        
        state.musicasTocadas++;
        let mus = state.musicas.shift();
        state.musicas.push(mus);
        mus.categoria = 'MÚSICA';
        return mus;
    }

    // ... (Mantenha as funções playTrack e triggerTransition exatamente como estão no código anterior) ...

    function playTrack(player, isFirst = false) {
        const data = getNextTrack();
        player.src = data.caminho_arquivo;
        player.volume = isFirst ? 1 : 0;
        player.load(); 

        player.oncanplaythrough = () => {
            player.play().then(() => {
                statusDisplay.innerHTML = `
                    <span class="text-xs font-bold px-2 py-1 rounded bg-${data.categoria === 'MÚSICA' ? 'blue' : 'yellow'}-500/20 text-${data.categoria === 'MÚSICA' ? 'blue' : 'yellow'}-400 mb-1 block w-max mx-auto uppercase">
                        ${data.categoria}
                    </span>
                    <p class="text-white font-semibold truncate w-64">${data.titulo}</p>
                `;
                if (!isFirst) {
                    let vol = 0;
                    let fadeIn = setInterval(() => {
                        vol += 0.05;
                        if (vol >= 1) { player.volume = 1; clearInterval(fadeIn); }
                        else { player.volume = vol; }
                    }, 100);
                }
            }).catch(e => console.error("Erro de Play:", e));
        };

        player.ontimeupdate = () => {
            if (isNaN(player.duration) || state.isFading) return;
            let timeLeft = player.duration - player.currentTime;
            if (timeLeft <= FADE_TIME) {
                triggerTransition(player);
            }
        };

        player.onended = () => {
            if (!state.isFading) triggerTransition(player);
        };
    }

    function triggerTransition(player) {
        state.isFading = true;
        let nextPlayer = (currentPlayer === player1) ? player2 : player1;
        currentPlayer = nextPlayer;
        playTrack(nextPlayer);
        let vol = 1;
        let fadeOut = setInterval(() => {
            vol -= 0.05;
            if (vol <= 0) {
                player.volume = 0;
                player.pause();
                clearInterval(fadeOut);
                setTimeout(() => { state.isFading = false; }, 500);
            } else { player.volume = vol; }
        }, 100);
    }

    btnPlay.addEventListener('click', () => {
        [player1, player2].forEach(p => { p.play().then(() => p.pause()); });
        btnPlay.style.display = 'none';
        playTrack(currentPlayer, true);
    });
});