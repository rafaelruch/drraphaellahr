# Atualizar um tema WordPress pelo botão "Atualizar" (via GitHub Releases)

Guia reutilizável. Explica como fazer **qualquer tema próprio** aparecer na tela
de *Aparência → Temas / Painel → Atualizações* com "Atualização disponível" e ser
atualizado pelo **botão Atualizar** do WordPress — sem plugin pago, sem FTP.

> Implementado no tema `lahr-editorial` (repo público `rafaelruch/drraphaellahr`).
> Para adaptar a outro tema, só há **2 constantes** para trocar (veja o checklist).

---

## 1. Como funciona (visão geral)

O WordPress já sabe atualizar temas — ele só precisa que **alguém diga** que
existe versão nova e **onde baixar o .zip**. O core faz isso via WordPress.org;
para um tema próprio, nós mesmos "injetamos" essa informação por um filtro.

```
GitHub Release (tag vX.Y.Z + zip)  ──►  filtro no tema lê a API do GitHub
        ▲                                        │
        │ você publica com bin/release.sh        ▼
   git push + gh release create        WP mostra "Atualização disponível"
                                                 │  cliente clica em "Atualizar"
                                                 ▼
                                 WP baixa o zip do release e reinstala o tema
```

Três peças fazem tudo:

| Peça | Papel |
|------|-------|
| Cabeçalhos do `style.css` | `Version:` (o WP compara com a do release) e `Update URI:` |
| `inc/theme-updater.php` | injeta a atualização + corrige o nome da pasta do zip |
| `bin/release.sh` | bumpa a versão, faz commit/push e cria o GitHub Release |

Requisitos: **repositório público** (sem token) e **GitHub CLI** (`gh`) autenticado
na máquina de quem publica. O cliente não precisa de nada — só clicar em Atualizar.

---

## 2. Cabeçalhos obrigatórios no `style.css`

```css
/*
Theme Name: Lahr Cinematic
Version: 4.1.4
Update URI: https://github.com/rafaelruch/drraphaellahr
Text Domain: lahr-cinematic
*/
```

- **`Version:`** é o que o WP compara com a tag do release. A tag `v4.1.4`
  precisa ser **maior** que a `Version:` instalada para aparecer atualização.
- **`Update URI:`** evita que o WordPress.org "sequestre" o slug do tema
  (boa prática desde o WP 5.8).

---

## 3. O arquivo `inc/theme-updater.php` (copiável)

Inclua com `require_once get_theme_file_path('/inc/theme-updater.php');` no
`functions.php`. **Troque apenas** `LAHR_UPDATE_SLUG` (pasta do tema) e
`LAHR_UPDATE_REPO` (`usuario/repo`).

```php
<?php
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'LAHR_UPDATE_SLUG', 'lahr-editorial' );        // <- pasta do tema
define( 'LAHR_UPDATE_REPO', 'rafaelruch/drraphaellahr' ); // <- usuario/repo público

/** Lê o último Release do GitHub (cache de 6h para não bater na API toda hora). */
function lahr_update_latest_release() {
	$cache = get_transient( 'lahr_update_release' );
	if ( false !== $cache ) return $cache;

	$resp = wp_remote_get(
		'https://api.github.com/repos/' . LAHR_UPDATE_REPO . '/releases/latest',
		array( 'timeout' => 15, 'headers' => array(
			'Accept' => 'application/vnd.github+json',
			'User-Agent' => 'lahr-editorial-updater',
		) )
	);
	if ( is_wp_error( $resp ) || 200 !== (int) wp_remote_retrieve_response_code( $resp ) ) {
		set_transient( 'lahr_update_release', array(), 2 * HOUR_IN_SECONDS );
		return array();
	}
	$d   = json_decode( wp_remote_retrieve_body( $resp ), true );
	$tag = isset( $d['tag_name'] ) ? $d['tag_name'] : '';
	if ( '' === $tag ) {
		set_transient( 'lahr_update_release', array(), 2 * HOUR_IN_SECONDS );
		return array();
	}
	$info = array(
		'version' => ltrim( $tag, 'vV' ),
		'package' => 'https://github.com/' . LAHR_UPDATE_REPO . '/archive/refs/tags/' . rawurlencode( $tag ) . '.zip',
		'url'     => isset( $d['html_url'] ) ? $d['html_url'] : '',
		'notes'   => isset( $d['body'] ) ? $d['body'] : '',
	);
	set_transient( 'lahr_update_release', $info, 6 * HOUR_IN_SECONDS );
	return $info;
}

/** Injeta a atualização no transient de temas do WordPress. */
add_filter( 'pre_set_site_transient_update_themes', function ( $transient ) {
	if ( ! is_object( $transient ) ) return $transient;
	$theme     = wp_get_theme( LAHR_UPDATE_SLUG );
	$installed = $theme->exists() ? $theme->get( 'Version' ) : '0';
	$rel       = lahr_update_latest_release();
	if ( empty( $rel['version'] ) || empty( $rel['package'] ) ) return $transient;

	if ( version_compare( $rel['version'], $installed, '>' ) ) {
		$transient->response[ LAHR_UPDATE_SLUG ] = array(
			'theme' => LAHR_UPDATE_SLUG, 'new_version' => $rel['version'],
			'url' => $rel['url'], 'package' => $rel['package'],
		);
	} else {
		unset( $transient->response[ LAHR_UPDATE_SLUG ] );
		$transient->no_update[ LAHR_UPDATE_SLUG ] = array(
			'theme' => LAHR_UPDATE_SLUG, 'new_version' => $installed,
			'url' => 'https://github.com/' . LAHR_UPDATE_REPO, 'package' => '',
		);
	}
	return $transient;
} );

/**
 * O zip do GitHub extrai para "REPO-<tag>/" (ex.: drraphaellahr-4.1.4/).
 * Renomeia a pasta de origem para o SLUG do tema antes de instalar —
 * senão o WP criaria um tema novo com nome errado.
 */
add_filter( 'upgrader_source_selection', function ( $source, $remote_source, $upgrader, $args = array() ) {
	global $wp_filesystem;
	$is_our_theme = ( isset( $args['theme'] ) && LAHR_UPDATE_SLUG === $args['theme'] )
		|| ( false !== strpos( (string) $source, 'drraphaellahr-' ) ); // <- prefixo do repo
	if ( ! $is_our_theme ) return $source;
	$desired = trailingslashit( $remote_source ) . LAHR_UPDATE_SLUG;
	if ( untrailingslashit( $source ) === untrailingslashit( $desired ) ) return $source;
	if ( $wp_filesystem && $wp_filesystem->move( $source, $desired, true ) ) {
		return trailingslashit( $desired );
	}
	return $source;
}, 10, 4 );

/** "Verificar novamente" (force-check) busca o release na hora.
 *  PRIORIDADE 1 é obrigatória: o core roda wp_update_themes() neste mesmo hook
 *  na prioridade 10 — o cache tem de ser limpo antes, senão só aparece na 2ª clicada. */
add_action( 'load-update-core.php', function () {
	if ( ! empty( $_GET['force-check'] ) ) delete_transient( 'lahr_update_release' );
}, 1 );

/** Limpa o cache após concluir uma atualização. */
add_action( 'upgrader_process_complete', function () {
	delete_transient( 'lahr_update_release' );
} );
```

⚠️ **Ponto de atenção ao adaptar:** no filtro `upgrader_source_selection` há um
`strpos( $source, 'drraphaellahr-' )`. Esse prefixo é o **nome do repositório**
(o zip do GitHub sempre extrai para `<repo>-<tag>/`). Troque `drraphaellahr-`
pelo nome do **seu** repo. Se não trocar, a renomeação da pasta não acontece e o
tema é instalado com o nome errado.

---

## 4. Publicar uma nova versão — `bin/release.sh`

```sh
#!/bin/sh
set -e
VER="$1"; NOTES="${2:-Nova versão $1}"; REPO="rafaelruch/drraphaellahr"
[ -z "$VER" ] && { echo "Uso: ./bin/release.sh 4.1.5 \"notas\""; exit 1; }
cd "$(dirname "$0")/.."
sed -i '' "s/^Version: .*/Version: $VER/" style.css   # bump no cabeçalho
git add style.css
git commit -m "release: v$VER — $NOTES"
git push origin main
gh release create "v$VER" -R "$REPO" --title "v$VER" --notes "$NOTES"
```

Uso: `./bin/release.sh 4.1.5 "O que mudou"`.

### Cuidado com o hook `post-commit` (auto-push)

Este repo tem um `post-commit` que **empurra cada commit em segundo plano**. Isso
cria uma **corrida**: quando o `release.sh` chega no seu próprio `git push`, o hook
já empurrou, e o push do script falha (`failed to push some refs`). Com `set -e`,
o script aborta **antes** do `gh release create`.

Duas formas de lidar:

1. **Manual e controlada** (recomendada quando há vários arquivos no release):
   ```sh
   # bump manual da Version no style.css, depois:
   git add -A && git commit -m "..."      # o hook empurra sozinho
   sleep 3 && git fetch origin            # confirmar que local == origin/main
   git tag vX.Y.Z <sha> && git push origin vX.Y.Z
   gh release create vX.Y.Z --verify-tag --title vX.Y.Z --notes "..."
   ```
2. **Só o release.sh**: se ele abortar no push, é benigno — o commit já subiu pelo
   hook. Basta rodar o `gh release create` na mão depois.

> **Regra de ouro:** a **tag** do release precisa apontar para um commit que já
> contenha **todos** os arquivos alterados. O `release.sh` original só faz
> `git add style.css` — se você mudou outros arquivos, **commite-os antes**,
> senão o zip do release sai sem as mudanças.

---

## 5. Como o cliente atualiza (produção)

1. *Painel → Atualizações* (ou *Aparência → Temas*).
2. Se não aparecer na hora, clicar em **"Verificar novamente"** (o cache é de 6h;
   o link chama `update-core.php?force-check=1`, que zera o cache do tema).
   > Armadilha real: o hook que zera o cache precisa ter **prioridade 1** (o core
   > checa temas na prioridade 10 do mesmo hook). Sem isso, só aparece na 2ª clicada.
3. Marcar o tema em **Temas** e clicar em **Atualizar temas**.
4. Pronto — o WP baixa o zip do release e reinstala.

---

## 6. O que **NÃO** vem no update (importante)

O update troca **arquivos do tema (código)**. Ele **não** traz:

- **Conteúdo/config em ACF** (campos, banners, cards) — isso mora no **banco** de
  cada ambiente. Local e produção divergem; conteúdo se edita no **admin de produção**.
- Uploads da Biblioteca de Mídia.
- `wp-config.php` e segredos (ex.: tokens) — nunca versionados.

Ou seja: **código sobe por release; conteúdo se edita em produção**.

---

## 7. Checklist para aplicar em um tema novo

1. `style.css`: definir `Version:` e `Update URI: https://github.com/<user>/<repo>`.
2. Copiar `inc/theme-updater.php` e trocar:
   - `LAHR_UPDATE_SLUG` → **pasta** do novo tema;
   - `LAHR_UPDATE_REPO` → `<user>/<repo>` (público);
   - o prefixo `drraphaellahr-` no `upgrader_source_selection` → `<repo>-`.
3. `require_once` do arquivo no `functions.php`.
4. Repo público no GitHub; `gh auth login` na máquina que publica.
5. (Opcional) copiar `bin/release.sh` e ajustar `REPO`.
6. Publicar o primeiro release: `./bin/release.sh 1.0.1 "primeira versão auto-update"`.
7. Testar: instalar uma versão anterior e conferir se aparece "Atualização disponível".

Renomear as funções/prefixos `lahr_*` é opcional (evita colisão se dois temas com
este updater conviverem no mesmo site — raro em produção).

---

## Referência rápida

- **API usada:** `GET https://api.github.com/repos/<repo>/releases/latest`
- **Zip do pacote:** `https://github.com/<repo>/archive/refs/tags/<tag>.zip`
- **Cache:** transient `lahr_update_release`, 6h (2h em caso de erro); zerado por
  force-check e ao concluir um update.
- **Comparação de versão:** `version_compare( tag_sem_v, Version_instalada, '>' )`.
