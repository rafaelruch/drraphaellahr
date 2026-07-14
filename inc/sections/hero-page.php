<?php
/**
 * Seção HERO de página interna (cn-phero).
 * Flag do descritor: layout = 'split' (default) | 'single' | 'centered'.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function lahr_section_hero_page( $sec ) {
	$p      = $sec['key'];
	$crumb  = lahr_field( "{$p}_crumb", get_the_title() );
	$titulo = lahr_field( "{$p}_titulo" );
	$lede   = lahr_field( "{$p}_lede" );
	$meta   = (array) lahr_field( "{$p}_meta", array() );
	$layout = isset( $sec['layout'] ) ? $sec['layout'] : 'split';

	$home = esc_url( home_url( '/' ) );

	if ( 'centered' === $layout ) {
		$html  = '<section class="cn-phero"><div class="cn-phero__glow"></div>';
		$html .= '<div class="cn-phero__inner" style="grid-template-columns: 1fr; text-align: center; justify-items: center;">';
		$html .= '<div style="max-width: 720px;">';
		$html .= '<div class="cn-phero__crumbs" style="justify-content: center;"><a href="' . $home . '">Início</a><span>/</span><span>' . esc_html( $crumb ) . '</span></div>';
		$html .= '<h1 class="cn-phero__title" style="text-align: center;">' . lahr_rich( $titulo ) . '</h1>';
		if ( '' !== $lede ) {
			$html .= '<p class="cn-phero__lede" style="margin: var(--s-4) auto 0; text-align: center;">' . lahr_rich( $lede ) . '</p>';
		}
		$html .= '</div></div></section>';
		return $html;
	}

	if ( 'single' === $layout ) {
		$html  = '<section class="cn-phero"><div class="cn-phero__glow"></div>';
		$html .= '<div class="cn-phero__inner" style="grid-template-columns: 1fr;">';
		$html .= '<div>';
		$html .= '<div class="cn-phero__crumbs"><a href="' . $home . '">Início</a><span>/</span><span>' . esc_html( $crumb ) . '</span></div>';
		$html .= '<h1 class="cn-phero__title">' . lahr_rich( $titulo ) . '</h1>';
		if ( '' !== $lede ) {
			$html .= '<p class="cn-phero__lede" style="margin-top: var(--s-3);">' . lahr_rich( $lede ) . '</p>';
		}
		$html .= '</div></div></section>';
		return $html;
	}

	// split (padrão)
	$html  = '<section class="cn-phero"><div class="cn-phero__glow"></div>';
	$html .= '<div class="cn-phero__inner">';
	$html .= '<div>';
	$html .= '<div class="cn-phero__crumbs"><a href="' . $home . '">Início</a><span>/</span><span>' . esc_html( $crumb ) . '</span></div>';
	$html .= '<h1 class="cn-phero__title">' . lahr_rich( $titulo ) . '</h1>';
	$html .= '</div>';
	$html .= '<div>';
	if ( '' !== $lede ) {
		$html .= '<p class="cn-phero__lede">' . lahr_rich( $lede ) . '</p>';
	}
	if ( $meta ) {
		$html .= '<div class="cn-phero__meta">';
		foreach ( $meta as $m ) {
			$v = isset( $m['valor'] ) ? $m['valor'] : '';
			$l = isset( $m['rotulo'] ) ? $m['rotulo'] : '';
			$html .= '<div class="cn-phero__meta-item"><strong>' . esc_html( $v ) . '</strong><span>' . esc_html( $l ) . '</span></div>';
		}
		$html .= '</div>';
	}
	$html .= '</div>';
	$html .= '</div></section>';
	return $html;
}
