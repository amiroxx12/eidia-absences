<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Initialisation du compte | EIDIA</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center px-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
        <div class="bg-blue-900 p-6 text-center text-white">
            <div class="w-16 h-16 bg-blue-800 rounded-full flex items-center justify-center mx-auto mb-3 shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h1 class="text-xl font-bold uppercase tracking-wide">Activer mon compte</h1>
            <p class="text-blue-200 text-xs mt-1">Veuillez définir votre mot de passe pour les prochaines connexions.</p>
        </div>

        <div class="p-8">
            <?php if (isset($error)): ?>
                <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-500 text-red-700 text-xs rounded">
                    <p class="font-bold">Erreur :</p>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <form action="<?= $_SERVER['SCRIPT_NAME'] ?>/parent/save-password" method="POST" class="space-y-5">
                
                <div>
                    <label for="password" class="block text-xs font-bold text-gray-700 uppercase mb-1">Nouveau mot de passe</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           minlength="6"
                           placeholder="6 caractères minimum"
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 outline-none transition-all"
                    >
                </div>

                <div>
                    <label for="confirm_password" class="block text-xs font-bold text-gray-700 uppercase mb-1">Confirmer le mot de passe</label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           required 
                           placeholder="Saisissez à nouveau"
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 outline-none transition-all"
                    >
                </div>

                <div class="pt-2">
                    <button type="submit" 
                            class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold text-sm shadow-lg hover:bg-blue-700 hover:shadow-blue-200 transition-all transform active:scale-95">
                        Activer mon accès
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-gray-50 px-8 py-4 text-center border-t border-gray-100">
            <p class="text-[10px] text-gray-400 italic leading-tight">
                Une fois activé, vous pourrez vous connecter avec votre email : <br>
                <span class="font-bold text-gray-600"><?= htmlspecialchars($_SESSION['parent_email'] ?? '') ?></span>
            </p>
        </div>
    </div>

</body>
</html>