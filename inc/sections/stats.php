<?php
/**
 * Seção STATS — números de destaque. Flag: bg_mid.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function lahr_section_stats( $sec ) {
	$p     = $sec['key'];
	$itens = (array) lahr_field( "{$p}_itens", array() );

	$html  = lahr_section_open( $sec, 'cn-section' );
	// Espaçamento característico do bloco de números.
	$html  = rtrim( $html, '>' ) . ' style="padding-top: 96px; padding-bottom: 96px;">';
	$html .= '<div class="cn-stats__grid">';

	foreach ( $itens as $st ) {
		$valor   = isset( $st['valor'] ) ? $st['valor'] : '';
		$label   = isset( $st['label'] ) ? $st['label'] : '';
		$counter = isset( $st['counter'] ) ? $st['counter'] : '';
		$suffix  = isset( $st['suffix'] ) ? $st['suffix'] : '';

		$html .= '<div class="cn-stat"><div class="cn-stat__num">';
		if ( '' !== $counter && null !== $counter ) {
			$html .= '<span data-counter="' . esc_attr( $counter ) . '">0</span>';
			if ( '' !== $suffix ) {
				$html .= '<small>' . esc_html( $suffix ) . '</small>';
			}
		} else {
			$html .= esc_html( $valor );
		}
		$html .= '</div><div class="cn-stat__label">' . esc_html( $label ) . '</div></div>';
	}

	$html .= '</div></section>';
	return $html;
}
