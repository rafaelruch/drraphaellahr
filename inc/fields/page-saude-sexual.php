<?php
/**
 * Página SAÚDE SEXUAL MASCULINA (ID 10).
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

lahr_register_page(
	'saude-sexual-masculina',
	array(
		array( 'type' => 'hero_page', 'key' => 'hero' ),
		array( 'type' => 'quote', 'key' => 'quote1' ),
		array( 'type' => 'split', 'key' => 'split_de' ),
		array( 'type' => 'split', 'key' => 'split_trt', 'reverse' => true, 'bg_mid' => true ),
		array( 'type' => 'cards', 'key' => 'outros', 'tilt' => false, 'top_gap' => true ),
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
		$f = array_merge( $f, lahr_fields_split( 'split_de', 'Disfunção erétil' ) );
		$f = array_merge( $f, lahr_fields_split( 'split_trt', 'Reposição hormonal' ) );
		$f = array_merge( $f, lahr_fields_cards( 'outros', 'Outras frentes' ) );
		$f = array_merge( $f, lahr_fields_cta( 'cta', 'CTA final' ) );

		acf_add_local_field_group(
			array(
				'key'             => 'group_lahr_page_saude_sexual',
				'title'           => 'Conteúdo — Saúde Sexual Masculina',
				'fields'          => lahr_ns_keys( $f, 'saudesexual' ),
				'location'        => array( array( array( 'param' => 'post', 'operator' => '==', 'value' => 10 ) ) ),
				'menu_order'      => 0,
				'position'        => 'normal',
				'style'           => 'default',
				'label_placement' => 'top',
				'hide_on_screen'  => array( 'the_content' ),
			)
		);
	}
);
