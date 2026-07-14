<?php
/**
 * Seção SHARP-CTA — bloco de investimento/CTA forte.
 * Flag do descritor: bg_mid.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function lahr_section_sharp_cta( $sec ) {
	$p      = $sec['key'];
	$titulo = lahr_field( "{$p}_titulo" );
	$lede   = lahr_field( "{$p}_lede" );
	$label  = lahr_field( "{$p}_cta_label" );
	$url    = lahr_field( "{$p}_cta_url", '/agendar/' );

	$html  = lahr_section_open( $sec, 'cn-section' );
	$html .= '<div class="cn-container"><div class="cn-sharp-cta">';
	$html .= '<h2 class="cn-sharp-cta__title">' . lahr_rich( $titulo ) . '</h2>';
	if ( '' !== $lede ) {
		$html .= '<p class="cn-sharp-cta__lede">' . lahr_rich( $lede ) . '</p>';
	}
	if ( '' !== $label ) {
		$html .= lahr_btn_html( $label, $url, 'gold' );
	}
	$html .= '</div></div></section>';
	return $html;
}
