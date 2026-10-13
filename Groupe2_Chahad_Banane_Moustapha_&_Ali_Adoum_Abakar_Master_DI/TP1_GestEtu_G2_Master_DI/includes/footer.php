<?php
/**
 * =============================================================================
 *  includes/footer.php — Pied de page commun et scripts JavaScript
 * =============================================================================
 */
?>
</main>

<footer class="app-footer">
  <div class="container-xxl d-flex flex-wrap justify-content-between gap-2">
    <span>&copy; <?= date('Y') ?> <?= h(ETABLISSEMENT) ?> — Gestion des étudiants</span>
    <span class="text-secondary">
      TP5 refactorisé — version <?= h(APP_VERSION) ?> — année académique <?= h(ANNEE_ACADEMIQUE) ?>
    </span>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
