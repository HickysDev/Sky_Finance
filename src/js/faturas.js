/**
 * FaturasSim — cálculo de em qual fatura cada parcela de uma compra a crédito cai,
 * e projeção do total de cada fatura afetada.
 *
 * Extraído do Simulador para ser reaproveitado (ex.: na Lista de Desejos) sem
 * duplicar a regra de fechamento. Depende de jQuery e de window.App.ctrl.gastos.
 */
window.FaturasSim = {

  /**
   * Em qual (mês, ano) cada parcela cai, a partir do dia de fechamento do cartão.
   * Regra: compra NO dia do fechamento ou depois entra na fatura do mês seguinte.
   * O `>=` espelha GastosModel::adicionarGasto — com `>` a projeção mostrava um
   * mês a menos que o lançamento realmente gravado ao confirmar a compra.
   * @returns [{ mes, ano, parcela, idx }]
   */
  calcularParcelas: function (valor, tipo, numParcelas, dataStr, fechamento) {
    var d   = new Date(dataStr + 'T12:00:00');
    var dia = d.getDate();
    var mes = d.getMonth() + 1;
    var ano = d.getFullYear();

    if (dia >= fechamento) {
      mes++;
      if (mes > 12) { mes = 1; ano++; }
    }

    var n   = tipo === 'avista' ? 1 : (parseInt(numParcelas, 10) || 1);
    var vlr = (parseFloat(valor) || 0) / n;
    var out = [];

    for (var i = 0; i < n; i++) {
      var m = mes + i;
      var a = ano;
      while (m > 12) { m -= 12; a++; }
      out.push({ mes: m, ano: a, parcela: vlr, idx: i + 1 });
    }
    return out;
  },

  /**
   * Projeta o impacto: para cada mês afetado, busca a fatura atual do cartão e o
   * gasto total do mês, somando a parcela que cairia ali.
   * @param opts { valor, tipo, numParcelas, dataStr, fechamento, cartaoId }
   * @param onDone recebe [{ mes, ano, idx, total, parcela, faturaAtual, novoTotal, gastoTotalMes, ultima }]
   */
  projetar: function (opts, onDone) {
    var self     = this;
    var parcelas = self.calcularParcelas(opts.valor, opts.tipo, opts.numParcelas, opts.dataStr, opts.fechamento);

    // Meses únicos afetados (parcelas do mesmo mês somam na mesma fatura).
    var meses = {};
    parcelas.forEach(function (p) {
      meses[p.ano + '-' + String(p.mes).padStart(2, '0')] = { mes: p.mes, ano: p.ano };
    });

    var keys      = Object.keys(meses);
    var faturas   = {};   // fatura atual do cartão no mês
    var gastosMes = {};   // gasto total do mês (todas as despesas)
    var pending   = keys.length * 2;   // 2 buscas por mês: fatura + dashboard

    function montar() {
      var last = parcelas.length - 1;
      return parcelas.map(function (p, i) {
        var k     = p.ano + '-' + String(p.mes).padStart(2, '0');
        var atual = faturas[k] || 0;
        return {
          mes: p.mes, ano: p.ano, idx: p.idx, total: parcelas.length,
          parcela: p.parcela, faturaAtual: atual, novoTotal: atual + p.parcela,
          gastoTotalMes: (gastosMes[k] || 0) + p.parcela,
          ultima: (i === last && parcelas.length > 1)
        };
      });
    }

    if (!pending) { onDone(montar()); return; }

    keys.forEach(function (k) {
      // Fatura atual do cartão naquele mês
      $.ajax({
        type: 'POST', url: App.ctrl.gastos,
        data: { acao: 'buscaFatura', mes: meses[k].mes, ano: meses[k].ano, cartaoId: opts.cartaoId },
        dataType: 'json',
        success: function (data) {
          var total = 0;
          if (data && !$.isEmptyObject(data)) {
            $.each(data, function (_, g) {
              var vt = parseFloat(String(g.valortotal || '0').replace(/\./g, '').replace(',', '.'));
              total += isNaN(vt) ? 0 : vt;
            });
          }
          faturas[k] = total;
        },
        error: function () { faturas[k] = 0; },
        complete: function () { if (--pending === 0) onDone(montar()); }
      });
      // Gasto total do mês (débito + crédito + recorrentes + contas + fixas)
      $.ajax({
        type: 'POST', url: App.ctrl.gastos,
        data: { acao: 'dashboard', mes: meses[k].mes, ano: meses[k].ano },
        dataType: 'json',
        success: function (data) { gastosMes[k] = data ? (parseFloat(data.totalGasto) || 0) : 0; },
        error: function () { gastosMes[k] = 0; },
        complete: function () { if (--pending === 0) onDone(montar()); }
      });
    });
  }
};
