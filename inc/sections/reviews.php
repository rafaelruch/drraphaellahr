<?php
/**
 * Seção REVIEWS — carrossel de Google Reviews (shortcode existente).
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function lahr_section_reviews( $sec ) {
	return do_shortcode( '[lahr_reviews]' );
}
