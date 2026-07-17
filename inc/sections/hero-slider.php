<?php
/**
 * Seção HERO — fundo fixo (vídeo/foto, mídia única editável nas Configurações)
 * com os TEXTOS passando por cima como slides (banners do CPT lahr_banner).
 *
 * O fundo é mostrado por inteiro (object-fit: contain) por padrão; apenas os
 * textos rotacionam. 1 texto → estático (sem controles).
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** Fundo fixo da hero (vídeo ou foto), a partir das Configurações. */
function lahr_hero_bg_html() {
	$tipo    = lahr_opt( 'hero_bg_tipo', 'video' );
	$contain = lahr_opt_bool( 'hero_bg_contain', true );
	$cls     = 'cn-hero__bg ' . ( $contain ? 'cn-hero__bg--contain' : 'cn-hero__bg--cover' );

	if ( 'foto' === $tipo ) {
		$img = lahr_opt( 'hero_bg_imagem' );
		if ( ! $img ) {
			return '<div class="' . esc_attr( $cls ) . '"></div>';
		}
		return '<div class="' . esc_attr( $cls ) . '">' . lahr_img( $img, array( 'eager' => true, 'class' => 'cn-hero__bg-img', 'alt' => '' ) ) . '</div>';
	}

	// Vídeo.
	$mp4    = lahr_opt( 'hero_bg_video' );
	$webm   = lahr_opt( 'hero_bg_video_webm' );
	$poster = lahr_opt( 'hero_bg_poster' );
	if ( ! $mp4 && ! $webm ) {
		return '<div class="' . esc_attr( $cls ) . '"></div>';
	}
	$poster_url = lahr_img_url( $poster );
	$pattr      = $poster_url ? ' poster="' . esc_url( $poster_url ) . '"' : '';

	$sources = '';
	if ( $webm ) {
		$sources .= '<source src="' . esc_url( wp_get_attachment_url( $webm ) ) . '" type="video/webm">';
	}
	if ( $mp4 ) {
		$sources .= '<source src="' . esc_url( wp_get_attachment_url( $mp4 ) ) . '" type="video/mp4">';
	}

	$html  = '<div class="' . esc_attr( $cls ) . '">';
	$html .= '<video class="cn-hero__bg-video" autoplay muted loop playsinline preload="metadata"' . $pattr . ' aria-hidden="true">' . $sources . '</video>';
	$html .= '</div>';
	return $html;
}

/** Um slide de TEXTO (sobre o fundo fixo). */
function lahr_hero_text_slide_html( $slide ) {
	$eyebrow = isset( $slide['eyebrow'] ) ? $slide['eyebrow'] : '';
	$titulo  = isset( $slide['titulo'] ) ? $slide['titulo'] : '';
	$lede    = isset( $slide['lede'] ) ? $slide['lede'] : '';
	$ctas    = isset( $slide['ctas'] ) ? (array) $slide['ctas'] : array();

	$html  = '<div class="cn-hero__slide"><div class="cn-hero__text">';
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
			$txt = isset( $c['texto'] ) ? $c['texto'] : '';
			$url = isset( $c['url'] ) ? $c['url'] : '';
			$est = isset( $c['estilo'] ) ? $c['estilo'] : 'gold';
			$html .= lahr_btn_html( $txt, $url, $est, ( 'ghost' === $est ) );
		}
		$html .= '</div>';
	}
	$html .= '</div></div>';
	return $html;
}

function lahr_section_hero_slider( $sec ) {
	$slides   = function_exists( 'lahr_get_banners' ) ? lahr_get_banners() : array();
	$autoplay = lahr_opt_bool( 'hero_autoplay', true );
	$interval = (int) lahr_opt( 'hero_intervalo', 6 );
	$count    = count( $slides );

	if ( 0 === $count ) {
		return '';
	}

	$atts = '';
	if ( $count > 1 ) {
		$atts .= ' data-lahr-slider';
		if ( $autoplay ) {
			$atts .= ' data-autoplay="1" data-interval="' . esc_attr( $interval ) . '"';
		}
	}

	$fit_class = lahr_opt_bool( 'hero_bg_contain', true ) ? ' cn-hero--contain' : ' cn-hero--cover';

	$html  = '<section class="cn-hero cn-hero--video' . $fit_class . ( $count > 1 ? ' cn-hero--slider' : '' ) . '"' . $atts . '>';
	$html .= lahr_hero_bg_html();
	$html .= '<div class="cn-hero__scrim" aria-hidden="true"></div>';

	$html .= '<div class="cn-hero__textwrap"><div class="cn-hero__track">';
	foreach ( $slides as $slide ) {
		$html .= lahr_hero_text_slide_html( $slide );
	}
	$html .= '</div></div>';

	if ( $count > 1 ) {
		$svg_prev = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>';
		$svg_next = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>';
		$html .= '<div class="cn-hero__arrows" aria-hidden="false">';
		$html .= '<button type="button" class="cn-hero__arrow cn-hero__arrow--prev" aria-label="Texto anterior" data-lahr-prev>' . $svg_prev . '</button>';
		$html .= '<button type="button" class="cn-hero__arrow cn-hero__arrow--next" aria-label="Próximo texto" data-lahr-next>' . $svg_next . '</button>';
		$html .= '</div>';
		$html .= '<div class="cn-hero__dots" role="tablist" aria-label="Textos">';
		for ( $i = 0; $i < $count; $i++ ) {
			$sel = 0 === $i ? ' is-active" aria-selected="true' : '" aria-selected="false';
			$html .= '<button type="button" class="cn-hero__dot' . $sel . '" role="tab" aria-label="Texto ' . ( $i + 1 ) . '" data-lahr-dot="' . $i . '"></button>';
		}
		$html .= '</div>';
	}

	$html .= '<div class="cn-hero__scroll" aria-hidden="true"><span>Scroll</span><span class="cn-hero__scroll-line"></span></div>';
	$html .= '</section>';
	return $html;
}
