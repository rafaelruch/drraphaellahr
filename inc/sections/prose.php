<?php
/**
 * Seção PROSE — bloco de texto rico (cn-prose) em container.
 * Flags do descritor: bg_mid, narrow.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function lahr_section_prose( $sec ) {
	$p        = $sec['key'];
	$titulo   = lahr_field( "{$p}_titulo" );
	$conteudo = lahr_field( "{$p}_conteudo" );

	$html  = lahr_section_open( $sec, 'cn-section' );
	$html .= '<div class="cn-container"><div class="cn-prose">';
	if ( '' !== $titulo ) {
		$html .= '<h3>' . lahr_rich( $titulo ) . '</h3>';
	}
	$html .= wp_kses_post( $conteudo );
	$html .= '</div></div></section>';
	return $html;
}
