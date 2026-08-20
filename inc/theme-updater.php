<?php
/**
 * Auto-update do tema a partir dos Releases do GitHub (repo público).
 *
 * Fluxo para o cliente: quando um novo **Release** é publicado em
 * github.com/rafaelruch/drraphaellahr com uma tag de versão maior que a
 * instalada, o WordPress mostra "Atualização disponível" só para este tema —
 * e o botão **Atualizar** baixa o zip do release e reinstala o tema.
 *
 * Nada de token: o repositório é público. Consulta cacheada (6h) para não
 * bater na API a cada carregamento do admin.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'LAHR_UPDATE_SLUG', 'lahr-editorial' ); // pasta do tema
define( 'LAHR_UPDATE_REPO', 'rafaelruch/drraphaellahr' );

/**
 * Lê o último Release publicado no GitHub (cacheado).
 *
 * @return array { version, package, url, notes } (vazio se indisponível)
 */
function lahr_update_latest_release() {
	$cache = get_transient( 'lahr_update_release' );
	if ( false !== $cache ) {
		return $cache;
	}

	$resp = wp_remote_get(
		'https://api.github.com/repos/' . LAHR_UPDATE_REPO . '/releases/latest',
		array(
			'timeout' => 15,
			'headers' => array(
				'Accept'     => 'application/vnd.github+json',
				'User-Agent' => 'lahr-editorial-updater',
			),
		)
	);

	if ( is_wp_error( $resp ) || 200 !== (int) wp_remote_retrieve_response_code( $resp ) ) {
		set_transient( 'lahr_update_release', array(), 2 * HOUR_IN_SECONDS );
		return array();
	}

	$d   = json_decode( wp_remote_retrieve_body( $resp ), true );
	$tag = isset( $d['tag_name'] ) ? $d['tag_name'] : '';
	if ( '' === $tag ) {
		set_transient( 'lahr_update_release', array(), 2 * HOUR_IN_SECONDS );
		return array();
	}

	$info = array(
		'version' => ltrim( $tag, 'vV' ),
		'package' => 'https://github.com/' . LAHR_UPDATE_REPO . '/archive/refs/tags/' . rawurlencode( $tag ) . '.zip',
		'url'     => isset( $d['html_url'] ) ? $d['html_url'] : '',
		'notes'   => isset( $d['body'] ) ? $d['body'] : '',
	);
	set_transient( 'lahr_update_release', $info, 6 * HOUR_IN_SECONDS );
	return $info;
}

/**
 * Injeta a atualização no transient de temas do WordPress.
 */
add_filter(
	'pre_set_site_transient_update_themes',
	function ( $transient ) {
		if ( ! is_object( $transient ) ) {
			return $transient;
		}
		$theme     = wp_get_theme( LAHR_UPDATE_SLUG );
		$installed = $theme->exists() ? $theme->get( 'Version' ) : '0';
		$rel       = lahr_update_latest_release();

		if ( empty( $rel['version'] ) || empty( $rel['package'] ) ) {
			return $transient;
		}

		if ( version_compare( $rel['version'], $installed, '>' ) ) {
			$transient->response[ LAHR_UPDATE_SLUG ] = array(
				'theme'       => LAHR_UPDATE_SLUG,
				'new_version' => $rel['version'],
				'url'         => $rel['url'],
				'package'     => $rel['package'],
			);
		} else {
			// Marca como "sem atualização" (some da lista de updates).
			if ( isset( $transient->response[ LAHR_UPDATE_SLUG ] ) ) {
				unset( $transient->response[ LAHR_UPDATE_SLUG ] );
			}
			$transient->no_update[ LAHR_UPDATE_SLUG ] = array(
				'theme'       => LAHR_UPDATE_SLUG,
				'new_version' => $installed,
				'url'         => 'https://github.com/' . LAHR_UPDATE_REPO,
				'package'     => '',
			);
		}
		return $transient;
	}
);

/**
 * O zip do GitHub extrai para "drraphaellahr-<tag>/". Renomeia a pasta de
 * origem para o slug do tema antes do WordPress instalar.
 */
add_filter(
	'upgrader_source_selection',
	function ( $source, $remote_source, $upgrader, $args = array() ) {
		global $wp_filesystem;
		$is_our_theme = ( isset( $args['theme'] ) && LAHR_UPDATE_SLUG === $args['theme'] )
			|| ( false !== strpos( (string) $source, 'drraphaellahr-' ) );
		if ( ! $is_our_theme ) {
			return $source;
		}
		$desired = trailingslashit( $remote_source ) . LAHR_UPDATE_SLUG;
		if ( untrailingslashit( $source ) === untrailingslashit( $desired ) ) {
			return $source;
		}
		if ( $wp_filesystem && $wp_filesystem->move( $source, $desired, true ) ) {
			return trailingslashit( $desired );
		}
		return $source;
	},
	10,
	4
);

/**
 * "Verificar novamente" (force-check) busca o release na hora.
 */
add_action(
	'load-update-core.php',
	function () {
		if ( ! empty( $_GET['force-check'] ) ) {
			delete_transient( 'lahr_update_release' );
		}
	}
);

/** Limpa o cache após concluir uma atualização. */
add_action(
	'upgrader_process_complete',
	function () {
		delete_transient( 'lahr_update_release' );
	}
);
