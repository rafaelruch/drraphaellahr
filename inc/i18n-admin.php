<?php
/**
 * Painel "Traduções (EN)": mostra, por página, quantos textos estão
 * traduzidos e lista os que ainda aparecem em português no site em inglês.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action(
	'admin_menu',
	function () {
		add_submenu_page(
			'edit.php?post_type=lahr_config',
			'Traduções (EN)',
			'Traduções (EN)',
			'edit_pages',
			'lahr-traducoes',
			'lahr_i18n_admin_page'
		);
	}
);

/**
 * Percorre os campos de um post e classifica cada texto traduzível.
 *
 * @return array{editado:int, automatico:int, faltando:array}
 */
function lahr_i18n_coverage( $post_id ) {
	$res = array( 'editado' => 0, 'automatico' => 0, 'faltando' => array() );
	if ( ! function_exists( 'acf_get_field_groups' ) ) {
		return $res;
	}
	$walk = function ( $fields, $prefix, $labels ) use ( &$walk, &$res, $post_id ) {
		foreach ( (array) $fields as $f ) {
			if ( empty( $f['name'] ) ) {
				continue;
			}
			$name  = $prefix . $f['name'];
			$label = trim( $labels . ' › ' . ( $f['label'] ?? $f['name'] ), ' ›' );
			if ( 'group' === $f['type'] ) {
				$walk( $f['sub_fields'] ?? array(), $name . '_', $label );
				continue;
			}
			if ( 'repeater' === $f['type'] ) {
				$n = (int) get_post_meta( $post_id, $name, true );
				for ( $i = 0; $i < $n; $i++ ) {
					$walk( $f['sub_fields'] ?? array(), $name . '_' . $i . '_', $label . ' #' . ( $i + 1 ) );
				}
				continue;
			}
			if ( ! lahr_i18n_is_translatable( $f ) ) {
				continue;
			}
			$pt = get_post_meta( $post_id, $name, true );
			if ( ! is_string( $pt ) || '' === trim( $pt ) || preg_match( '#^(/|https?://|\#|mailto:|tel:)#', trim( $pt ) ) || preg_match( '#^[\d\s.,+%:/×x~–-]*(min|h|m)?$#u', trim( $pt ) ) ) {
				continue;
			}
			if ( metadata_exists( 'post', $post_id, LAHR_EN_PREFIX . $name ) ) {
				$res['editado']++;
			} elseif ( null !== lahr_i18n_lookup( $pt ) ) {
				$res['automatico']++;
			} else {
				$res['faltando'][] = array( 'campo' => $label, 'pt' => $pt );
			}
		}
	};
	foreach ( acf_get_field_groups( array( 'post_id' => $post_id ) ) as $g ) {
		$walk( acf_get_fields( $g['key'] ), '', '' );
	}
	return $res;
}

function lahr_i18n_admin_page() {
	if ( ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	$posts = get_posts(
		array(
			'post_type'   => lahr_i18n_post_types(),
			'post_status' => 'publish',
			'numberposts' => 200,
			'orderby'     => array( 'post_type' => 'ASC', 'menu_order' => 'ASC', 'title' => 'ASC' ),
		)
	);
	echo '<div class="wrap"><h1>Traduções (EN)</h1>';
	echo '<p>Situação do site em inglês. <strong>Editado</strong> = texto revisado no modo English. <strong>Automático</strong> = tradução inicial do tema (pode ser revisada). <strong>Faltando</strong> = ainda aparece em português no site em inglês — clique em <em>Editar em inglês</em> para traduzir.</p>';
	echo '<table class="widefat striped" style="max-width:1100px"><thead><tr><th>Página / conteúdo</th><th>Editado</th><th>Automático</th><th>Faltando</th><th></th></tr></thead><tbody>';
	$total_missing = 0;
	foreach ( $posts as $p ) {
		$c    = lahr_i18n_coverage( $p->ID );
		$miss = count( $c['faltando'] );
		$total_missing += $miss;
		$edit = admin_url( 'post.php?post=' . $p->ID . '&action=edit&lahr_lang=en' );
		$view = 'page' === $p->post_type && 'publish' === $p->post_status ? ' · <a href="' . esc_url( lahr_i18n_url( $p->ID, 'en' ) ) . '" target="_blank" rel="noopener">ver</a>' : '';
		printf(
			'<tr><td><strong>%s</strong></td><td>%d</td><td>%d</td><td style="%s">%d</td><td><a href="%s">Editar em inglês</a>%s</td></tr>',
			esc_html( get_the_title( $p ) ),
			$c['editado'],
			$c['automatico'],
			$miss ? 'color:#b32d2e;font-weight:700' : 'color:#008a20',
			$miss,
			esc_url( $edit ),
			$view // phpcs:ignore WordPress.Security.EscapeOutput
		);
		if ( $miss ) {
			echo '<tr><td colspan="5" style="padding-left:28px"><ul style="margin:0;list-style:disc">';
			foreach ( $c['faltando'] as $m ) {
				echo '<li><em>' . esc_html( $m['campo'] ) . ':</em> ' . esc_html( wp_trim_words( wp_strip_all_tags( $m['pt'] ), 18 ) ) . '</li>';
			}
			echo '</ul></td></tr>';
		}
	}
	echo '</tbody></table>';
	echo $total_missing ? '' : '<p style="color:#008a20;font-weight:600">Tudo traduzido. Nenhum texto em português aparece no site em inglês.</p>';
	echo '</div>';
}
