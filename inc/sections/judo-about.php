<?php
/**
 * Seção JUDÔ (filosofia — página Sobre). Layout distinto do judô da Home.
 * Flag do descritor: anchor (default 'judo').
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function lahr_section_judo_about( $sec ) {
	$p       = $sec['key'];
	$caption = lahr_field( "{$p}_caption" );
	$quote   = lahr_field( "{$p}_quote" );
	$author  = lahr_field( "{$p}_author" );
	$princ   = (array) lahr_field( "{$p}_princ", array() );
	$anchor  = isset( $sec['anchor'] ) ? $sec['anchor'] : 'judo';

	$html  = '<section id="' . esc_attr( $anchor ) . '" class="cn-judo"><div class="cn-judo__inner">';
	$html .= '<div>';
	if ( '' !== $caption ) {
		$html .= '<p class="cn-judo__caption">' . esc_html( $caption ) . '</p>';
	}
	$html .= '<p class="cn-judo__quote">' . lahr_rich( $quote ) . '</p>';
	if ( '' !== $author ) {
		$html .= '<p class="cn-judo__author">' . esc_html( $author ) . '</p>';
	}
	$html .= '</div><div><ul class="cn-judo__principles">';
	foreach ( $princ as $pr ) {
		$k = isset( $pr['kanji'] ) ? $pr['kanji'] : '';
		$t = isset( $pr['texto'] ) ? $pr['texto'] : '';
		$html .= '<li><strong>' . esc_html( $k ) . '</strong><span>' . lahr_rich( $t ) . '</span></li>';
	}
	$html .= '</ul></div></div></section>';
	return $html;
}
