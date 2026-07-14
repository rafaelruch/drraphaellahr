<?php
/**
 * Página HOME (ID 5) — composição de seções + grupo de campos ACF.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Ordem das seções da Home.
lahr_register_page(
	'home',
	array(
		array( 'type' => 'hero_slider', 'key' => 'hero' ),
		array( 'type' => 'marquee', 'key' => 'marquee' ),
		array( 'type' => 'cards', 'key' => 'pilares', 'bg_mid' => true, 'tilt' => true ),
		array( 'type' => 'about', 'key' => 'about' ),
		array( 'type' => 'judo', 'key' => 'judo' ),
		array( 'type' => 'services', 'key' => 'services' ),
		array( 'type' => 'cards', 'key' => 'protocolo', 'tilt' => true ),
		array( 'type' => 'stats', 'key' => 'stats', 'bg_mid' => true ),
		array( 'type' => 'reviews', 'key' => 'reviews' ),
		array( 'type' => 'cta', 'key' => 'cta' ),
	)
);

add_action(
	'acf/init',
	function () {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}
		$fields = array();
		$fields = array_merge( $fields, lahr_fields_hero_slider( 'hero', 'Hero (slider)' ) );
		$fields = array_merge( $fields, lahr_fields_marquee( 'marquee', 'Faixa deslizante' ) );
		$fields = array_merge( $fields, lahr_fields_cards( 'pilares', 'Pilares (diferencial)' ) );
		$fields = array_merge( $fields, lahr_fields_about( 'about', 'Sobre o médico' ) );
		$fields = array_merge( $fields, lahr_fields_judo( 'judo', 'Judô / Filosofia' ) );
		$fields = array_merge( $fields, lahr_fields_services( 'services', 'Procedimentos' ) );
		$fields = array_merge( $fields, lahr_fields_cards( 'protocolo', 'Protocolo Lahr' ) );
		$fields = array_merge( $fields, lahr_fields_stats( 'stats', 'Números' ) );
		$fields = array_merge( $fields, lahr_fields_cta( 'cta', 'CTA final' ) );

		acf_add_local_field_group(
			array(
				'key'            => 'group_lahr_page_home',
				'title'          => 'Conteúdo da Home',
				'fields'         => lahr_ns_keys( $fields, 'home' ),
				'location'       => array(
					array(
						array(
							'param'    => 'post',
							'operator' => '==',
							'value'    => 5,
						),
					),
				),
				'menu_order'     => 0,
				'position'       => 'normal',
				'style'          => 'default',
				'label_placement' => 'top',
				'hide_on_screen' => array( 'the_content' ),
			)
		);
	}
);
