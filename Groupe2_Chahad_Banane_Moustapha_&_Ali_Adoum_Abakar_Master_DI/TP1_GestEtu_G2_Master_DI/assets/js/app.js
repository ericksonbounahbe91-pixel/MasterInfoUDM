/* =============================================================================
 *  assets/js/app.js — Comportements côté client (vanilla JS, sans dépendance)
 * =============================================================================
 *  1. Mode sombre persistant (localStorage)
 *  2. Affichage des toasts Bootstrap (messages flash)
 *  3. Loader de soumission sur les formulaires
 *  4. Recherche live (fetch -> api/search.php)
 *  5. Cases à cocher : tout sélectionner + compteur du bouton de lot
 *  6. Filtres à application automatique
 * ========================================================================== */
'use strict';

document.addEventListener('DOMContentLoaded', function () {

  /* ---------------------------------------------------------------------------
   * 1. Mode sombre persistant
   * ------------------------------------------------------------------------ */
  var racine = document.documentElement;
  var bouton = document.getElementById('bascule-theme');

  function appliquerTheme(theme) {
    racine.setAttribute('data-bs-theme', theme);
    localStorage.setItem('tp5-theme', theme);
    if (bouton) {
      bouton.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
      var icone = bouton.querySelector('i');
      if (icone) { icone.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars'; }
    }
  }
  appliquerTheme(localStorage.getItem('tp5-theme') || 'light');
  if (bouton) {
    bouton.addEventListener('click', function () {
      appliquerTheme(racine.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark');
    });
  }

  /* ---------------------------------------------------------------------------
   * 2. Toasts (messages flash injectés par includes/header.php)
   * ------------------------------------------------------------------------ */
  document.querySelectorAll('#zone-toasts .toast').forEach(function (element) {
    new bootstrap.Toast(element).show();
  });

  /* ---------------------------------------------------------------------------
   * 3. Loader de soumission : évite les doubles clics
   * ------------------------------------------------------------------------ */
  document.querySelectorAll('form[data-chargement]').forEach(function (formulaire) {
    formulaire.addEventListener('submit', function () {
      var boutonEnvoi = formulaire.querySelector('[type="submit"]');
      if (!boutonEnvoi || boutonEnvoi.dataset.dejaEnvoye) { return; }
      boutonEnvoi.dataset.dejaEnvoye = '1';
      boutonEnvoi.classList.add('bouton-charge');
      boutonEnvoi.insertAdjacentHTML('afterbegin',
        '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>');
      setTimeout(function () { boutonEnvoi.disabled = true; }, 0);
    });
  });

  /* ---------------------------------------------------------------------------
   * 4. Recherche live : interroge api/search.php et propose des résultats
   * ------------------------------------------------------------------------ */
  var champ = document.getElementById('recherche-live');
  var zone  = document.getElementById('resultats-live');
  var minuteur = null;

  if (champ && zone) {
    champ.addEventListener('input', function () {
      clearTimeout(minuteur);
      var terme = champ.value.trim();
      if (terme.length < 2) { zone.innerHTML = ''; zone.classList.add('d-none'); return; }
      /* Anti-rebond : on attend 250 ms après la dernière frappe */
      minuteur = setTimeout(function () {
        fetch('api/search.php?q=' + encodeURIComponent(terme), { headers: { 'Accept': 'application/json' } })
          .then(function (r) { return r.ok ? r.json() : Promise.reject(r.status); })
          .then(function (donnees) {
            if (!donnees.resultats || donnees.resultats.length === 0) {
              zone.innerHTML = '<div class="list-group-item text-secondary">Aucun résultat.</div>';
            } else {
              zone.innerHTML = donnees.resultats.map(function (e) {
                return '<a class="list-group-item list-group-item-action" href="form.php?id='
                  + encodeURIComponent(e.idEtu) + '">'
                  + '<strong>' + e.nomEtu + '</strong> ' + e.prenomEtu
                  + ' <span class="text-secondary small">— ' + e.idEtu + '</span></a>';
              }).join('');
            }
            zone.classList.remove('d-none');
          })
          .catch(function () { zone.classList.add('d-none'); });
      }, 250);
    });
    /* Fermeture au clic extérieur */
    document.addEventListener('click', function (evenement) {
      if (!zone.contains(evenement.target) && evenement.target !== champ) { zone.classList.add('d-none'); }
    });
  }

  /* ---------------------------------------------------------------------------
   * 5. Sélection multiple (génération de cartes en lot)
   * ------------------------------------------------------------------------ */
  var toutCocher = document.getElementById('tout-cocher');
  var cases = function () { return Array.prototype.slice.call(document.querySelectorAll('.case-etu')); };
  var boutonLot = document.getElementById('btn-lot');

  function majCompteur() {
    if (!boutonLot) { return; }
    var n = cases().filter(function (c) { return c.checked; }).length;
    boutonLot.disabled = n === 0;
    boutonLot.innerHTML = '<i class="bi bi-collection"></i> Générer les cartes sélectionnées ('
      + n + ')';
  }
  if (toutCocher) {
    toutCocher.addEventListener('change', function () {
      cases().forEach(function (c) { c.checked = toutCocher.checked; });
      majCompteur();
    });
  }
  cases().forEach(function (c) { c.addEventListener('change', majCompteur); });
  majCompteur();

  /* ---------------------------------------------------------------------------
   * 6. Filtres : application automatique au changement
   * ------------------------------------------------------------------------ */
  document.querySelectorAll('[data-filtre-auto]').forEach(function (select) {
    select.addEventListener('change', function () { select.form.submit(); });
  });

  /* Impression de l'aperçu de carte */
  var boutonImprimer = document.getElementById('btn-imprimer');
  if (boutonImprimer) {
    boutonImprimer.addEventListener('click', function () { window.print(); });
  }
});
