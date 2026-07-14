<?php
/**
 * Lahr — helpers de renderização de seção (compartilhados).
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Cabeçalho de seção (cn-sec-head). Eyebrow/lede saem apenas se preenchidos.
 *
 * @param string $eyebrow Texto pequeno acima do título.
 * @param string $titulo  Título (aceita <em>/<strong>).
 * @param string $lede    Subtítulo.
 * @return string
 */
function lahr_sechead_html( $eyebrow, $titulo, $lede = '' ) {
	if ( '' === $eyebrow && '' === $titulo && '' === $lede ) {
		return '';
	}
	$html  = '<header class="cn-sec-head">';
	if ( '' !== $eyebrow ) {
		$html .= '<span class="cn-sec-head__eyebrow">' . esc_html( $eyebrow ) . '</span>';
	}
	if ( '' !== $titulo ) {
		$html .= '<h2 class="cn-sec-head__title">' . lahr_rich( $titulo ) . '</h2>';
	}
	if ( '' !== $lede ) {
		$html .= '<p class="cn-sec-head__lede">' . lahr_rich( $lede ) . '</p>';
	}
	$html .= '</header>';
	return $html;
}

/**
 * Botão no padrão do tema.
 *
 * @param string $label Texto.
 * @param string $url   URL.
 * @param string $style 'gold' | 'ghost'.
 * @param bool   $arrow Adiciona seta.
 * @return string
 */
function lahr_btn_html( $label, $url, $style = 'gold', $arrow = false ) {
	if ( '' === $label ) {
		return '';
	}
	$class = 'cn-btn cn-btn--' . ( 'ghost' === $style ? 'ghost' : 'gold' );
	$inner = esc_html( $label );
	if ( $arrow ) {
		$inner .= ' <span class="arrow">→</span>';
	}
	return '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '">' . $inner . '</a>';
}

/**
 * Abre a tag <section> com classes/âncora conforme flags do descritor.
 *
 * @param array  $sec  Descritor da seção.
 * @param string $base Classe base (ex.: 'cn-section').
 * @param array  $mods Modificadores extras já resolvidos.
 * @return string
 */
function lahr_section_open( $sec, $base, $mods = array() ) {
	$classes = array( $base );
	if ( ! empty( $sec['bg_mid'] ) ) {
		$classes[] = 'cn-section--bg-mid';
	}
	if ( ! empty( $sec['narrow'] ) ) {
		$classes[] = 'cn-section--narrow';
	}
	foreach ( $mods as $m ) {
		$classes[] = $m;
	}
	$id = ! empty( $sec['anchor'] ) ? ' id="' . esc_attr( $sec['anchor'] ) . '"' : '';
	return '<section' . $id . ' class="' . esc_attr( implode( ' ', $classes ) ) . '">';
}
