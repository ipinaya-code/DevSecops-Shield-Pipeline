<?php
/** @var string|null $error */
/** @var array<string, string> $old */
/** @var string $csrf */
?>

<div class="max-w-md mx-auto mt-6 mb-10">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>
        
        <!-- Cabecera -->
        <div class="text-center mb-6">
            <div class="inline-flex bg-gradient-to-tr from-emerald-600 to-blue-500 p-3 rounded-2xl shadow-lg shadow-emerald-500/20 mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Crear Cuenta</h1>
            <p class="text-slate-400 text-sm mt-1">Regístrate en Aginnova Shield por única vez.</p>
        </div>

        <!-- Alerta de Error -->
        <?php if ($error): ?>
            <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm flex items-center space-x-2">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <form method="post" action="/register" class="space-y-4">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
            
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Nombre Completo</label>
                <input type="text" name="name" value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required minlength="2" autocomplete="name"
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition text-sm"
                       placeholder="Tu Nombre">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Correo Electrónico</label>
                <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required autocomplete="email"
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition text-sm"
                       placeholder="usuario@dominio.com">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Contraseña (Mín. 8 caracteres)</label>
                <input type="password" name="password" required minlength="8" autocomplete="new-password"
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition text-sm"
                       placeholder="••••••••">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Confirmar Contraseña</label>
                <input type="password" name="password_confirm" required minlength="8" autocomplete="new-password"
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition text-sm"
                       placeholder="••••••••">
            </div>

            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-semibold py-2.5 px-4 rounded-xl shadow-lg shadow-emerald-600/20 transition duration-200 text-sm mt-2">
                Registrarme en el Sistema
            </button>
        </form>

        <!-- Enlace a Login -->
        <p class="text-center text-sm text-slate-400 mt-6">
            ¿Ya tienes una cuenta? <a href="/" class="text-emerald-400 hover:underline font-medium">Entra aquí</a>
        </p>
    </div>
</div>