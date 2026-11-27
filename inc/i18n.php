<?php
/**
 * Lahr — Idiomas: Português (padrão) + Inglês, sem plugin.
 *
 * COMO FUNCIONA
 * - Uma página = os dois idiomas. Nada é duplicado: o texto em inglês de cada
 *   campo ACF é gravado ao lado do português, no meta `_lahr_en_{campo}`.
 * - Só TEXTO é traduzido (campos text/textarea/wysiwyg). Imagens, vídeos,
 *   números, contatos, IDs de pixel e a QUANTIDADE/ORDEM de itens dos
 *   repetidores são compartilhados — editados em Português.
 * - Leitura em inglês: valor editado em inglês → dicionário do tema
 *   (data/i18n-en.php, tradução inicial) → português (nunca fica vazio).
 * - URLs: /en/ + slug em inglês (ex.: /en/penile-enhancement/). Slug e título
 *   em inglês são editáveis na caixa "Idioma" de cada página.
 * - Admin: caixa "Idioma" no editor alterna "Editando: Português | English".
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

const LAHR_I18N_VERSION = '1.0.0';
const LAHR_EN_PREFIX    = '_lahr_en_';

/* =====================================================================
 * Idioma atual
 * ================================================================== */

/** Idiomas suportados. */
function lahr_langs() {
	return array(
		'pt' => array( 'label' => 'PT', 'name' => 'Português', 'locale' => 'pt-BR' ),
		'en' => array( 'label' => 'EN', 'name' => 'English', 'locale' => 'en' ),
	);
}

/** Caminho da requisição relativo à home, sem barras nas pontas. */
function lahr_i18n_request_path() {
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore
	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	$home = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	if ( '' !== $home && '/' !== $home && 0 === strpos( $path, $home ) ) {
		$path = substr( $path, strlen( $home ) );
	}
	return trim( $path, '/' );
}

/**
 * Idioma da requisição: 'pt' | 'en'.
 * - Site: URL começando em /en/.
 * - Admin/AJAX: parâmetro `lahr_lang` (editor em inglês, formulário em inglês).
 */
function lahr_lang() {
	if ( ! empty( $GLOBALS['lahr_lang_stack'] ) ) {
		return end( $GLOBALS['lahr_lang_stack'] );
	}
	static $lang = null;
	if ( null === $lang ) {
		if ( is_admin() ) {
			$req  = isset( $_REQUEST['lahr_lang'] ) ? sanitize_key( wp_unslash( $_REQUEST['lahr_lang'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$lang = 'en' === $req ? 'en' : 'pt';
		} else {
			$lang = preg_match( '#^en(/|$)#', lahr_i18n_request_path() ) ? 'en' : 'pt';
		}
	}
	return $lang;
}

/** Executa leituras num idioma específico (pilha). */
function lahr_lang_push( $lang ) {
	$GLOBALS['lahr_lang_stack'][] = ( 'en' === $lang ? 'en' : 'pt' );
}
function lahr_lang_pop() {
	if ( ! empty( $GLOBALS['lahr_lang_stack'] ) ) {
		array_pop( $GLOBALS['lahr_lang_stack'] );
	}
}

/* =====================================================================
 * Dicionário (tradução inicial + textos fixos do tema)
 * ================================================================== */

function lahr_i18n_norm( $s ) {
	return trim( str_replace( array( "\r\n", "\r" ), "\n", (string) $s ) );
}

/**
 * Chave de busca no dicionário: ignora diferenças de formatação que o editor
 * do WordPress introduz (parágrafos <p>/<br>, espaços e quebras de linha).
 */
function lahr_i18n_key( $s ) {
	$s = preg_replace( '#</?p\b[^>]*>|<br\s*/?>#i', ' ', (string) $s );
	$s = preg_replace( '/\s+/u', ' ', $s );
	$s = preg_replace( '/\s*(<[^>]+>)\s*/', '$1', $s );
	return trim( $s );
}

/** Mapa PT → EN (arquivo PHP: carregado uma vez, cacheado pelo OPcache). */
function lahr_i18n_dict() {
	static $dict = null;
	if ( null === $dict ) {
		$file = get_theme_file_path( '/data/i18n-en.php' );
		$dict = file_exists( $file ) ? (array) include $file : array();
	}
	return $dict;
}

/** Tradução do dicionário para um texto em português (null se não houver). */
function lahr_i18n_lookup( $pt ) {
	$dict = lahr_i18n_dict();
	$key  = lahr_i18n_key( $pt );
	return isset( $dict[ $key ] ) ? $dict[ $key ] : null;
}

/** Texto fixo do tema: devolve a versão em inglês quando o idioma é EN. */
function lahr__( $pt ) {
	if ( 'en' !== lahr_lang() ) {
		return $pt;
	}
	$en = lahr_i18n_lookup( $pt );
	return null === $en ? $pt : $en;
}

/* =====================================================================
 * Campos traduzíveis + armazenamento do inglês
 * ================================================================== */

/** Campos de texto que NÃO mudam com o idioma (contato, integrações). */
function lahr_i18n_shared_names() {
	return array(
		'telefone', 'whatsapp_num', 'instagram_user', 'instagram_url', 'lead_email', 'google_perfil_url',
		'crm_rqe', 'wa_base', 'wa_group', 'wa_token', 'meta_pixel_id', 'ga4_id', 'wa_nome',
	);
}

/** Tipos de post com conteúdo bilíngue. */
function lahr_i18n_post_types() {
	return array( 'page', 'lahr_config', 'lahr_banner' );
}

function lahr_i18n_post_in_scope( $post_id ) {
	if ( ! is_numeric( $post_id ) || (int) $post_id <= 0 ) {
		return false;
	}
	return in_array( get_post_type( (int) $post_id ), lahr_i18n_post_types(), true );
}

function lahr_i18n_is_translatable( $field ) {
	// Tipo vazio = campo lido por nome cuja referência no banco é antiga (chave
	// renomeada); trata como texto — valores não textuais não têm tradução e
	// caem no português de qualquer forma.
	if ( ! empty( $field['type'] ) && ! in_array( $field['type'], array( 'text', 'textarea', 'wysiwyg' ), true ) ) {
		return false;
	}
	$base = ! empty( $field['_name'] ) ? $field['_name'] : ( $field['name'] ?? '' );
	return ! in_array( $base, lahr_i18n_shared_names(), true );
}

/**
 * Valor em inglês de um meta (nome completo, ex.: "home_faq_itens_2_pergunta").
 * Editado em inglês → dicionário → null (o chamador usa o português).
 */
function lahr_i18n_en_value( $post_id, $name ) {
	$en = get_post_meta( (int) $post_id, LAHR_EN_PREFIX . $name, false );
	if ( $en ) {
		return $en[0];
	}
	$pt = get_post_meta( (int) $post_id, $name, true );
	if ( ! is_string( $pt ) || '' === trim( $pt ) ) {
		return null;
	}
	return lahr_i18n_lookup( $pt );
}

/** Leitura direta de meta (fora do ACF), respeitando o idioma. */
function lahr_meta( $post_id, $name ) {
	if ( 'en' === lahr_lang() ) {
		$v = lahr_i18n_en_value( $post_id, $name );
		if ( null !== $v ) {
			return $v;
		}
	}
	return get_post_meta( (int) $post_id, $name, true );
}

/** ACF — leitura: em inglês, campos de texto vêm da versão em inglês. */
add_filter(
	'acf/pre_load_value',
	function ( $null, $post_id, $field ) {
		if ( null !== $null || 'en' !== lahr_lang() || ! lahr_i18n_is_translatable( $field ) || ! lahr_i18n_post_in_scope( $post_id ) ) {
			return $null;
		}
		$v = lahr_i18n_en_value( $post_id, $field['name'] );
		return null === $v ? $null : $v;
	},
	10,
	3
);

/** ACF — gravação no modo inglês: texto vai para `_lahr_en_*`; o resto segue normal. */
add_filter(
	'acf/pre_update_value',
	function ( $check, $value, $post_id, $field ) {
		if ( null !== $check || ! is_admin() || 'en' !== lahr_lang() || ! lahr_i18n_is_translatable( $field ) || ! lahr_i18n_post_in_scope( $post_id ) ) {
			return $check;
		}
		$post_id = (int) $post_id;
		$name    = $field['name'];
		$key     = LAHR_EN_PREFIX . $name;
		$value   = apply_filters( 'acf/update_value', $value, $post_id, $field, $value );
		$value   = null === $value ? '' : $value;

		// Campo nunca editado em inglês e enviado sem alteração → mantém "automático"
		// (segue o dicionário/português e recebe melhorias futuras da tradução).
		if ( ! metadata_exists( 'post', $post_id, $key ) ) {
			$pt      = get_post_meta( $post_id, $name, true );
			$auto    = is_string( $pt ) ? lahr_i18n_lookup( $pt ) : null;
			$shown   = null === $auto ? $pt : $auto;
			if ( lahr_i18n_key( wp_unslash( $value ) ) === lahr_i18n_key( $shown ) ) {
				return true;
			}
		}
		update_metadata( 'post', $post_id, $key, $value );
		return true;
	},
	10,
	4
);

/**
 * Repetidores editados em PORTUGUÊS: quando linhas são reordenadas ou
 * removidas, o inglês acompanha (move/apaga `_lahr_en_{rep}_{i}_*`).
 */
add_filter(
	'acf/update_value/type=repeater',
	function ( $value, $post_id, $field ) {
		if ( 'en' === lahr_lang() || ! is_array( $value ) || ! lahr_i18n_post_in_scope( $post_id ) ) {
			return $value;
		}
		$post_id = (int) $post_id;
		$base    = LAHR_EN_PREFIX . $field['name'] . '_';
		$all     = get_post_meta( $post_id );
		$rows    = array(); // índice antigo => [ sufixo => valor ]
		foreach ( $all as $k => $vals ) {
			if ( 0 === strpos( $k, $base ) && preg_match( '/^(\d+)_(.+)$/', substr( $k, strlen( $base ) ), $m ) ) {
				$rows[ (int) $m[1] ][ $m[2] ] = maybe_unserialize( $vals[0] );
			}
		}
		if ( ! $rows ) {
			return $value;
		}
		$map = array(); // antigo => novo
		$new = 0;
		foreach ( array_keys( $value ) as $row_key ) {
			if ( preg_match( '/^row-(\d+)$/', (string) $row_key, $m ) ) {
				$map[ (int) $m[1] ] = $new;
			}
			$new++;
		}
		$identity = true;
		foreach ( $rows as $old => $_ ) {
			if ( ! isset( $map[ $old ] ) || $map[ $old ] !== $old ) {
				$identity = false;
				break;
			}
		}
		if ( $identity ) {
			return $value;
		}
		foreach ( $rows as $old => $subs ) {
			foreach ( $subs as $suffix => $_ ) {
				delete_post_meta( $post_id, $base . $old . '_' . $suffix );
			}
		}
		foreach ( $rows as $old => $subs ) {
			if ( ! isset( $map[ $old ] ) ) {
				continue; // linha removida
			}
			foreach ( $subs as $suffix => $v ) {
				update_post_meta( $post_id, $base . $map[ $old ] . '_' . $suffix, wp_slash( $v ) );
			}
		}
		return $value;
	},
	5,
	3
);

/* =====================================================================
 * URLs em inglês
 * ================================================================== */

/** Slugs em inglês padrão (editáveis por página na caixa "Idioma"). */
function lahr_i18n_default_slugs() {
	return array(
		'harmonizacao-peniana'    => 'penile-enhancement',
		'protese-peniana'         => 'penile-prosthesis',
		'cirurgia-robotica'       => 'robotic-surgery',
		'estetica-masculina'      => 'male-aesthetics',
		'saude-sexual-masculina'  => 'mens-sexual-health',
		'planejamento-familiar'   => 'family-planning',
		'sobre'                   => 'about',
		'agendar'                 => 'book-a-consultation',
		'obrigado'                => 'thank-you',
		'politica-de-privacidade' => 'privacy-policy',
		'termos-de-uso'           => 'terms-of-use',
	);
}

/** Slug em inglês de uma página. */
function lahr_i18n_en_slug( $post_id ) {
	$slug = (string) get_post_meta( $post_id, '_lahr_i18n_slug_en', true );
	if ( '' === $slug ) {
		$pt   = get_post_field( 'post_name', $post_id );
		$map  = lahr_i18n_default_slugs();
		$slug = isset( $map[ $pt ] ) ? $map[ $pt ] : $pt;
	}
	return sanitize_title( $slug );
}

/** Título em inglês de uma página (caixa "Idioma" → dicionário → português). */
function lahr_i18n_en_title( $post_id ) {
	$t = trim( (string) get_post_meta( $post_id, '_lahr_i18n_title_en', true ) );
	if ( '' !== $t ) {
		return $t;
	}
	$pt = get_post_field( 'post_title', $post_id );
	$en = lahr_i18n_lookup( $pt );
	return null === $en ? $pt : $en;
}

/** URL de uma página num idioma. */
function lahr_i18n_url( $post_id, $lang ) {
	$post_id = (int) $post_id;
	if ( 'en' !== $lang ) {
		lahr_lang_push( 'pt' );
		$url = get_permalink( $post_id );
		lahr_lang_pop();
		return $url;
	}
	if ( (int) get_option( 'page_on_front' ) === $post_id ) {
		return home_url( '/en/' );
	}
	return home_url( '/en/' . lahr_i18n_en_slug( $post_id ) . '/' );
}

/**
 * Mapas das páginas publicadas: 'en' => [ slug_en => ID ], 'pt' => [ caminho_pt => ID ].
 * Cacheado (transient) e invalidado ao salvar páginas.
 */
function lahr_i18n_maps() {
	static $maps = null;
	if ( null !== $maps ) {
		return $maps;
	}
	$maps = get_transient( 'lahr_i18n_maps' );
	if ( is_array( $maps ) && isset( $maps['v'] ) && LAHR_I18N_VERSION === $maps['v'] ) {
		return $maps;
	}
	$maps  = array( 'v' => LAHR_I18N_VERSION, 'en' => array(), 'pt' => array() );
	$pages = get_posts(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'numberposts' => 200,
			'fields'      => 'ids',
		)
	);
	$front = (int) get_option( 'page_on_front' );
	foreach ( $pages as $id ) {
		$maps['pt'][ trim( (string) get_page_uri( $id ), '/' ) ] = $id;
		if ( $id !== $front ) {
			$maps['en'][ lahr_i18n_en_slug( $id ) ] = $id;
		}
	}
	set_transient( 'lahr_i18n_maps', $maps, DAY_IN_SECONDS );
	return $maps;
}
add_action(
	'save_post_page',
	function () {
		delete_transient( 'lahr_i18n_maps' );
	}
);

/** Rotas /en/ e /en/{slug}/. */
add_action(
	'init',
	function () {
		add_rewrite_tag( '%lahr_en%', '([^&]+)' );
		add_rewrite_rule( '^en/?$', 'index.php?lahr_en=__home__', 'top' );
		add_rewrite_rule( '^en/(.+?)/?$', 'index.php?lahr_en=$matches[1]', 'top' );
		if ( get_option( 'lahr_i18n_rules' ) !== LAHR_I18N_VERSION ) {
			flush_rewrite_rules( false );
			update_option( 'lahr_i18n_rules', LAHR_I18N_VERSION, true );
		}
	},
	20
);

add_filter(
	'request',
	function ( $vars ) {
		if ( ! isset( $vars['lahr_en'] ) ) {
			return $vars;
		}
		$path = trim( (string) $vars['lahr_en'], '/' );
		if ( '__home__' === $path ) {
			$front = (int) get_option( 'page_on_front' );
			return $front ? array( 'page_id' => $front ) : array();
		}
		$maps = lahr_i18n_maps();
		if ( isset( $maps['en'][ $path ] ) ) {
			return array( 'page_id' => $maps['en'][ $path ] );
		}
		if ( isset( $maps['pt'][ $path ] ) ) { // /en/{slug-em-português}/ também funciona
			return array( 'page_id' => $maps['pt'][ $path ] );
		}
		return array( 'error' => '404' );
	}
);

/** Em inglês, links de página (canonical, "ver página") apontam para /en/. */
add_filter(
	'page_link',
	function ( $link, $post_id ) {
		return 'en' === lahr_lang() ? lahr_i18n_url( $post_id, 'en' ) : $link;
	},
	10,
	2
);

/** Não redirecionar /en/... para a URL em português. */
add_filter(
	'redirect_canonical',
	function ( $redirect ) {
		return 'en' === lahr_lang() ? false : $redirect;
	}
);

/**
 * Converte um link interno de página em português para a versão em inglês.
 * Links externos, arquivos, âncoras e links já em /en/ ficam como estão.
 */
function lahr_i18n_localize_url( $url ) {
	$url = (string) $url;
	if ( '' === $url || '#' === $url[0] || preg_match( '#^(mailto:|tel:|javascript:|data:)#i', $url ) ) {
		return $url;
	}
	$p        = wp_parse_url( $url );
	$home     = wp_parse_url( home_url( '/' ) );
	$is_local = empty( $p['host'] ) || strtolower( $p['host'] ) === strtolower( $home['host'] ?? '' );
	if ( ! $is_local || ( empty( $p['host'] ) && isset( $p['path'] ) && '/' !== substr( $p['path'], 0, 1 ) ) ) {
		return $url;
	}
	$path  = isset( $p['path'] ) ? $p['path'] : '/';
	$hpath = isset( $home['path'] ) ? rtrim( $home['path'], '/' ) : '';
	if ( '' !== $hpath && 0 === strpos( $path, $hpath ) ) {
		$path = substr( $path, strlen( $hpath ) );
	}
	$path = trim( $path, '/' );
	if ( preg_match( '#^(en|wp-admin|wp-content|wp-includes|wp-json|feed)(/|$)#', $path ) || preg_match( '#\.[a-z0-9]{2,5}$#i', $path ) ) {
		return $url;
	}
	$tail = ( isset( $p['query'] ) ? '?' . $p['query'] : '' ) . ( isset( $p['fragment'] ) ? '#' . $p['fragment'] : '' );
	if ( '' === $path ) {
		return home_url( '/en/' ) . $tail;
	}
	$maps = lahr_i18n_maps();
	if ( isset( $maps['pt'][ $path ] ) ) {
		return lahr_i18n_url( $maps['pt'][ $path ], 'en' ) . $tail;
	}
	return $url;
}

/** Reescreve os href de <a> de um HTML (ignora links com hreflang / data-lahr-keep). */
function lahr_i18n_localize_html( $html ) {
	return preg_replace_callback(
		'#<a\b[^>]*>#i',
		function ( $m ) {
			$tag = $m[0];
			if ( false !== stripos( $tag, 'hreflang=' ) || false !== stripos( $tag, 'data-lahr-keep' ) ) {
				return $tag;
			}
			return preg_replace_callback(
				'#\bhref=(["\'])(.*?)\1#i',
				function ( $h ) {
					$orig = html_entity_decode( $h[2], ENT_QUOTES );
					$new  = lahr_i18n_localize_url( $orig );
					return $new === $orig ? $h[0] : 'href=' . $h[1] . esc_url( $new ) . $h[1];
				},
				$tag
			);
		},
		(string) $html
	);
}

/** Páginas em inglês: todos os links internos passam a apontar para /en/. */
add_action(
	'template_redirect',
	function () {
		if ( 'en' === lahr_lang() && ! is_admin() && ! wp_doing_ajax() ) {
			ob_start( 'lahr_i18n_localize_html' );
		}
	},
	0
);

/* =====================================================================
 * <head>: lang, título, hreflang
 * ================================================================== */

add_filter(
	'language_attributes',
	function ( $output ) {
		$locale = lahr_langs()[ lahr_lang() ]['locale'];
		$output = preg_replace( '/\blang="[^"]*"/', 'lang="' . esc_attr( $locale ) . '"', $output, 1, $count );
		return $count ? $output : trim( $output . ' lang="' . esc_attr( $locale ) . '"' );
	},
	20
);

add_filter(
	'document_title_parts',
	function ( $parts ) {
		if ( 'en' !== lahr_lang() ) {
			return $parts;
		}
		if ( is_page() && ! is_front_page() && isset( $parts['title'] ) ) {
			$parts['title'] = lahr_i18n_en_title( get_queried_object_id() );
		}
		if ( isset( $parts['tagline'] ) ) {
			$parts['tagline'] = lahr__( $parts['tagline'] );
		}
		return $parts;
	}
);

/** URLs (pt, en) da página atual — usadas no seletor e no hreflang. */
function lahr_i18n_current_urls() {
	if ( is_page() ) {
		$id = get_queried_object_id();
		return array( 'pt' => lahr_i18n_url( $id, 'pt' ), 'en' => lahr_i18n_url( $id, 'en' ) );
	}
	return array( 'pt' => home_url( '/' ), 'en' => home_url( '/en/' ) );
}

add_action(
	'wp_head',
	function () {
		if ( ! is_page() ) {
			return;
		}
		$u = lahr_i18n_current_urls();
		printf( '<link rel="alternate" hreflang="pt-BR" href="%s" />' . "\n", esc_url( $u['pt'] ) );
		printf( '<link rel="alternate" hreflang="en" href="%s" />' . "\n", esc_url( $u['en'] ) );
		printf( '<link rel="alternate" hreflang="x-default" href="%s" />' . "\n", esc_url( $u['pt'] ) );
	},
	2
);

/** Seletor de idioma (header). */
function lahr_i18n_switcher_html( $extra_class = '' ) {
	$u   = lahr_i18n_current_urls();
	$cur = lahr_lang();
	$out = '<nav class="cn-lang' . ( $extra_class ? ' ' . esc_attr( $extra_class ) : '' ) . '" aria-label="' . esc_attr( lahr__( 'Idioma' ) ) . '">';
	$i   = 0;
	foreach ( lahr_langs() as $code => $l ) {
		if ( $i++ ) {
			$out .= '<span class="cn-lang__sep" aria-hidden="true"></span>';
		}
		$active = $code === $cur;
		$out   .= sprintf(
			'<a href="%1$s" hreflang="%2$s" lang="%2$s" class="cn-lang__link%3$s"%4$s title="%5$s">%6$s</a>',
			esc_url( $u[ $code ] ),
			esc_attr( $l['locale'] ),
			$active ? ' is-active' : '',
			$active ? ' aria-current="true"' : '',
			esc_attr( $l['name'] ),
			esc_html( $l['label'] )
		);
	}
	return $out . '</nav>';
}

/* =====================================================================
 * Sitemap das páginas em inglês (wp-sitemap.xml → lahren)
 * ================================================================== */

add_action(
	'init',
	function () {
		if ( ! function_exists( 'wp_register_sitemap_provider' ) || ! class_exists( 'WP_Sitemaps_Provider' ) ) {
			return;
		}
		require_once get_theme_file_path( '/inc/i18n-sitemap.php' );
		wp_register_sitemap_provider( 'lahren', new Lahr_EN_Sitemap_Provider() );
	}
);

/* =====================================================================
 * Admin: alternar idioma de edição, título/endereço em inglês
 * ================================================================== */

/** Está editando a versão em inglês? (tela de edição do admin) */
function lahr_i18n_editing_en() {
	return is_admin() && 'en' === lahr_lang();
}

add_action(
	'add_meta_boxes',
	function () {
		foreach ( lahr_i18n_post_types() as $pt ) {
			add_meta_box( 'lahr_i18n', 'Idioma / Language', 'lahr_i18n_metabox', $pt, 'side', 'high' );
		}
	}
);

function lahr_i18n_metabox( $post ) {
	$en   = lahr_i18n_editing_en();
	$base = admin_url( 'post.php?post=' . $post->ID . '&action=edit' );
	wp_nonce_field( 'lahr_i18n_box', 'lahr_i18n_nonce' );
	echo '<input type="hidden" name="lahr_lang" value="' . ( $en ? 'en' : 'pt' ) . '">';
	echo '<div class="lahr-i18n-switch">';
	printf( '<a href="%s" class="%s">Português</a>', esc_url( $base ), $en ? '' : 'is-active' );
	printf( '<a href="%s" class="%s">English</a>', esc_url( $base . '&lahr_lang=en' ), $en ? 'is-active' : '' );
	echo '</div>';
	if ( $en ) {
		echo '<p class="lahr-i18n-note"><strong>Você está editando a versão em INGLÊS.</strong> Só os textos mudam aqui. Imagens, vídeos, contatos e a quantidade/ordem de itens são compartilhados — altere-os em Português.</p>';
	} else {
		echo '<p class="lahr-i18n-note lahr-i18n-note--pt">Editando a versão em <strong>Português</strong>. Clique em <em>English</em> para editar os textos em inglês.</p>';
	}
	if ( 'page' === $post->post_type ) {
		$title = (string) get_post_meta( $post->ID, '_lahr_i18n_title_en', true );
		$slug  = (string) get_post_meta( $post->ID, '_lahr_i18n_slug_en', true );
		$front = (int) get_option( 'page_on_front' ) === (int) $post->ID;
		echo '<p><label><strong>Título em inglês</strong><br><input type="text" name="lahr_i18n_title_en" value="' . esc_attr( $title ) . '" placeholder="' . esc_attr( lahr_i18n_en_title( $post->ID ) ) . '" style="width:100%"></label></p>';
		if ( ! $front ) {
			echo '<p><label><strong>Endereço em inglês</strong><br><code>/en/</code><input type="text" name="lahr_i18n_slug_en" value="' . esc_attr( $slug ) . '" placeholder="' . esc_attr( lahr_i18n_en_slug( $post->ID ) ) . '" style="width:70%"><code>/</code></label></p>';
		}
		if ( 'publish' === $post->post_status ) {
			printf( '<p><a href="%s" target="_blank" rel="noopener">Ver página em inglês ↗</a></p>', esc_url( lahr_i18n_url( $post->ID, 'en' ) ) );
		}
	}
}

add_action(
	'save_post',
	function ( $post_id ) {
		if ( ! isset( $_POST['lahr_i18n_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['lahr_i18n_nonce'] ) ), 'lahr_i18n_box' ) ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) || 'page' !== get_post_type( $post_id ) ) {
			return;
		}
		$title = isset( $_POST['lahr_i18n_title_en'] ) ? sanitize_text_field( wp_unslash( $_POST['lahr_i18n_title_en'] ) ) : '';
		$slug  = isset( $_POST['lahr_i18n_slug_en'] ) ? sanitize_title( wp_unslash( $_POST['lahr_i18n_slug_en'] ) ) : '';
		'' === $title ? delete_post_meta( $post_id, '_lahr_i18n_title_en' ) : update_post_meta( $post_id, '_lahr_i18n_title_en', $title );
		'' === $slug ? delete_post_meta( $post_id, '_lahr_i18n_slug_en' ) : update_post_meta( $post_id, '_lahr_i18n_slug_en', $slug );
		delete_transient( 'lahr_i18n_maps' );
	}
);

/** Modo inglês: marca campos compartilhados e trava estrutura dos repetidores. */
add_filter(
	'acf/prepare_field',
	function ( $field ) {
		if ( ! $field || ! lahr_i18n_editing_en() ) {
			return $field;
		}
		$containers = array( 'tab', 'message', 'accordion', 'group' );
		if ( in_array( $field['type'], $containers, true ) ) {
			return $field;
		}
		$cls = '';
		if ( 'repeater' === $field['type'] || 'flexible_content' === $field['type'] ) {
			$cls = 'lahr-i18n-rows-locked';
		} elseif ( ! lahr_i18n_is_translatable( $field ) ) {
			$cls = 'lahr-i18n-shared';
		}
		if ( $cls ) {
			$field['wrapper']['class'] = trim( ( $field['wrapper']['class'] ?? '' ) . ' ' . $cls );
		}
		return $field;
	}
);

add_filter(
	'admin_body_class',
	function ( $classes ) {
		return lahr_i18n_editing_en() ? $classes . ' lahr-editing-en' : $classes;
	}
);

add_action(
	'admin_head',
	function () {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'post' !== $screen->base || ! in_array( $screen->post_type, lahr_i18n_post_types(), true ) ) {
			return;
		}
		?>
<style>
.lahr-i18n-switch{display:flex;border:1px solid #c3c4c7;border-radius:4px;overflow:hidden;margin:4px 0 10px}
.lahr-i18n-switch a{flex:1;text-align:center;padding:7px 0;text-decoration:none;color:#1d2327;font-weight:600;background:#f6f7f7}
.lahr-i18n-switch a+a{border-left:1px solid #c3c4c7}
.lahr-i18n-switch a.is-active{background:#2271b1;color:#fff}
.lahr-i18n-note{margin:0 0 10px;padding:8px 10px;background:#fcf9e8;border-left:4px solid #dba617;font-size:12px;line-height:1.5}
.lahr-i18n-note--pt{background:#f0f6fc;border-left-color:#72aee6}
.lahr-editing-en #poststuff .acf-postbox > .postbox-header h2::after,
.lahr-editing-en .edit-post-meta-boxes-area .acf-postbox .postbox-header h2::after{content:'  ·  ENGLISH';color:#2271b1;font-weight:700}
.lahr-editing-en .acf-postbox{box-shadow:inset 4px 0 0 #2271b1}
.lahr-editing-en .lahr-i18n-shared{opacity:.45;pointer-events:none}
.lahr-editing-en .lahr-i18n-shared > .acf-label label::after{content:' (compartilhado — edite em Português)';font-weight:400;font-style:italic}
.lahr-editing-en .lahr-i18n-rows-locked .acf-actions,
.lahr-editing-en .lahr-i18n-rows-locked .acf-row-handle .acf-icon,
.lahr-editing-en .lahr-i18n-rows-locked .acf-row-handle.order{pointer-events:none}
.lahr-editing-en .lahr-i18n-rows-locked .acf-actions,
.lahr-editing-en .lahr-i18n-rows-locked .acf-row-handle .acf-icon{display:none !important}
</style>
		<?php
	}
);

/** Listagem de páginas: atalho "Editar em inglês". */
add_filter(
	'page_row_actions',
	function ( $actions, $post ) {
		if ( current_user_can( 'edit_post', $post->ID ) ) {
			$actions['lahr_edit_en'] = sprintf( '<a href="%s">Editar em inglês</a>', esc_url( admin_url( 'post.php?post=' . $post->ID . '&action=edit&lahr_lang=en' ) ) );
		}
		return $actions;
	},
	10,
	2
);

/** Mantém o modo inglês ao salvar (redireciona de volta com lahr_lang=en). */
add_filter(
	'redirect_post_location',
	function ( $location ) {
		return lahr_i18n_editing_en() ? add_query_arg( 'lahr_lang', 'en', $location ) : $location;
	}
);

/** Textos usados pelo main.js (enviados já traduzidos em LAHR.i18n). */
function lahr_i18n_js_strings() {
	if ( 'en' !== lahr_lang() ) {
		return new stdClass();
	}
	$keys = array(
		'Olá! Gostaria de agendar uma consulta com o Dr. Raphael.', 'Nome:', 'Cidade:', 'Interesse:', 'Como me encontrou:', 'Mensagem:',
		'Solicitação enviada', 'Acabamos de abrir o WhatsApp com sua mensagem pronta. Caso não tenha aberto automaticamente,',
		'clique aqui para abrir', 'Retorno em até 24h.', 'Olá! Meu nome é', 'Gostaria de saber sobre', 'Meu WhatsApp:', 'agora',
		'<p><strong>Dados enviados.</strong> Recebemos seu contato e retornamos pelo WhatsApp em até <strong>24h</strong>, de forma discreta.</p>',
	);
	$out = array();
	foreach ( $keys as $k ) {
		$out[ $k ] = lahr__( $k );
	}
	return $out;
}

/** Data relativa das avaliações do Google ("2 meses atrás" → "2 months ago"). */
function lahr_i18n_relative_date( $s ) {
	if ( 'en' !== lahr_lang() ) {
		return $s;
	}
	$s     = (string) $s;
	$ed    = 0 === stripos( $s, 'Editado ' ) ? 'Edited ' : '';
	$s     = preg_replace( '/^Editado\s+/i', '', $s );
	$units = array(
		'minuto' => 'minute', 'minutos' => 'minute', 'hora' => 'hour', 'horas' => 'hour', 'dia' => 'day', 'dias' => 'day',
		'semana' => 'week', 'semanas' => 'week', 'mês' => 'month', 'mes' => 'month', 'meses' => 'month', 'ano' => 'year', 'anos' => 'year',
	);
	if ( preg_match( '/^(\d+|um|uma)\s+(minutos?|horas?|dias?|semanas?|m[eê]s|meses|anos?)\s+atr[aá]s$/iu', trim( $s ), $m ) ) {
		$n    = is_numeric( $m[1] ) ? (int) $m[1] : 1;
		$unit = mb_strtolower( $m[2] );
		$en   = $units[ $unit ] ?? $unit;
		return $ed . ( 1 === $n ? ( 'hour' === $en ? 'an' : 'a' ) : $n ) . ' ' . $en . ( 1 === $n ? '' : 's' ) . ' ago';
	}
	return $ed . $s;
}

/** Link de acessibilidade do WordPress ("Pular para o conteúdo") no idioma da página. */
add_filter(
	'gettext',
	function ( $translation, $text, $domain ) {
		if ( 'default' === $domain && 'Skip to content' === $text && ! is_admin() ) {
			return 'en' === lahr_lang() ? 'Skip to content' : 'Pular para o conteúdo';
		}
		if ( 'default' === $domain && 'Page not found' === $text && ! is_admin() ) {
			return 'en' === lahr_lang() ? 'Page not found' : 'Página não encontrada';
		}
		return $translation;
	},
	10,
	3
);
