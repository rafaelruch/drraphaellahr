<?php
/**
 * Seção JUDÔ — filosofia com vídeo de fundo, galeria e princípios.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function lahr_section_judo( $sec ) {
	$p        = $sec['key'];
	$video_id = lahr_field( "{$p}_video" );
	$poster   = lahr_field( "{$p}_poster" );
	$eyebrow  = lahr_field( "{$p}_eyebrow" );
	$titulo   = lahr_field( "{$p}_titulo" );
	$lede     = lahr_field( "{$p}_lede" );
	$galeria  = (array) lahr_field( "{$p}_galeria", array() );
	$princ    = (array) lahr_field( "{$p}_princ", array() );

	$video_url  = $video_id ? wp_get_attachment_url( $video_id ) : '';
	$poster_url = lahr_img_url( $poster );

	$html  = '<section class="cn-judo">';
	if ( $video_url ) {
		$type   = ( substr( $video_url, -5 ) === '.webm' ) ? 'video/webm' : 'video/mp4';
		$poster_attr = $poster_url ? ' poster="' . esc_url( $poster_url ) . '"' : '';
		$html .= '<video class="cn-judo__video" autoplay muted loop playsinline preload="metadata"' . $poster_attr . ' aria-hidden="true">';
		$html .= '<source src="' . esc_url( $video_url ) . '" type="' . esc_attr( $type ) . '">';
		$html .= '</video>';
	}
	$html .= '<div class="cn-judo__overlay"></div>';
	$html .= '<div class="cn-judo__inner">';
	$html .= '<header class="cn-judo__head">';
	if ( '' !== $eyebrow ) {
		$html .= '<span class="cn-sec-head__eyebrow">' . esc_html( $eyebrow ) . '</span>';
	}
	$html .= '<h2 class="cn-judo__title">' . lahr_rich( $titulo ) . '</h2>';
	if ( '' !== $lede ) {
		$html .= '<p class="cn-judo__lede">' . lahr_rich( $lede ) . '</p>';
	}
	$html .= '</header>';

	if ( $galeria ) {
		$html .= '<div class="cn-judo__gallery">';
		foreach ( $galeria as $i => $g ) {
			$cls = '0' === (string) $i || 0 === $i ? 'cn-judo__photo cn-judo__photo--lg' : 'cn-judo__photo';
			$img = isset( $g['imagem'] ) ? $g['imagem'] : '';
			$alt = isset( $g['alt'] ) ? $g['alt'] : '';
			$html .= '<figure class="' . esc_attr( $cls ) . '">' . lahr_img( $img, array( 'alt' => $alt ) ) . '</figure>';
		}
		$html .= '</div>';
	}

	if ( $princ ) {
		$html .= '<ul class="cn-judo__principles">';
		foreach ( $princ as $pr ) {
			$k = isset( $pr['kanji'] ) ? $pr['kanji'] : '';
			$l = isset( $pr['label'] ) ? $pr['label'] : '';
			$d = isset( $pr['desc'] ) ? $pr['desc'] : '';
			$html .= '<li><strong>' . esc_html( $k ) . '</strong>';
			$html .= '<span class="cn-judo__principle-label">' . esc_html( $l ) . '</span>';
			$html .= '<span class="cn-judo__principle-desc">' . lahr_rich( $d ) . '</span></li>';
		}
		$html .= '</ul>';
	}

	$html .= '</div></section>';
	return $html;
}
