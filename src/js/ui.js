/* Sky Finance — UI 2.0: movimento como resposta a ações.
   1) Pulso no controle clicado (check de pago/recebido, ações rápidas).
   2) Valores dos cards do dashboard contam até o número novo quando mudam
      (ex.: trocar de mês), mostrando que os dados foram atualizados.
   Respeita prefers-reduced-motion. */
(function () {
  var reduzido = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ── 1. Pulso no clique ──────────────────────────────────────────
  var ALVOS = '.item-check, .medeve-check, .ar-btn, .btn-comprar, .btn-reverter-compra, .desejo-check';
  document.addEventListener('click', function (e) {
    if (reduzido) return;
    var el = e.target.closest && e.target.closest(ALVOS);
    if (!el) return;
    el.classList.remove('sky-pulso');
    void el.offsetWidth;                 // reinicia a animação se clicar de novo
    el.classList.add('sky-pulso');
    setTimeout(function () { el.classList.remove('sky-pulso'); }, 450);
  }, true);

  // ── 2. Contagem dos valores ─────────────────────────────────────
  // "R$ 1.234,56" / "R$ − 283,04" → número; e de volta, no mesmo formato.
  function lerValor(txt) {
    var m = String(txt).match(/(−|-)?\s*([\d.]+,\d{2})/);
    if (!m) return null;
    var n = parseFloat(m[2].replace(/\./g, '').replace(',', '.'));
    return m[1] ? -n : n;
  }
  function formatar(modelo, n) {
    var abs = Math.abs(n).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    // Troca só "− 1.234,56" (sinal + número), mantendo o espaço depois do "R$"
    return String(modelo).replace(/(?:[−-]\s?)?[\d.]+,\d{2}/, (n < 0 ? '− ' : '') + abs);
  }
  var ultimo = new WeakMap();   // elemento → último valor mostrado
  var anima = new WeakMap();

  function contar(el, de, para, texto) {
    if (reduzido || de === para) { return; }
    var ini = performance.now(), dur = 520;
    var id = (anima.get(el) || 0) + 1; anima.set(el, id);
    el.classList.add('sky-contando');
    (function passo(t) {
      if (anima.get(el) !== id) return;              // outra contagem assumiu
      var p = Math.min(1, (t - ini) / dur);
      var e = 1 - Math.pow(1 - p, 3);                // desacelera no fim
      el.textContent = formatar(texto, de + (para - de) * e);
      if (p < 1) requestAnimationFrame(passo);
      else { el.textContent = texto; el.classList.remove('sky-contando'); }
    })(ini);
  }

  var SEL_VALORES = '.kpi-valor, .kpi-sm-valor, .kpi-extra-valor';
  function observa(raiz) {
    raiz.querySelectorAll(SEL_VALORES).forEach(function (el) {
      if (el.dataset.skyVisto) return;
      el.dataset.skyVisto = '1';
      var chave = el.closest('[class*="col"]') ? el.closest('.kpi-card, .kpi-sm') : null;
      var texto = el.textContent, novo = lerValor(texto);
      if (novo === null) return;
      // Mesmo card (pela posição + rótulo) já mostrou um valor antes? anima a partir dele
      var rot = chave ? (chave.querySelector('.kpi-label, .kpi-sm-label') || {}).textContent : '';
      var k = (rot || '') + '|' + Array.prototype.indexOf.call(document.querySelectorAll(SEL_VALORES), el);
      var antes = memoria[k];
      memoria[k] = novo;
      contar(el, antes === undefined ? 0 : antes, novo, texto);
    });
  }
  var memoria = {};

  document.addEventListener('DOMContentLoaded', function () {
    var alvo = document.getElementById('kpiRow');
    if (!alvo) return;                                  // só o dashboard tem os KPIs
    var raiz = alvo.parentElement;
    var t = null;
    new MutationObserver(function () {
      clearTimeout(t);
      t = setTimeout(function () { observa(raiz); }, 0);
    }).observe(raiz, { childList: true, subtree: true });
  });
})();

/* ═══ UX / ACESSIBILIDADE (aplicado em todas as telas) ════════════════════
   Referências: heurísticas de Nielsen (status do sistema, prevenção e
   recuperação de erros, reconhecimento), WCAG 2.2 (nomes acessíveis,
   rótulos, teclado, área de toque) e Baymard (formulários). */
(function () {
  // ── 1. Nomes, rótulos e teclado (corrige o que for criado depois também) ──
  var CLICAVEIS = '.pessoa-card-header, .cartao-mini, .cartao-mini-modal, .resp-chip, .leg-cat-fatura, ' +
                  '.cat-list-row, .sim-cartao-chip, .divisao-opcao, .item-check, .medeve-check, .desejo-check, .linha-clicavel';

  function texto(el) { return (el.textContent || '').replace(/\s+/g, ' ').trim(); }

  function corrige(raiz) {
    // Botões/links só com ícone: nome vindo do title (ou "Fechar" no X dos modais)
    raiz.querySelectorAll('button, a.btn, [role="button"]').forEach(function (b) {
      if (b.getAttribute('aria-label') || texto(b)) return;
      var nome = b.getAttribute('title') || b.getAttribute('data-bs-original-title') ||
                 (b.classList.contains('btn-close') ? 'Fechar' : '');
      if (nome) b.setAttribute('aria-label', nome);
    });
    // Botão que só tem ícone (sem texto): recebe a classe de "ação de linha"
    raiz.querySelectorAll('.btn:not(.btn-acao-icone)').forEach(function (b) {
      if (b.classList.contains('btn-close') || texto(b)) return;
      if (!b.querySelector('i.bi')) return;
      if (b.matches('.btn-primary, .btn-success, .btn-danger, .btn-calc')) return;
      b.classList.add('btn-acao-icone');
    });
    // Rótulo visível ligado ao campo (clicar no rótulo foca o campo; leitor de tela lê)
    raiz.querySelectorAll('label.form-label:not([for])').forEach(function (l) {
      var bloco = l.parentElement, campo = bloco && bloco.querySelector('input:not([type=hidden]), select, textarea');
      if (!campo || campo === l) return;
      if (!campo.id) campo.id = 'campo-' + Math.random().toString(36).slice(2, 9);
      l.setAttribute('for', campo.id);
    });
    // Campos ainda sem nome: usa title/placeholder; seletor de mês ganha "Mês"
    raiz.querySelectorAll('input:not([type=hidden]), select, textarea').forEach(function (c) {
      if (c.getAttribute('aria-label') || c.labels && c.labels.length) return;
      var nome = c.getAttribute('title') || c.getAttribute('placeholder') ||
                 (/^mes/i.test(c.id || '') ? 'Mês' : '') || (c.type === 'date' ? 'Data' : '');
      if (nome) c.setAttribute('aria-label', nome);
    });
    // Títulos de seção viram nível 2 (depois do título da página, nível 1)
    raiz.querySelectorAll('.fs-secao-titulo, .painel > h5.titulo, .painel > h6.titulo').forEach(function (h) {
      if (h.closest('.modal')) return;
      h.setAttribute('role', 'heading'); h.setAttribute('aria-level', '2');
    });
    // Títulos dentro de cards (ex.: nome do cofrinho) ficam no nível 3
    raiz.querySelectorAll('.card-title').forEach(function (h) {
      if (h.closest('.modal')) return;
      h.setAttribute('role', 'heading'); h.setAttribute('aria-level', '3');
    });
    // Áreas com rolagem própria: alcançáveis pelo teclado
    raiz.querySelectorAll('.tabela-rolagem, #listaCategorias, #catLista, .checkout-itens').forEach(function (r) {
      if (!r.hasAttribute('tabindex')) r.setAttribute('tabindex', '0');
    });
    // Elementos clicáveis feitos com <div>: viram botões para teclado e leitor de tela
    raiz.querySelectorAll(CLICAVEIS).forEach(function (el) {
      if (/^(BUTTON|A|INPUT|SELECT|TR)$/.test(el.tagName)) return;
      if (!el.hasAttribute('role')) el.setAttribute('role', 'button');
      if (!el.hasAttribute('tabindex')) el.setAttribute('tabindex', '0');
    });
  }
  // Enter/Espaço em "botões" que são <div> disparam o clique
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter' && e.key !== ' ') return;
    var el = e.target;
    if (!el.matches || !el.matches('[role="button"]:not(button):not(a)')) return;
    e.preventDefault();
    el.click();
  });

  var t = null;
  function agenda() { clearTimeout(t); t = setTimeout(function () { corrige(document); }, 80); }
  document.addEventListener('DOMContentLoaded', function () {
    corrige(document);
    new MutationObserver(agenda).observe(document.body, { childList: true, subtree: true });
  });

  // ── 2. Botão mostra que está salvando e não aceita clique duplo ──
  // (Nielsen: visibilidade do status; prevenção de erros — lançamento duplicado)
  if (!window.jQuery) return;
  var ultimoClique = null, ultimoT = 0, emEspera = [];
  document.addEventListener('click', function (e) {
    var b = e.target.closest && e.target.closest('.btn');
    if (!b || b.classList.contains('btn-close') || b.hasAttribute('data-bs-toggle') || b.hasAttribute('data-bs-dismiss')) return;
    ultimoClique = b; ultimoT = performance.now();
  }, true);
  jQuery(document).ajaxSend(function (ev, xhr, opt) {
    if (!ultimoClique || performance.now() - ultimoT > 400) return;
    if (/acao=(busca|buscar|listar|resumo|dashboard|buscaFatura)\b/.test(String(opt.data || ''))) return; // leituras não travam
    var b = ultimoClique; ultimoClique = null;
    if (b.classList.contains('is-carregando')) return;
    b.classList.add('is-carregando');
    b.setAttribute('aria-busy', 'true');
    b.dataset.skyDesab = b.disabled ? '1' : '';
    b.disabled = true;
    emEspera.push(b);
  });
  jQuery(document).ajaxStop(function () {
    emEspera.forEach(function (b) {
      b.classList.remove('is-carregando');
      b.removeAttribute('aria-busy');
      if (!b.dataset.skyDesab) b.disabled = false;
    });
    emEspera = [];
  });

  // ── 3. Erros que dizem o que fazer (Nielsen: reconhecer e se recuperar de erros) ──
  // Sessão expirada (401): a mensagem genérica da tela ("Erro ao carregar...")
  // não ajuda; só a mensagem abaixo, que diz o que fazer, aparece.
  jQuery.ajaxPrefilter(function (opt) {
    var original = opt.error;
    if (!original) return;
    opt.error = function (xhr) {
      if (xhr && xhr.status === 401) return;
      var ctx = this, args = arguments;
      (Array.isArray(original) ? original : [original]).forEach(function (f) { f.apply(ctx, args); });
    };
  });
  var avisouSessao = false;
  jQuery(document).ajaxError(function (ev, xhr) {
    if (xhr.status === 401 && !avisouSessao) {
      avisouSessao = true;
      toastr.error('Sua sessão expirou. Você será levado ao login para entrar de novo.');
      setTimeout(function () { location.href = (window.App && App.base ? App.base : '') + '/login.php'; }, 2500);
    } else if (xhr.status === 0 && xhr.statusText !== 'abort') {
      toastr.error('Sem conexão com o servidor. Confira se o sistema está rodando e tente de novo.');
    }
  });
})();

/* ═══ Tema claro: contraste das cores próprias (categorias, valores) ═══════
   A paleta foi feita para fundo escuro. No tema claro, cada texto com cor
   própria e contraste < 4,5:1 recebe a MESMA cor, escurecida só o necessário.
   A cor original fica guardada e volta ao trocar para um tema escuro. */
(function () {
  function rgb(c) { var m = String(c).match(/\d+(\.\d+)?/g); return m ? m.slice(0, 3).map(Number) : null; }
  function lum(c) {
    return c.map(function (v) { v /= 255; return v <= .03928 ? v / 12.92 : Math.pow((v + .055) / 1.055, 2.4); })
            .reduce(function (s, v, i) { return s + v * [.2126, .7152, .0722][i]; }, 0);
  }
  function contraste(a, b) { var x = lum(a), y = lum(b); return (Math.max(x, y) + .05) / (Math.min(x, y) + .05); }
  // Fundo real atrás do elemento: compõe as camadas translúcidas (ex.: badge
  // com a cor da categoria a 13%) sobre o primeiro fundo opaco encontrado.
  function fundo(el) {
    var camadas = [];
    for (var e = el; e && e.nodeType === 1; e = e.parentElement) {
      var c = getComputedStyle(e).backgroundColor, v = rgb(c);
      if (!v) continue;
      var a = (String(c).match(/rgba\([^)]*,\s*([\d.]+)\)/) || [])[1];
      a = a === undefined ? 1 : parseFloat(a);
      if (a <= 0) continue;
      camadas.push([v, a]);
      if (a >= 1) break;
    }
    var base = [255, 255, 255];
    for (var i = camadas.length - 1; i >= 0; i--) {
      var cor = camadas[i][0], al = camadas[i][1];
      base = base.map(function (b, k) { return cor[k] * al + b * (1 - al); });
    }
    return base;
  }
  function ajusta() {
    var claro = document.documentElement.classList.contains('light-mode');
    if (!claro) {
      document.querySelectorAll('[data-sky-cor-orig]').forEach(function (el) {
        el.style.color = el.getAttribute('data-sky-cor-orig');
        el.removeAttribute('data-sky-cor-orig');
      });
      return;
    }
    document.querySelectorAll('[style*="color"]').forEach(function (el) {
      if (el.hasAttribute('data-sky-cor-orig') || !el.style.color || !(el.textContent || '').trim()) return;
      var c = rgb(getComputedStyle(el).color); if (!c) return;
      var bg = fundo(el);
      // Só escurece texto que já é mais escuro que o fundo (texto claro sobre
      // fundo sólido, como badge com letra branca, fica como está)
      if (lum(c) > lum(bg)) return;
      var meta = el.matches('.kpi-extra-valor') ? 6 : 5;   // valor sobre dois fundos sobrepostos: folga maior
      if (contraste(c, bg) >= meta || lum(c) < .02) return;
      var orig = el.style.color, k = 1;
      while (contraste(c.map(function (v) { return v * k; }), bg) < meta && k > .2) k -= .03;
      el.setAttribute('data-sky-cor-orig', orig);
      el.style.color = 'rgb(' + c.map(function (v) { return Math.round(v * k); }).join(',') + ')';
    });
  }
  var t = null;
  function agenda() { clearTimeout(t); t = setTimeout(ajusta, 120); }
  document.addEventListener('DOMContentLoaded', function () {
    ajusta();
    new MutationObserver(agenda).observe(document.body, { childList: true, subtree: true });
    new MutationObserver(agenda).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    // Modais e menus ficam escondidos na carga: ajusta também quando aparecem
    document.addEventListener('shown.bs.modal', agenda);
    document.addEventListener('shown.bs.dropdown', agenda);
  });
})();

/* Menu do avatar: na barra lateral (≥ 1200px) o avatar fica no rodapé, então
   o menu abre PARA CIMA desde o início. Abrir para baixo fazia o Popper
   "virar" o menu no meio da animação (descia e subia). */
(function () {
  var mq = window.matchMedia('(min-width: 1200px)');
  function ajusta() {
    var dd = document.querySelector('.nav-user-dropdown');
    if (dd) dd.classList.toggle('dropup', mq.matches);
  }
  document.addEventListener('DOMContentLoaded', ajusta);
  if (mq.addEventListener) mq.addEventListener('change', ajusta); else mq.addListener(ajusta);
})();
