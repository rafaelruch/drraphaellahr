<?php
/**
 * Sitemap das páginas em inglês (/wp-sitemap-lahren-1.xml).
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Lahr_EN_Sitemap_Provider extends WP_Sitemaps_Provider {

	public function __construct() {
		$this->name        = 'lahren';
		$this->object_type = 'page';
	}

	public function get_url_list( $page_num, $object_subtype = '' ) {
		$ids  = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'numberposts' => 200,
				'fields'      => 'ids',
				'orderby'     => 'menu_order title',
				'order'       => 'ASC',
			)
		);
		$list = array();
		foreach ( $ids as $id ) {
			if ( 'obrigado' === get_post_field( 'post_name', $id ) ) {
				continue;
			}
			$list[] = array(
				'loc'     => lahr_i18n_url( $id, 'en' ),
				'lastmod' => get_post_modified_time( 'Y-m-d\TH:i:s+00:00', true, $id ),
			);
		}
		return $list;
	}

	public function get_max_num_pages( $object_subtype = '' ) {
		return 1;
	}
}
