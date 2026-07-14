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
			'lahr_origem'  => 'Origem',
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
		if ( isset( $map[ $col ] ) ) {
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
	);

	// Validação mínima.
	if ( mb_strlen( $data['nome'] ) < 3 || preg_replace( '/\D/', '', $data['whatsapp'] ) === '' ) {
		wp_send_json_error( array( 'msg' => 'dados incompletos' ), 422 );
	}

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
			. '<p>Olá, ' . esc_html( $data['nome'] ) . '.</p>'
			. '<p>Recebemos sua solicitação de agendamento. Nossa equipe entrará em contato pelo WhatsApp informado em até <strong>24 horas</strong>, sempre de forma discreta e profissional.</p>'
			. '<p>Atendimento particular, sigiloso e exclusivo do Dr. Raphael Lahr · CRM-SC 15336.</p>'
			. '<p style="color:#888;font-size:12px">Este é um e-mail automático de confirmação. Não é necessário respondê-lo.</p>'
			. '</div>';
		wp_mail( $data['email'], 'Recebemos sua solicitação — Dr. Raphael Lahr', $msg, $headers );
	}
}
