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
    wp_enqueue_style( 'lahr-editorial', get_stylesheet_uri(), array(), $theme->get( 'Version' ) );
    wp_enqueue_script( 'lahr-editorial-js', get_theme_file_uri( '/assets/js/main.js' ), array(), $theme->get( 'Version' ), true );
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
