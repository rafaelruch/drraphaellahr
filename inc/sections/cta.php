<?php
/**
 * Seção CTA final (cn-cta).
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function lahr_section_cta( $sec ) {
	$p      = $sec['key'];
	$titulo = lahr_field( "{$p}_titulo" );
	$lede   = lahr_field( "{$p}_lede" );
	$label  = lahr_field( "{$p}_cta_label" );
	$url    = lahr_field( "{$p}_cta_url", '/agendar/' );

	$html  = '<section class="cn-cta">';
	$html .= '<div class="cn-cta__glow"></div>';
	$html .= '<div class="cn-cta__inner">';
	$html .= '<h2 class="cn-cta__title">' . lahr_rich( $titulo ) . '</h2>';
	if ( '' !== $lede ) {
		$html .= '<p class="cn-cta__lede">' . lahr_rich( $lede ) . '</p>';
	}
	if ( '' !== $label ) {
		$html .= lahr_btn_html( $label, $url, 'gold' );
	}
	$html .= '</div></section>';
	return $html;
}
