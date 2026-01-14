<?php
// On récupère les erreurs s'il y en a
$error = $error ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - EIDIA Absences</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= defined('BASE_URL') ? BASE_URL : '/eidia-absences/public' ?>/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="login-page d-flex align-items-center min-vh-100">

    <div class="login-background">
        <div class="login-shape shape-1"></div>
        <div class="login-shape shape-2"></div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5 col-xl-4">
                
                <div class="login-container card border-0 shadow-lg" style="border-radius: 15px;">
                    <div class="card-body p-4 p-md-5">

                        <div class="text-center mb-4">
                            <div class="logo mb-3 text-primary">
                                <i class="fas fa-graduation-cap fa-3x"></i>
                            </div>
                            <h2 class="fw-bold text-dark">EIDIA Absences</h2>
                            <p class="text-muted small">Portail de gestion</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger py-2 text-center small shadow-sm rounded-3" role="alert">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <form action="<?= defined('BASE_URL') ? BASE_URL : '/eidia-absences/public' ?>/login" method="POST">
                            
                            <div class="mb-3">
                                <label for="email" class="form-label small text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Email</label>
                                
                                <div class="d-flex align-items-center bg-white border rounded p-2 shadow-sm">
                                    <span class="ps-3 pe-2 text-secondary">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input 
                                        type="email" 
                                        class="form-control border-0 shadow-none ps-1" 
                                        style="background: transparent;"
                                        id="email" 
                                        name="email" 
                                        required 
                                        placeholder="votre.email@eidia.ma"
                                    >
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label small text-muted text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Mot de passe</label>
                                
                                <div class="d-flex align-items-center bg-white border rounded p-2 shadow-sm">
                                    <span class="ps-3 pe-2 text-secondary">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input 
                                        type="password" 
                                        class="form-control border-0 shadow-none ps-1"
                                        style="background: transparent;" 
                                        id="password" 
                                        name="password" 
                                        required 
                                        placeholder="••••••••"
                                    >
                                </div>
                            </div>

                            <div class="d-grid gap-2 pt-2">
                                <button type="submit" class="btn btn-primary btn-lg shadow-sm" style="border-radius: 8px;">
                                    Se connecter
                                </button>
                            </div>

                        </form>
                    </div>
                    
                    <div class="card-footer bg-light text-center py-3 border-0" style="border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                        <small class="text-muted"><i class="fas fa-shield-alt me-1"></i> Accès sécurisé</small>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>