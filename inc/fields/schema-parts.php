<?php
/**
 * Lahr — construtores de campos ACF reutilizáveis.
 *
 * Builders genéricos (lahr_f_*) e schemas de tipos de seção (lahr_fields_*).
 * Campos definidos em PHP → 0 query de config, versionável.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* =========================================================================
 * Builders genéricos
 * ====================================================================== */

/** Aba (organiza o painel de edição). */
function lahr_f_tab( $key, $label ) {
	return array(
		'key'       => $key,
		'label'     => $label,
		'type'      => 'tab',
		'placement' => 'top',
	);
}

/** Campo de texto simples. */
function lahr_f_text( $key, $name, $label, $default = '', $instructions = '' ) {
	return array(
		'key'           => $key,
		'label'         => $label,
		'name'          => $name,
		'type'          => 'text',
		'default_value' => $default,
		'instructions'  => $instructions,
	);
}

/** Texto com ênfases inline permitidas (itálico dourado <em>, <strong>). */
function lahr_f_rich( $key, $name, $label, $default = '' ) {
	return array(
		'key'           => $key,
		'label'         => $label,
		'name'          => $name,
		'type'          => 'text',
		'default_value' => $default,
		'instructions'  => 'Use &lt;em&gt;palavra&lt;/em&gt; para destaque dourado e &lt;strong&gt; para negrito.',
	);
}

/** Área de texto. */
function lahr_f_textarea( $key, $name, $label, $default = '', $rows = 3 ) {
	return array(
		'key'           => $key,
		'label'         => $label,
		'name'          => $name,
		'type'          => 'textarea',
		'default_value' => $default,
		'rows'          => $rows,
		'new_lines'     => '',
	);
}

/** Editor visual (WYSIWYG) — para blocos de prosa/FAQ. */
function lahr_f_wysiwyg( $key, $name, $label ) {
	return array(
		'key'          => $key,
		'label'        => $label,
		'name'         => $name,
		'type'         => 'wysiwyg',
		'tabs'         => 'all',
		'toolbar'      => 'basic',
		'media_upload' => 0,
	);
}

/** Imagem (retorna ID do anexo). */
function lahr_f_image( $key, $name, $label, $instructions = '' ) {
	return array(
		'key'           => $key,
		'label'         => $label,
		'name'          => $name,
		'type'          => 'image',
		'return_format' => 'id',
		'preview_size'  => 'medium',
		'library'       => 'all',
		'instructions'  => $instructions,
	);
}

/** Arquivo (retorna ID) — usado para vídeos. */
function lahr_f_file( $key, $name, $label, $mime = 'video/mp4', $instructions = '' ) {
	return array(
		'key'           => $key,
		'label'         => $label,
		'name'          => $name,
		'type'          => 'file',
		'return_format' => 'id',
		'library'       => 'all',
		'mime_types'    => $mime,
		'instructions'  => $instructions,
	);
}

/** Verdadeiro/Falso. */
function lahr_f_true_false( $key, $name, $label, $default = 0, $ui = 1 ) {
	return array(
		'key'           => $key,
		'label'         => $label,
		'name'          => $name,
		'type'          => 'true_false',
		'ui'            => $ui,
		'default_value' => $default,
	);
}

/** Número. */
function lahr_f_number( $key, $name, $label, $default = '', $instructions = '' ) {
	return array(
		'key'           => $key,
		'label'         => $label,
		'name'          => $name,
		'type'          => 'number',
		'default_value' => $default,
		'instructions'  => $instructions,
	);
}

/** Select. */
function lahr_f_select( $key, $name, $label, $choices, $default = '' ) {
	return array(
		'key'           => $key,
		'label'         => $label,
		'name'          => $name,
		'type'          => 'select',
		'choices'       => $choices,
		'default_value' => $default,
		'ui'            => 1,
	);
}

/** Acordeão (separador visual colapsável no painel de edição). */
function lahr_f_accordion( $key, $label, $open = 0 ) {
	return array(
		'key'       => $key,
		'label'     => $label,
		'type'      => 'accordion',
		'open'      => $open,
		'multi_expand' => 1,
	);
}

/** Repetidor de links (label + url [+ is_cta]). Usado em menus/rodapé. */
function lahr_f_link_repeater( $key, $name, $label, $with_cta = false ) {
	$subs = array(
		lahr_f_text( $key . '_label', 'label', 'Texto' ),
		lahr_f_text( $key . '_url', 'url', 'URL' ),
	);
	if ( $with_cta ) {
		$subs[] = lahr_f_true_false( $key . '_cta', 'is_cta', 'Botão destaque (CTA)?', 0 );
	}
	return array(
		'key'          => $key,
		'label'        => $label,
		'name'         => $name,
		'type'         => 'repeater',
		'layout'       => 'table',
		'button_label' => 'Adicionar link',
		'sub_fields'   => $subs,
	);
}

/* =========================================================================
 * Schemas por tipo de seção. Cada função recebe $p (prefixo = key da seção)
 * e devolve os campos ACF (com nomes/keys prefixados) precedidos de um
 * acordeão que rotula a seção no painel de edição.
 * ====================================================================== */

/** Sub-campos de cabeçalho (eyebrow/título/lede) — usados dentro de outros. */
function lahr_sub_sechead( $p ) {
	return array(
		lahr_f_text( "field_lahr_{$p}_eyebrow", "{$p}_eyebrow", 'Eyebrow (texto pequeno)' ),
		lahr_f_rich( "field_lahr_{$p}_titulo", "{$p}_titulo", 'Título' ),
		lahr_f_textarea( "field_lahr_{$p}_lede", "{$p}_lede", 'Subtítulo (lede)' ),
	);
}

/** HERO SLIDER (Home). */
function lahr_fields_hero_slider( $p, $label = 'Hero (slider)' ) {
	return array(
		lahr_f_accordion( "acc_{$p}", $label, 1 ),
		array(
			'key'          => "field_lahr_{$p}_slides",
			'label'        => 'Slides',
			'name'         => "{$p}_slides",
			'type'         => 'repeater',
			'layout'       => 'block',
			'button_label' => 'Adicionar slide',
			'sub_fields'   => array(
				lahr_f_select( "field_lahr_{$p}_s_tipo", 'tipo_midia', 'Tipo de mídia', array( 'foto' => 'Foto', 'video' => 'Vídeo' ), 'foto' ),
				lahr_f_image( "field_lahr_{$p}_s_imagem", 'imagem', 'Imagem (foto)' ),
				lahr_f_file( "field_lahr_{$p}_s_video", 'video', 'Vídeo (mp4/webm)', 'mp4,webm' ),
				lahr_f_image( "field_lahr_{$p}_s_poster", 'poster', 'Poster do vídeo' ),
				lahr_f_text( "field_lahr_{$p}_s_eyebrow", 'eyebrow', 'Eyebrow' ),
				lahr_f_rich( "field_lahr_{$p}_s_titulo", 'titulo', 'Título' ),
				lahr_f_textarea( "field_lahr_{$p}_s_lede", 'lede', 'Texto (lede)' ),
				array(
					'key'          => "field_lahr_{$p}_s_ctas",
					'label'        => 'Botões',
					'name'         => 'ctas',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Adicionar botão',
					'sub_fields'   => array(
						lahr_f_text( "field_lahr_{$p}_s_cta_txt", 'texto', 'Texto' ),
						lahr_f_text( "field_lahr_{$p}_s_cta_url", 'url', 'URL' ),
						lahr_f_select( "field_lahr_{$p}_s_cta_est", 'estilo', 'Estilo', array( 'gold' => 'Dourado', 'ghost' => 'Contorno' ), 'gold' ),
					),
				),
			),
		),
		lahr_f_true_false( "field_lahr_{$p}_autoplay", "{$p}_autoplay", 'Autoplay?', 0 ),
		lahr_f_number( "field_lahr_{$p}_intervalo", "{$p}_intervalo", 'Intervalo do autoplay (segundos)', 6 ),
	);
}

/** HERO de página interna (cn-phero). */
function lahr_fields_hero_page( $p, $label = 'Hero da página' ) {
	return array(
		lahr_f_accordion( "acc_{$p}", $label, 1 ),
		lahr_f_text( "field_lahr_{$p}_crumb", "{$p}_crumb", 'Breadcrumb (nome da página)' ),
		lahr_f_rich( "field_lahr_{$p}_titulo", "{$p}_titulo", 'Título' ),
		lahr_f_textarea( "field_lahr_{$p}_lede", "{$p}_lede", 'Texto (lede)' ),
		array(
			'key'          => "field_lahr_{$p}_meta",
			'label'        => 'Indicadores (meta)',
			'name'         => "{$p}_meta",
			'type'         => 'repeater',
			'layout'       => 'table',
			'button_label' => 'Adicionar indicador',
			'sub_fields'   => array(
				lahr_f_text( "field_lahr_{$p}_meta_v", 'valor', 'Valor (ex.: ~45min)' ),
				lahr_f_text( "field_lahr_{$p}_meta_l", 'rotulo', 'Rótulo (ex.: Procedimento)' ),
			),
		),
	);
}

/** MARQUEE (faixa deslizante). */
function lahr_fields_marquee( $p, $label = 'Faixa deslizante (marquee)' ) {
	return array(
		lahr_f_accordion( "acc_{$p}", $label, 0 ),
		array(
			'key'          => "field_lahr_{$p}_itens",
			'label'        => 'Itens',
			'name'         => "{$p}_itens",
			'type'         => 'repeater',
			'layout'       => 'table',
			'button_label' => 'Adicionar item',
			'sub_fields'   => array(
				lahr_f_text( "field_lahr_{$p}_item", 'texto', 'Texto' ),
			),
		),
	);
}

/** CARDS (grade de cartões com cabeçalho). */
function lahr_fields_cards( $p, $label = 'Cartões' ) {
	$s = array( lahr_f_accordion( "acc_{$p}", $label, 0 ) );
	$s = array_merge( $s, lahr_sub_sechead( $p ) );
	$s[] = array(
		'key'          => "field_lahr_{$p}_cards",
		'label'        => 'Cartões',
		'name'         => "{$p}_cards",
		'type'         => 'repeater',
		'layout'       => 'block',
		'button_label' => 'Adicionar cartão',
		'sub_fields'   => array(
			lahr_f_text( "field_lahr_{$p}_c_num", 'num', 'Número/índice (ex.: 01, i)' ),
			lahr_f_text( "field_lahr_{$p}_c_titulo", 'titulo', 'Título' ),
			lahr_f_textarea( "field_lahr_{$p}_c_desc", 'desc', 'Descrição' ),
			lahr_f_text( "field_lahr_{$p}_c_url", 'url', 'Link do cartão inteiro (opcional)' ),
			lahr_f_text( "field_lahr_{$p}_c_cta", 'cta_label', 'Texto do CTA (opcional)' ),
			lahr_f_text( "field_lahr_{$p}_c_cta_url", 'cta_url', 'Link do CTA interno (opcional)' ),
		),
	);
	return $s;
}

/** SERVICES (grade de serviços da Home). */
function lahr_fields_services( $p, $label = 'Serviços' ) {
	$s = array( lahr_f_accordion( "acc_{$p}", $label, 0 ) );
	$s = array_merge( $s, lahr_sub_sechead( $p ) );
	$s[] = array(
		'key'          => "field_lahr_{$p}_itens",
		'label'        => 'Serviços',
		'name'         => "{$p}_itens",
		'type'         => 'repeater',
		'layout'       => 'block',
		'button_label' => 'Adicionar serviço',
		'sub_fields'   => array(
			lahr_f_text( "field_lahr_{$p}_sv_num", 'num', 'Número (ex.: 01)' ),
			lahr_f_text( "field_lahr_{$p}_sv_titulo", 'titulo', 'Título' ),
			lahr_f_textarea( "field_lahr_{$p}_sv_desc", 'desc', 'Descrição' ),
			lahr_f_text( "field_lahr_{$p}_sv_url", 'url', 'Link' ),
			lahr_f_text( "field_lahr_{$p}_sv_cta", 'cta_label', 'Texto do link', ),
		),
	);
	return $s;
}

/** ABOUT (bloco do médico com foto e credenciais). */
function lahr_fields_about( $p, $label = 'Sobre (bloco com foto)' ) {
	return array(
		lahr_f_accordion( "acc_{$p}", $label, 0 ),
		lahr_f_image( "field_lahr_{$p}_imagem", "{$p}_imagem", 'Foto' ),
		lahr_f_text( "field_lahr_{$p}_eyebrow", "{$p}_eyebrow", 'Eyebrow' ),
		lahr_f_rich( "field_lahr_{$p}_titulo", "{$p}_titulo", 'Título' ),
		lahr_f_wysiwyg( "field_lahr_{$p}_texto", "{$p}_texto", 'Parágrafos' ),
		array(
			'key'          => "field_lahr_{$p}_creds",
			'label'        => 'Credenciais',
			'name'         => "{$p}_creds",
			'type'         => 'repeater',
			'layout'       => 'table',
			'button_label' => 'Adicionar credencial',
			'sub_fields'   => array(
				lahr_f_text( "field_lahr_{$p}_cred_v", 'valor', 'Valor (ex.: 17)' ),
				lahr_f_text( "field_lahr_{$p}_cred_l", 'rotulo', 'Rótulo (ex.: Anos)' ),
				lahr_f_number( "field_lahr_{$p}_cred_c", 'counter', 'Contador animado (opcional)' ),
			),
		),
	);
}

/** JUDÔ (bloco de filosofia com vídeo, galeria e princípios). */
function lahr_fields_judo( $p, $label = 'Judô / Filosofia' ) {
	return array(
		lahr_f_accordion( "acc_{$p}", $label, 0 ),
		lahr_f_file( "field_lahr_{$p}_video", "{$p}_video", 'Vídeo de fundo (mp4)', 'mp4,webm' ),
		lahr_f_image( "field_lahr_{$p}_poster", "{$p}_poster", 'Poster do vídeo' ),
		lahr_f_text( "field_lahr_{$p}_eyebrow", "{$p}_eyebrow", 'Eyebrow' ),
		lahr_f_rich( "field_lahr_{$p}_titulo", "{$p}_titulo", 'Título' ),
		lahr_f_textarea( "field_lahr_{$p}_lede", "{$p}_lede", 'Texto (lede)' ),
		array(
			'key'          => "field_lahr_{$p}_galeria",
			'label'        => 'Galeria (3 fotos)',
			'name'         => "{$p}_galeria",
			'type'         => 'repeater',
			'layout'       => 'table',
			'button_label' => 'Adicionar foto',
			'sub_fields'   => array(
				lahr_f_image( "field_lahr_{$p}_g_img", 'imagem', 'Foto' ),
				lahr_f_text( "field_lahr_{$p}_g_alt", 'alt', 'Descrição (alt)' ),
			),
		),
		array(
			'key'          => "field_lahr_{$p}_princ",
			'label'        => 'Princípios',
			'name'         => "{$p}_princ",
			'type'         => 'repeater',
			'layout'       => 'block',
			'button_label' => 'Adicionar princípio',
			'sub_fields'   => array(
				lahr_f_text( "field_lahr_{$p}_pr_k", 'kanji', 'Kanji' ),
				lahr_f_text( "field_lahr_{$p}_pr_l", 'label', 'Rótulo (ex.: Rei · Respeito)' ),
				lahr_f_textarea( "field_lahr_{$p}_pr_d", 'desc', 'Descrição' ),
			),
		),
	);
}

/** JUDÔ (versão filosofia — página Sobre). */
function lahr_fields_judo_about( $p, $label = 'Judô / Filosofia' ) {
	return array(
		lahr_f_accordion( "acc_{$p}", $label, 0 ),
		lahr_f_text( "field_lahr_{$p}_caption", "{$p}_caption", 'Legenda (caption)' ),
		lahr_f_rich( "field_lahr_{$p}_quote", "{$p}_quote", 'Citação' ),
		lahr_f_text( "field_lahr_{$p}_author", "{$p}_author", 'Autor' ),
		array(
			'key'          => "field_lahr_{$p}_princ",
			'label'        => 'Princípios',
			'name'         => "{$p}_princ",
			'type'         => 'repeater',
			'layout'       => 'block',
			'button_label' => 'Adicionar princípio',
			'sub_fields'   => array(
				lahr_f_text( "field_lahr_{$p}_pr_k", 'kanji', 'Kanji' ),
				lahr_f_textarea( "field_lahr_{$p}_pr_t", 'texto', 'Texto' ),
			),
		),
	);
}

/** GALLERY (grade de imagens com cabeçalho). */
function lahr_fields_gallery( $p, $label = 'Galeria de imagens' ) {
	$s = array( lahr_f_accordion( "acc_{$p}", $label, 0 ) );
	$s = array_merge( $s, lahr_sub_sechead( $p ) );
	$s[] = array(
		'key'          => "field_lahr_{$p}_imgs",
		'label'        => 'Imagens',
		'name'         => "{$p}_imgs",
		'type'         => 'repeater',
		'layout'       => 'table',
		'button_label' => 'Adicionar imagem',
		'sub_fields'   => array(
			lahr_f_image( "field_lahr_{$p}_img", 'imagem', 'Imagem' ),
			lahr_f_text( "field_lahr_{$p}_img_alt", 'alt', 'Descrição (alt)' ),
		),
	);
	return $s;
}

/** QUOTE (citação de destaque). */
function lahr_fields_quote( $p, $label = 'Citação' ) {
	return array(
		lahr_f_accordion( "acc_{$p}", $label, 0 ),
		lahr_f_textarea( "field_lahr_{$p}_texto", "{$p}_texto", 'Citação' ),
		lahr_f_text( "field_lahr_{$p}_cite", "{$p}_cite", 'Assinatura (cite)' ),
	);
}

/** SPLIT (imagem + texto). */
function lahr_fields_split( $p, $label = 'Split (imagem + texto)' ) {
	return array(
		lahr_f_accordion( "acc_{$p}", $label, 0 ),
		lahr_f_text( "field_lahr_{$p}_eyebrow", "{$p}_eyebrow", 'Eyebrow' ),
		lahr_f_rich( "field_lahr_{$p}_titulo", "{$p}_titulo", 'Título' ),
		lahr_f_wysiwyg( "field_lahr_{$p}_texto", "{$p}_texto", 'Texto' ),
		lahr_f_image( "field_lahr_{$p}_imagem", "{$p}_imagem", 'Imagem' ),
	);
}

/** STEPS (etapas numeradas). */
function lahr_fields_steps( $p, $label = 'Etapas' ) {
	$s = array( lahr_f_accordion( "acc_{$p}", $label, 0 ) );
	$s = array_merge( $s, lahr_sub_sechead( $p ) );
	$s[] = array(
		'key'          => "field_lahr_{$p}_itens",
		'label'        => 'Etapas',
		'name'         => "{$p}_itens",
		'type'         => 'repeater',
		'layout'       => 'block',
		'button_label' => 'Adicionar etapa',
		'sub_fields'   => array(
			lahr_f_text( "field_lahr_{$p}_st_num", 'num', 'Número (ex.: 01)' ),
			lahr_f_text( "field_lahr_{$p}_st_titulo", 'titulo', 'Título' ),
			lahr_f_textarea( "field_lahr_{$p}_st_desc", 'desc', 'Descrição' ),
		),
	);
	return $s;
}

/** COMPARE (colunas comparativas flexíveis — 2+ colunas, destaque opcional). */
function lahr_fields_compare( $p, $label = 'Comparação' ) {
	$s = array( lahr_f_accordion( "acc_{$p}", $label, 0 ) );
	$s = array_merge( $s, lahr_sub_sechead( $p ) );
	$s[] = array(
		'key'          => "field_lahr_{$p}_cols",
		'label'        => 'Colunas',
		'name'         => "{$p}_cols",
		'type'         => 'repeater',
		'layout'       => 'block',
		'button_label' => 'Adicionar coluna',
		'sub_fields'   => array(
			lahr_f_select( "field_lahr_{$p}_col_hl", 'destaque', 'Destaque', array( 'none' => 'Nenhum', 'good' => 'Positivo (dourado)', 'bad' => 'Negativo' ), 'none' ),
			lahr_f_text( "field_lahr_{$p}_col_tag", 'tag', 'Tag (ex.: Recomendado)' ),
			lahr_f_text( "field_lahr_{$p}_col_nome", 'nome', 'Nome' ),
			lahr_f_textarea( "field_lahr_{$p}_col_desc", 'descricao', 'Descrição (opcional)' ),
			array(
				'key'          => "field_lahr_{$p}_col_itens",
				'label'        => 'Itens',
				'name'         => 'itens',
				'type'         => 'repeater',
				'layout'       => 'table',
				'button_label' => 'Adicionar item',
				'sub_fields'   => array( lahr_f_text( "field_lahr_{$p}_col_it", 'texto', 'Item' ) ),
			),
		),
	);
	return $s;
}

/** PROSE (bloco de texto rico dentro de container estreito). */
function lahr_fields_prose( $p, $label = 'Texto (prosa)' ) {
	return array(
		lahr_f_accordion( "acc_{$p}", $label, 0 ),
		lahr_f_rich( "field_lahr_{$p}_titulo", "{$p}_titulo", 'Título (opcional)' ),
		lahr_f_wysiwyg( "field_lahr_{$p}_conteudo", "{$p}_conteudo", 'Conteúdo' ),
	);
}

/** SECTION genérica (cabeçalho + conteúdo rico). */
function lahr_fields_section( $p, $label = 'Seção' ) {
	$s = array( lahr_f_accordion( "acc_{$p}", $label, 0 ) );
	$s = array_merge( $s, lahr_sub_sechead( $p ) );
	$s[] = lahr_f_wysiwyg( "field_lahr_{$p}_conteudo", "{$p}_conteudo", 'Conteúdo (opcional)' );
	return $s;
}

/** STATS (números de destaque). */
function lahr_fields_stats( $p, $label = 'Números (stats)' ) {
	return array(
		lahr_f_accordion( "acc_{$p}", $label, 0 ),
		array(
			'key'          => "field_lahr_{$p}_itens",
			'label'        => 'Números',
			'name'         => "{$p}_itens",
			'type'         => 'repeater',
			'layout'       => 'block',
			'button_label' => 'Adicionar número',
			'sub_fields'   => array(
				lahr_f_text( "field_lahr_{$p}_stt_valor", 'valor', 'Valor exibido (ex.: 5,0 / CRM-SC)' ),
				lahr_f_text( "field_lahr_{$p}_stt_label", 'label', 'Rótulo' ),
				lahr_f_number( "field_lahr_{$p}_stt_counter", 'counter', 'Contador animado (número, opcional)' ),
				lahr_f_text( "field_lahr_{$p}_stt_suffix", 'suffix', 'Sufixo (ex.: +) opcional' ),
			),
		),
	);
}

/** SHARP-CTA (bloco de investimento/CTA forte). */
function lahr_fields_sharp_cta( $p, $label = 'CTA (investimento)' ) {
	return array(
		lahr_f_accordion( "acc_{$p}", $label, 0 ),
		lahr_f_rich( "field_lahr_{$p}_titulo", "{$p}_titulo", 'Título' ),
		lahr_f_textarea( "field_lahr_{$p}_lede", "{$p}_lede", 'Texto' ),
		lahr_f_text( "field_lahr_{$p}_cta_label", "{$p}_cta_label", 'Texto do botão' ),
		lahr_f_text( "field_lahr_{$p}_cta_url", "{$p}_cta_url", 'URL do botão' ),
		lahr_f_select( "field_lahr_{$p}_cta_estilo", "{$p}_cta_estilo", 'Estilo do botão', array( 'gold' => 'Dourado', 'ghost' => 'Contorno' ), 'gold' ),
	);
}

/** FAQ (perguntas frequentes). */
function lahr_fields_faq( $p, $label = 'FAQ' ) {
	$s = array( lahr_f_accordion( "acc_{$p}", $label, 0 ) );
	$s = array_merge( $s, lahr_sub_sechead( $p ) );
	$s[] = array(
		'key'          => "field_lahr_{$p}_itens",
		'label'        => 'Perguntas',
		'name'         => "{$p}_itens",
		'type'         => 'repeater',
		'layout'       => 'block',
		'button_label' => 'Adicionar pergunta',
		'sub_fields'   => array(
			lahr_f_text( "field_lahr_{$p}_q", 'pergunta', 'Pergunta' ),
			lahr_f_wysiwyg( "field_lahr_{$p}_a", 'resposta', 'Resposta' ),
		),
	);
	return $s;
}

/** AGENDAR-FORM — painel lateral editável + formulário fixo. */
function lahr_fields_agendar_form( $p, $label = 'Formulário de agendamento' ) {
	return array(
		lahr_f_accordion( "acc_{$p}", $label, 0 ),
		lahr_f_text( "field_lahr_{$p}_eyebrow", "{$p}_eyebrow", 'Eyebrow' ),
		lahr_f_rich( "field_lahr_{$p}_titulo", "{$p}_titulo", 'Título' ),
		lahr_f_textarea( "field_lahr_{$p}_intro", "{$p}_intro", 'Texto introdutório' ),
		array(
			'key'          => "field_lahr_{$p}_trust",
			'label'        => 'Lista de compromissos',
			'name'         => "{$p}_trust",
			'type'         => 'repeater',
			'layout'       => 'table',
			'button_label' => 'Adicionar item',
			'sub_fields'   => array( lahr_f_text( "field_lahr_{$p}_trust_it", 'texto', 'Item' ) ),
		),
	);
}

/** CTA final. */
function lahr_fields_cta( $p, $label = 'CTA final' ) {
	return array(
		lahr_f_accordion( "acc_{$p}", $label, 0 ),
		lahr_f_rich( "field_lahr_{$p}_titulo", "{$p}_titulo", 'Título' ),
		lahr_f_textarea( "field_lahr_{$p}_lede", "{$p}_lede", 'Texto' ),
		lahr_f_text( "field_lahr_{$p}_cta_label", "{$p}_cta_label", 'Texto do botão' ),
		lahr_f_text( "field_lahr_{$p}_cta_url", "{$p}_cta_url", 'URL do botão' ),
	);
}
