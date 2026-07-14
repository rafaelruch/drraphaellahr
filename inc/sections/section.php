<?php
/**
 * Seção genérica (cn-section) — cabeçalho + conteúdo rico opcional.
 * Flags do descritor: bg_mid, narrow.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function lahr_section_section( $sec ) {
	$p        = $sec['key'];
	$eyebrow  = lahr_field( "{$p}_eyebrow" );
	$titulo   = lahr_field( "{$p}_titulo" );
	$lede     = lahr_field( "{$p}_lede" );
	$conteudo = lahr_field( "{$p}_conteudo" );

	$html  = lahr_section_open( $sec, 'cn-section' );
	$html .= '<div class="cn-container">';
	$html .= lahr_sechead_html( $eyebrow, $titulo, $lede );
	if ( '' !== $conteudo ) {
		$html .= '<div class="cn-prose">' . wp_kses_post( $conteudo ) . '</div>';
	}
	$html .= '</div></section>';
	return $html;
}
