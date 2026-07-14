<?php
/**
 * Páginas legais — POLÍTICA DE PRIVACIDADE (ID 15) e TERMOS DE USO (ID 16).
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

lahr_register_page(
	'politica-de-privacidade',
	array(
		array( 'type' => 'hero_page', 'key' => 'hero', 'layout' => 'single' ),
		array( 'type' => 'prose', 'key' => 'conteudo', 'narrow' => true ),
	)
);

lahr_register_page(
	'termos-de-uso',
	array(
		array( 'type' => 'hero_page', 'key' => 'hero', 'layout' => 'single' ),
		array( 'type' => 'prose', 'key' => 'conteudo', 'narrow' => true ),
	)
);

add_action(
	'acf/init',
	function () {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		$build = function () {
			$f = array();
			$f = array_merge( $f, lahr_fields_hero_page( 'hero', 'Hero da página' ) );
			$f = array_merge( $f, lahr_fields_prose( 'conteudo', 'Conteúdo' ) );
			return $f;
		};

		acf_add_local_field_group(
			array(
				'key'             => 'group_lahr_page_privacidade',
				'title'           => 'Conteúdo — Política de Privacidade',
				'fields'          => $build(),
				'location'        => array( array( array( 'param' => 'post', 'operator' => '==', 'value' => 15 ) ) ),
				'position'        => 'normal',
				'style'           => 'default',
				'label_placement' => 'top',
				'hide_on_screen'  => array( 'the_content' ),
			)
		);

		acf_add_local_field_group(
			array(
				'key'             => 'group_lahr_page_termos',
				'title'           => 'Conteúdo — Termos de Uso',
				'fields'          => $build(),
				'location'        => array( array( array( 'param' => 'post', 'operator' => '==', 'value' => 16 ) ) ),
				'position'        => 'normal',
				'style'           => 'default',
				'label_placement' => 'top',
				'hide_on_screen'  => array( 'the_content' ),
			)
		);
	}
);
