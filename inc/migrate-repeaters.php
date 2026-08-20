<?php
/**
 * Migração automática (uma vez) — repetidores do formato antigo → formato ACF Pro.
 *
 * Durante o período em que o site rodou com o ACF gratuito (sem o tipo
 * `repeater`), os repetidores foram gravados como um único array serializado.
 * Com o ACF Pro, o formato esperado é outro (contador + sub-metas por linha).
 * Sem converter, o Pro lê "1 linha" e o front-end/admin quebram.
 *
 * Esta rotina reconverte os dados (relê o array e re-salva via update_field, que
 * agora grava no formato do Pro). Idempotente e travada por opção — roda só uma
 * vez, e só quando o ACF Pro estiver ativo.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Converte todos os repetidores que ainda estão no formato antigo.
 *
 * @return int Quantidade de repetidores convertidos.
 */
function lahr_convert_old_repeaters() {
	if ( ! function_exists( 'acf_get_field_groups' ) || ! function_exists( 'update_field' ) ) {
		return 0;
	}
	$converted = 0;
	$posts = get_posts(
		array(
			'post_type'        => array( 'page', 'lahr_config', 'lahr_banner' ),
			'post_status'      => 'any',
			'numberposts'      => 200,
			'fields'           => 'ids',
			'suppress_filters' => true,
		)
	);
	foreach ( $posts as $pid ) {
		foreach ( (array) acf_get_field_groups( array( 'post_id' => $pid ) ) as $g ) {
			foreach ( (array) acf_get_fields( $g['key'] ) as $f ) {
				if ( ( $f['type'] ?? '' ) !== 'repeater' ) {
					continue;
				}
				$raw = get_post_meta( $pid, $f['name'], true );
				if ( is_array( $raw ) && $raw ) { // formato antigo (array serializado num único meta)
					update_field( $f['key'], $raw, $pid );
					$converted++;
				}
			}
		}
	}
	return $converted;
}

/**
 * Executa uma vez, no admin, quando o ACF Pro estiver ativo.
 */
add_action(
	'admin_init',
	function () {
		if ( get_option( 'lahr_repeaters_converted' ) ) {
			return;
		}
		if ( ! defined( 'ACF_PRO' ) || ! ACF_PRO ) {
			return; // só quando o ACF Pro estiver ativo
		}
		$n = lahr_convert_old_repeaters();
		update_option( 'lahr_repeaters_converted', 1, false );
		if ( $n ) {
			error_log( 'LAHR: ' . $n . ' repetidores convertidos para o formato ACF Pro.' );
		}
	}
);
