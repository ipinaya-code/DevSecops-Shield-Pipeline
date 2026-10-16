<?php
/** @var string|null $error */
/** @var string|null $success */
/** @var string $oldEmail */
/** @var string $csrf */
?>

<div class="max-w-md mx-auto mt-10">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-32 h-32 bg-blue-500/10 rounded-full blur-2xl pointer-events-none"></div>
        
        <!-- Cabecera -->
        <div class="text-center mb-8">
            <div class="inline-flex bg-gradient-to-tr from-blue-600 to-emerald-500 p-3 rounded-2xl shadow-lg shadow-blue-500/20 mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Iniciar Sesión</h1>
            <p class="text-slate-400 text-sm mt-1">Accede al portal seguro de Aginnova Shield.</p>
        </div>

        <!-- Alertas de Éxito / Error -->
        <?php if ($success): ?>
            <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center space-x-2">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm flex items-center space-x-2">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <form method="post" action="/login" class="space-y-5">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
            
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Correo Electrónico</label>
                <input type="email" name="email" value="<?= htmlspecialchars($oldEmail, ENT_QUOTES, 'UTF-8') ?>" required autocomplete="email"
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition text-sm"
                       placeholder="usuario@dominio.com">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Contraseña</label>
                <input type="password" name="password" required autocomplete="current-password"
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition text-sm"
                       placeholder="••••••••">
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-semibold py-2.5 px-4 rounded-xl shadow-lg shadow-blue-600/20 transition duration-200 text-sm">
                Entrar al Sistema
            </button>
        </form>

        <!-- Enlace de Registro -->
        <p class="text-center text-sm text-slate-400 mt-6">
            ¿Primera vez por aquí? <a href="/register" class="text-blue-400 hover:underline font-medium">Crea tu cuenta</a>
        </p>
    </div>
</div>