<?php
/**
 * Lahr — aviso no admin se o ACF estiver inativo.
 *
 * O conteúdo das páginas é renderizado a partir de campos ACF; sem o plugin,
 * nada é exibido/editável.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action(
	'admin_notices',
	function () {
		if ( function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}
		echo '<div class="notice notice-error"><p><strong>Lahr:</strong> o plugin <em>Advanced Custom Fields</em> está inativo. O conteúdo das páginas não será exibido nem editável até ativá-lo.</p></div>';
	}
);
