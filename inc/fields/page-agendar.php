<?php
/**
 * Página AGENDAR CONSULTA (ID 13).
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

lahr_register_page(
	'agendar',
	array(
		array( 'type' => 'hero_page', 'key' => 'hero' ),
		array( 'type' => 'agendar_form', 'key' => 'form' ),
		array( 'type' => 'cards', 'key' => 'localizacao', 'bg_mid' => true, 'tilt' => false, 'top_gap' => true ),
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
		$f = array_merge( $f, lahr_fields_agendar_form( 'form', 'Formulário' ) );
		$f = array_merge( $f, lahr_fields_cards( 'localizacao', 'Localização' ) );

		acf_add_local_field_group(
			array(
				'key'             => 'group_lahr_page_agendar',
				'title'           => 'Conteúdo — Agendar Consulta',
				'fields'          => $f,
				'location'        => array( array( array( 'param' => 'post', 'operator' => '==', 'value' => 13 ) ) ),
				'menu_order'      => 0,
				'position'        => 'normal',
				'style'           => 'default',
				'label_placement' => 'top',
				'hide_on_screen'  => array( 'the_content' ),
			)
		);
	}
);
