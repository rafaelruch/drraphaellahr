<?php
/**
 * Seção FAQ — acordeão (cn-faq) + Schema FAQPage no <head>.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function lahr_section_faq( $sec ) {
	$p       = $sec['key'];
	$eyebrow = lahr_field( "{$p}_eyebrow" );
	$titulo  = lahr_field( "{$p}_titulo" );
	$lede    = lahr_field( "{$p}_lede" );
	$itens   = (array) lahr_field( "{$p}_itens", array() );

	$html  = '<section class="cn-faq"><div class="cn-faq__inner">';
	$html .= lahr_sechead_html( $eyebrow, $titulo, $lede );
	$html .= '<ul class="cn-faq__list">';
	foreach ( $itens as $it ) {
		$q = isset( $it['pergunta'] ) ? $it['pergunta'] : '';
		$a = isset( $it['resposta'] ) ? $it['resposta'] : '';
		$html .= '<li class="cn-faq__item">';
		$html .= '<button type="button" class="cn-faq__q"><span>' . esc_html( $q ) . '</span><span class="cn-faq__q-icon">+</span></button>';
		$html .= '<div class="cn-faq__a"><div class="cn-faq__a-inner">' . wp_kses_post( $a ) . '</div></div>';
		$html .= '</li>';
	}
	$html .= '</ul></div></section>';
	return $html;
}

/**
 * Schema.org FAQPage — coletado dos campos das seções FAQ da página atual.
 */
add_action(
	'wp_head',
	function () {
		if ( ! is_page() ) {
			return;
		}
		$slug     = get_post_field( 'post_name', get_the_ID() );
		$sections = isset( $GLOBALS['lahr_page_sections'][ $slug ] ) ? $GLOBALS['lahr_page_sections'][ $slug ] : array();
		$qas      = array();

		foreach ( $sections as $sec ) {
			if ( 'faq' !== ( $sec['type'] ?? '' ) ) {
				continue;
			}
			$itens = (array) lahr_field( $sec['key'] . '_itens', array() );
			foreach ( $itens as $it ) {
				$q = isset( $it['pergunta'] ) ? trim( wp_strip_all_tags( $it['pergunta'] ) ) : '';
				$a = isset( $it['resposta'] ) ? trim( wp_strip_all_tags( $it['resposta'] ) ) : '';
				if ( '' === $q || '' === $a ) {
					continue;
				}
				$qas[] = array(
					'@type'          => 'Question',
					'name'           => $q,
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => $a,
					),
				);
			}
		}

		if ( empty( $qas ) ) {
			return;
		}
		$schema = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $qas,
		);
		echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
	},
	20
);
