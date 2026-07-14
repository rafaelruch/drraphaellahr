<?php
/**
 * Lahr — helpers de campo/render.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Valor de campo ACF do post atual (fallback seguro se ACF ausente).
 *
 * @param string $name    Nome do campo.
 * @param mixed  $default Valor padrão.
 * @param int    $post_id ID opcional (default: post atual).
 * @return mixed
 */
function lahr_field( $name, $default = '', $post_id = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}
	$v = get_field( $name, $post_id );
	return ( null === $v || false === $v || '' === $v ) ? $default : $v;
}

/**
 * Valor de configuração global (armazenado no CPT lahr_config).
 *
 * @param string $name    Nome do campo.
 * @param mixed  $default Valor padrão.
 * @return mixed
 */
function lahr_opt( $name, $default = '' ) {
	if ( ! function_exists( 'get_field' ) || ! function_exists( 'lahr_config_id' ) ) {
		return $default;
	}
	$id = lahr_config_id();
	if ( ! $id ) {
		return $default;
	}
	$v = get_field( $name, $id );
	return ( null === $v || false === $v || '' === $v ) ? $default : $v;
}

/**
 * Sanitiza texto permitindo apenas ênfases inline usadas no design.
 * (itálico dourado, negrito, quebra de linha, span com classe)
 *
 * @param string $html Texto potencialmente com <em>/<strong>/<br>.
 * @return string
 */
function lahr_rich( $html ) {
	return wp_kses(
		(string) $html,
		array(
			'em'     => array(),
			'strong' => array(),
			'br'     => array(),
			'span'   => array( 'class' => array() ),
		)
	);
}

/**
 * <img> a partir de ID de anexo, com dimensões e prioridade de carga.
 *
 * @param int   $id   ID do anexo.
 * @param array $args size|class|eager|alt.
 * @return string HTML da imagem (vazio se sem ID).
 */
function lahr_img( $id, $args = array() ) {
	if ( ! $id ) {
		return '';
	}
	$args = wp_parse_args(
		$args,
		array(
			'size'  => 'full',
			'class' => '',
			'eager' => false,
			'alt'   => '',
		)
	);
	$atts = array(
		'class' => $args['class'],
	);
	if ( '' !== $args['alt'] ) {
		$atts['alt'] = $args['alt'];
	}
	if ( $args['eager'] ) {
		$atts['loading']       = 'eager';
		$atts['fetchpriority'] = 'high';
	} else {
		$atts['loading'] = 'lazy';
	}
	return wp_get_attachment_image( (int) $id, $args['size'], false, $atts );
}

/**
 * URL de <img> de anexo (para casos onde só a URL é necessária).
 *
 * @param int    $id   ID do anexo.
 * @param string $size Tamanho.
 * @return string
 */
function lahr_img_url( $id, $size = 'full' ) {
	if ( ! $id ) {
		return '';
	}
	$src = wp_get_attachment_image_src( (int) $id, $size );
	return $src ? $src[0] : '';
}
