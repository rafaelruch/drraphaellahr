<?php
/**
 * Lahr — header, footer e widget WhatsApp dinâmicos.
 *
 * Blocos dinâmicos (server-rendered) que reproduzem exatamente o HTML original
 * de parts/header.html e parts/footer.html, lendo os valores das Configurações
 * do Site (CPT lahr_config). Inseridos nos template-parts do tema de blocos.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registra os blocos dinâmicos do tema.
 */
add_action(
	'init',
	function () {
		register_block_type(
			'lahr/site-header',
			array(
				'render_callback' => 'lahr_render_site_header',
				'supports'        => array( 'html' => false ),
			)
		);
		register_block_type(
			'lahr/site-footer',
			array(
				'render_callback' => 'lahr_render_site_footer',
				'supports'        => array( 'html' => false ),
			)
		);
	}
);

/**
 * Header.
 *
 * @return string
 */
function lahr_render_site_header() {
	$logo = lahr_img_url( lahr_opt( 'logo_header', 21 ) );
	if ( ! $logo ) {
		$logo = content_url( '/uploads/2026/05/logo-dr-raphael.png' );
	}
	$nav_main   = (array) lahr_opt( 'nav_principal', array() );
	$nav_mobile = (array) lahr_opt( 'nav_mobile', array() );

	ob_start();
	?>
<header class="cn-header">
    <div class="cn-header__inner">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="cn-brand" aria-label="Dr. Raphael Lahr — Início">
            <img src="<?php echo esc_url( $logo ); ?>" alt="Dr. Raphael Lahr" class="cn-brand__logo" width="260" height="70">
        </a>
        <nav class="cn-nav" aria-label="Menu principal">
			<?php foreach ( $nav_main as $item ) : ?>
				<?php $cta = ! empty( $item['is_cta'] ) ? ' class="cn-nav__cta"' : ''; ?>
            <a href="<?php echo esc_url( $item['url'] ); ?>"<?php echo $cta; // phpcs:ignore ?>><?php echo esc_html( $item['label'] ); ?></a>
			<?php endforeach; ?>
        </nav>
        <button class="cn-mobile-toggle" aria-label="Menu" data-lm-mobile-open>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="3" y1="7" x2="21" y2="7"/><line x1="3" y1="17" x2="21" y2="17"/></svg>
        </button>
    </div>
    <div class="cn-mobile-menu" data-lm-mobile-menu>
        <button class="cn-mobile-menu__close" aria-label="Fechar" data-lm-mobile-close>×</button>
		<?php foreach ( $nav_mobile as $item ) : ?>
        <a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
		<?php endforeach; ?>
    </div>
</header>
	<?php
	return ob_get_clean();
}

/**
 * Footer + widget WhatsApp.
 *
 * @return string
 */
function lahr_render_site_footer() {
	$logo = lahr_img_url( lahr_opt( 'logo_footer', 21 ) );
	if ( ! $logo ) {
		$logo = content_url( '/uploads/2026/05/logo-dr-raphael.png' );
	}
	$crm       = lahr_opt( 'crm_rqe', 'CRM-SC 15336 · RQE 12374' );
	$texto     = lahr_opt( 'footer_texto', '' );
	$proc_tit  = lahr_opt( 'footer_proc_titulo', 'Procedimentos' );
	$inst_tit  = lahr_opt( 'footer_inst_titulo', 'Institucional' );
	$cont_tit  = lahr_opt( 'footer_contato_titulo', 'Contato' );
	$proc      = (array) lahr_opt( 'footer_proc', array() );
	$inst      = (array) lahr_opt( 'footer_inst', array() );
	$tel       = lahr_opt( 'telefone', '(47) 99970-1100' );
	$wa_num    = lahr_opt( 'whatsapp_num', '5547999701100' );
	$ig_user   = lahr_opt( 'instagram_user', '@drraphaellahrurgologista' );
	$ig_url    = lahr_opt( 'instagram_url', 'https://instagram.com/drraphaellahrurgologista' );
	$endereco  = lahr_opt( 'endereco', 'Jurerê — Florianópolis/SC' );
	$copyright = lahr_opt( 'copyright', '© 2026 Dr. Raphael Lahr' );
	$assinatura = lahr_opt( 'assinatura', 'Desenvolvido por RUCH Digital' );

	// Widget.
	$wa_nome    = lahr_opt( 'wa_nome', 'Dr. Raphael Lahr' );
	$wa_status  = lahr_opt( 'wa_status', 'Atendimento agora' );
	$wa_bolha   = lahr_opt( 'wa_bolha', '' );
	$wa_int     = (array) lahr_opt( 'wa_interesses', array() );
	$wa_priv    = lahr_opt( 'wa_privacidade', 'Atendimento sigiloso. Não pedimos fotos nem documentos.' );
	$bolha_lines = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $wa_bolha ) ) );

	ob_start();
	?>
<footer class="cn-footer">
    <div class="cn-footer__grid">
        <div class="cn-footer__brand">
            <img src="<?php echo esc_url( $logo ); ?>" alt="Dr. Raphael Lahr" loading="lazy">
            <p class="cn-footer__crm"><?php echo esc_html( $crm ); ?></p>
            <p><?php echo esc_html( $texto ); ?></p>
        </div>
        <div>
            <h4><?php echo esc_html( $proc_tit ); ?></h4>
            <ul>
				<?php foreach ( $proc as $item ) : ?>
                <li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a></li>
				<?php endforeach; ?>
            </ul>
        </div>
        <div>
            <h4><?php echo esc_html( $inst_tit ); ?></h4>
            <ul>
				<?php foreach ( $inst as $item ) : ?>
                <li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a></li>
				<?php endforeach; ?>
            </ul>
        </div>
        <div>
            <h4><?php echo esc_html( $cont_tit ); ?></h4>
            <ul>
                <li><a href="https://wa.me/<?php echo esc_attr( $wa_num ); ?>" rel="noopener" target="_blank"><?php echo esc_html( $tel ); ?></a></li>
                <li><a href="<?php echo esc_url( $ig_url ); ?>" rel="noopener" target="_blank"><?php echo esc_html( $ig_user ); ?></a></li>
                <li><?php echo esc_html( $endereco ); ?></li>
            </ul>
        </div>
    </div>
    <div class="cn-footer__bottom">
        <span><?php echo esc_html( $copyright ); ?></span>
        <span><?php echo wp_kses( $assinatura, array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) ) ); ?></span>
    </div>
</footer>

<div class="lm-wa">
    <button type="button" class="lm-wa__trigger" aria-label="Chat" aria-expanded="false" data-lm-wa-open>
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.464 3.488"/></svg>
        <span class="lm-wa__badge">1</span>
    </button>
    <div class="lm-wa__panel" role="dialog" data-lm-wa-panel>
        <header class="lm-wa__head">
            <div class="lm-wa__avatar">Rl</div>
            <div class="lm-wa__head-text">
                <p class="lm-wa__name"><?php echo esc_html( $wa_nome ); ?></p>
                <p class="lm-wa__status"><span class="lm-wa__dot"></span> <?php echo esc_html( $wa_status ); ?></p>
            </div>
            <button type="button" class="lm-wa__close" aria-label="Fechar" data-lm-wa-close>×</button>
        </header>
        <div class="lm-wa__body">
            <div class="lm-wa__bubble">
				<?php foreach ( $bolha_lines as $line ) : ?>
                <p><?php echo lahr_rich( $line ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endforeach; ?>
                <span class="lm-wa__time">agora</span>
            </div>
            <form class="lm-wa__form" data-lm-wa-form novalidate>
                <label class="lm-wa__field">
                    <span>Seu nome</span>
                    <input type="text" name="nome" required minlength="3" autocomplete="name">
                </label>
                <label class="lm-wa__field">
                    <span>WhatsApp</span>
                    <input type="tel" name="telefone" required inputmode="tel" autocomplete="tel" placeholder="(00) 00000-0000" data-lm-mask="phone">
                </label>
                <label class="lm-wa__field">
                    <span>Sobre o que gostaria de conversar?</span>
                    <select name="interesse">
                        <option value="">— Selecione (opcional) —</option>
						<?php foreach ( $wa_int as $opt ) : ?>
                        <option value="<?php echo esc_attr( $opt['valor'] ); ?>"><?php echo esc_html( $opt['valor'] ); ?></option>
						<?php endforeach; ?>
                    </select>
                </label>
                <button type="submit" class="lm-wa__submit">Conversar no WhatsApp →</button>
                <p class="lm-wa__privacy"><?php echo esc_html( $wa_priv ); ?></p>
            </form>
        </div>
    </div>
</div>
	<?php
	return ob_get_clean();
}
