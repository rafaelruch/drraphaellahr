<?php
/**
 * Lahr — captura de leads do formulário /agendar.
 *
 * Armazena cada envio como CPT `lahr_lead` (visível no admin, exportável) e
 * envia e-mail de notificação ao consultório + auto-reply ao paciente.
 * O formulário e o fluxo de WhatsApp existentes são preservados: o JS faz um
 * POST paralelo (keepalive) para este endpoint.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ------------------------------------------------------------ Atribuição */
/** Normaliza um rótulo de UTM para comparação (minúsculas, "_"/espaço → "-"). */
function lahr_attr_norm( $v ) {
	return trim( str_replace( array( '_', ' ', '+' ), '-', strtolower( trim( (string) $v ) ) ), '-' );
}

/**
 * O visitante veio do perfil do Google Meu Negócio (Perfil da Empresa / Maps)?
 *
 * Sinal principal: UTMs no link "Site" do perfil (ex.: ?utm_source=gmn&utm_medium=organic&utm_campaign=perfil).
 * Sinal secundário: referrer do Google Maps / app do Maps / links g.page e share.google.
 * Observação: um clique no perfil exibido na busca do Google chega só com referrer
 * "google.com" — indistinguível da busca orgânica — por isso o link do perfil
 * DEVE ter UTMs para a atribuição ser 100% confiável.
 */
function lahr_lead_is_gmn( array $d ) {
	$tags = array(
		'gmn', 'gmb', 'gbp', 'google-meu-negocio', 'meu-negocio', 'googlemeunegocio', 'google-my-business',
		'googlemybusiness', 'my-business', 'google-business', 'googlebusiness', 'google-business-profile',
		'business-profile', 'perfil-da-empresa', 'perfil-google', 'google-perfil', 'google-maps', 'googlemaps', 'maps',
	);
	foreach ( array( 'utm_source', 'utm_medium', 'utm_campaign' ) as $k ) {
		if ( ! empty( $d[ $k ] ) && in_array( lahr_attr_norm( $d[ $k ] ), $tags, true ) ) {
			return true;
		}
	}
	$ref = strtolower( (string) ( $d['ref'] ?? '' ) );
	return '' !== $ref && (bool) preg_match( '#(^|\.)maps\.google\.|^(www\.)?google\.[a-z.]+/maps|business\.google\.com|(^|\.)g\.page$|^share\.google$|maps\.app\.goo\.gl|com\.google\.android\.apps\.maps#', $ref );
}

/**
 * Regra ÚNICA de canal a partir dos sinais de atribuição (cookie ou UTMs gravados).
 * Usada no envio (cookie lahr_attr), no painel e no backfill dos leads antigos.
 *
 * @param array $d { gclid, wbraid, gbraid, fbclid, utm_source, utm_medium, utm_campaign, ref }
 * @return string 'Google Ads' | 'Google Meu Negócio' | 'Google Orgânico' | 'Meta Ads' | '<utm_source>' | '<site de origem>' | 'Direto / Orgânico'
 */
function lahr_lead_canal_from( array $d ) {
	$src = lahr_attr_norm( $d['utm_source'] ?? '' );
	$med = lahr_attr_norm( $d['utm_medium'] ?? '' );
	$ref = strtolower( (string) ( $d['ref'] ?? '' ) );

	// 1) Clique pago com identificador do Google (inclui anúncios locais no Maps).
	if ( ! empty( $d['gclid'] ) || ! empty( $d['wbraid'] ) || ! empty( $d['gbraid'] ) ) {
		return 'Google Ads';
	}
	// 2) Perfil do Google Meu Negócio.
	if ( lahr_lead_is_gmn( $d ) ) {
		return 'Google Meu Negócio';
	}
	// 3) utm_source=google: só é Ads se o meio for pago (ou não informado — compatível com links antigos).
	if ( in_array( $src, array( 'google', 'adwords', 'gads', 'google-ads', 'googleads' ), true ) ) {
		return in_array( $med, array( 'organic', 'organico', 'seo', 'referral', 'local' ), true ) ? 'Google Orgânico' : 'Google Ads';
	}
	// 4) Meta.
	if ( ! empty( $d['fbclid'] ) || in_array( $src, array( 'facebook', 'instagram', 'meta', 'fb', 'ig', 'meta-ads', 'metaads' ), true ) ) {
		return 'Meta Ads';
	}
	// 5) Outra fonte nomeada por UTM.
	if ( '' !== $src ) {
		return sanitize_text_field( (string) $d['utm_source'] );
	}
	// 6) Sem UTM: classifica pelo site de origem (referrer).
	if ( '' !== $ref ) {
		$host = preg_replace( '#^www\.#', '', strtok( $ref, '/' ) );
		if ( preg_match( '#(^|\.)google\.[a-z.]+$#', $host ) ) {
			return 'Google Orgânico';
		}
		if ( preg_match( '#(^|\.)(bing\.com|duckduckgo\.com|yahoo\.com|search\.brave\.com|ecosia\.org)$#', $host ) ) {
			return 'Busca orgânica (outros)';
		}
		if ( preg_match( '#(^|\.)(instagram\.com|facebook\.com|fb\.com|threads\.net)$#', $host ) ) {
			return 'Instagram / Facebook';
		}
		return sanitize_text_field( $host );
	}
	return 'Direto / Orgânico';
}

/** Lê o cookie lahr_attr (setado por attribution.js; last-touch, 90 dias). */
function lahr_lead_attr_cookie() {
	$raw = isset( $_COOKIE['lahr_attr'] ) ? wp_unslash( $_COOKIE['lahr_attr'] ) : '';
	$d   = json_decode( (string) $raw, true );
	return is_array( $d ) ? $d : array();
}

/** Sinais de atribuição gravados num lead (para recalcular o canal). */
function lahr_lead_attr_meta( $post_id ) {
	$d = array();
	foreach ( array( 'gclid', 'wbraid', 'gbraid', 'fbclid', 'utm_source', 'utm_medium', 'utm_campaign', 'ref' ) as $k ) {
		$d[ $k ] = (string) get_post_meta( $post_id, $k, true );
	}
	return $d;
}

/** Canal de um lead salvo: meta `canal` (leads novos) ou derivado dos sinais gravados (antigos). */
function lahr_lead_get_canal( $post_id ) {
	$c = (string) get_post_meta( $post_id, 'canal', true );
	if ( '' !== $c ) {
		return $c;
	}
	return lahr_lead_canal_from( lahr_lead_attr_meta( $post_id ) );
}

/** Rótulo do formulário de origem. */
function lahr_lead_form_label( $v ) {
	return 'agendar' === $v ? 'Formulário Agendar' : ( 'widget' === $v ? 'Botão de WhatsApp' : 'Site' );
}

/**
 * CPT de leads (criado apenas programaticamente).
 */
add_action(
	'init',
	function () {
		register_post_type(
			'lahr_lead',
			array(
				'labels'              => array(
					'name'          => 'Leads / Agendamentos',
					'singular_name' => 'Lead',
					'menu_name'     => 'Leads',
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => false,
				'menu_position'       => 58,
				'menu_icon'           => 'dashicons-email-alt',
				'supports'            => array( 'title' ),
				'capability_type'     => 'post',
				'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'        => true,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'has_archive'         => false,
				'rewrite'             => false,
			)
		);
	}
);

/** Colunas do admin para a listagem de leads. */
add_filter(
	'manage_lahr_lead_posts_columns',
	function ( $cols ) {
		return array(
			'cb'            => isset( $cols['cb'] ) ? $cols['cb'] : '',
			'title'        => 'Nome',
			'lahr_whatsapp' => 'WhatsApp',
			'lahr_cidade'  => 'Cidade',
			'lahr_interesse' => 'Interesse',
			'lahr_canal'     => 'Canal',
			'lahr_form'      => 'Formulário',
			'lahr_origem'    => 'Como encontrou',
			'lahr_idioma'    => 'Idioma',
			'date'         => 'Recebido em',
		);
	}
);
add_action(
	'manage_lahr_lead_posts_custom_column',
	function ( $col, $post_id ) {
		$map = array(
			'lahr_whatsapp'  => 'whatsapp',
			'lahr_cidade'    => 'cidade',
			'lahr_interesse' => 'interesse',
			'lahr_origem'    => 'origem',
		);
		if ( 'lahr_canal' === $col ) {
			echo esc_html( lahr_lead_get_canal( $post_id ) );
		} elseif ( 'lahr_idioma' === $col ) {
			echo 'en' === get_post_meta( $post_id, 'idioma', true ) ? 'Inglês' : 'Português';
		} elseif ( 'lahr_form' === $col ) {
			echo esc_html( lahr_lead_form_label( get_post_meta( $post_id, 'origem_form', true ) ) );
		} elseif ( isset( $map[ $col ] ) ) {
			echo esc_html( get_post_meta( $post_id, $map[ $col ], true ) );
		}
	},
	10,
	2
);

/**
 * Endpoint AJAX (visitantes logados e anônimos).
 */
add_action( 'wp_ajax_lahr_lead', 'lahr_handle_lead' );
add_action( 'wp_ajax_nopriv_lahr_lead', 'lahr_handle_lead' );

function lahr_handle_lead() {
	// Nonce.
	if ( ! check_ajax_referer( 'lahr_lead', '_nonce', false ) ) {
		wp_send_json_error( array( 'msg' => 'nonce' ), 403 );
	}
	// Honeypot: responde ok silenciosamente para bots.
	if ( ! empty( $_POST['website'] ) ) {
		wp_send_json_success( array( 'bot' => true ) );
	}

	$data = array(
		'nome'      => sanitize_text_field( wp_unslash( $_POST['nome'] ?? '' ) ),
		'whatsapp'  => sanitize_text_field( wp_unslash( $_POST['whatsapp'] ?? '' ) ),
		'email'     => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
		'cidade'    => sanitize_text_field( wp_unslash( $_POST['cidade'] ?? '' ) ),
		'interesse' => sanitize_text_field( wp_unslash( $_POST['interesse'] ?? '' ) ),
		'origem'    => sanitize_text_field( wp_unslash( $_POST['origem'] ?? '' ) ),
		'mensagem'  => sanitize_textarea_field( wp_unslash( $_POST['mensagem'] ?? '' ) ),
		'utm_source'   => sanitize_text_field( wp_unslash( $_POST['utm_source'] ?? '' ) ),
		'utm_medium'   => sanitize_text_field( wp_unslash( $_POST['utm_medium'] ?? '' ) ),
		'utm_campaign' => sanitize_text_field( wp_unslash( $_POST['utm_campaign'] ?? '' ) ),
		'utm_term'     => sanitize_text_field( wp_unslash( $_POST['utm_term'] ?? '' ) ),
		'utm_content'  => sanitize_text_field( wp_unslash( $_POST['utm_content'] ?? '' ) ),
		'origem_form'  => sanitize_text_field( wp_unslash( $_POST['origem_form'] ?? 'agendar' ) ),
		'idioma'       => 'en' === lahr_lang() ? 'en' : 'pt',
	);

	// Validação mínima.
	if ( mb_strlen( $data['nome'] ) < 3 || preg_replace( '/\D/', '', $data['whatsapp'] ) === '' ) {
		wp_send_json_error( array( 'msg' => 'dados incompletos' ), 422 );
	}

	// Atribuição (cookie lahr_attr): completa UTMs ausentes (ex.: widget) e define o canal.
	$attr = lahr_lead_attr_cookie();
	foreach ( array( 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content' ) as $k ) {
		if ( '' === $data[ $k ] && ! empty( $attr[ $k ] ) ) {
			$data[ $k ] = sanitize_text_field( (string) $attr[ $k ] );
		}
	}
	foreach ( array( 'gclid', 'wbraid', 'gbraid', 'fbclid', 'ref' ) as $k ) {
		$data[ $k ] = ! empty( $attr[ $k ] ) ? sanitize_text_field( (string) $attr[ $k ] ) : '';
	}
	$data['canal'] = lahr_lead_canal_from( $data );

	// Armazena.
	$post_id = wp_insert_post(
		array(
			'post_type'   => 'lahr_lead',
			'post_status' => 'publish',
			'post_title'  => $data['nome'] . ( $data['cidade'] ? ' — ' . $data['cidade'] : '' ),
		),
		true
	);
	if ( is_wp_error( $post_id ) ) {
		wp_send_json_error( array( 'msg' => 'store' ), 500 );
	}
	foreach ( $data as $k => $v ) {
		if ( '' !== $v ) {
			update_post_meta( $post_id, $k, $v );
		}
	}

	// E-mails.
	lahr_lead_send_emails( $data );

	// Gatilho único da automação (notificação no grupo do WhatsApp via Uazapi, etc.).
	do_action( 'lahr_lead_created', $data, $post_id );

	wp_send_json_success( array( 'id' => $post_id ) );
}

/** Envia notificação ao consultório e auto-reply ao paciente. */
function lahr_lead_send_emails( $data ) {
	$to      = lahr_opt( 'lead_email', get_option( 'admin_email' ) );
	$blog    = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	$admin   = get_option( 'admin_email' );
	$headers = array( 'Content-Type: text/html; charset=UTF-8', 'From: ' . $blog . ' <' . $admin . '>' );

	// Notificação interna.
	$rows = '';
	$labels = array(
		'nome' => 'Nome', 'whatsapp' => 'WhatsApp', 'email' => 'E-mail', 'cidade' => 'Cidade',
		'interesse' => 'Interesse', 'origem' => 'Como encontrou', 'mensagem' => 'Mensagem',
	);
	if ( 'en' === ( $data['idioma'] ?? '' ) ) {
		$rows .= '<tr><td style="padding:6px 12px;font-weight:bold;background:#f6f6f6">Idioma</td><td style="padding:6px 12px">Inglês (site em inglês)</td></tr>';
	}
	foreach ( $labels as $k => $lbl ) {
		if ( ! empty( $data[ $k ] ) ) {
			$rows .= '<tr><td style="padding:6px 12px;font-weight:bold;background:#f6f6f6">' . esc_html( $lbl ) . '</td><td style="padding:6px 12px">' . nl2br( esc_html( $data[ $k ] ) ) . '</td></tr>';
		}
	}
	$utm = array_filter( array( $data['utm_source'], $data['utm_medium'], $data['utm_campaign'] ) );
	if ( $utm ) {
		$rows .= '<tr><td style="padding:6px 12px;font-weight:bold;background:#f6f6f6">Campanha</td><td style="padding:6px 12px">' . esc_html( implode( ' / ', $utm ) ) . '</td></tr>';
	}
	$body = '<h2 style="font-family:sans-serif">Novo agendamento</h2><table style="border-collapse:collapse;font-family:sans-serif;font-size:14px">' . $rows . '</table>';

	$notify_headers = $headers;
	if ( ! empty( $data['email'] ) ) {
		$notify_headers[] = 'Reply-To: ' . $data['nome'] . ' <' . $data['email'] . '>';
	}
	wp_mail( $to, 'Novo agendamento: ' . $data['nome'], $body, $notify_headers );

	// Auto-reply ao paciente.
	if ( ! empty( $data['email'] ) ) {
		$msg = '<div style="font-family:sans-serif;font-size:15px;line-height:1.6;color:#0d1624">'
			. '<p>' . esc_html( lahr__( 'Olá,' ) ) . ' ' . esc_html( $data['nome'] ) . '.</p>'
			. '<p>' . wp_kses_post( lahr__( 'Recebemos sua solicitação de agendamento. Nossa equipe entrará em contato pelo WhatsApp informado em até <strong>24 horas</strong>, sempre de forma discreta e profissional.' ) ) . '</p>'
			. '<p>' . esc_html( lahr__( 'Atendimento sigiloso e exclusivo do Dr. Raphael Lahr · CRM-SC 15336.' ) ) . '</p>'
			. '<p style="color:#888;font-size:12px">' . esc_html( lahr__( 'Este é um e-mail automático de confirmação. Não é necessário respondê-lo.' ) ) . '</p>'
			. '</div>';
		wp_mail( $data['email'], lahr__( 'Recebemos sua solicitação — Dr. Raphael Lahr' ), $msg, $headers );
	}
}

/* ------------------------------------------------ Filtro por canal (listagem) */
add_action(
	'restrict_manage_posts',
	function ( $post_type ) {
		if ( 'lahr_lead' !== $post_type ) {
			return;
		}
		$cur  = isset( $_GET['lahr_canal'] ) ? sanitize_text_field( wp_unslash( $_GET['lahr_canal'] ) ) : '';
		$opts = array( 'Google Ads', 'Google Meu Negócio', 'Google Orgânico', 'Meta Ads', 'Instagram / Facebook', 'Direto / Orgânico' );
		echo '<select name="lahr_canal"><option value="">Todos os canais</option>';
		foreach ( $opts as $o ) {
			printf( '<option value="%s"%s>%s</option>', esc_attr( $o ), selected( $cur, $o, false ), esc_html( $o ) );
		}
		echo '</select>';
	}
);
add_action(
	'pre_get_posts',
	function ( $q ) {
		if ( ! is_admin() || ! $q->is_main_query() || 'lahr_lead' !== $q->get( 'post_type' ) ) {
			return;
		}
		$c = isset( $_GET['lahr_canal'] ) ? sanitize_text_field( wp_unslash( $_GET['lahr_canal'] ) ) : '';
		if ( '' !== $c ) {
			$q->set( 'meta_query', array( array( 'key' => 'canal', 'value' => $c ) ) );
		}
	}
);
