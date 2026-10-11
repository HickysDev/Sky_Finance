</div>
</main>
<script>
  // Padrão visual dos gráficos (Chart.js é carregado só nas telas com gráfico).
  // Roda antes do $(document).ready das páginas, que é onde os gráficos nascem.
  (function () {
    if (!window.Chart) return;
    var claro = document.documentElement.classList.contains('light-mode');
    Chart.defaults.font.family = '"Manrope", system-ui, sans-serif';
    Chart.defaults.font.size = 12;
    Chart.defaults.color = claro ? '#475569' : '#A6ABC0';
    Chart.defaults.borderColor = claro ? 'rgba(15,23,42,.08)' : 'rgba(255,255,255,.06)';
    var tt = Chart.defaults.plugins.tooltip;
    tt.backgroundColor = claro ? '#FFFFFF' : '#2D2F45';
    tt.titleColor = claro ? '#0F172A' : '#F0F0F5';
    tt.bodyColor = claro ? '#334155' : '#D4D7E3';
    tt.borderColor = claro ? '#E2E8F0' : '#363852';
    tt.borderWidth = 1;
    tt.padding = 10;
    tt.cornerRadius = 8;
    tt.titleFont = { weight: '700' };
    tt.boxPadding = 4;
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
  })();
</script>
<!-- Licença gratuita Vecteezy: atribuição "Vecteezy.com" com link (tema Gatos astronautas) -->
<footer id="creditoTema" class="credito-tema" style="display:none;">
  Estampa do tema: <a href="https://pt.vecteezy.com/vetor-gratis/cat-pattern" target="_blank" rel="noopener">Cat Pattern Vetores por Vecteezy</a>
</footer>
<script>
  (function () {
    var c = document.getElementById('creditoTema');
    if (c && document.documentElement.classList.contains('tema-astronautas')) c.style.display = '';
  })();
</script>
</body>

</html>