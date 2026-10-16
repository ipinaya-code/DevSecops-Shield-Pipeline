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