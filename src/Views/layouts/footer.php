</div> 

    <footer class="bg-light text-center text-lg-start mt-5 border-top py-3">
        <div class="container">
            <div class="text-center text-muted">
                <small>
                    © <?= date('Y') ?> EIDIA Absences - Système de Gestion <br>
                    Développé avec par l'équipe.
                </small>
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