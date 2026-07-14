<?php
/**
 * Página SOBRE O DR. RAPHAEL (ID 12).
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

lahr_register_page(
	'sobre',
	array(
		array( 'type' => 'hero_page', 'key' => 'hero' ),
		array( 'type' => 'split', 'key' => 'split_apres', 'reverse' => true ),
		array( 'type' => 'cards', 'key' => 'credenciais', 'bg_mid' => true, 'tilt' => false ),
		array( 'type' => 'section', 'key' => 'areas', 'narrow' => true ),
		array( 'type' => 'quote', 'key' => 'quote1' ),
		array( 'type' => 'judo_about', 'key' => 'judo', 'anchor' => 'judo' ),
		array( 'type' => 'gallery', 'key' => 'tatame' ),
		array( 'type' => 'cards', 'key' => 'locais', 'bg_mid' => true, 'tilt' => false, 'top_gap' => true ),
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
		$f = array_merge( $f, lahr_fields_split( 'split_apres', 'Apresentação' ) );
		$f = array_merge( $f, lahr_fields_cards( 'credenciais', 'Credenciais' ) );
		$f = array_merge( $f, lahr_fields_section( 'areas', 'Áreas de atuação' ) );
		$f = array_merge( $f, lahr_fields_quote( 'quote1', 'Citação — método' ) );
		$f = array_merge( $f, lahr_fields_judo_about( 'judo', 'Judô / Filosofia' ) );
		$f = array_merge( $f, lahr_fields_gallery( 'tatame', 'Galeria — tatame' ) );
		$f = array_merge( $f, lahr_fields_cards( 'locais', 'Onde atende' ) );
		$f = array_merge( $f, lahr_fields_cta( 'cta', 'CTA final' ) );

		acf_add_local_field_group(
			array(
				'key'             => 'group_lahr_page_sobre',
				'title'           => 'Conteúdo — Sobre o Dr. Raphael',
				'fields'          => $f,
				'location'        => array( array( array( 'param' => 'post', 'operator' => '==', 'value' => 12 ) ) ),
				'menu_order'      => 0,
				'position'        => 'normal',
				'style'           => 'default',
				'label_placement' => 'top',
				'hide_on_screen'  => array( 'the_content' ),
			)
		);
	}
);
