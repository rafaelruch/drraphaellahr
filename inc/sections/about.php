<?php
/**
 * Seção ABOUT — bloco do médico (foto + credenciais).
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function lahr_section_about( $sec ) {
	$p       = $sec['key'];
	$img     = lahr_field( "{$p}_imagem" );
	$eyebrow = lahr_field( "{$p}_eyebrow" );
	$titulo  = lahr_field( "{$p}_titulo" );
	$texto   = lahr_field( "{$p}_texto" );
	$creds   = (array) lahr_field( "{$p}_creds", array() );

	$html  = '<section class="cn-about"><div class="cn-about__grid">';
	$html .= '<figure class="cn-about__photo" data-parallax data-parallax-speed="0.08">';
	$html .= lahr_img( $img, array( 'class' => '', 'alt' => wp_strip_all_tags( $titulo ) ) );
	$html .= '</figure>';
	$html .= '<div class="cn-about__content">';
	if ( '' !== $eyebrow ) {
		$html .= '<span class="cn-eyebrow">' . esc_html( $eyebrow ) . '</span>';
	}
	$html .= '<h2>' . lahr_rich( $titulo ) . '</h2>';
	// Parágrafos (WYSIWYG) já vêm com <p>…</p>.
	$html .= wp_kses_post( $texto );

	if ( $creds ) {
		$html .= '<ul class="cn-about__creds">';
		foreach ( $creds as $c ) {
			$valor   = isset( $c['valor'] ) ? $c['valor'] : '';
			$rotulo  = isset( $c['rotulo'] ) ? $c['rotulo'] : '';
			$counter = isset( $c['counter'] ) ? $c['counter'] : '';
			if ( '' !== $counter && null !== $counter ) {
				$html .= '<li><strong data-counter="' . esc_attr( $counter ) . '">' . esc_html( $valor ) . '</strong><span>' . esc_html( $rotulo ) . '</span></li>';
			} else {
				$html .= '<li><strong>' . esc_html( $valor ) . '</strong><span>' . esc_html( $rotulo ) . '</span></li>';
			}
		}
		$html .= '</ul>';
	}

	$html .= '</div></div></section>';
	return $html;
}
