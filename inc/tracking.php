<?php
/**
 * Rastreamento — Meta Pixel + Google (GA4) — padrão Delva/Vaxx/Uro Centro.
 *
 * <head>: Meta Pixel (init + PageView) e Google tag (gtag config GA4) em todas
 * as páginas. Evento de Lead disparado na página /obrigado (formulário Agendar)
 * e no submit do widget de WhatsApp (via JS). IDs editáveis em
 * Configurações do Site → Rastreamento (Pixels); vazios = desativado.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** IDs (default = os do Dr. Raphael; sobrescrevíveis no painel). */
function lahr_meta_pixel_id() {
	return trim( (string) lahr_opt( 'meta_pixel_id', '1513020196487461' ) );
}
function lahr_ga4_id() {
	return trim( (string) lahr_opt( 'ga4_id', 'G-84DP2Q169C' ) );
}

/** Base dos pixels no <head>. */
add_action(
	'wp_head',
	function () {
		if ( is_admin() ) {
			return;
		}
		$pixel = lahr_meta_pixel_id();
		$ga4   = lahr_ga4_id();

		// Meta Pixel.
		if ( $pixel ) {
			?>
<!-- Meta Pixel -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init','<?php echo esc_js( $pixel ); ?>');
fbq('track','PageView');
</script>
<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo esc_attr( $pixel ); ?>&ev=PageView&noscript=1"/></noscript>
<!-- /Meta Pixel -->
			<?php
		}

		// Google tag (GA4).
		if ( $ga4 ) {
			?>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ga4 ); ?>"></script>
<script>
window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}
gtag('js',new Date());
gtag('config','<?php echo esc_js( $ga4 ); ?>');
</script>
<!-- /Google tag -->
			<?php
		}
	},
	1
);

/** Evento de Lead na página /obrigado (conversão do formulário Agendar). */
add_action(
	'wp_footer',
	function () {
		if ( ! is_page() || 'obrigado' !== get_post_field( 'post_name', get_queried_object_id() ) ) {
			return;
		}
		echo "<script>try{if(window.fbq)fbq('track','Lead');if(window.gtag)gtag('event','generate_lead',{currency:'BRL',value:0});}catch(e){}</script>";
	},
	20
);
