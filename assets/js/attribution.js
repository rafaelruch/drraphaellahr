/* Atribuição de origem — captura gclid/fbclid/utm no cookie lahr_attr (last-touch, 90d). */
(function () {
  'use strict';
  try {
    var p = new URLSearchParams(location.search);
    var keys = ['gclid', 'wbraid', 'gbraid', 'fbclid', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
    var hit = keys.some(function (k) { return p.get(k); });
    if (!hit) { return; } // só sobrescreve quando chega com sinal de anúncio
    var data = {};
    keys.forEach(function (k) { var v = p.get(k); if (v) { data[k] = v; } });
    document.cookie = 'lahr_attr=' + encodeURIComponent(JSON.stringify(data)) + ';path=/;max-age=' + (90 * 86400) + ';SameSite=Lax';
  } catch (e) { /* silencioso */ }
})();
