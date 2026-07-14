<?php
/**
 * Lahr — construtores de campos ACF reutilizáveis.
 *
 * Builders genéricos (lahr_f_*) e schemas de tipos de seção (lahr_fields_*).
 * Campos definidos em PHP → 0 query de config, versionável.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* =========================================================================
 * Builders genéricos
 * ====================================================================== */

/** Aba (organiza o painel de edição). */
function lahr_f_tab( $key, $label ) {
	return array(
		'key'       => $key,
		'label'     => $label,
		'type'      => 'tab',
		'placement' => 'top',
	);
}

/** Campo de texto simples. */
function lahr_f_text( $key, $name, $label, $default = '', $instructions = '' ) {
	return array(
		'key'           => $key,
		'label'         => $label,
		'name'          => $name,
		'type'          => 'text',
		'default_value' => $default,
		'instructions'  => $instructions,
	);
}

/** Texto com ênfases inline permitidas (itálico dourado <em>, <strong>). */
function lahr_f_rich( $key, $name, $label, $default = '' ) {
	return array(
		'key'           => $key,
		'label'         => $label,
		'name'          => $name,
		'type'          => 'text',
		'default_value' => $default,
		'instructions'  => 'Use &lt;em&gt;palavra&lt;/em&gt; para destaque dourado e &lt;strong&gt; para negrito.',
	);
}

/** Área de texto. */
function lahr_f_textarea( $key, $name, $label, $default = '', $rows = 3 ) {
	return array(
		'key'           => $key,
		'label'         => $label,
		'name'          => $name,
		'type'          => 'textarea',
		'default_value' => $default,
		'rows'          => $rows,
		'new_lines'     => '',
	);
}

/** Editor visual (WYSIWYG) — para blocos de prosa/FAQ. */
function lahr_f_wysiwyg( $key, $name, $label ) {
	return array(
		'key'          => $key,
		'label'        => $label,
		'name'         => $name,
		'type'         => 'wysiwyg',
		'tabs'         => 'all',
		'toolbar'      => 'basic',
		'media_upload' => 0,
	);
}

/** Imagem (retorna ID do anexo). */
function lahr_f_image( $key, $name, $label, $instructions = '' ) {
	return array(
		'key'           => $key,
		'label'         => $label,
		'name'          => $name,
		'type'          => 'image',
		'return_format' => 'id',
		'preview_size'  => 'medium',
		'library'       => 'all',
		'instructions'  => $instructions,
	);
}

/** Arquivo (retorna ID) — usado para vídeos. */
function lahr_f_file( $key, $name, $label, $mime = 'video/mp4', $instructions = '' ) {
	return array(
		'key'           => $key,
		'label'         => $label,
		'name'          => $name,
		'type'          => 'file',
		'return_format' => 'id',
		'library'       => 'all',
		'mime_types'    => $mime,
		'instructions'  => $instructions,
	);
}

/** Verdadeiro/Falso. */
function lahr_f_true_false( $key, $name, $label, $default = 0, $ui = 1 ) {
	return array(
		'key'           => $key,
		'label'         => $label,
		'name'          => $name,
		'type'          => 'true_false',
		'ui'            => $ui,
		'default_value' => $default,
	);
}

/** Número. */
function lahr_f_number( $key, $name, $label, $default = '', $instructions = '' ) {
	return array(
		'key'           => $key,
		'label'         => $label,
		'name'          => $name,
		'type'          => 'number',
		'default_value' => $default,
		'instructions'  => $instructions,
	);
}

/** Select. */
function lahr_f_select( $key, $name, $label, $choices, $default = '' ) {
	return array(
		'key'           => $key,
		'label'         => $label,
		'name'          => $name,
		'type'          => 'select',
		'choices'       => $choices,
		'default_value' => $default,
		'ui'            => 1,
	);
}

/** Repetidor de links (label + url [+ is_cta]). Usado em menus/rodapé. */
function lahr_f_link_repeater( $key, $name, $label, $with_cta = false ) {
	$subs = array(
		lahr_f_text( $key . '_label', 'label', 'Texto' ),
		lahr_f_text( $key . '_url', 'url', 'URL' ),
	);
	if ( $with_cta ) {
		$subs[] = lahr_f_true_false( $key . '_cta', 'is_cta', 'Botão destaque (CTA)?', 0 );
	}
	return array(
		'key'          => $key,
		'label'        => $label,
		'name'         => $name,
		'type'         => 'repeater',
		'layout'       => 'table',
		'button_label' => 'Adicionar link',
		'sub_fields'   => $subs,
	);
}
