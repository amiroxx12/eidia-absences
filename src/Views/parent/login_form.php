<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Parents | EIDIA</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center px-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-blue-900 p-8 text-center text-white">
            <h1 class="text-2xl font-bold uppercase tracking-widest">EIDIA</h1>
            <p class="text-blue-300 text-sm mt-2">Espace Parents - Connexion</p>
        </div>

        <div class="p-8">
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm rounded shadow-sm">
                    <p class="font-bold text-xs uppercase mb-1">Erreur</p>
                    <p><?= htmlspecialchars($_SESSION['error_message']) ?></p>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 text-sm rounded shadow-sm">
                    <p><?= $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?></p>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm rounded shadow-sm">
                    <p class="font-bold text-xs uppercase mb-1">Erreur de connexion</p>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <form action="<?= $_SERVER['SCRIPT_NAME'] ?>/parent/login/submit" method="POST" class="space-y-6">
                
                <div>
                    <label for="email" class="block text-xs font-bold text-gray-700 uppercase mb-2">Adresse Email</label>
                    <div class="relative">
                        <input type="email" id="email" name="email" required 
                               placeholder="votre@email.com"
                               class="w-full pl-4 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label for="password" class="block text-xs font-bold text-gray-700 uppercase">Mot de passe</label>
                    </div>
                    <input type="password" id="password" name="password" required 
                           placeholder="••••••••"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
                </div>

                <button type="submit" 
                        class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold text-sm shadow-lg hover:bg-blue-700 transition-all transform active:scale-95">
                    Se connecter
                </button>
            </form>
        </div>

        <div class="bg-gray-50 px-8 py-5 text-center border-t border-gray-100">
            <p class="text-xs text-gray-500">
                Vous n'avez pas encore activé votre compte ? <br>
                <span class="text-blue-600 font-semibold">Utilisez le lien reçu par email.</span>
            </p>
        </div>
    </div>

</body>
</html>