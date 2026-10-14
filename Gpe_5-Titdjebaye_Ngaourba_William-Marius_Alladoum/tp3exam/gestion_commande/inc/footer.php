</main>
<footer class="pied">
  Page générée en <?= number_format((hrtime(true) - $debut) / 1e6, 1, ',', ' ') ?> ms
  · <?= count($GLOBALS['journal_sql'] ?? []) ?> requête(s) SQL
</footer>
</body>
</html>
