<?php
/**
 * Painel de Leads — gráfico por período + contagens por canal, formulário,
 * interesse, cidade, campanha (UTM) e "como encontrou"; exportação CSV.
 *
 * Submenu "📊 Painel" dentro de Leads. Uma consulta por carregamento (posts do
 * período, meta cache primado pelo WP_Query) e agregação em PHP. Gráfico em SVG
 * gerado no servidor — sem biblioteca externa.
 *
 * @package Lahr_Editorial
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'LAHR_LEADS_DASH_SLUG', 'lahr-leads-painel' );
define( 'LAHR_LEADS_DASH_MAX', 10000 ); // teto de segurança por consulta

/* ------------------------------------------------------------------ Menu */
add_action(
	'admin_menu',
	function () {
		add_submenu_page(
			'edit.php?post_type=lahr_lead',
			'Painel de Leads',
			'📊 Painel',
			'edit_posts',
			LAHR_LEADS_DASH_SLUG,
			'lahr_leads_dash_page',
			0
		);
	}
);

/* --------------------------------------------------------------- Período */
/**
 * Lê o período da query string.
 *
 * @return array { key, start (DateTimeImmutable|null), end (DateTimeImmutable), de, ate }
 */
function lahr_leads_dash_period() {
	$tz   = wp_timezone();
	$hoje = new DateTimeImmutable( 'today', $tz );
	$key  = isset( $_GET['periodo'] ) ? sanitize_key( wp_unslash( $_GET['periodo'] ) ) : '30';
	$de   = isset( $_GET['de'] ) ? sanitize_text_field( wp_unslash( $_GET['de'] ) ) : '';
	$ate  = isset( $_GET['ate'] ) ? sanitize_text_field( wp_unslash( $_GET['ate'] ) ) : '';
	$re   = '/^\d{4}-\d{2}-\d{2}$/';

	if ( preg_match( $re, $de ) && preg_match( $re, $ate ) ) {
		$s = DateTimeImmutable::createFromFormat( '!Y-m-d', $de, $tz );
		$e = DateTimeImmutable::createFromFormat( '!Y-m-d', $ate, $tz );
		if ( $s && $e ) {
			if ( $s > $e ) {
				$t = $s; $s = $e; $e = $t;
			}
			return array( 'key' => 'custom', 'start' => $s, 'end' => $e, 'de' => $s->format( 'Y-m-d' ), 'ate' => $e->format( 'Y-m-d' ) );
		}
	}
	if ( 'tudo' === $key ) {
		return array( 'key' => 'tudo', 'start' => null, 'end' => $hoje, 'de' => '', 'ate' => '' );
	}
	$days = in_array( $key, array( '7', '30', '90', '365' ), true ) ? (int) $key : 30;
	return array( 'key' => (string) $days, 'start' => $hoje->modify( '-' . ( $days - 1 ) . ' days' ), 'end' => $hoje, 'de' => '', 'ate' => '' );
}

/* --------------------------------------------------------------- Consulta */
/** Posts de lead do período (meta cache primado; sem contagem total). */
function lahr_leads_dash_fetch( array $per ) {
	$args = array(
		'post_type'              => 'lahr_lead',
		'post_status'            => 'publish',
		'posts_per_page'         => LAHR_LEADS_DASH_MAX,
		'orderby'                => 'date',
		'order'                  => 'ASC',
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
		'ignore_sticky_posts'    => true,
	);
	if ( $per['start'] ) {
		$args['date_query'] = array(
			array(
				'after'     => $per['start']->format( 'Y-m-d 00:00:00' ),
				'before'    => $per['end']->format( 'Y-m-d 23:59:59' ),
				'inclusive' => true,
			),
		);
	}
	$q = new WP_Query( $args );
	return $q->posts;
}

/* ------------------------------------------------------------- Agregação */
function lahr_leads_dash_aggregate( array $posts ) {
	$agg = array(
		'total'     => count( $posts ),
		'dias'      => array(),
		'canal'     => array(),
		'form'      => array(),
		'interesse' => array(),
		'cidade'    => array(),
		'campanha'  => array(),
		'encontrou' => array(),
	);
	$inc = static function ( array &$a, $k, $vazio ) {
		$k = trim( (string) $k );
		if ( '' === $k ) {
			$k = $vazio;
		}
		$a[ $k ] = ( $a[ $k ] ?? 0 ) + 1;
	};
	foreach ( $posts as $p ) {
		$id  = $p->ID;
		$day = substr( $p->post_date, 0, 10 );
		$agg['dias'][ $day ] = ( $agg['dias'][ $day ] ?? 0 ) + 1;
		$inc( $agg['canal'], lahr_lead_get_canal( $id ), 'Direto / Orgânico' );
		$inc( $agg['form'], lahr_lead_form_label( get_post_meta( $id, 'origem_form', true ) ), 'Site' );
		$inc( $agg['interesse'], get_post_meta( $id, 'interesse', true ), '(não informado)' );
		$inc( $agg['cidade'], get_post_meta( $id, 'cidade', true ), '(não informada)' );
		$inc( $agg['campanha'], get_post_meta( $id, 'utm_campaign', true ), '(sem campanha)' );
		$inc( $agg['encontrou'], get_post_meta( $id, 'origem', true ), '(não informado)' );
	}
	return $agg;
}

/** Baldes do gráfico: dia (≤92 dias), semana (≤400) ou mês. */
function lahr_leads_dash_buckets( DateTimeImmutable $start, DateTimeImmutable $end, array $dias ) {
	$tz    = wp_timezone();
	$ndays = (int) $start->diff( $end )->days + 1;
	$gran  = $ndays <= 92 ? 'dia' : ( $ndays <= 400 ? 'semana' : 'mes' );
	$b     = array();
	if ( 'dia' === $gran ) {
		for ( $d = $start; $d <= $end; $d = $d->modify( '+1 day' ) ) {
			$b[ $d->format( 'Y-m-d' ) ] = array( 'label' => $d->format( 'd/m' ), 'n' => 0 );
		}
	} elseif ( 'semana' === $gran ) {
		for ( $d = $start->modify( 'monday this week' ); $d <= $end; $d = $d->modify( '+7 days' ) ) {
			$b[ $d->format( 'Y-m-d' ) ] = array( 'label' => $d->format( 'd/m' ), 'n' => 0 );
		}
	} else {
		for ( $d = $start->modify( 'first day of this month' ); $d <= $end; $d = $d->modify( '+1 month' ) ) {
			$b[ $d->format( 'Y-m' ) ] = array( 'label' => wp_date( 'M/y', $d->getTimestamp() ), 'n' => 0 );
		}
	}
	foreach ( $dias as $day => $n ) {
		$d = DateTimeImmutable::createFromFormat( '!Y-m-d', $day, $tz );
		if ( ! $d ) {
			continue;
		}
		$k = 'dia' === $gran ? $day : ( 'semana' === $gran ? $d->modify( 'monday this week' )->format( 'Y-m-d' ) : $d->format( 'Y-m' ) );
		if ( isset( $b[ $k ] ) ) {
			$b[ $k ]['n'] += $n;
		}
	}
	return array( $gran, $b );
}

/* ---------------------------------------------------------------- Gráfico */
function lahr_leads_dash_svg( array $b ) {
	$n = count( $b );
	if ( ! $n ) {
		return '';
	}
	$W = 1000; $H = 280; $padL = 44; $padR = 12; $padT = 18; $padB = 36;
	$max   = max( 1, max( array_column( $b, 'n' ) ) );
	$steps = 4;
	$nice  = (int) max( $steps, ceil( $max / $steps ) * $steps );
	$plotW = $W - $padL - $padR;
	$plotH = $H - $padT - $padB;
	$slot  = $plotW / $n;
	$barW  = max( 2, $slot * ( $n > 60 ? 0.72 : 0.6 ) );
	$every = max( 1, (int) ceil( $n / 12 ) );

	$s = '<svg class="lahr-dash__svg" viewBox="0 0 ' . $W . ' ' . $H . '" role="img" aria-label="Leads por período">';
	for ( $i = 0; $i <= $steps; $i++ ) {
		$y = $padT + $plotH - ( $plotH * $i / $steps );
		$s .= '<line x1="' . $padL . '" x2="' . ( $W - $padR ) . '" y1="' . round( $y, 1 ) . '" y2="' . round( $y, 1 ) . '" stroke="#f0f0f1"/>';
		$s .= '<text x="' . ( $padL - 8 ) . '" y="' . round( $y + 4, 1 ) . '" text-anchor="end" font-size="11" fill="#646970">' . (int) ( $nice * $i / $steps ) . '</text>';
	}
	$i = 0;
	foreach ( $b as $bk ) {
		$x = $padL + $i * $slot + ( $slot - $barW ) / 2;
		$h = $plotH * $bk['n'] / $nice;
		$y = $padT + $plotH - $h;
		$s .= '<g><title>' . esc_html( $bk['label'] ) . ': ' . (int) $bk['n'] . ' lead' . ( 1 === (int) $bk['n'] ? '' : 's' ) . '</title>';
		$s .= '<rect class="bar" x="' . round( $x, 1 ) . '" y="' . round( $y, 1 ) . '" width="' . round( $barW, 1 ) . '" height="' . round( $h, 1 ) . '" rx="2"/>';
		if ( $bk['n'] > 0 && $n <= 40 ) {
			$s .= '<text x="' . round( $x + $barW / 2, 1 ) . '" y="' . round( $y - 5, 1 ) . '" text-anchor="middle" font-size="11" fill="#1d2327">' . (int) $bk['n'] . '</text>';
		}
		if ( 0 === $i % $every ) {
			$s .= '<text x="' . round( $x + $barW / 2, 1 ) . '" y="' . ( $H - $padB + 18 ) . '" text-anchor="middle" font-size="11" fill="#646970">' . esc_html( $bk['label'] ) . '</text>';
		}
		$s .= '</g>';
		$i++;
	}
	return $s . '</svg>';
}

/* ---------------------------------------------------------------- Tabelas */
function lahr_leads_dash_color( $k ) {
	$map = array(
		'Google Ads'         => '#4285F4',
		'Meta Ads'           => '#E1306C',
		'Direto / Orgânico'  => '#8c8f94',
		'Formulário Agendar' => '#b8955a',
		'Botão de WhatsApp'  => '#25D366',
	);
	return $map[ $k ] ?? '#b8955a';
}

function lahr_leads_dash_table( $titulo, array $counts, $total, $limit = 10, $nota = '' ) {
	echo '<div class="lahr-dash__card"><h2>' . esc_html( $titulo ) . '</h2>';
	if ( ! $counts ) {
		echo '<p class="lahr-dash__empty">Sem dados no período.</p></div>';
		return;
	}
	arsort( $counts );
	$rows   = array_slice( $counts, 0, $limit, true );
	$outros = array_sum( $counts ) - array_sum( $rows );
	echo '<table class="lahr-dash__table">';
	$row = static function ( $k, $n ) use ( $total ) {
		$pct = $total ? $n / $total * 100 : 0;
		echo '<tr><td class="k">' . esc_html( $k ) . '</td><td class="n">' . esc_html( number_format_i18n( $n ) ) . '</td><td class="p">' . esc_html( number_format_i18n( $pct, 1 ) ) . '%</td>'
			. '<td class="b"><span style="width:' . esc_attr( round( $pct, 1 ) ) . '%;background:' . esc_attr( lahr_leads_dash_color( $k ) ) . '"></span></td></tr>';
	};
	foreach ( $rows as $k => $n ) {
		$row( $k, $n );
	}
	if ( $outros > 0 ) {
		$row( 'Outros', $outros );
	}
	echo '</table>';
	if ( $nota ) {
		echo '<p class="lahr-dash__nota">' . wp_kses_post( $nota ) . '</p>';
	}
	echo '</div>';
}

/* --------------------------------------------------------------- Página */
function lahr_leads_dash_page() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( 'Sem permissão.' );
	}
	$tz    = wp_timezone();
	$per   = lahr_leads_dash_period();
	$posts = lahr_leads_dash_fetch( $per );
	if ( ! $per['start'] ) { // "tudo": começa no primeiro lead
		$first        = $posts ? DateTimeImmutable::createFromFormat( '!Y-m-d', substr( $posts[0]->post_date, 0, 10 ), $tz ) : null;
		$per['start'] = $first ? $first : $per['end'];
	}
	$agg   = lahr_leads_dash_aggregate( $posts );
	list( $gran, $b ) = lahr_leads_dash_buckets( $per['start'], $per['end'], $agg['dias'] );
	$ndays = (int) $per['start']->diff( $per['end'] )->days + 1;
	$media = $agg['total'] / max( 1, $ndays );
	$base  = admin_url( 'edit.php?post_type=lahr_lead&page=' . LAHR_LEADS_DASH_SLUG );
	$csv   = wp_nonce_url(
		add_query_arg( array_filter( array( 'action' => 'lahr_leads_csv', 'periodo' => $per['key'], 'de' => $per['de'], 'ate' => $per['ate'] ) ), admin_url( 'admin-post.php' ) ),
		'lahr_leads_csv'
	);
	$presets = array( '7' => '7 dias', '30' => '30 dias', '90' => '90 dias', '365' => '12 meses', 'tudo' => 'Tudo' );
	$gran_lbl = array( 'dia' => 'por dia', 'semana' => 'por semana', 'mes' => 'por mês' );
	$top_canal = $agg['canal'] ? array_search( max( $agg['canal'] ), $agg['canal'], true ) : '—';
	?>
	<style>
	.lahr-dash{max-width:1280px}
	.lahr-dash__bar{display:flex;flex-wrap:wrap;gap:10px 18px;align-items:center;margin:12px 0 20px}
	.lahr-dash__presets a{display:inline-block;padding:6px 13px;border:1px solid #c3c4c7;border-radius:999px;background:#fff;text-decoration:none;color:#1d2327;margin-right:4px}
	.lahr-dash__presets a.is-active{background:#1d2327;color:#fff;border-color:#1d2327}
	.lahr-dash__range{display:flex;gap:6px;align-items:center}
	.lahr-dash__range input[type=date]{height:30px}
	.lahr-dash__periodo{color:#646970;margin:0 0 14px}
	.lahr-dash__kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:18px}
	.lahr-dash__kpi{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:16px 18px}
	.lahr-dash__kpi b{display:block;font-size:30px;line-height:1.1;font-weight:600;color:#1d2327;margin-bottom:6px}
	.lahr-dash__kpi span{color:#646970;font-size:12px;text-transform:uppercase;letter-spacing:.04em}
	.lahr-dash__card{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:18px 20px;margin-bottom:18px}
	.lahr-dash__card h2{margin:0 0 12px;font-size:15px}
	.lahr-dash__grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(360px,1fr));gap:18px}
	.lahr-dash__grid .lahr-dash__card{margin:0}
	.lahr-dash__svg{width:100%;height:auto;display:block}
	.lahr-dash__svg rect.bar{fill:#b8955a}
	.lahr-dash__svg g:hover rect.bar{fill:#8f6f3a}
	.lahr-dash__table{width:100%;border-collapse:collapse}
	.lahr-dash__table td{padding:7px 6px;border-bottom:1px solid #f0f0f1;vertical-align:middle}
	.lahr-dash__table td.n{text-align:right;font-weight:600;white-space:nowrap}
	.lahr-dash__table td.p{text-align:right;color:#646970;white-space:nowrap;width:60px}
	.lahr-dash__table td.b{width:34%}
	.lahr-dash__table td.b span{display:block;height:8px;border-radius:4px;min-width:2px}
	.lahr-dash__nota{color:#646970;font-size:12px;margin:10px 0 0}
	.lahr-dash__empty{color:#646970;margin:0}
	</style>
	<div class="wrap lahr-dash">
		<h1>Painel de Leads</h1>

		<div class="lahr-dash__bar">
			<span class="lahr-dash__presets">
				<?php foreach ( $presets as $k => $lbl ) : ?>
					<a href="<?php echo esc_url( $base . '&periodo=' . $k ); ?>" class="<?php echo $per['key'] === (string) $k ? 'is-active' : ''; ?>"><?php echo esc_html( $lbl ); ?></a>
				<?php endforeach; ?>
			</span>
			<form method="get" action="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>" class="lahr-dash__range">
				<input type="hidden" name="post_type" value="lahr_lead">
				<input type="hidden" name="page" value="<?php echo esc_attr( LAHR_LEADS_DASH_SLUG ); ?>">
				<input type="date" name="de" value="<?php echo esc_attr( $per['de'] ); ?>" aria-label="De">
				<span>até</span>
				<input type="date" name="ate" value="<?php echo esc_attr( $per['ate'] ); ?>" aria-label="Até">
				<button class="button">Aplicar</button>
			</form>
			<a class="button button-primary" href="<?php echo esc_url( $csv ); ?>">Exportar CSV</a>
		</div>

		<p class="lahr-dash__periodo">
			<?php echo esc_html( $per['start']->format( 'd/m/Y' ) . ' – ' . $per['end']->format( 'd/m/Y' ) . ' · ' . $ndays . ' dia' . ( 1 === $ndays ? '' : 's' ) ); ?>
		</p>

		<div class="lahr-dash__kpis">
			<div class="lahr-dash__kpi"><b><?php echo esc_html( number_format_i18n( $agg['total'] ) ); ?></b><span>Leads no período</span></div>
			<div class="lahr-dash__kpi"><b><?php echo esc_html( number_format_i18n( $media, 1 ) ); ?></b><span>Média por dia</span></div>
			<div class="lahr-dash__kpi"><b><?php echo esc_html( number_format_i18n( $agg['form']['Formulário Agendar'] ?? 0 ) ); ?></b><span>Formulário Agendar</span></div>
			<div class="lahr-dash__kpi"><b><?php echo esc_html( number_format_i18n( $agg['form']['Botão de WhatsApp'] ?? 0 ) ); ?></b><span>Botão de WhatsApp</span></div>
			<div class="lahr-dash__kpi"><b style="font-size:20px;padding-top:6px"><?php echo esc_html( $top_canal ); ?></b><span>Principal canal</span></div>
		</div>

		<div class="lahr-dash__card">
			<h2>Leads <?php echo esc_html( $gran_lbl[ $gran ] ); ?></h2>
			<?php if ( $agg['total'] ) : ?>
				<?php echo lahr_leads_dash_svg( $b ); // phpcs:ignore WordPress.Security.EscapeOutput -- SVG gerado internamente, valores escapados. ?>
			<?php else : ?>
				<p class="lahr-dash__empty">Nenhum lead no período.</p>
			<?php endif; ?>
		</div>

		<div class="lahr-dash__grid">
			<?php
			lahr_leads_dash_table(
				'Por canal (origem do tráfego)',
				$agg['canal'],
				$agg['total'],
				10,
				'<strong>Google Ads</strong> = gclid ou utm_source=google · <strong>Meta Ads</strong> = fbclid ou utm_source=facebook/instagram · outro utm_source = nome da fonte · sem sinal = <strong>Direto / Orgânico</strong>.'
			);
			lahr_leads_dash_table( 'Por formulário', $agg['form'], $agg['total'] );
			lahr_leads_dash_table( 'Por interesse', $agg['interesse'], $agg['total'] );
			lahr_leads_dash_table( 'Por cidade', $agg['cidade'], $agg['total'] );
			lahr_leads_dash_table( 'Por campanha (utm_campaign)', $agg['campanha'], $agg['total'] );
			lahr_leads_dash_table( 'Como encontrou (declarado no formulário)', $agg['encontrou'], $agg['total'] );
			?>
		</div>
	</div>
	<?php
}

/* ------------------------------------------------------------------ CSV */
function lahr_leads_dash_csv_safe( $v ) {
	$v = (string) $v;
	return ( '' !== $v && strpbrk( $v[0], '=+-@' ) !== false ) ? "'" . $v : $v; // evita injeção de fórmula no Excel
}
add_action(
	'admin_post_lahr_leads_csv',
	function () {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( 'Sem permissão.' );
		}
		check_admin_referer( 'lahr_leads_csv' );
		$per   = lahr_leads_dash_period();
		$posts = lahr_leads_dash_fetch( $per );
		$ini   = $per['start'] ? $per['start']->format( 'Y-m-d' ) : 'inicio';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="leads-' . $ini . '_' . $per['end']->format( 'Y-m-d' ) . '.csv"' );
		$out = fopen( 'php://output', 'w' );
		fwrite( $out, "\xEF\xBB\xBF" ); // BOM → Excel abre em UTF-8
		fputcsv( $out, array( 'Recebido em', 'Nome', 'WhatsApp', 'E-mail', 'Cidade', 'Interesse', 'Canal', 'Formulário', 'Como encontrou', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid', 'Mensagem' ), ';' );
		foreach ( $posts as $p ) {
			$m = static function ( $k ) use ( $p ) {
				return lahr_leads_dash_csv_safe( get_post_meta( $p->ID, $k, true ) );
			};
			fputcsv(
				$out,
				array(
					$p->post_date,
					$m( 'nome' ) ?: lahr_leads_dash_csv_safe( $p->post_title ),
					$m( 'whatsapp' ), $m( 'email' ), $m( 'cidade' ), $m( 'interesse' ),
					lahr_lead_get_canal( $p->ID ),
					lahr_lead_form_label( get_post_meta( $p->ID, 'origem_form', true ) ),
					$m( 'origem' ), $m( 'utm_source' ), $m( 'utm_medium' ), $m( 'utm_campaign' ), $m( 'utm_term' ), $m( 'utm_content' ),
					$m( 'gclid' ), $m( 'fbclid' ), $m( 'mensagem' ),
				),
				';'
			);
		}
		fclose( $out );
		exit;
	}
);

/* ------------------------------------------------- Backfill do canal (1x) */
add_action(
	'admin_init',
	function () {
		if ( get_option( 'lahr_leads_canal_backfill' ) ) {
			return;
		}
		$ids = get_posts(
			array(
				'post_type'   => 'lahr_lead',
				'post_status' => 'any',
				'numberposts' => 2000,
				'fields'      => 'ids',
				'meta_query'  => array( array( 'key' => 'canal', 'compare' => 'NOT EXISTS' ) ),
			)
		);
		if ( $ids ) {
			update_meta_cache( 'post', $ids );
			foreach ( $ids as $id ) {
				update_post_meta( $id, 'canal', lahr_lead_get_canal( $id ) );
			}
		}
		update_option( 'lahr_leads_canal_backfill', 1, false );
	}
);
