<?php
/**
 * Seção COMPARE — colunas comparativas flexíveis.
 * Flags do descritor:
 *   wrap    'compare' (default, wrapper cn-compare) | 'section' (cn-section + container)
 *   bg_mid, narrow (quando wrap='section')
 *   grid_style  string de estilo inline opcional no grid (ex.: max-width)
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function lahr_section_compare( $sec ) {
	$p       = $sec['key'];
	$eyebrow = lahr_field( "{$p}_eyebrow" );
	$titulo  = lahr_field( "{$p}_titulo" );
	$lede    = lahr_field( "{$p}_lede" );
	$cols    = (array) lahr_field( "{$p}_cols", array() );
	$wrap    = isset( $sec['wrap'] ) ? $sec['wrap'] : 'compare';

	if ( 'section' === $wrap ) {
		$html  = lahr_section_open( $sec, 'cn-section' );
		$html .= '<div class="cn-container">';
	} else {
		$html  = '<section class="cn-compare"><div class="cn-compare__inner">';
	}
	$html .= lahr_sechead_html( $eyebrow, $titulo, $lede );

	$gstyle = ! empty( $sec['grid_style'] ) ? ' style="' . esc_attr( $sec['grid_style'] ) . '"' : '';
	$html  .= '<div class="cn-compare__grid"' . $gstyle . '>';
	foreach ( $cols as $col ) {
		$hl    = isset( $col['destaque'] ) ? $col['destaque'] : 'none';
		$tag   = isset( $col['tag'] ) ? $col['tag'] : '';
		$nome  = isset( $col['nome'] ) ? $col['nome'] : '';
		$desc  = isset( $col['descricao'] ) ? $col['descricao'] : '';
		$itens = isset( $col['itens'] ) ? (array) $col['itens'] : array();

		$colcls = 'cn-compare__col';
		if ( 'good' === $hl ) {
			$colcls .= ' cn-compare__col--good';
		} elseif ( 'bad' === $hl ) {
			$colcls .= ' cn-compare__col--bad';
		}
		$html .= '<article class="' . esc_attr( $colcls ) . '">';
		if ( '' !== $tag ) {
			$html .= '<span class="cn-compare__tag">' . esc_html( $tag ) . '</span>';
		}
		$html .= '<h3 class="cn-compare__name">' . lahr_rich( $nome ) . '</h3>';
		if ( '' !== $desc ) {
			$html .= '<p class="cn-compare__desc">' . lahr_rich( $desc ) . '</p>';
		}
		$html .= '<ul class="cn-compare__list">';
		foreach ( $itens as $it ) {
			$txt = isset( $it['texto'] ) ? $it['texto'] : '';
			$html .= '<li>' . lahr_rich( $txt ) . '</li>';
		}
		$html .= '</ul></article>';
	}
	$html .= '</div>';

	if ( 'section' === $wrap ) {
		$html .= '</div></section>';
	} else {
		$html .= '</div></section>';
	}
	return $html;
}
