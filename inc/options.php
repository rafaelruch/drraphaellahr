<?php
/**
 * Lahr — Configurações globais do site.
 *
 * ACF free não possui Options Page (recurso Pro). Usamos um CPT privado
 * `lahr_config` com uma única entrada, ao qual anexamos o grupo de campos
 * globais (marca, contato, navegação, rodapé, widget WhatsApp). Isso preserva
 * a UI de repetidores do ACF sem depender do Pro.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registra o CPT de configuração.
 */
add_action(
	'init',
	function () {
		register_post_type(
			'lahr_config',
			array(
				'labels'              => array(
					'name'          => 'Configurações do Site',
					'singular_name' => 'Configurações do Site',
					'menu_name'     => 'Configurações do Site',
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => false,
				'menu_position'       => 59,
				'menu_icon'           => 'dashicons-admin-settings',
				'supports'            => array( 'title' ),
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'has_archive'         => false,
				'rewrite'             => false,
				'capability_type'     => 'page',
				'map_meta_cap'        => true,
			)
		);
	}
);

/**
 * Garante que exista exatamente uma entrada de configuração e devolve seu ID.
 * O ID é cacheado numa option para evitar query em cada chamada.
 *
 * @return int
 */
function lahr_config_id() {
	static $cached = null;
	if ( null !== $cached ) {
		return $cached;
	}

	$id = (int) get_option( 'lahr_config_id' );
	if ( $id && 'lahr_config' === get_post_type( $id ) && 'trash' !== get_post_status( $id ) ) {
		$cached = $id;
		return $id;
	}

	// Procura uma entrada existente.
	$existing = get_posts(
		array(
			'post_type'        => 'lahr_config',
			'post_status'      => array( 'publish', 'draft', 'private' ),
			'numberposts'      => 1,
			'fields'           => 'ids',
			'suppress_filters' => true,
		)
	);
	if ( ! empty( $existing ) ) {
		$id = (int) $existing[0];
	} else {
		// Cria a entrada única.
		$id = (int) wp_insert_post(
			array(
				'post_type'   => 'lahr_config',
				'post_status' => 'publish',
				'post_title'  => 'Configurações do Site',
			)
		);
	}

	if ( $id ) {
		update_option( 'lahr_config_id', $id, false );
	}
	$cached = $id;
	return $id;
}

// Garante a criação da entrada única no admin.
add_action( 'admin_init', 'lahr_config_id' );

/**
 * Redireciona o menu "Configurações do Site" direto para a edição da entrada única.
 */
add_action(
	'admin_menu',
	function () {
		global $submenu;
		$id = lahr_config_id();
		if ( $id ) {
			// Substitui o "Adicionar novo"/listagem por link direto de edição.
			add_submenu_page(
				'edit.php?post_type=lahr_config',
				'Editar Configurações',
				'Editar Configurações',
				'edit_pages',
				'post.php?post=' . $id . '&action=edit'
			);
		}
	},
	11
);

/**
 * Grupo de campos globais (anexado ao CPT lahr_config).
 */
add_action(
	'acf/init',
	function () {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'      => 'group_lahr_config',
				'title'    => 'Configurações do Site',
				'location' => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'lahr_config',
						),
					),
				),
				'menu_order'            => 0,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'hide_on_screen'        => array( 'the_content' ),
				'fields'                => array(

					// ---------- MARCA ----------
					lahr_f_tab( 'cfg_tab_marca', 'Marca' ),
					lahr_f_image( 'field_lahr_cfg_logo_header', 'logo_header', 'Logo (header)' ),
					lahr_f_image( 'field_lahr_cfg_logo_footer', 'logo_footer', 'Logo (rodapé)' ),
					lahr_f_text( 'field_lahr_cfg_crm_rqe', 'crm_rqe', 'CRM / RQE', 'CRM-SC 15336 · RQE 12374' ),
					lahr_f_textarea( 'field_lahr_cfg_footer_texto', 'footer_texto', 'Texto institucional (rodapé)' ),

					// ---------- CONTATO ----------
					lahr_f_tab( 'cfg_tab_contato', 'Contato' ),
					lahr_f_text( 'field_lahr_cfg_telefone', 'telefone', 'Telefone / WhatsApp (exibição)', '(47) 99970-1100' ),
					lahr_f_text( 'field_lahr_cfg_whatsapp_num', 'whatsapp_num', 'WhatsApp (somente dígitos, com DDI)', '5547999701100' ),
					lahr_f_text( 'field_lahr_cfg_instagram_user', 'instagram_user', 'Instagram (@usuário)', '@drraphaellahrurgologista' ),
					lahr_f_text( 'field_lahr_cfg_instagram_url', 'instagram_url', 'Instagram (URL)', 'https://instagram.com/drraphaellahrurgologista' ),
					lahr_f_text( 'field_lahr_cfg_endereco', 'endereco', 'Endereço', 'Jurerê — Florianópolis/SC' ),
					lahr_f_text( 'field_lahr_cfg_lead_email', 'lead_email', 'E-mail para receber os leads do formulário', '', 'Deixe em branco para usar o e-mail do administrador do site.' ),

					// ---------- NAVEGAÇÃO ----------
					lahr_f_tab( 'cfg_tab_nav', 'Navegação' ),
					lahr_f_link_repeater( 'field_lahr_cfg_nav_principal', 'nav_principal', 'Menu principal (desktop)', true ),
					lahr_f_link_repeater( 'field_lahr_cfg_nav_mobile', 'nav_mobile', 'Menu mobile', false ),

					// ---------- RODAPÉ ----------
					lahr_f_tab( 'cfg_tab_footer', 'Rodapé' ),
					lahr_f_text( 'field_lahr_cfg_footer_proc_titulo', 'footer_proc_titulo', 'Título coluna "Procedimentos"', 'Procedimentos' ),
					lahr_f_link_repeater( 'field_lahr_cfg_footer_proc', 'footer_proc', 'Links — Procedimentos', false ),
					lahr_f_text( 'field_lahr_cfg_footer_inst_titulo', 'footer_inst_titulo', 'Título coluna "Institucional"', 'Institucional' ),
					lahr_f_link_repeater( 'field_lahr_cfg_footer_inst', 'footer_inst', 'Links — Institucional', false ),
					lahr_f_text( 'field_lahr_cfg_footer_contato_titulo', 'footer_contato_titulo', 'Título coluna "Contato"', 'Contato' ),
					lahr_f_text( 'field_lahr_cfg_copyright', 'copyright', 'Copyright', '© 2026 Dr. Raphael Lahr' ),
					lahr_f_text( 'field_lahr_cfg_assinatura', 'assinatura', 'Assinatura', 'Desenvolvido por RUCH Digital' ),

					// ---------- WIDGET WHATSAPP ----------
					lahr_f_tab( 'cfg_tab_wa', 'Widget WhatsApp' ),
					lahr_f_text( 'field_lahr_cfg_wa_nome', 'wa_nome', 'Nome exibido', 'Dr. Raphael Lahr' ),
					lahr_f_text( 'field_lahr_cfg_wa_status', 'wa_status', 'Status', 'Atendimento agora' ),
					lahr_f_textarea( 'field_lahr_cfg_wa_bolha', 'wa_bolha', 'Texto da bolha (uma frase por linha)' ),
					array(
						'key'          => 'field_lahr_cfg_wa_interesses',
						'label'        => 'Opções de interesse (select)',
						'name'         => 'wa_interesses',
						'type'         => 'repeater',
						'layout'       => 'table',
						'button_label' => 'Adicionar opção',
						'sub_fields'   => array(
							lahr_f_text( 'field_lahr_cfg_wa_int_valor', 'valor', 'Opção' ),
						),
					),
					lahr_f_text( 'field_lahr_cfg_wa_privacidade', 'wa_privacidade', 'Nota de privacidade', 'Atendimento sigiloso. Não pedimos fotos nem documentos.' ),

					// ---------- HERO (SLIDER) ----------
					lahr_f_tab( 'cfg_tab_hero', 'Hero (slider)' ),
					array(
						'key'          => 'field_lahr_cfg_hero_msg',
						'label'        => 'Banners da Hero',
						'type'         => 'message',
						'message'      => 'As imagens/vídeos e textos dos banners são editados no menu <strong>“Banners (Hero)”</strong>. Aqui você controla só o comportamento do slider.',
					),
					lahr_f_true_false( 'field_lahr_cfg_hero_autoplay', 'hero_autoplay', 'Passar sozinho (autoplay)?', 1 ),
					lahr_f_number( 'field_lahr_cfg_hero_intervalo', 'hero_intervalo', 'Intervalo do autoplay (segundos)', 6 ),
				),
			)
		);
	}
);
