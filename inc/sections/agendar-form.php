<?php
/**
 * Seção AGENDAR-FORM — painel lateral editável + formulário fixo.
 * O formulário é funcional (main.js: máscara, validação, envio ao WhatsApp,
 * redirecionamento para /obrigado/) — sua estrutura é mantida.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function lahr_section_agendar_form( $sec ) {
	$p       = $sec['key'];
	$eyebrow = lahr_field( "{$p}_eyebrow" );
	$titulo  = lahr_field( "{$p}_titulo" );
	$intro   = lahr_field( "{$p}_intro" );
	$trust   = (array) lahr_field( "{$p}_trust", array() );

	ob_start();
	?>
<section class="cn-section" style="padding-top: var(--s-6);">
    <div class="cn-form-grid">
        <aside class="cn-form-side">
            <span class="cn-split__eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
            <h2><?php echo lahr_rich( $titulo ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
            <p><?php echo esc_html( $intro ); ?></p>
            <ul class="cn-trust-list">
				<?php foreach ( $trust as $t ) : ?>
                <li><?php echo esc_html( isset( $t['texto'] ) ? $t['texto'] : '' ); ?></li>
				<?php endforeach; ?>
            </ul>
        </aside>

        <form class="cn-form" id="lahr-agendar" novalidate data-track-form="agendar">
            <div class="cn-form__field">
                <label for="f-nome">Nome completo <span class="req">*</span></label>
                <input type="text" id="f-nome" name="nome" required minlength="3" autocomplete="name" placeholder="Como podemos te chamar?">
            </div>

            <div class="cn-form__row">
                <div class="cn-form__field">
                    <label for="f-whatsapp">WhatsApp <span class="req">*</span></label>
                    <input type="tel" id="f-whatsapp" name="whatsapp" required inputmode="tel" autocomplete="tel" placeholder="(00) 00000-0000" data-lm-mask="phone">
                </div>
                <div class="cn-form__field">
                    <label for="f-email">E-mail (opcional)</label>
                    <input type="email" id="f-email" name="email" autocomplete="email" placeholder="seu@email.com">
                </div>
            </div>

            <div class="cn-form__field">
                <label for="f-cidade">Cidade de origem <span class="req">*</span></label>
                <input type="text" id="f-cidade" name="cidade" required placeholder="Florianópolis, Joinville, Curitiba…">
            </div>

            <div class="cn-form__field">
                <label for="f-interesse">Área de interesse <span class="req">*</span></label>
                <select id="f-interesse" name="interesse" required>
                    <option value="">— Selecione —</option>
                    <option value="Harmonização Peniana">Harmonização Peniana</option>
                    <option value="Correção de Harmonização">Correção de Harmonização (PMMA / outro)</option>
                    <option value="Prótese Peniana">Prótese Peniana</option>
                    <option value="Cirurgia Robótica">Cirurgia Robótica (Próstata / Rim / Bexiga)</option>
                    <option value="Disfunção Erétil">Disfunção Erétil</option>
                    <option value="Reposição Hormonal">Reposição Hormonal (Testosterona)</option>
                    <option value="Vasectomia">Vasectomia</option>
                    <option value="Reversão de Vasectomia">Reversão de Vasectomia</option>
                    <option value="Check-up Masculino">Check-up Masculino</option>
                    <option value="Outros">Outros</option>
                </select>
            </div>

            <div class="cn-form__field">
                <label for="f-origem">Como nos encontrou? (opcional)</label>
                <select id="f-origem" name="origem">
                    <option value="">— Selecione —</option>
                    <option value="Google">Google</option>
                    <option value="Instagram">Instagram</option>
                    <option value="Indicação de paciente">Indicação de paciente</option>
                    <option value="Indicação de outro médico">Indicação de outro médico</option>
                    <option value="YouTube">YouTube</option>
                    <option value="Outros">Outros</option>
                </select>
            </div>

            <div class="cn-form__field">
                <label for="f-mensagem">Mensagem (opcional)</label>
                <textarea id="f-mensagem" name="mensagem" rows="4" maxlength="500" placeholder="Pode falar com tranquilidade. Tudo aqui é sigiloso."></textarea>
                <span class="cn-form__hint" data-char-counter>0 / 500</span>
            </div>

            <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;height:0;width:0;opacity:0;" aria-hidden="true">

            <input type="hidden" name="utm_source">
            <input type="hidden" name="utm_medium">
            <input type="hidden" name="utm_campaign">
            <input type="hidden" name="utm_term">
            <input type="hidden" name="utm_content">

            <button type="submit" class="cn-btn cn-btn--gold cn-form__submit">Solicitar Avaliação →</button>

            <p class="cn-form__legal">
                Ao enviar, você concorda com nossa <a href="/politica-de-privacidade/">Política de Privacidade</a>
                e autoriza o contato pelos meios informados.
            </p>
        </form>
    </div>
</section>
	<?php
	return ob_get_clean();
}
