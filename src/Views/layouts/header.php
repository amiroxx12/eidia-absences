<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EIDIA Absences</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand" href="/eidia-absences/public/dashboard">
        <i class="fas fa-university"></i> EIDIA Absences
    </a>
    
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="/eidia-absences/public/dashboard">Tableau de bord</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/eidia-absences/public/import">Importer CSV</a>
        </li>
      </ul>
      
      <div class="d-flex">
        <span class="navbar-text text-white me-3">
            Bonjour, <?php echo $_SESSION['user_name'] ?? 'Invité'; ?>
        </span>
        <a href="/eidia-absences/public/logout" class="btn btn-danger btn-sm">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
        </a>
      </div>
    </div>
  </div>
</nav>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>