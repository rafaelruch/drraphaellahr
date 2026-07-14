<?php
/**
 * Seção STEPS — etapas numeradas (cn-steps).
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function lahr_section_steps( $sec ) {
	$p       = $sec['key'];
	$eyebrow = lahr_field( "{$p}_eyebrow" );
	$titulo  = lahr_field( "{$p}_titulo" );
	$lede    = lahr_field( "{$p}_lede" );
	$itens   = (array) lahr_field( "{$p}_itens", array() );

	$html  = '<section class="cn-steps">';
	$html .= '<div class="cn-container">' . lahr_sechead_html( $eyebrow, $titulo, $lede ) . '</div>';
	$html .= '<ul class="cn-steps__list">';
	foreach ( $itens as $it ) {
		$num = isset( $it['num'] ) ? $it['num'] : '';
		$t   = isset( $it['titulo'] ) ? $it['titulo'] : '';
		$d   = isset( $it['desc'] ) ? $it['desc'] : '';
		$html .= '<li class="cn-step">';
		$html .= '<span class="cn-step__num">' . esc_html( $num ) . '</span>';
		$html .= '<h3 class="cn-step__title">' . lahr_rich( $t ) . '</h3>';
		$html .= '<p class="cn-step__desc">' . lahr_rich( $d ) . '</p>';
		$html .= '</li>';
	}
	$html .= '</ul></section>';
	return $html;
}
