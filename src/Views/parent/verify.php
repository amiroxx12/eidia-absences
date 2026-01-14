<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification Parent | EIDIA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #f3f4f6; }
    </style>
</head>
<body class="h-screen flex items-center justify-center px-4">

    <div class="max-w-md w-full bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="bg-blue-900 p-6 text-center">
            <h1 class="text-white text-xl font-bold uppercase tracking-wider">Espace Parents</h1>
            <p class="text-blue-200 text-sm mt-1">Accès Sécurisé</p>
        </div>

        <div class="p-8">
            
            <div class="mb-6 text-center">
                <p class="text-gray-600">
                    Pour accéder au dossier de l'étudiant, veuillez confirmer votre identité en saisissant votre <strong>Numéro de CIN</strong>.
                </p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm rounded">
                    <p class="font-bold">Erreur :</p>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <form action="<?= $_SERVER['SCRIPT_NAME'] ?>/parent/check-cin" method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">

                <div>
                    <label for="cin" class="block text-sm font-medium text-gray-700 mb-1">Votre CIN (Identité Nationale)</label>
                    <input type="text" 
                           id="cin" 
                           name="cin" 
                           required 
                           placeholder="Ex: AB123456"
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors uppercase"
                    >
                </div>

                <button type="submit" 
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    Accéder au dossier
                </button>
            </form>
        </div>

        <div class="bg-gray-50 px-8 py-4 text-center">
            <p class="text-xs text-gray-500">
                &copy; <?= date('Y') ?> EIDIA - Service Scolarité
            </p>
        </div>
    </div>

</body>
</html>