<?php
/**
 * Página HARMONIZAÇÃO PENIANA (ID 6).
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

lahr_register_page(
	'harmonizacao-peniana',
	array(
		array( 'type' => 'hero_page', 'key' => 'hero' ),
		array( 'type' => 'quote', 'key' => 'quote1' ),
		array( 'type' => 'split', 'key' => 'split_oque' ),
		array( 'type' => 'steps', 'key' => 'steps' ),
		array( 'type' => 'compare', 'key' => 'compare' ),
		array( 'type' => 'split', 'key' => 'split_correcao', 'reverse' => true, 'bg_mid' => true, 'anchor' => 'correcao' ),
		array( 'type' => 'prose', 'key' => 'prose_tipos', 'narrow' => true ),
		array( 'type' => 'cards', 'key' => 'pmma', 'bg_mid' => true, 'tilt' => false, 'top_gap' => true, 'anchor' => 'pmma' ),
		array( 'type' => 'split', 'key' => 'split_duracao' ),
		array( 'type' => 'sharp_cta', 'key' => 'sharp', 'bg_mid' => true ),
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
		$f = array_merge( $f, lahr_fields_quote( 'quote1', 'Citação — diferencial' ) );
		$f = array_merge( $f, lahr_fields_split( 'split_oque', 'O que é' ) );
		$f = array_merge( $f, lahr_fields_steps( 'steps', 'Etapas do procedimento' ) );
		$f = array_merge( $f, lahr_fields_compare( 'compare', 'Ácido Hialurônico vs PMMA' ) );
		$f = array_merge( $f, lahr_fields_split( 'split_correcao', 'Correção de harmonização' ) );
		$f = array_merge( $f, lahr_fields_prose( 'prose_tipos', 'Tipos de correção' ) );
		$f = array_merge( $f, lahr_fields_cards( 'pmma', 'Remoção de PMMA' ) );
		$f = array_merge( $f, lahr_fields_split( 'split_duracao', 'Duração e manutenção' ) );
		$f = array_merge( $f, lahr_fields_sharp_cta( 'sharp', 'Investimento' ) );
		$f = array_merge( $f, lahr_fields_faq( 'faq', 'FAQ' ) );
		$f = array_merge( $f, lahr_fields_cta( 'cta', 'CTA final' ) );

		acf_add_local_field_group(
			array(
				'key'             => 'group_lahr_page_harmonizacao',
				'title'           => 'Conteúdo — Harmonização Peniana',
				'fields'          => lahr_ns_keys( $f, 'harmonizacao' ),
				'location'        => array( array( array( 'param' => 'post', 'operator' => '==', 'value' => 6 ) ) ),
				'menu_order'      => 0,
				'position'        => 'normal',
				'style'           => 'default',
				'label_placement' => 'top',
				'hide_on_screen'  => array( 'the_content' ),
			)
		);
	}
);
