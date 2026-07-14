<?php
/**
 * Seção SERVICES — grade de serviços (Home), 3 por linha.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function lahr_section_services( $sec ) {
	$p       = $sec['key'];
	$eyebrow = lahr_field( "{$p}_eyebrow" );
	$titulo  = lahr_field( "{$p}_titulo" );
	$lede    = lahr_field( "{$p}_lede" );
	$itens   = (array) lahr_field( "{$p}_itens", array() );

	$html  = lahr_section_open( $sec, 'cn-services' );
	$html .= '<div class="cn-container">';
	$html .= lahr_sechead_html( $eyebrow, $titulo, $lede );

	$rows = array_chunk( $itens, 3 );
	foreach ( $rows as $i => $row ) {
		$style = ( 0 === $i && count( $rows ) > 1 ) ? ' style="margin-bottom: 24px;"' : '';
		$html .= '<div class="cn-cards"' . $style . '>';
		foreach ( $row as $s ) {
			$num  = isset( $s['num'] ) ? $s['num'] : '';
			$t    = isset( $s['titulo'] ) ? $s['titulo'] : '';
			$desc = isset( $s['desc'] ) ? $s['desc'] : '';
			$url  = isset( $s['url'] ) ? $s['url'] : '';
			$cta  = isset( $s['cta_label'] ) && '' !== $s['cta_label'] ? $s['cta_label'] : 'Saiba mais';
			$html .= '<a href="' . esc_url( $url ) . '" class="cn-card" data-tilt>';
			$html .= '<span class="cn-card__num">' . esc_html( $num ) . '</span>';
			$html .= '<h3 class="cn-card__title">' . lahr_rich( $t ) . '</h3>';
			$html .= '<p class="cn-card__desc">' . lahr_rich( $desc ) . '</p>';
			$html .= '<span class="cn-card__cta">' . esc_html( $cta ) . ' <span class="cn-card__cta-arrow">→</span></span>';
			$html .= '</a>';
		}
		$html .= '</div>';
	}

	$html .= '</div></section>';
	return $html;
}
