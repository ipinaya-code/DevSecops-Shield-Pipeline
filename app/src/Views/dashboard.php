<?php
/** @var array<string, mixed> $user */
/** @var string $csrf */
?>

<div class="space-y-6">
    <!-- Encabezado de Bienvenida -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20 mb-3">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                    Sesión Segura Verificada
                </span>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">
                    Hola, <?= htmlspecialchars((string) $user['name'], ENT_QUOTES, 'UTF-8') ?> 👋
                </h1>
                <p class="text-slate-400 text-sm mt-1">
                    Bienvenido al panel de control de Aginnova Shield. Tu entorno está protegido y operativo.
                </p>
            </div>
            
            <!-- Botón de Cerrar Sesión -->
            <div>
                <form method="post" action="/logout">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-medium text-rose-400 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 transition duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                        </svg>
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Sección de Análisis de Repositorios GitHub -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-lg mb-6">
        <h2 class="text-base font-semibold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-400" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
            </svg>
            Análisis de Seguridad en Tiempo Real
        </h2>
        
        <!-- Input de GitHub Username -->
        <div class="flex gap-3 mb-4">
            <input 
                type="text" 
                id="githubUsername" 
                placeholder="Ingresa tu usuario de GitHub..." 
                class="flex-1 px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500"
            >
            <button 
                onclick="fetchRepositories()" 
                class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-medium transition duration-200 flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Buscar
            </button>
        </div>

        <!-- Lista de Repositorios -->
        <div id="repositoriesList" class="hidden mb-4">
            <h3 class="text-sm font-medium text-slate-400 mb-3">Selecciona un repositorio:</h3>
            <div id="reposContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-64 overflow-y-auto">
                <!-- Repositorios se cargarán aquí -->
            </div>
        </div>

        <!-- Panel de Progreso -->
        <div id="progressPanel" class="hidden">
            <h3 class="text-sm font-medium text-slate-400 mb-3">Progreso del Análisis:</h3>
            <div id="progressSteps" class="space-y-2">
                <!-- Pasos del análisis -->
            </div>
            <div id="scanResults" class="mt-4 hidden">
                <!-- Resultados finales -->
            </div>
        </div>
    </div>

    <!-- Tarjetas de Información del Sistema y Usuario -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Detalles de Cuenta -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-lg">
            <h2 class="text-base font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.5h.008v.008h-.008V9zm0 3h.008v.008h-.008V12zm0 3h.008v.008h-.008V15z"/>
                </svg>
                Credenciales del Perfil
            </h2>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between py-2 border-b border-slate-800">
                    <span class="text-slate-400">Correo Electrónico:</span>
                    <span class="text-slate-200 font-medium"><?= htmlspecialchars((string) $user['email'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="flex justify-between py-2 border-b border-slate-800">
                    <span class="text-slate-400">Fecha de Registro:</span>
                    <span class="text-slate-200 font-medium"><?= htmlspecialchars((string) $user['created_at'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-slate-400">ID de Usuario:</span>
                    <span class="text-slate-200 font-medium">#<?= htmlspecialchars((string) $user['id'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>
        </div>

        <!-- Estado del Pipeline de Seguridad -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-lg">
            <h2 class="text-base font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                </svg>
                Estado de Protección CI/CD
            </h2>
            <div class="space-y-3 text-sm text-slate-300">
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-950/50 border border-slate-800">
                    <span>Análisis SAST (Semgrep)</span>
                    <span class="text-emerald-400 font-semibold text-xs bg-emerald-500/10 px-2.5 py-1 rounded-md border border-emerald-500/20">Protegido</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-950/50 border border-slate-800">
                    <span>Escaneo de Dependencias (Trivy)</span>
                    <span class="text-emerald-400 font-semibold text-xs bg-emerald-500/10 px-2.5 py-1 rounded-md border border-emerald-500/20">Actualizado</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-950/50 border border-slate-800">
                    <span>Pruebas DAST (OWASP ZAP)</span>
                    <span class="text-blue-400 font-semibold text-xs bg-blue-500/10 px-2.5 py-1 rounded-md border border-blue-500/20">Monitoreando</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentScanId = null;
    let progressInterval = null;

    // Buscar repositorios de GitHub
    async function fetchRepositories() {
        const username = document.getElementById('githubUsername').value.trim();
        if (!username) {
            alert('Por favor ingresa un usuario de GitHub');
            return;
        }

        try {
            const response = await fetch(`/api/github/repos?username=${encodeURIComponent(username)}`);
            const data = await response.json();

            if (data.error) {
                alert('Error: ' + data.error);
                return;
            }

            displayRepositories(data.repositories);
        } catch (error) {
            alert('Error al buscar repositorios: ' + error.message);
        }
    }

    // Mostrar repositorios
    function displayRepositories(repos) {
        const container = document.getElementById('reposContainer');
        const listDiv = document.getElementById('repositoriesList');
        
        container.innerHTML = repos.map(repo => `
            <div class="p-3 rounded-xl bg-slate-950 border border-slate-700 hover:border-purple-500/50 cursor-pointer transition duration-200"
                 onclick="selectRepository('${repo.full_name}', '${repo.clone_url}')">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-white font-medium text-sm">${repo.name}</span>
                    <span class="text-xs text-slate-400">${repo.language}</span>
                </div>
                <p class="text-xs text-slate-500 truncate">${repo.description}</p>
                <div class="flex items-center gap-3 mt-2 text-xs text-slate-400">
                    <span>⭐ ${repo.stars}</span>
                    <span>📦 ${repo.size} KB</span>
                </div>
            </div>
        `).join('');

        listDiv.classList.remove('hidden');
    }

    // Seleccionar repositorio
    async function selectRepository(fullName, cloneUrl) {
        const [username, repoName] = fullName.split('/');
        
        try {
            const response = await fetch('/api/github/clone', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `username=${encodeURIComponent(username)}&repo_name=${encodeURIComponent(repoName)}`
            });
            const data = await response.json();

            if (data.error) {
                alert('Error: ' + data.error);
                return;
            }

            startScan(data.clone_path, fullName);
        } catch (error) {
            alert('Error al clonar repositorio: ' + error.message);
        }
    }

    // Iniciar análisis
    async function startScan(clonePath, repoName) {
        currentScanId = repoName.replace(/\//g, '-');
        
        document.getElementById('repositoriesList').classList.add('hidden');
        document.getElementById('progressPanel').classList.remove('hidden');
        document.getElementById('scanResults').classList.add('hidden');

        try {
            const response = await fetch('/api/scan/start', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `clone_path=${encodeURIComponent(clonePath)}&repo_name=${encodeURIComponent(currentScanId)}`
            });
            const data = await response.json();

            if (data.error) {
                alert('Error: ' + data.error);
                return;
            }

            // Iniciar polling de progreso
            progressInterval = setInterval(checkProgress, 2000);
        } catch (error) {
            alert('Error al iniciar análisis: ' + error.message);
        }
    }

    // Verificar progreso
    async function checkProgress() {
        try {
            const response = await fetch(`/api/scan/progress?scan_id=${encodeURIComponent(currentScanId)}`);
            const data = await response.json();

            if (data.error) {
                clearInterval(progressInterval);
                return;
            }

            updateProgressUI(data.progress);

            if (data.progress.status === 'completed') {
                clearInterval(progressInterval);
                showResults(data.progress);
            }
        } catch (error) {
            console.error('Error checking progress:', error);
        }
    }

    // Actualizar UI de progreso
    function updateProgressUI(progress) {
        const container = document.getElementById('progressSteps');
        
        const steps = [
            { key: 'clone', label: 'Clonar Repositorio' },
            { key: 'semgrep', label: 'Análisis SAST (Semgrep)' },
            { key: 'trivy_sca', label: 'Escaneo Dependencias (Trivy)' },
            { key: 'trivy_container', label: 'Análisis Contenedor (Trivy)' },
            { key: 'zap', label: 'Pruebas DAST (OWASP ZAP)' },
            { key: 'quality_gate', label: 'Quality Gate' }
        ];

        container.innerHTML = steps.map(step => {
            const stepData = progress.steps[step.key] || { status: 'pending', message: 'Esperando...' };
            const statusColors = {
                pending: 'bg-slate-700 border-slate-600',
                running: 'bg-purple-500/20 border-purple-500 animate-pulse',
                completed: 'bg-emerald-500/20 border-emerald-500'
            };
            const statusIcons = {
                pending: '⏳',
                running: '🔄',
                completed: '✅'
            };

            return `
                <div class="flex items-center justify-between p-3 rounded-xl ${statusColors[stepData.status]} border">
                    <div class="flex items-center gap-3">
                        <span class="text-lg">${statusIcons[stepData.status]}</span>
                        <span class="text-white text-sm">${step.label}</span>
                    </div>
                    <span class="text-xs text-slate-300">${stepData.message}</span>
                </div>
            `;
        }).join('');
    }

    // Mostrar resultados finales
    function showResults(progress) {
        const resultsDiv = document.getElementById('scanResults');
        const findings = progress.findings || {};
        
        const totalCritical = Object.values(findings).reduce((sum, f) => sum + (f.critical || 0), 0);
        const totalWarning = Object.values(findings).reduce((sum, f) => sum + (f.warning || 0), 0);

        resultsDiv.innerHTML = `
            <div class="p-4 rounded-xl ${progress.gate_passed ? 'bg-emerald-500/10 border-emerald-500/30' : 'bg-red-500/10 border-red-500/30'} border">
                <h4 class="text-lg font-semibold ${progress.gate_passed ? 'text-emerald-400' : 'text-red-400'} mb-3">
                    ${progress.gate_passed ? '✅ QUALITY GATE: PASADO' : '❌ QUALITY GATE: FALLADO'}
                </h4>
                <p class="text-sm text-slate-300 mb-4">${progress.gate_message}</p>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-3 rounded-lg bg-slate-950/50">
                        <div class="text-2xl font-bold text-red-400">${totalCritical}</div>
                        <div class="text-xs text-slate-400">Críticos</div>
                    </div>
                    <div class="text-center p-3 rounded-lg bg-slate-950/50">
                        <div class="text-2xl font-bold text-yellow-400">${totalWarning}</div>
                        <div class="text-xs text-slate-400">Advertencias</div>
                    </div>
                </div>
            </div>
        `;

        resultsDiv.classList.remove('hidden');
    }

    // Enter key en input
    document.getElementById('githubUsername').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            fetchRepositories();
        }
    });
</script>