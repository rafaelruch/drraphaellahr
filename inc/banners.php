<?php
/**
 * Lahr — módulo de Banners da Hero.
 *
 * Cada banner é um post do CPT `lahr_banner`, editado na sua própria tela
 * (campos simples, sem repetidor aninhado). A hero da Home consome os banners
 * publicados, ordenados por "Ordem" (menu_order). Muito mais funcional e
 * confiável para edição pelo cliente.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registra o CPT de banners.
 */
add_action(
	'init',
	function () {
		register_post_type(
			'lahr_banner',
			array(
				'labels'              => array(
					'name'               => 'Banners (Hero)',
					'singular_name'      => 'Banner',
					'menu_name'          => 'Banners (Hero)',
					'add_new'            => 'Adicionar banner',
					'add_new_item'       => 'Adicionar novo banner',
					'edit_item'          => 'Editar banner',
					'new_item'           => 'Novo banner',
					'all_items'          => 'Todos os banners',
					'not_found'          => 'Nenhum banner ainda.',
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => false,
				'menu_position'       => 57,
				'menu_icon'           => 'dashicons-images-alt2',
				'supports'            => array( 'title', 'page-attributes' ),
				'capability_type'     => 'page',
				'map_meta_cap'        => true,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'has_archive'         => false,
				'rewrite'             => false,
			)
		);
	}
);

/**
 * Campos do banner (chaves próprias — sem colisão).
 */
add_action(
	'acf/init',
	function () {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		$cond_foto  = array( array( array( 'field' => 'field_banner_tipo', 'operator' => '==', 'value' => 'foto' ) ) );
		$cond_video = array( array( array( 'field' => 'field_banner_tipo', 'operator' => '==', 'value' => 'video' ) ) );

		acf_add_local_field_group(
			array(
				'key'      => 'group_lahr_banner',
				'title'    => 'Banner da Hero',
				'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'lahr_banner' ) ) ),
				'position' => 'normal',
				'style'    => 'default',
				'fields'   => array(
					array(
						'key'     => 'field_banner_tipo',
						'label'   => 'Tipo de mídia',
						'name'    => 'tipo_midia',
						'type'    => 'button_group',
						'choices' => array( 'foto' => 'Foto', 'video' => 'Vídeo' ),
						'default_value' => 'foto',
					),
					array(
						'key'               => 'field_banner_imagem',
						'label'             => 'Imagem (foto de fundo)',
						'name'              => 'imagem',
						'type'              => 'image',
						'return_format'     => 'id',
						'preview_size'      => 'medium',
						'conditional_logic' => $cond_foto,
					),
					array(
						'key'               => 'field_banner_video',
						'label'             => 'Vídeo (mp4/webm)',
						'name'              => 'video',
						'type'              => 'file',
						'return_format'     => 'id',
						'mime_types'        => 'mp4,webm',
						'conditional_logic' => $cond_video,
					),
					array(
						'key'               => 'field_banner_poster',
						'label'             => 'Poster do vídeo (imagem)',
						'name'              => 'poster',
						'type'              => 'image',
						'return_format'     => 'id',
						'preview_size'      => 'medium',
						'conditional_logic' => $cond_video,
					),
					array(
						'key'   => 'field_banner_eyebrow',
						'label' => 'Eyebrow (linha pequena acima do título)',
						'name'  => 'eyebrow',
						'type'  => 'text',
					),
					array(
						'key'          => 'field_banner_titulo',
						'label'        => 'Título',
						'name'         => 'titulo',
						'type'         => 'text',
						'instructions' => 'Use &lt;em&gt;palavra&lt;/em&gt; para destaque dourado. Use &lt;span&gt;...&lt;/span&gt; para quebrar em linhas.',
					),
					array(
						'key'   => 'field_banner_lede',
						'label' => 'Texto (subtítulo)',
						'name'  => 'lede',
						'type'  => 'textarea',
						'rows'  => 3,
					),
					array(
						'key'     => 'field_banner_tab_ctas',
						'label'   => 'Botões',
						'type'    => 'message',
						'message' => 'Configure até dois botões. Deixe o texto em branco para ocultar.',
					),
					array( 'key' => 'field_banner_cta1_txt', 'label' => 'Botão 1 — texto', 'name' => 'cta1_texto', 'type' => 'text' ),
					array( 'key' => 'field_banner_cta1_url', 'label' => 'Botão 1 — link', 'name' => 'cta1_url', 'type' => 'text', 'default_value' => '/agendar/' ),
					array( 'key' => 'field_banner_cta1_est', 'label' => 'Botão 1 — estilo', 'name' => 'cta1_estilo', 'type' => 'button_group', 'choices' => array( 'gold' => 'Dourado', 'ghost' => 'Contorno' ), 'default_value' => 'gold' ),
					array( 'key' => 'field_banner_cta2_txt', 'label' => 'Botão 2 — texto', 'name' => 'cta2_texto', 'type' => 'text' ),
					array( 'key' => 'field_banner_cta2_url', 'label' => 'Botão 2 — link', 'name' => 'cta2_url', 'type' => 'text' ),
					array( 'key' => 'field_banner_cta2_est', 'label' => 'Botão 2 — estilo', 'name' => 'cta2_estilo', 'type' => 'button_group', 'choices' => array( 'gold' => 'Dourado', 'ghost' => 'Contorno' ), 'default_value' => 'ghost' ),
				),
			)
		);
	}
);

/**
 * Coluna de miniatura na listagem de banners (facilita identificar).
 */
add_filter(
	'manage_lahr_banner_posts_columns',
	function ( $cols ) {
		$new = array();
		foreach ( $cols as $k => $v ) {
			if ( 'title' === $k ) {
				$new['lahr_thumb'] = 'Prévia';
			}
			$new[ $k ] = $v;
		}
		return $new;
	}
);
add_action(
	'manage_lahr_banner_posts_custom_column',
	function ( $col, $post_id ) {
		if ( 'lahr_thumb' !== $col ) {
			return;
		}
		$img = get_post_meta( $post_id, 'imagem', true );
		if ( ! $img ) {
			$img = get_post_meta( $post_id, 'poster', true );
		}
		if ( $img ) {
			echo wp_get_attachment_image( (int) $img, array( 80, 50 ), false, array( 'style' => 'width:80px;height:50px;object-fit:cover;border-radius:4px' ) );
		} else {
			echo '—';
		}
	},
	10,
	2
);

/**
 * Retorna os banners publicados, normalizados para o renderizador da hero.
 *
 * @return array Lista de slides: tipo_midia, imagem, video, poster, eyebrow, titulo, lede, ctas[].
 */
function lahr_get_banners() {
	$posts = get_posts(
		array(
			'post_type'        => 'lahr_banner',
			'post_status'      => 'publish',
			'numberposts'      => 20,
			'orderby'          => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
			'suppress_filters' => false,
		)
	);

	$slides = array();
	foreach ( $posts as $p ) {
		$id   = $p->ID;
		$ctas = array();
		$c1   = get_post_meta( $id, 'cta1_texto', true );
		if ( '' !== $c1 ) {
			$ctas[] = array( 'texto' => $c1, 'url' => get_post_meta( $id, 'cta1_url', true ), 'estilo' => get_post_meta( $id, 'cta1_estilo', true ) ?: 'gold' );
		}
		$c2 = get_post_meta( $id, 'cta2_texto', true );
		if ( '' !== $c2 ) {
			$ctas[] = array( 'texto' => $c2, 'url' => get_post_meta( $id, 'cta2_url', true ), 'estilo' => get_post_meta( $id, 'cta2_estilo', true ) ?: 'ghost' );
		}
		$slides[] = array(
			'tipo_midia' => get_post_meta( $id, 'tipo_midia', true ) ?: 'foto',
			'imagem'     => get_post_meta( $id, 'imagem', true ),
			'video'      => get_post_meta( $id, 'video', true ),
			'poster'     => get_post_meta( $id, 'poster', true ),
			'eyebrow'    => get_post_meta( $id, 'eyebrow', true ),
			'titulo'     => get_post_meta( $id, 'titulo', true ),
			'lede'       => get_post_meta( $id, 'lede', true ),
			'ctas'       => $ctas,
		);
	}
	return $slides;
}
