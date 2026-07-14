<?php
/**
 * Seção COMPARE — duas colunas comparativas (cn-compare).
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function lahr_compare_col( $mod, $tag, $nome, $itens ) {
	$html  = '<article class="cn-compare__col cn-compare__col--' . esc_attr( $mod ) . '">';
	if ( '' !== $tag ) {
		$html .= '<span class="cn-compare__tag">' . esc_html( $tag ) . '</span>';
	}
	$html .= '<h3 class="cn-compare__name">' . lahr_rich( $nome ) . '</h3>';
	$html .= '<ul class="cn-compare__list">';
	foreach ( (array) $itens as $it ) {
		$txt = isset( $it['texto'] ) ? $it['texto'] : '';
		$html .= '<li>' . lahr_rich( $txt ) . '</li>';
	}
	$html .= '</ul></article>';
	return $html;
}

function lahr_section_compare( $sec ) {
	$p       = $sec['key'];
	$eyebrow = lahr_field( "{$p}_eyebrow" );
	$titulo  = lahr_field( "{$p}_titulo" );
	$lede    = lahr_field( "{$p}_lede" );

	$html  = '<section class="cn-compare"><div class="cn-compare__inner">';
	$html .= lahr_sechead_html( $eyebrow, $titulo, $lede );
	$html .= '<div class="cn-compare__grid">';
	$html .= lahr_compare_col( 'good', lahr_field( "{$p}_good_tag" ), lahr_field( "{$p}_good_nome" ), lahr_field( "{$p}_good_itens", array() ) );
	$html .= lahr_compare_col( 'bad', lahr_field( "{$p}_bad_tag" ), lahr_field( "{$p}_bad_nome" ), lahr_field( "{$p}_bad_itens", array() ) );
	$html .= '</div></div></section>';
	return $html;
}
