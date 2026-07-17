<?php
/**
 * Lahr Editorial — functions.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Theme setup.
 */
add_action( 'after_setup_theme', function () {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'custom-logo' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
    add_theme_support( 'editor-styles' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'align-wide' );
} );

/**
 * Enqueue assets.
 */
add_action( 'wp_enqueue_scripts', function () {
    $theme = wp_get_theme();
    // Versão = data de modificação do arquivo → cache-bust automático a cada mudança.
    $css_ver = @filemtime( get_theme_file_path( '/style.css' ) ) ?: $theme->get( 'Version' );
    $js_ver  = @filemtime( get_theme_file_path( '/assets/js/main.js' ) ) ?: $theme->get( 'Version' );
    wp_enqueue_style( 'lahr-editorial', get_stylesheet_uri(), array(), $css_ver );
    wp_enqueue_script( 'lahr-editorial-js', get_theme_file_uri( '/assets/js/main.js' ), array(), $js_ver, true );

    // Expõe configurações editáveis ao JS (número WhatsApp, página de obrigado).
    $wa_num = function_exists( 'lahr_opt' ) ? lahr_opt( 'whatsapp_num', '5547999701100' ) : '5547999701100';
    wp_localize_script(
        'lahr-editorial-js',
        'LAHR',
        array(
            'waNumber'  => preg_replace( '/\D/', '', (string) $wa_num ),
            'thanksUrl' => home_url( '/obrigado/' ),
            'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
            'leadNonce' => wp_create_nonce( 'lahr_lead' ),
        )
    );

    // Hero slider: enfileira apenas onde há uma seção hero_slider registrada.
    if ( is_page() ) {
        $slug = get_post_field( 'post_name', get_queried_object_id() );
        $secs = isset( $GLOBALS['lahr_page_sections'][ $slug ] ) ? $GLOBALS['lahr_page_sections'][ $slug ] : array();
        foreach ( $secs as $s ) {
            if ( isset( $s['type'] ) && 'hero_slider' === $s['type'] ) {
                $hs_ver = @filemtime( get_theme_file_path( '/assets/js/hero-slider.js' ) ) ?: $theme->get( 'Version' );
                wp_enqueue_script( 'lahr-hero-slider', get_theme_file_uri( '/assets/js/hero-slider.js' ), array(), $hs_ver, true );
                break;
            }
        }
    }
} );

/**
 * Disable default emoji scripts (perf).
 */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

/**
 * Google Reviews loader.
 */
require_once get_theme_file_path( '/inc/google-reviews.php' );

/**
 * Lahr — editabilidade por campos (ACF).
 */
require_once get_theme_file_path( '/inc/helpers.php' );
require_once get_theme_file_path( '/inc/acf-check.php' );
require_once get_theme_file_path( '/inc/options.php' );
require_once get_theme_file_path( '/inc/header-footer.php' );
require_once get_theme_file_path( '/inc/banners.php' );
require_once get_theme_file_path( '/inc/leads.php' );
require_once get_theme_file_path( '/inc/render.php' );
