<?php
/**
 * Página PLANEJAMENTO FAMILIAR (ID 11).
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

lahr_register_page(
	'planejamento-familiar',
	array(
		array( 'type' => 'hero_page', 'key' => 'hero' ),
		array( 'type' => 'split', 'key' => 'split_vas' ),
		array( 'type' => 'cards', 'key' => 'procedimentos', 'bg_mid' => true, 'tilt' => false, 'top_gap' => true ),
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
		$f = array_merge( $f, lahr_fields_split( 'split_vas', 'Vasectomia' ) );
		$f = array_merge( $f, lahr_fields_cards( 'procedimentos', 'Procedimentos' ) );
		$f = array_merge( $f, lahr_fields_cta( 'cta', 'CTA final' ) );

		acf_add_local_field_group(
			array(
				'key'             => 'group_lahr_page_planejamento',
				'title'           => 'Conteúdo — Planejamento Familiar',
				'fields'          => $f,
				'location'        => array( array( array( 'param' => 'post', 'operator' => '==', 'value' => 11 ) ) ),
				'menu_order'      => 0,
				'position'        => 'normal',
				'style'           => 'default',
				'label_placement' => 'top',
				'hide_on_screen'  => array( 'the_content' ),
			)
		);
	}
);
