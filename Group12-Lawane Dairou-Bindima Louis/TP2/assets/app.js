// Formulaire d'enregistrement : ajout / retrait dynamique de lignes produit
(function () {
  var box = document.getElementById('lignes');
  var add = document.getElementById('addRow');
  if (!box || !add) return;
  var tpl = box.querySelector('.form-row');
  add.addEventListener('click', function () {
    var c = tpl.cloneNode(true);
    c.querySelector('select').selectedIndex = 0;
    c.querySelector('input').value = 1;
    box.appendChild(c);
    c.querySelector('select').focus();
  });
  box.addEventListener('click', function (e) {
    if (!e.target.classList.contains('rm')) return;
    if (box.querySelectorAll('.form-row').length > 1) e.target.closest('.form-row').remove();
  });
})();
document.addEventListener('submit', function (e) {
  var m = e.target.getAttribute('data-confirm');
  if (m && !window.confirm(m)) e.preventDefault();
});
