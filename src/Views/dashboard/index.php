<?php 
// On inclut le header
require_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-primary">Bienvenue sur votre Espace Admin</h2>
                <p class="card-text">Gérez les absences et les étudiants de l'EIDIA.</p>
                <hr>
                
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-white">
                            <i class="fas fa-file-upload fa-3x text-success mb-3"></i>
                            <h4>Importer</h4>
                            <p>Charger un fichier CSV d'absences.</p>
                            <a href="/eidia-absences/public/import" class="btn btn-outline-success">Aller à l'import</a>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-white">
                            <i class="fas fa-users fa-3x text-info mb-3"></i>
                            <h4>Étudiants</h4>
                            <p>Voir la liste des inscrits.</p>
                            <a href="#" class="btn btn-outline-info">Voir la liste</a>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="alert alert-info">
                            <strong>Debug Session :</strong><br>
                            Role : <?php echo $_SESSION['user_role']; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// On inclut le footer
require_once __DIR__ . '/../layouts/footer.php'; 
?>