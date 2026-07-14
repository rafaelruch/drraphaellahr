<?php
/**
 * Seção MARQUEE — faixa deslizante (duplicada para loop contínuo).
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function lahr_section_marquee( $sec ) {
	$p     = $sec['key'];
	$itens = (array) lahr_field( "{$p}_itens", array() );

	$item_html = '';
	foreach ( $itens as $it ) {
		$txt = isset( $it['texto'] ) ? $it['texto'] : '';
		$item_html .= '<span>' . esc_html( $txt ) . '</span><span class="cn-marquee__star">✦</span>';
	}

	$html  = '<div class="cn-marquee"><div class="cn-marquee__track">';
	$html .= '<div class="cn-marquee__item">' . $item_html . '</div>';
	$html .= '<div class="cn-marquee__item" aria-hidden="true">' . $item_html . '</div>';
	$html .= '</div></div>';
	return $html;
}
