    </main>
    <footer class="pied">
      Page générée en <?= number_format((hrtime(true) - $debut) / 1e6, 1, ',', ' ') ?> ms
      · <?= count(isset($GLOBALS['journal_sql']) ? $GLOBALS['journal_sql'] : []) ?> requête(s) SQL
    </footer>
  </div>
</div>
</body>
</html>
