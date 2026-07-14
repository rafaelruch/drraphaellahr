<?php
/**
 * Seção QUOTE — citação de destaque (cn-quote).
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function lahr_section_quote( $sec ) {
	$p     = $sec['key'];
	$texto = lahr_field( "{$p}_texto" );
	$cite  = lahr_field( "{$p}_cite" );

	$html  = '<section class="cn-quote"><div class="cn-quote__inner">';
	$html .= '<span class="cn-quote__mark">"</span>';
	$html .= '<p class="cn-quote__text">' . lahr_rich( $texto ) . '</p>';
	if ( '' !== $cite ) {
		$html .= '<span class="cn-quote__cite">' . esc_html( $cite ) . '</span>';
	}
	$html .= '</div></section>';
	return $html;
}
