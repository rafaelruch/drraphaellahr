<?php
/**
 * Google Reviews — leitor do JSON cacheado.
 *
 * Em produção, esse JSON é regerado por cron usando a Google Places API.
 * Localmente, foi extraído via Playwright. Veja docs/google-reviews-setup.md.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Retorna os dados de avaliações do Google.
 *
 * @return array { place_id, name, overall_rating, total_reviews, reviews[] }
 */
function lahr_get_google_reviews() {
    static $cache = null;
    if ( $cache !== null ) return $cache;

    $file = get_theme_file_path( '/data/google-reviews.json' );
    if ( ! file_exists( $file ) ) {
        $cache = array( 'reviews' => array(), 'overall_rating' => 5.0, 'total_reviews' => 0 );
        return $cache;
    }
    $raw = file_get_contents( $file );
    $data = json_decode( $raw, true );
    $cache = is_array( $data ) ? $data : array( 'reviews' => array() );
    return $cache;
}

/**
 * Renderiza a seção de depoimentos com carrossel.
 */
function lahr_render_google_reviews_section() {
    $data = lahr_get_google_reviews();
    if ( empty( $data['reviews'] ) ) return;

    $rating       = number_format( $data['overall_rating'] ?? 5, 1, ',', '' );
    $total        = (int) ( $data['total_reviews'] ?? count( $data['reviews'] ) );
    $place_url    = 'https://g.page/r/CY7ZhAcvc3gaEAE/review';
    $reviews_url  = 'https://www.google.com/maps/place/Dr.+Raphael+Lahr+%7C+Urologista+em+Florian%C3%B3polis';

    ob_start();
    ?>
    <section class="cn-testimonials cn-reviews" aria-label="Depoimentos de pacientes">
        <div class="cn-container">
            <header class="cn-sec-head cn-reviews__head">
                <span class="cn-sec-head__eyebrow">05 — Depoimentos · Google</span>
                <h2 class="cn-sec-head__title">Sobre o atendimento e o <em>resultado</em>.</h2>
                <div class="cn-reviews__meta">
                    <span class="cn-reviews__stars" aria-label="<?php echo esc_attr( $rating ); ?> de 5 estrelas">
                        <?php for ( $i = 0; $i < 5; $i++ ) : ?>
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <?php endfor; ?>
                    </span>
                    <span class="cn-reviews__rating"><?php echo esc_html( $rating ); ?></span>
                    <span class="cn-reviews__divider"></span>
                    <a class="cn-reviews__count" href="<?php echo esc_url( $reviews_url ); ?>" target="_blank" rel="noopener">
                        Baseado em <strong><?php echo esc_html( $total ); ?> avaliações no Google</strong>
                    </a>
                </div>
            </header>

            <div class="cn-carousel" data-carousel>
                <div class="cn-carousel__viewport" data-carousel-viewport>
                    <ul class="cn-carousel__track" data-carousel-track>
                        <?php foreach ( $data['reviews'] as $i => $r ) :
                            $stars  = (int) ( $r['stars'] ?? 5 );
                            $avatar = ! empty( $r['avatar_local'] ) ? $r['avatar_local'] : ( $r['avatar'] ?? '' );
                            $initial = mb_strtoupper( mb_substr( $r['name'] ?? 'P', 0, 1, 'UTF-8' ), 'UTF-8' );
                        ?>
                            <li class="cn-carousel__slide">
                                <article class="cn-review">
                                    <div class="cn-review__stars" aria-label="<?php echo esc_attr( $stars ); ?> de 5">
                                        <?php for ( $s = 0; $s < 5; $s++ ) : ?>
                                            <svg viewBox="0 0 24 24" fill="currentColor" class="<?php echo $s < $stars ? 'is-filled' : ''; ?>" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        <?php endfor; ?>
                                    </div>
                                    <blockquote class="cn-review__quote">
                                        <?php echo esc_html( $r['text'] ); ?>
                                    </blockquote>
                                    <footer class="cn-review__author">
                                        <span class="cn-review__avatar">
                                            <?php if ( $avatar ) : ?>
                                                <img src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $r['name'] ); ?>" loading="lazy" width="48" height="48">
                                            <?php else : ?>
                                                <span class="cn-review__initial"><?php echo esc_html( $initial ); ?></span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="cn-review__meta">
                                            <strong class="cn-review__name"><?php echo esc_html( $r['name'] ); ?></strong>
                                            <span class="cn-review__date"><?php echo esc_html( $r['date'] ); ?> · Google</span>
                                        </span>
                                        <span class="cn-review__google" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" width="20" height="20"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                                        </span>
                                    </footer>
                                </article>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="cn-carousel__controls">
                    <button class="cn-carousel__btn" type="button" data-carousel-prev aria-label="Anterior">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 18l-6-6 6-6"/></svg>
                    </button>
                    <div class="cn-carousel__dots" data-carousel-dots></div>
                    <button class="cn-carousel__btn" type="button" data-carousel-next aria-label="Próximo">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 18l6-6-6-6"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <?php // JSON-LD AggregateRating + Reviews ?>
        <script type="application/ld+json">
        <?php
        $ld = array(
            '@context'        => 'https://schema.org',
            '@type'           => 'MedicalBusiness',
            'name'            => 'Dr. Raphael Lahr | Urologista',
            'aggregateRating' => array(
                '@type'       => 'AggregateRating',
                'ratingValue' => $rating,
                'reviewCount' => $total,
                'bestRating'  => 5,
                'worstRating' => 1,
            ),
            'review' => array(),
        );
        foreach ( array_slice( $data['reviews'], 0, 10 ) as $r ) {
            $ld['review'][] = array(
                '@type'        => 'Review',
                'reviewRating' => array(
                    '@type'       => 'Rating',
                    'ratingValue' => (int) ( $r['stars'] ?? 5 ),
                    'bestRating'  => 5,
                ),
                'author'       => array(
                    '@type' => 'Person',
                    'name'  => $r['name'] ?? '',
                ),
                'reviewBody'   => $r['text'] ?? '',
            );
        }
        echo wp_json_encode( $ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
        ?>
        </script>
    </section>
    <?php
    return ob_get_clean();
}

/**
 * Shortcode opcional para inserir a seção manualmente.
 */
add_shortcode( 'lahr_reviews', function () {
    return lahr_render_google_reviews_section();
} );
