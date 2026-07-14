<?php
/**
 * Página OBRIGADO (ID 14).
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

lahr_register_page(
	'obrigado',
	array(
		array( 'type' => 'hero_page', 'key' => 'hero', 'layout' => 'centered' ),
		array( 'type' => 'cards', 'key' => 'proximos', 'narrow' => true, 'tilt' => false ),
	)
);

add_action(
	'acf/init',
	function () {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}
		$f = array();
		$f = array_merge( $f, lahr_fields_hero_page( 'hero', 'Hero da página' ) );
		$f = array_merge( $f, lahr_fields_cards( 'proximos', 'Próximos passos' ) );

		acf_add_local_field_group(
			array(
				'key'             => 'group_lahr_page_obrigado',
				'title'           => 'Conteúdo — Obrigado',
				'fields'          => $f,
				'location'        => array( array( array( 'param' => 'post', 'operator' => '==', 'value' => 14 ) ) ),
				'menu_order'      => 0,
				'position'        => 'normal',
				'style'           => 'default',
				'label_placement' => 'top',
				'hide_on_screen'  => array( 'the_content' ),
			)
		);
	}
);

// Evento de conversão (GTM) na página de obrigado.
add_action(
	'wp_footer',
	function () {
		if ( ! is_page() || 'obrigado' !== get_post_field( 'post_name', get_queried_object_id() ) ) {
			return;
		}
		echo "<script>if(window.dataLayer){window.dataLayer.push({event:'lead_form_thankyou',page_path:'/obrigado/'});}</script>";
	}
);
