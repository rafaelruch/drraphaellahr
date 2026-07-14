<?php
/**
 * Página CIRURGIA ROBÓTICA (ID 8).
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

lahr_register_page(
	'cirurgia-robotica',
	array(
		array( 'type' => 'hero_page', 'key' => 'hero' ),
		array( 'type' => 'quote', 'key' => 'quote1' ),
		array( 'type' => 'split', 'key' => 'split_oque' ),
		array( 'type' => 'cards', 'key' => 'vantagens', 'bg_mid' => true, 'tilt' => false, 'top_gap' => true ),
		array( 'type' => 'cards', 'key' => 'procedimentos', 'tilt' => false, 'top_gap' => true ),
		array( 'type' => 'split', 'key' => 'split_prostata', 'reverse' => true, 'bg_mid' => true ),
		array( 'type' => 'section', 'key' => 'localizacao', 'narrow' => true ),
		array( 'type' => 'faq', 'key' => 'faq' ),
		array( 'type' => 'cta', 'key' => 'cta' ),
	)
);

add_action(
	'acf/init',
	function () {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}
		$f = array();
		$f = array_merge( $f, lahr_fields_hero_page( 'hero', 'Hero da página' ) );
		$f = array_merge( $f, lahr_fields_quote( 'quote1', 'Citação' ) );
		$f = array_merge( $f, lahr_fields_split( 'split_oque', 'O que é' ) );
		$f = array_merge( $f, lahr_fields_cards( 'vantagens', 'Vantagens' ) );
		$f = array_merge( $f, lahr_fields_cards( 'procedimentos', 'Procedimentos' ) );
		$f = array_merge( $f, lahr_fields_split( 'split_prostata', 'Câncer de próstata' ) );
		$f = array_merge( $f, lahr_fields_section( 'localizacao', 'Localização / logística' ) );
		$f = array_merge( $f, lahr_fields_faq( 'faq', 'FAQ' ) );
		$f = array_merge( $f, lahr_fields_cta( 'cta', 'CTA final' ) );

		acf_add_local_field_group(
			array(
				'key'             => 'group_lahr_page_cirurgia',
				'title'           => 'Conteúdo — Cirurgia Robótica',
				'fields'          => lahr_ns_keys( $f, 'cirurgia' ),
				'location'        => array( array( array( 'param' => 'post', 'operator' => '==', 'value' => 8 ) ) ),
				'menu_order'      => 0,
				'position'        => 'normal',
				'style'           => 'default',
				'label_placement' => 'top',
				'hide_on_screen'  => array( 'the_content' ),
			)
		);
	}
);
