<?php
/**
 * Seção HERO SLIDER (Home) — repetidor de slides foto/vídeo.
 * 1 slide → renderiza estático (idêntico ao hero original), sem controles/JS.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function lahr_hero_slide_html( $slide, $eager ) {
	$tipo    = isset( $slide['tipo_midia'] ) ? $slide['tipo_midia'] : 'foto';
	$img     = isset( $slide['imagem'] ) ? $slide['imagem'] : '';
	$video   = isset( $slide['video'] ) ? $slide['video'] : '';
	$poster  = isset( $slide['poster'] ) ? $slide['poster'] : '';
	$eyebrow = isset( $slide['eyebrow'] ) ? $slide['eyebrow'] : '';
	$titulo  = isset( $slide['titulo'] ) ? $slide['titulo'] : '';
	$lede    = isset( $slide['lede'] ) ? $slide['lede'] : '';
	$ctas    = isset( $slide['ctas'] ) ? (array) $slide['ctas'] : array();

	$html  = '<div class="cn-hero__slide">';
	$html .= '<div class="cn-hero__split">';
	$html .= '<div class="cn-hero__text">';
	if ( '' !== $eyebrow ) {
		$html .= '<span class="cn-hero__eyebrow">' . esc_html( $eyebrow ) . '</span>';
	}
	$html .= '<h1 class="cn-hero__title">' . lahr_rich( $titulo ) . '</h1>';
	if ( '' !== $lede ) {
		$html .= '<p class="cn-hero__lede">' . lahr_rich( $lede ) . '</p>';
	}
	if ( $ctas ) {
		$html .= '<div class="cn-hero__ctas">';
		foreach ( $ctas as $c ) {
			$txt   = isset( $c['texto'] ) ? $c['texto'] : '';
			$url   = isset( $c['url'] ) ? $c['url'] : '';
			$est   = isset( $c['estilo'] ) ? $c['estilo'] : 'gold';
			$html .= lahr_btn_html( $txt, $url, $est, ( 'ghost' === $est ) );
		}
		$html .= '</div>';
	}
	$html .= '</div>'; // .cn-hero__text

	$html .= '<div class="cn-hero__media cn-hero__media--bw">';
	if ( 'video' === $tipo && $video ) {
		$vurl   = wp_get_attachment_url( $video );
		$type   = ( substr( (string) $vurl, -5 ) === '.webm' ) ? 'video/webm' : 'video/mp4';
		$purl   = lahr_img_url( $poster );
		$pattr  = $purl ? ' poster="' . esc_url( $purl ) . '"' : '';
		$html  .= '<video class="cn-hero__video" autoplay muted loop playsinline preload="metadata"' . $pattr . ' aria-hidden="true"><source src="' . esc_url( $vurl ) . '" type="' . esc_attr( $type ) . '"></video>';
	} else {
		$html .= lahr_img( $img, array( 'eager' => $eager, 'alt' => wp_strip_all_tags( $titulo ) ) );
	}
	$html .= '<div class="cn-hero__media-tint" aria-hidden="true"></div>';
	$html .= '</div>'; // .cn-hero__media

	$html .= '</div>'; // .cn-hero__split
	$html .= '</div>'; // .cn-hero__slide
	return $html;
}

function lahr_section_hero_slider( $sec ) {
	$p        = $sec['key'];
	$slides   = (array) lahr_field( "{$p}_slides", array() );
	$autoplay = (bool) lahr_field( "{$p}_autoplay", 0 );
	$interval = (int) lahr_field( "{$p}_intervalo", 6 );
	$count    = count( $slides );

	if ( 0 === $count ) {
		return '';
	}

	$section_atts = ' data-cursor-glow';
	if ( $count > 1 ) {
		$section_atts .= ' data-lahr-slider';
		if ( $autoplay ) {
			$section_atts .= ' data-autoplay="1" data-interval="' . esc_attr( $interval ) . '"';
		}
	}

	$html  = '<section class="cn-hero cn-hero--split' . ( $count > 1 ? ' cn-hero--slider' : '' ) . '"' . $section_atts . '>';
	$html .= '<div class="cn-hero__cursor-glow" data-glow></div>';
	$html .= '<div class="cn-hero__glow-1"></div>';
	$html .= '<div class="cn-hero__glow-2"></div>';

	$html .= '<div class="cn-hero__track">';
	foreach ( $slides as $i => $slide ) {
		$html .= lahr_hero_slide_html( $slide, 0 === $i );
	}
	$html .= '</div>';

	if ( $count > 1 ) {
		$html .= '<div class="cn-hero__arrows" aria-hidden="false">';
		$html .= '<button type="button" class="cn-hero__arrow cn-hero__arrow--prev" aria-label="Slide anterior" data-lahr-prev>‹</button>';
		$html .= '<button type="button" class="cn-hero__arrow cn-hero__arrow--next" aria-label="Próximo slide" data-lahr-next>›</button>';
		$html .= '</div>';
		$html .= '<div class="cn-hero__dots" role="tablist" aria-label="Slides">';
		for ( $i = 0; $i < $count; $i++ ) {
			$sel = 0 === $i ? ' is-active" aria-selected="true' : '" aria-selected="false';
			$html .= '<button type="button" class="cn-hero__dot' . $sel . '" role="tab" aria-label="Slide ' . ( $i + 1 ) . '" data-lahr-dot="' . $i . '"></button>';
		}
		$html .= '</div>';
	}

	$html .= '<div class="cn-hero__scroll" aria-hidden="true"><span>Scroll</span><span class="cn-hero__scroll-line"></span></div>';
	$html .= '</section>';
	return $html;
}
