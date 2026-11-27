/* Atribuição de origem — cookie lahr_attr (last-touch, 90d).
 * - Chegou com gclid/fbclid/utm_* → sempre grava (sobrescreve).
 * - Chegou sem UTM, vindo de outro site (Google, Maps, Instagram...) → grava o
 *   site de origem (ref) SÓ se ainda não houver atribuição — uma visita orgânica
 *   posterior não apaga o crédito de um anúncio/perfil anterior.
 * O canal (Google Ads, Google Meu Negócio, Google Orgânico...) é decidido no
 * servidor por lahr_lead_canal_from(). */
(function () {
  'use strict';
  try {
    var p = new URLSearchParams(location.search);
    var keys = ['gclid', 'wbraid', 'gbraid', 'fbclid', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
    var data = {};
    keys.forEach(function (k) { var v = p.get(k); if (v) { data[k] = v.slice(0, 200); } });
    var hit = Object.keys(data).length > 0;

    var ref = '';
    if (document.referrer) {
      try {
        var r = new URL(document.referrer);
        if (r.hostname && r.hostname !== location.hostname) {
          ref = r.hostname + (/^\/maps/.test(r.pathname) ? '/maps' : '');
        }
      } catch (e) { /* referrer inválido */ }
    }

    if (!hit) {
      if (!ref || /(?:^|;\s*)lahr_attr=/.test(document.cookie)) { return; }
    }
    if (ref) { data.ref = ref; }
    document.cookie = 'lahr_attr=' + encodeURIComponent(JSON.stringify(data)) + ';path=/;max-age=' + (90 * 86400) + ';SameSite=Lax';
  } catch (e) { /* silencioso */ }
})();
