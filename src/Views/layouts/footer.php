</main> <footer class="bg-white text-center text-lg-start mt-auto border-top py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <div class="text-muted small">
                        <strong>EIDIA Absences</strong> &copy; <?= date('Y') ?> - Tous droits réservés.
                    </div>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item"><a href="#" class="text-muted small text-decoration-none">Support</a></li>
                        <li class="list-inline-item"><span class="text-muted">·</span></li>
                        <li class="list-inline-item"><a href="#" class="text-muted small text-decoration-none">Mentions Légales</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000); 
        });
    </script>

</body>
</html>