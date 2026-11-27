# Site bilíngue (Português + Inglês)

O tema tem português e inglês nativos, sem plugin. Português é o padrão, e o inglês fica em `/en/`.

## Como editar o inglês

1. Abra a página (ou Configurações do Site, ou um Banner).
2. Na caixa **Idioma / Language**, na lateral, clique em **English**. Na lista de páginas também existe o atalho **Editar em inglês**.
3. Edite os textos e salve. Os campos mostram o texto em inglês atual.

No modo English **só os textos mudam**. Imagens, vídeos, telefone/WhatsApp, IDs de pixel e a **quantidade e ordem** dos itens das listas são compartilhados entre os idiomas. Por isso esses campos aparecem esmaecidos no modo English e devem ser alterados em Português. Ao reordenar ou remover itens em Português, o inglês acompanha automaticamente.

Na caixa **Idioma** de cada página também ficam o **título** e o **endereço** em inglês (ex.: `/en/penile-enhancement/`).

## De onde vem o texto em inglês

Para cada campo, nesta ordem:
1. o texto salvo no modo English;
2. a tradução inicial do tema (`data/i18n-en.php`);
3. o português, para nunca ficar vazio.

Os textos fixos do código (formulário, widget do WhatsApp, e-mail ao paciente, etc.) usam `lahr__( 'texto em português' )`. A tradução fica no mesmo `data/i18n-en.php`.

**Configurações do Site → Traduções (EN)** mostra, por página, o que já foi revisado, o que usa a tradução automática e o que ainda aparece em português.

## Técnico

| Peça | Onde |
|---|---|
| Idioma atual, armazenamento, URLs, hreflang, seletor | `inc/i18n.php` |
| Painel "Traduções (EN)" | `inc/i18n-admin.php` |
| Sitemap das páginas em inglês | `inc/i18n-sitemap.php` (`/wp-sitemap-lahren-1.xml`) |
| Dicionário PT → EN | `data/i18n-en.php` |

- **Armazenamento:** o texto em inglês de um campo ACF `x` fica no meta `_lahr_en_x`, na mesma página, por meio dos filtros `acf/pre_load_value` e `acf/pre_update_value`. Fora do ACF, use `lahr_meta( $id, 'campo' )`.
- **Idioma da requisição:** no site, é a URL `/en/...`. No admin e no AJAX, é o parâmetro `lahr_lang=en`, que o JS envia junto com o lead.
- **Links internos:** nas páginas `/en/`, todo `<a href>` que aponta para uma página em português é convertido para a versão em inglês. Links com `hreflang` ou `data-lahr-keep` ficam como estão.
- **Leads:** gravam `idioma` (`pt`/`en`). A mensagem no grupo do WhatsApp avisa quando o lead veio do site em inglês. O paciente recebe o e-mail no idioma dele, e o consultório continua recebendo em português.
- **Telefone:** no inglês, o formulário aceita formato internacional (sem a máscara brasileira).
