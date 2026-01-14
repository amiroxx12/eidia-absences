<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi Discipline | EIDIA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 pb-10">

    <nav class="bg-blue-900 text-white p-4 shadow-md">
        <div class="max-w-4xl mx-auto flex justify-between items-center">
            <div>
                <h1 class="font-bold uppercase tracking-tight">EIDIA</h1>
                <p class="text-[10px] text-blue-200 uppercase">Parent : <?= htmlspecialchars($currentChild['prenom'] . ' ' . $currentChild['nom']) ?></p>
            </div>
            <a href="<?= $_SERVER['SCRIPT_NAME'] ?>/parent/logout" class="text-xs bg-red-600 hover:bg-red-700 transition px-3 py-2 rounded font-bold shadow-sm">Déconnexion</a>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto mt-6 px-4">
        
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 text-sm rounded shadow-sm">
                <?= $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (count($enfants) > 1): ?>
            <div class="mb-6 bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-3">Mes Enfants :</label>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($enfants as $enf): ?>
                        <a href="?child_id=<?= $enf['id'] ?>" 
                           class="px-4 py-2 rounded-lg text-xs font-bold transition
                           <?= $enf['id'] == $currentChild['id'] 
                               ? 'bg-blue-600 text-white shadow-md' 
                               : 'bg-gray-100 text-gray-500 hover:bg-gray-200' ?>">
                            <?= htmlspecialchars($enf['prenom']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-user-clock mr-2 text-blue-600"></i> Historique des absences
        </h2>

        <?php if (empty($absences)): ?>
            <div class="bg-white p-8 rounded-2xl shadow-sm text-center border border-dashed border-gray-300">
                <p class="text-gray-400 text-sm italic">Aucune absence enregistrée. Tout est en ordre ! ✨</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($absences as $abs): ?>
                    <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 
                        <?= $abs['justification_status'] === 'VALIDE' ? 'border-green-500' : ($abs['justification_status'] === 'EN_ATTENTE' ? 'border-amber-500' : 'border-red-500') ?>">
                        
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase"><?= date('d/m/Y', strtotime($abs['date_seance'])) ?> • <?= substr($abs['heure_debut'], 0, 5) ?></p>
                                <p class="font-bold text-gray-800"><?= htmlspecialchars($abs['matiere']) ?></p>
                            </div>
                            <span class="text-[9px] px-2 py-1 rounded font-black uppercase
                                <?= $abs['justification_status'] === 'VALIDE' ? 'bg-green-100 text-green-700' : ($abs['justification_status'] === 'EN_ATTENTE' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') ?>">
                                <?= str_replace('_', ' ', $abs['justification_status']) ?>
                            </span>
                        </div>

                        <div class="mt-3">
                            <?php if ($abs['justification_status'] === 'NON_JUSTIFIE' || $abs['justification_status'] === 'REFUSE'): ?>
                                <details class="group">
                                    <summary class="list-none flex justify-end">
                                        <span class="cursor-pointer text-[10px] font-bold bg-gray-800 text-white px-3 py-1.5 rounded-lg hover:bg-black transition uppercase">
                                            Justifier
                                        </span>
                                    </summary>
                                    <form action="<?= $_SERVER['SCRIPT_NAME'] ?>/parent/justify" method="POST" enctype="multipart/form-data" class="mt-3 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                        <input type="hidden" name="absence_id" value="<?= $abs['id'] ?>">
                                        <input type="hidden" name="table_name" value="<?= $abs['source_table'] ?>">
                                        <div class="mb-2">
                                            <input type="text" name="motif" required placeholder="Motif (ex: Maladie)" class="w-full text-xs p-2 border rounded outline-none">
                                        </div>
                                        <div class="mb-2">
                                            <input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf" class="w-full text-[10px] text-gray-500">
                                        </div>
                                        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded text-xs font-bold">Envoyer</button>
                                    </form>
                                </details>
                            <?php else: ?>
                                <p class="text-[10px] text-gray-400 text-right italic">Transmis le <?= date('d/m/Y', strtotime($abs['justification_date'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (isset($totalPages) && $totalPages > 1): ?>
            <div class="mt-6 flex items-center justify-between p-4 bg-white rounded-xl shadow-sm">
                <span class="text-[10px] font-bold text-gray-400 uppercase">Page <?= $page ?> / <?= $totalPages ?></span>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                        <a href="?child_id=<?= $currentChild['id'] ?>&page=<?= $page - 1 ?>" class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg text-xs font-bold">Précédent</a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?child_id=<?= $currentChild['id'] ?>&page=<?= $page + 1 ?>" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-bold shadow-md">Suivant</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</body>
</html>