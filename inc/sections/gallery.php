<?php
/**
 * Seção GALLERY — grade de imagens (3 col) com cabeçalho.
 * Flags do descritor: bg_mid, narrow.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function lahr_section_gallery( $sec ) {
	$p       = $sec['key'];
	$eyebrow = lahr_field( "{$p}_eyebrow" );
	$titulo  = lahr_field( "{$p}_titulo" );
	$lede    = lahr_field( "{$p}_lede" );
	$imgs    = (array) lahr_field( "{$p}_imgs", array() );

	$html  = lahr_section_open( $sec, 'cn-section' );
	$html .= '<div class="cn-container">';
	$html .= lahr_sechead_html( $eyebrow, $titulo, $lede );
	$html .= '<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--s-3); margin-top: var(--s-5);">';
	foreach ( $imgs as $g ) {
		$id  = isset( $g['imagem'] ) ? $g['imagem'] : '';
		$alt = isset( $g['alt'] ) ? $g['alt'] : '';
		$url = lahr_img_url( $id );
		if ( ! $url ) {
			continue;
		}
		$html .= '<figure style="aspect-ratio: 3/4; overflow: hidden; border: 1px solid var(--c-line); border-radius: 8px; margin: 0; position: relative;">';
		$html .= '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy" style="width:100%;height:100%;object-fit:cover;filter:grayscale(1) contrast(1.1)">';
		$html .= '</figure>';
	}
	$html .= '</div></div></section>';
	return $html;
}
