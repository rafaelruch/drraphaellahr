<?php
/**
 * Seção SPLIT — imagem + texto (cn-split).
 * Flags do descritor: reverse, bg_mid, anchor.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function lahr_section_split( $sec ) {
	$p       = $sec['key'];
	$eyebrow = lahr_field( "{$p}_eyebrow" );
	$titulo  = lahr_field( "{$p}_titulo" );
	$texto   = lahr_field( "{$p}_texto" );
	$img     = lahr_field( "{$p}_imagem" );

	$classes = array( 'cn-split' );
	if ( ! empty( $sec['reverse'] ) ) {
		$classes[] = 'cn-split--reverse';
	}
	if ( ! empty( $sec['bg_mid'] ) ) {
		$classes[] = 'cn-section--bg-mid';
	}
	$id = ! empty( $sec['anchor'] ) ? ' id="' . esc_attr( $sec['anchor'] ) . '"' : '';

	$html  = '<section' . $id . ' class="' . esc_attr( implode( ' ', $classes ) ) . '">';
	$html .= '<div class="cn-split__inner">';
	$html .= '<div class="cn-split__copy">';
	if ( '' !== $eyebrow ) {
		$html .= '<span class="cn-split__eyebrow">' . esc_html( $eyebrow ) . '</span>';
	}
	$html .= '<h2 class="cn-split__title">' . lahr_rich( $titulo ) . '</h2>';
	$html .= '<div class="cn-split__text">' . wp_kses_post( $texto ) . '</div>';
	$html .= '</div>';
	$html .= '<figure class="cn-split__figure">' . lahr_img( $img, array( 'alt' => wp_strip_all_tags( $titulo ) ) ) . '</figure>';
	$html .= '</div></section>';
	return $html;
}
