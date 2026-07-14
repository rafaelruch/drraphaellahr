<?php
/**
 * Lahr — roteador de renderização.
 *
 * As páginas ficam com o corpo vazio no editor; este roteador injeta as seções
 * (renderizadas a partir de campos ACF) via filtro `the_content`. Funciona em
 * tema de blocos porque o bloco core/post-content passa por `the_content`.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Mapa slug => lista ordenada de seções. Preenchido por inc/fields/page-*.php. */
$GLOBALS['lahr_page_sections'] = array();

/**
 * Registra a composição de seções de uma página.
 *
 * @param string $slug     Slug da página (post_name).
 * @param array  $sections Lista de seções: cada item ['type'=>string, 'key'=>string].
 */
function lahr_register_page( $slug, array $sections ) {
	$GLOBALS['lahr_page_sections'][ $slug ] = $sections;
}

/**
 * Renderiza todas as seções registradas de uma página.
 *
 * Cada seção item: [ 'type' => 'split', 'key' => 'home_split_1', ... ].
 * Chama lahr_section_{type}( $section ) que deve retornar HTML.
 *
 * @param string $slug Slug da página.
 * @return string
 */
function lahr_render_sections( $slug ) {
	$out = '';
	$sections = isset( $GLOBALS['lahr_page_sections'][ $slug ] ) ? $GLOBALS['lahr_page_sections'][ $slug ] : array();
	foreach ( $sections as $index => $sec ) {
		$type = isset( $sec['type'] ) ? $sec['type'] : '';
		$fn   = 'lahr_section_' . str_replace( '-', '_', $type );
		if ( function_exists( $fn ) ) {
			$sec['_index'] = $index;
			$out .= $fn( $sec );
		}
	}
	return $out;
}

/**
 * Injeta as seções no lugar do conteúdo, apenas em páginas mapeadas.
 * Prioridade 9 para rodar antes de wpautop/shortcodes de terceiros.
 */
add_filter(
	'the_content',
	function ( $content ) {
		if ( ! is_page() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		$slug = get_post_field( 'post_name', get_the_ID() );
		if ( empty( $GLOBALS['lahr_page_sections'][ $slug ] ) ) {
			return $content;
		}
		return lahr_render_sections( $slug );
	},
	9
);

/* -------------------------------------------------------------------------
 * Autoload das seções e dos grupos de campos por página.
 * Arquivos adicionados em fases seguintes são carregados automaticamente.
 * ---------------------------------------------------------------------- */

// Schemas reutilizáveis primeiro (definem funções usadas pelas páginas).
$lahr_schema = get_theme_file_path( '/inc/fields/schema-parts.php' );
if ( file_exists( $lahr_schema ) ) {
	require_once $lahr_schema;
}

// Renderizadores de seção.
foreach ( glob( get_theme_file_path( '/inc/sections/*.php' ) ) as $lahr_section_file ) {
	require_once $lahr_section_file;
}

// Grupos de campos + composição por página.
foreach ( glob( get_theme_file_path( '/inc/fields/page-*.php' ) ) as $lahr_page_file ) {
	require_once $lahr_page_file;
}
