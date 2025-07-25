<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>LCT TV - Canais Ao Vivo</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #0f172a;
      color: white;
      overflow-x: hidden;
      min-height: 100vh;
      margin: 0;
      display: flex;
      flex-direction: column;
    }
    canvas {
      position: fixed;
      top: 0;
      left: 0;
      z-index: -1;
      width: 100vw;
      height: 100vh;
    }
    .fade-in {
      animation: fadeIn 0.8s ease-in forwards;
    }
    .fade-out {
      animation: fadeOut 0.8s ease-out forwards;
    }
    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(10px);}
      to {opacity: 1; transform: translateY(0);}
    }
    @keyframes fadeOut {
      from {opacity: 1;}
      to {opacity: 0; visibility: hidden;}
    }
  </style>
</head>
<body>

  <!-- Partículas -->
  <canvas id="webCanvas"></canvas>

  <!-- Tela de Login -->
  <section id="login-screen" class="flex flex-col items-center justify-center min-h-screen p-4 bg-gradient-to-br from-indigo-900 via-purple-900 to-pink-900">
    <div class="bg-gray-900 bg-opacity-80 rounded-xl p-10 w-full max-w-md shadow-lg">
      <h1 class="text-4xl font-extrabold mb-6 text-center text-yellow-300 drop-shadow-lg">🔒 Login LCT TV</h1>
      <form id="login-form" class="flex flex-col gap-4">
        <input
          id="username"
          type="text"
          placeholder="Digite seu nome de usuário"
          class="px-4 py-3 rounded-lg bg-gray-800 text-white placeholder-gray-400 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-yellow-400"
          autocomplete="off"
          required
          autofocus
        />
        <button
          type="submit"
          class="bg-yellow-400 text-gray-900 font-bold py-3 rounded-lg hover:bg-yellow-500 transition"
        >
          LOGIN
        </button>
      </form>
    </div>
  </section>

  <!-- Tela do App (Canais) -->
  <section id="app-screen" class="hidden flex flex-col min-h-screen">
      <!-- Cabeçalho -->
  <header class="py-2 px-4 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 shadow-md flex justify-center items-center">
  <h1 class="text-xl font-bold text-white drop-shadow-[0_0_5px_rgba(255,255,255,0.4)]">
    📺 LCT <span class="text-yellow-300 animate-pulse">TV</span>
  </h1>
</header>




      
      <div class="flex items-center gap-4">
        <input
          id="search-input"
          type="text"
          placeholder="🔍 Buscar canal..."
          class="px-4 py-2 rounded-lg bg-gray-800 text-white border border-gray-700 focus:outline-none focus:ring-2 focus:ring-yellow-400 shadow-inner"
        />
        <button
          id="logout-btn"
          class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold transition"
          title="Sair"
        >
          Logout
        </button>
      </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="flex-1 flex flex-col gap-4 p-4 bg-[#0f172a]">
      <section
        id="player-container"
        class="relative aspect-video max-w-5xl mx-auto mt-4 bg-yellow rounded-xl shadow-2xl border-4 border-indigo-500/60 overflow-hidden transition-all duration-500"
      >
        <iframe
          id="video-player"
          class="w-full h-full"
          src=""
          allowfullscreen
          frameborder="0"
        ></iframe>
        <button
          id="fullscreen-btn"
          class="absolute top-3 right-3 p-2 bg-indigo-700/80 hover:bg-indigo-600 text-white rounded-md shadow-md transition-transform hover:scale-110"
          title="Tela cheia"
        >
          ⛶
        </button>
      </section>

      <p
        id="channel-title"
        class="text-center text-xl mt-4 text-white font-semibold tracking-wide transition-all duration-300 select-none"
      >
        Carregando canal...
      </p>

      <div
        id="loading-indicator"
        class="text-center text-gray-400 font-medium mt-2 animate-pulse"
      >
        🔄 Carregando canais...
      </div>

      <div
        id="no-results"
        class="hidden text-center text-red-500 font-bold mt-2 select-none"
      >
        Nenhum canal encontrado.
      </div>

      <div
        id="channel-grid"
        class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 fade-in p-2"
      ></div>
    </main>

    <!-- Rodapé -->
    <footer
      class="p-4 text-center text-sm text-gray-300 bg-gray-900 border-t border-gray-700 mt-auto select-none"
    >
      LCT TV © COPYRIGHT 2025 & 2026 @LEOMODZOFC TODOS DIRETOS RESERVADOS
    </footer>
  </section>

  <!-- JavaScript principal -->
  <script>
    // Referências DOM
    const loginScreen = document.getElementById('login-screen');
    const loginForm = document.getElementById('login-form');
    const appScreen = document.getElementById('app-screen');
    const playerContainer = document.getElementById('player-container');
    const videoPlayer = document.getElementById('video-player');
    const channelTitle = document.getElementById('channel-title');
    const channelGrid = document.getElementById('channel-grid');
    const searchInput = document.getElementById('search-input');
    const loadingIndicator = document.getElementById('loading-indicator');
    const noResultsMessage = document.getElementById('no-results');
    const fullscreenBtn = document.getElementById('fullscreen-btn');
    const logoutBtn = document.getElementById('logout-btn');

    const API_URL = 'https://api-flixy-canais.vercel.app/all';
    let allChannels = [];

    // Funções para salvar, pegar e limpar login no localStorage
    function saveLogin(username) {
      localStorage.setItem('lcttv_user', username);
    }
    function getSavedLogin() {
      return localStorage.getItem('lcttv_user');
    }
    function clearLogin() {
      localStorage.removeItem('lcttv_user');
    }

    // Ao carregar a página, verifica login salvo
    window.addEventListener('load', () => {
      const savedUser = getSavedLogin();
      if (savedUser) {
        loginScreen.style.display = 'none';
        appScreen.classList.remove('hidden');
        appScreen.classList.add('fade-in');
        loadChannels();
      }
    });

    // Formulário de login
    loginForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const username = document.getElementById('username').value.trim();
      if (!username) return alert('Por favor, informe o usuário');
      saveLogin(username);
      loginScreen.classList.add('fade-out');
      setTimeout(() => {
        loginScreen.style.display = 'none';
        appScreen.classList.remove('hidden');
        appScreen.classList.add('fade-in');
        loadChannels();
      }, 800);
    });

    // Logout
    logoutBtn.addEventListener('click', () => {
      clearLogin();
      videoPlayer.src = '';
      channelTitle.textContent = 'Carregando canal...';
      channelGrid.innerHTML = '';
      loadingIndicator.classList.remove('hidden');
      noResultsMessage.classList.add('hidden');
      appScreen.classList.add('hidden');
      loginScreen.style.display = 'flex';
      loginScreen.classList.remove('fade-out');
      loginScreen.classList.add('fade-in');
      document.getElementById('username').value = '';
      document.getElementById('username').focus();
    });

    // Carregar canais da API
    async function loadChannels() {
      try {
        const response = await fetch(API_URL);
        if (!response.ok) throw new Error(`Erro HTTP: ${response.status}`);
        const channels = await response.json();
        allChannels = channels;
        displayChannels(allChannels);
        if (allChannels.length > 0) {
          playChannel(allChannels[0].player_url, allChannels[0].nome);
        }
      } catch (error) {
        console.error('Erro ao carregar canais:', error);
        channelGrid.innerHTML = `<p class="text-red-500 col-span-full text-center">Erro ao carregar os canais. Tente novamente mais tarde.</p>`;
      } finally {
        loadingIndicator.classList.add('hidden');
        channelGrid.classList.add('fade-in');
      }
    }

    // Exibir canais na grade
    function displayChannels(channels) {
      channelGrid.innerHTML = '';
      noResultsMessage.classList.toggle('hidden', channels.length > 0);
      channels.forEach((channel) => {
        const card = document.createElement('div');
        card.className =
          'channel-card cursor-pointer bg-gray-900 rounded-xl overflow-hidden hover:ring-2 hover:ring-pink-500 transition-all shadow-lg hover:shadow-pink-500/30';
        card.addEventListener('click', () => playChannel(channel.player_url, channel.nome));
        const imageContainer = document.createElement('div');
        imageContainer.className = 'aspect-w-4 aspect-h-3 bg-gray-800';
        const img = document.createElement('img');
        img.src = channel.capa;
        img.alt = channel.nome;
        img.className = 'w-full h-full object-contain p-3';
        img.loading = 'lazy';
        img.onerror = () => {
          const fallback = document.createElement('div');
          fallback.className = 'fallback-img';
          fallback.textContent = channel.nome;
          imageContainer.replaceChild(fallback, img);
        };
        imageContainer.appendChild(img);
        const nameContainer = document.createElement('div');
        nameContainer.className = 'p-2 text-center bg-gray-800';
        const name = document.createElement('p');
        name.className = 'font-semibold text-sm text-gray-200 truncate';
        name.textContent = channel.nome;
        nameContainer.appendChild(name);
        card.appendChild(imageContainer);
        card.appendChild(nameContainer);
        channelGrid.appendChild(card);
      });
    }

    // Reproduzir canal selecionado
    function playChannel(url, name) {
      videoPlayer.src = url;
      channelTitle.innerHTML = `Assistindo agora: <span class="text-indigo-400 font-bold">${name}</span>`;
      playerContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Filtrar canais pelo input de busca
    function filterChannels() {
      const query = searchInput.value.toLowerCase().trim();
      const filtered = allChannels.filter((c) => c.nome.toLowerCase().includes(query));
      displayChannels(filtered);
    }

    searchInput.addEventListener('input', filterChannels);

    // Tela cheia
    fullscreenBtn.addEventListener('click', () => {
      if (!document.fullscreenElement) {
        playerContainer.requestFullscreen().catch((err) => {
          alert(`Erro ao ativar tela cheia: ${err.message}`);
        });
      } else {
        document.exitFullscreen();
      }
    });
  </script>

  <!-- Script das partículas -->
  <script>
    const canvas = document.getElementById('webCanvas');
    const ctx = canvas.getContext('2d');

    let width, height;
    let points = [];

    function resizeCanvas() {
      width = canvas.width = window.innerWidth;
      height = canvas.height = window.innerHeight;
    }
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    class Point {
      constructor() {
        this.x = Math.random() * width;
        this.y = Math.random() * height;
        this.vx = (Math.random() - 0.5) * 0.5;
        this.vy = (Math.random() - 0.5) * 0.5;
        this.radius = 1.5;
      }
      update() {
        this.x += this.vx;
        this.y += this.vy;
        if (this.x < 0 || this.x > width) this.vx *= -1;
        if (this.y < 0 || this.y > height) this.vy *= -1;
      }
      draw() {
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
        ctx.fillStyle = 'rgba(255, 255, 255, 0.5)';
        ctx.fill();
      }
    }

    function connectPoints() {
      for (let i = 0; i < points.length; i++) {
        for (let j = i + 1; j < points.length; j++) {
          const dx = points[i].x - points[j].x;
          const dy = points[i].y - points[j].y;
          const dist = Math.sqrt(dx * dx + dy * dy);
          if (dist < 100) {
            ctx.strokeStyle = `rgba(255, 255, 255, ${(100 - dist) / 150})`;
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(points[i].x, points[i].y);
            ctx.lineTo(points[j].x, points[j].y);
            ctx.stroke();
          }
        }
      }
    }

    function animate() {
      ctx.clearRect(0, 0, width, height);
      points.forEach((p) => {
        p.update();
        p.draw();
      });
      connectPoints();
      requestAnimationFrame(animate);
    }

    // Criar pontos
    for (let i = 0; i < 60; i++) {
      points.push(new Point());
    }
    animate();
  </script>
</body>
</html>
