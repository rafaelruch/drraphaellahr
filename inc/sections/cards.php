<?php
/**
 * Seção CARDS — grade de cartões com cabeçalho.
 * Flags do descritor: bg_mid, tilt (default true), top_gap, wrapper (default cn-section).
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function lahr_section_cards( $sec ) {
	$p       = $sec['key'];
	$eyebrow = lahr_field( "{$p}_eyebrow" );
	$titulo  = lahr_field( "{$p}_titulo" );
	$lede    = lahr_field( "{$p}_lede" );
	$cards   = lahr_field( "{$p}_cards", array() );
	$tilt    = isset( $sec['tilt'] ) ? (bool) $sec['tilt'] : true;
	$tiltatt = $tilt ? ' data-tilt' : '';
	$gap     = ! empty( $sec['top_gap'] ) ? ' style="margin-top: var(--s-5);"' : '';

	$html  = lahr_section_open( $sec, 'cn-section' );
	$html .= '<div class="cn-container">';
	$html .= lahr_sechead_html( $eyebrow, $titulo, $lede );
	$html .= '<div class="cn-cards"' . $gap . '>';

	foreach ( (array) $cards as $c ) {
		$num   = isset( $c['num'] ) ? $c['num'] : '';
		$ct    = isset( $c['titulo'] ) ? $c['titulo'] : '';
		$desc  = isset( $c['desc'] ) ? $c['desc'] : '';
		$url   = isset( $c['url'] ) ? $c['url'] : '';
		$cta   = isset( $c['cta_label'] ) ? $c['cta_label'] : '';

		$inner  = '<span class="cn-card__num">' . esc_html( $num ) . '</span>';
		$inner .= '<h3 class="cn-card__title">' . lahr_rich( $ct ) . '</h3>';
		$inner .= '<p class="cn-card__desc">' . lahr_rich( $desc ) . '</p>';
		if ( '' !== $cta ) {
			$inner .= '<span class="cn-card__cta">' . esc_html( $cta ) . ' <span class="cn-card__cta-arrow">→</span></span>';
		}

		if ( '' !== $url ) {
			$html .= '<a href="' . esc_url( $url ) . '" class="cn-card"' . $tiltatt . '>' . $inner . '</a>';
		} else {
			$html .= '<article class="cn-card"' . $tiltatt . '>' . $inner . '</article>';
		}
	}

	$html .= '</div></div></section>';
	return $html;
}
