<?php
/**
 * Notificação de leads no grupo do WhatsApp via Uazapi (padrão Delva/Vaxx/Uro Centro).
 *
 * Quando qualquer formulário do site gera um lead (formulário de /agendar OU o
 * widget de WhatsApp flutuante), um resumo cai no grupo interno
 * "RUCH 🤑 LEADS RAPHAEL LAHR", já com o canal de origem (Google Ads / Meta Ads /
 * Direto). Envio 100% server-side. Mesmo gatilho para os dois formulários:
 * `do_action( 'lahr_lead_created', $data, $post_id )` em inc/leads.php.
 *
 * Segurança do token: NUNCA no git. Lido nesta ordem:
 *   1) constante LAHR_UAZAPI_TOKEN no wp-config.php (recomendado em produção)
 *   2) Configurações do Site → Leads no grupo (WhatsApp)
 * Base e JID do grupo têm default no código e também são sobrescrevíveis.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* --------------------------------------------------------------- Config */

function lahr_uazapi_base() {
	$b = lahr_opt( 'wa_base', '' );
	return untrailingslashit( $b ?: 'https://ruch01.uazapi.com' );
}

/** JID do grupo RUCH 🤑 LEADS RAPHAEL LAHR (não é segredo — pode ir no código). */
function lahr_uazapi_group() {
	return lahr_opt( 'wa_group', '' ) ?: '120363429551685762@g.us';
}

function lahr_uazapi_token() {
	if ( defined( 'LAHR_UAZAPI_TOKEN' ) && LAHR_UAZAPI_TOKEN ) {
		return LAHR_UAZAPI_TOKEN;
	}
	return (string) lahr_opt( 'wa_token', '' );
}

/* ----------------------------------------------------------- Envio de texto */
/**
 * POST {base}/send/text — header token, body {number, text}.
 * IMPORTANTE: não sanitizar o $number (o JID do grupo termina em @g.us).
 *
 * @return array { sent:bool, code?:int, body?:string, reason?:string }
 */
function lahr_uazapi_send_text( $text, $number = '' ) {
	$base   = lahr_uazapi_base();
	$token  = lahr_uazapi_token();
	$number = $number ?: lahr_uazapi_group();
	if ( ! $base || ! $token ) {
		return array( 'sent' => false, 'reason' => 'sem-token' );
	}
	if ( ! $number ) {
		return array( 'sent' => false, 'reason' => 'sem-grupo' );
	}

	$resp = wp_remote_post(
		trailingslashit( $base ) . 'send/text',
		array(
			'timeout' => 20,
			'headers' => array( 'token' => $token, 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( array( 'number' => $number, 'text' => $text ) ),
		)
	);
	if ( is_wp_error( $resp ) ) {
		return array( 'sent' => false, 'reason' => $resp->get_error_message() );
	}
	$code = wp_remote_retrieve_response_code( $resp );
	return array( 'sent' => ( $code >= 200 && $code < 300 ), 'code' => $code, 'body' => wp_remote_retrieve_body( $resp ) );
}

/* ------------------------------------------- Atribuição de origem (cookie) */
/** Lê o cookie lahr_attr (setado por attribution.js) e deriva o canal. */
function lahr_lead_origem() {
	$raw = isset( $_COOKIE['lahr_attr'] ) ? wp_unslash( $_COOKIE['lahr_attr'] ) : '';
	$d   = json_decode( $raw, true );
	if ( ! is_array( $d ) ) {
		return array( 'canal' => 'Direto / Orgânico', 'campanha' => '' );
	}

	$src   = strtolower( $d['utm_source'] ?? '' );
	$canal = 'Direto / Orgânico';
	if ( ! empty( $d['gclid'] ) || ! empty( $d['wbraid'] ) || ! empty( $d['gbraid'] ) || in_array( $src, array( 'google', 'adwords', 'gads' ), true ) ) {
		$canal = 'Google Ads';
	} elseif ( ! empty( $d['fbclid'] ) || in_array( $src, array( 'facebook', 'instagram', 'meta', 'fb', 'ig' ), true ) ) {
		$canal = 'Meta Ads';
	} elseif ( $src ) {
		$canal = sanitize_text_field( $d['utm_source'] );
	}
	return array(
		'canal'    => $canal,
		'campanha' => isset( $d['utm_campaign'] ) ? sanitize_text_field( $d['utm_campaign'] ) : '',
	);
}

/* --------------------------------------------------- Formatação da mensagem */
function lahr_uazapi_format_lead( array $d ) {
	$o     = lahr_lead_origem();
	$nome  = $d['nome']      ?? '';
	$tel   = $d['whatsapp']  ?? '';
	$cid   = $d['cidade']    ?? '';
	$area  = $d['interesse'] ?? '';
	$email = $d['email']     ?? '';
	$msg   = $d['mensagem']  ?? '';

	$via = 'agendar' === ( $d['origem_form'] ?? '' ) ? 'Formulário Agendar' : ( 'widget' === ( $d['origem_form'] ?? '' ) ? 'Botão de WhatsApp' : 'Site' );

	$L   = array();
	$L[] = '🔔 *Novo lead pelo site — Dr. Raphael Lahr*';
	$L[] = '';
	$L[] = '🎯 *Canal:* ' . ( $o['canal'] ?: 'Direto / Orgânico' );
	if ( ! empty( $o['campanha'] ) ) {
		$L[] = '📣 *Campanha:* ' . $o['campanha'];
	}
	$L[] = '';
	$L[] = '👤 *Nome:* ' . $nome;
	if ( $tel )   { $L[] = '📱 *Telefone:* ' . $tel; }
	if ( $cid )   { $L[] = '📍 *Cidade:* ' . $cid; }
	if ( $area )  { $L[] = '🩺 *Interesse:* ' . $area; }
	if ( $email ) { $L[] = '✉️ *E-mail:* ' . $email; }
	if ( $msg )   { $L[] = ''; $L[] = '📝 ' . $msg; }
	$L[] = '';
	$L[] = '🌐 _Via: ' . $via . '_';
	return implode( "\n", $L );
}

/* --------------------------------------- Gatilho: um lead foi criado no site */
function lahr_notify_group( $meta ) {
	if ( ! is_array( $meta ) ) {
		return;
	}
	lahr_uazapi_send_text( lahr_uazapi_format_lead( $meta ) );
}
add_action( 'lahr_lead_created', 'lahr_notify_group', 10, 1 );

/* -------------------------------------------- Captura de atribuição (JS) */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( is_admin() ) {
			return;
		}
		$ver = @filemtime( get_theme_file_path( '/assets/js/attribution.js' ) ) ?: '1';
		wp_enqueue_script( 'lahr-attr', get_theme_file_uri( '/assets/js/attribution.js' ), array(), $ver, false );
	},
	24
);
