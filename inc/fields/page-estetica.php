<?php
/**
 * Página ESTÉTICA MASCULINA (ID 9).
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

lahr_register_page(
	'estetica-masculina',
	array(
		array( 'type' => 'hero_page', 'key' => 'hero' ),
		array( 'type' => 'split', 'key' => 'split_post' ),
		array( 'type' => 'split', 'key' => 'split_peyronie', 'reverse' => true, 'bg_mid' => true ),
		array( 'type' => 'sharp_cta', 'key' => 'cross' ),
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
		$f = array_merge( $f, lahr_fields_split( 'split_post', 'Postectomia' ) );
		$f = array_merge( $f, lahr_fields_split( 'split_peyronie', 'Doença de Peyronie' ) );
		$f = array_merge( $f, lahr_fields_sharp_cta( 'cross', 'CTA harmonização (cross-sell)' ) );
		$f = array_merge( $f, lahr_fields_cta( 'cta', 'CTA final' ) );

		acf_add_local_field_group(
			array(
				'key'             => 'group_lahr_page_estetica',
				'title'           => 'Conteúdo — Estética Masculina',
				'fields'          => lahr_ns_keys( $f, 'estetica' ),
				'location'        => array( array( array( 'param' => 'post', 'operator' => '==', 'value' => 9 ) ) ),
				'menu_order'      => 0,
				'position'        => 'normal',
				'style'           => 'default',
				'label_placement' => 'top',
				'hide_on_screen'  => array( 'the_content' ),
			)
		);
	}
);
