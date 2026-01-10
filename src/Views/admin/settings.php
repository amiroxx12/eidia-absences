<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-cogs text-primary me-2"></i>Configuration du Système</h2>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white p-0">
            <ul class="nav nav-tabs card-header-tabs m-0" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-3 px-4 border-0 border-bottom border-primary border-3 fw-bold" 
                            id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab">
                        <i class="fas fa-envelope me-2"></i> Email (SMTP)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 px-4 text-secondary" 
                            id="whatsapp-tab" data-bs-toggle="tab" data-bs-target="#whatsapp" type="button" role="tab">
                        <i class="fab fa-whatsapp me-2"></i> WhatsApp
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 px-4 text-secondary" 
                            id="templates-tab" data-bs-toggle="tab" data-bs-target="#templates" type="button" role="tab">
                        <i class="fas fa-edit me-2"></i> Modèles de Messages
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="settingsTabsContent">
                
                <div class="tab-pane fade show active" id="email" role="tabpanel">
                    <form method="POST" action="<?= BASE_URL ?>/settings/save">
                        <input type="hidden" name="config_type" value="smtp">
                        
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Serveur SMTP (Host)</label>
                                <input type="text" name="smtp_host" class="form-control" value="<?= htmlspecialchars($config['smtp_host'] ?? '') ?>" placeholder="ex: smtp.gmail.com">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Port</label>
                                <input type="number" name="smtp_port" class="form-control" value="<?= htmlspecialchars($config['smtp_port'] ?? '587') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Utilisateur SMTP (Email)</label>
                                <input type="text" name="smtp_user" class="form-control" value="<?= htmlspecialchars($config['smtp_user'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mot de passe SMTP (App Password)</label>
                                <input type="password" name="smtp_pass" class="form-control" value="<?= htmlspecialchars($config['smtp_pass'] ?? '') ?>">
                                <div class="form-text">Pour Gmail, utilisez un "Mot de passe d'application".</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Expéditeur (From)</label>
                                <input type="email" name="smtp_from" class="form-control" value="<?= htmlspecialchars($config['smtp_from'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nom Expéditeur</label>
                                <input type="text" name="smtp_name" class="form-control" value="<?= htmlspecialchars($config['smtp_name'] ?? 'EIDIA Admin') ?>">
                            </div>
                        </div>

                        <hr class="my-4">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Enregistrer Config Email</button>
                        <a href="<?= BASE_URL ?>/settings/test?channel=email" class="btn btn-outline-secondary float-end"><i class="fas fa-paper-plane me-2"></i>Envoyer un Email de test</a>
                    </form>
                </div>

                <div class="tab-pane fade" id="whatsapp" role="tabpanel">
                    <form method="POST" action="<?= BASE_URL ?>/settings/save">
                        <input type="hidden" name="config_type" value="whatsapp">
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Pour utiliser WhatsApp, vous avez besoin d'un compte Twilio ou MessageBird.
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Fournisseur</label>
                            <select name="whatsapp_provider" class="form-select">
                                <option value="twilio" <?= ($config['whatsapp_provider'] ?? '') == 'twilio' ? 'selected' : '' ?>>Twilio</option>
                            </select>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Account SID</label>
                                <input type="text" name="twilio_sid" class="form-control" value="<?= htmlspecialchars($config['twilio_sid'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Auth Token</label>
                                <input type="password" name="twilio_token" class="form-control" value="<?= htmlspecialchars($config['twilio_token'] ?? '') ?>">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Numéro Expéditeur (From)</label>
                                <input type="text" name="twilio_from" class="form-control" value="<?= htmlspecialchars($config['twilio_from'] ?? '') ?>" placeholder="ex: whatsapp:+14155238886">
                            </div>
                        </div>

                        <hr class="my-4">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-2"></i>Enregistrer Config WhatsApp</button>
                        <a href="<?= BASE_URL ?>/settings/test?channel=whatsapp&phone=212765516147" class="btn btn-outline-secondary float-end">
    <i class="fab fa-whatsapp me-2"></i>Envoyer un Test (Simulé)
</a>
                    </form>
                </div>

                <div class="tab-pane fade" id="templates" role="tabpanel">
                    
                    <div class="row">
                        <?php foreach ($templates as $tpl): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card h-100 border-<?= $tpl['channel'] === 'email' ? 'primary' : 'success' ?>">
                                    <div class="card-header bg-<?= $tpl['channel'] === 'email' ? 'primary' : 'success' ?> text-white d-flex justify-content-between">
                                        <span>
                                            <i class="fas fa-<?= $tpl['channel'] === 'email' ? 'envelope' : 'comment-alt' ?> me-2"></i>
                                            <?= $tpl['type'] === 'creation_compte' ? 'Création de Compte' : 'Alerte Absence' ?>
                                        </span>
                                        <small class="badge bg-light text-dark"><?= strtoupper($tpl['channel']) ?></small>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="<?= BASE_URL ?>/settings/save">
                                            <input type="hidden" name="template_id" value="<?= $tpl['id'] ?>">
                                            
                                            <?php if ($tpl['channel'] === 'email'): ?>
                                                <div class="mb-2">
                                                    <label class="form-label small fw-bold">Sujet</label>
                                                    <input type="text" name="subject" class="form-control form-control-sm" value="<?= htmlspecialchars($tpl['subject']) ?>">
                                                </div>
                                            <?php endif; ?>

                                            <div class="mb-2">
                                                <label class="form-label small fw-bold">Message</label>
                                                <textarea name="body" class="form-control" rows="6"><?= htmlspecialchars($tpl['body']) ?></textarea>
                                            </div>

                                            <div class="alert alert-light border p-2 mb-3">
                                                <small class="d-block text-muted mb-1">Variables disponibles :</small>
                                                <?php 
                                                    $vars = explode(',', $tpl['variables']); 
                                                    foreach($vars as $v) {
                                                        echo '<code class="me-1">' . trim($v) . '</code>';
                                                    }
                                                ?>
                                            </div>

                                            <button type="submit" class="btn btn-sm btn-dark w-100">Mettre à jour ce modèle</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function(){
    var triggerTabList = [].slice.call(document.querySelectorAll('#settingsTabs button'))
    triggerTabList.forEach(function (triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl)
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault()
            tabTrigger.show()
        })
    })
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>