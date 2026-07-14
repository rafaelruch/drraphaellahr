<?php
/**
 * Página PRÓTESE PENIANA (ID 7).
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

lahr_register_page(
	'protese-peniana',
	array(
		array( 'type' => 'hero_page', 'key' => 'hero' ),
		array( 'type' => 'quote', 'key' => 'quote1' ),
		array( 'type' => 'split', 'key' => 'split_indicacao' ),
		array( 'type' => 'compare', 'key' => 'tipos', 'wrap' => 'section', 'bg_mid' => true, 'grid_style' => 'max-width: 1000px; margin: var(--s-5) auto 0;' ),
		array( 'type' => 'steps', 'key' => 'steps', 'cols' => 4 ),
		array( 'type' => 'cards', 'key' => 'resultados', 'tilt' => false, 'top_gap' => true ),
		array( 'type' => 'split', 'key' => 'split_dif', 'reverse' => true, 'bg_mid' => true ),
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
		$f = array_merge( $f, lahr_fields_split( 'split_indicacao', 'Quando é indicada' ) );
		$f = array_merge( $f, lahr_fields_compare( 'tipos', 'Tipos de prótese' ) );
		$f = array_merge( $f, lahr_fields_steps( 'steps', 'Como é a cirurgia' ) );
		$f = array_merge( $f, lahr_fields_cards( 'resultados', 'Resultados' ) );
		$f = array_merge( $f, lahr_fields_split( 'split_dif', 'Diferencial — acompanhamento' ) );
		$f = array_merge( $f, lahr_fields_faq( 'faq', 'FAQ' ) );
		$f = array_merge( $f, lahr_fields_cta( 'cta', 'CTA final' ) );

		acf_add_local_field_group(
			array(
				'key'             => 'group_lahr_page_protese',
				'title'           => 'Conteúdo — Prótese Peniana',
				'fields'          => $f,
				'location'        => array( array( array( 'param' => 'post', 'operator' => '==', 'value' => 7 ) ) ),
				'menu_order'      => 0,
				'position'        => 'normal',
				'style'           => 'default',
				'label_placement' => 'top',
				'hide_on_screen'  => array( 'the_content' ),
			)
		);
	}
);
