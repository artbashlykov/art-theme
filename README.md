# ART Theme

Простая тема WordPress без сайдбара, направленная на увеличение скорости загрузки сайта (>95 PageSpeed), оптимизированная под мобильные устройства. Шапка, подвал, блог, записи и страницы настраиваются в Customizer. Gutenberg — для контента записей и страниц.

**Версия:** 1.0.12  
**Требования:** WordPress 6.0+ (протестировано с 7.0), PHP 7.4+

**Официальный репозиторий:** [https://github.com/artbashlykov/art-theme](https://github.com/artbashlykov/art-theme) (публичный)

**Материалы автора:** [https://forge.artbashlykov.ru](https://forge.artbashlykov.ru)

## Принципы

- **Классическая тема** — шапка, подвал и карточки контента в PHP/CSS + Customizer, не Site Editor
- **Customizer** — шапка, подвал, блог, записи, страницы, 404
- **Gutenberg** — для контента внутри записей и страниц (`the_content()`)
- **Скорость** — локальные шрифты Manrope и Rubik (латиница + кириллица), без Google Fonts CDN
- **Макет** — одна колонка, серый canvas, белые блоки контента (ширина настраивается)

## Структура

```
art-theme/
├── style.css              # заголовок темы (обязателен для WordPress)
├── screenshot.png         # превью темы (1200×900)
├── functions.php
├── header.php             # canvas + shell + шапка
├── footer.php             # подвал + закрытие shell
├── index.php, single.php, page.php, archive.php, search.php, 404.php
├── template-parts/
├── inc/
│   ├── fonts.php          # регистрация локальных шрифтов
│   ├── site-header.php    # рендер шапки
│   ├── site-footer.php    # рендер подвала
│   ├── class-updater.php  # GitHub Updates (PUC)
│   └── customizer/        # настройки Customizer
├── vendor/
│   └── plugin-update-checker/
└── assets/
    ├── css/
    │   ├── fonts.css      # @font-face
    │   └── theme-*.css    # split bundles (base, header, archive, …)
    └── fonts/             # Manrope, Rubik (.woff2)
```

## Установка

1. Склонируйте репозиторий в `wp-content/themes/art-theme` или скачайте zip из [GitHub Releases](https://github.com/artbashlykov/art-theme/releases).
2. Активируйте **ART Theme** в **Внешний вид → Темы**.
3. Назначьте меню в **Внешний вид → Меню** (область «Меню шапки») или в Customizer → Шапка.

## Обновления (GitHub Releases)

Тема использует [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker). Репозиторий **публичный** — токен GitHub на сайте не нужен.

Обновления появляются на **Внешний вид → Темы** и **Консоль → Обновления** после публикации release с asset **`art-theme.zip`**.

Для приватного форка в `wp-config.php` можно задать:

```php
define( 'ART_THEME_GITHUB_TOKEN', 'your-github-token' );
```

## Сборка Release zip

Для GitHub Release используется **фиксированное имя** `art-theme.zip` (без версии в имени файла).

```powershell
cd wp-content/themes/art-theme
php scripts/build-release.php
# или с путём:
php scripts/build-release.php art-theme.zip
```

Загрузка в релиз (пример):

```powershell
git tag v1.0.1
git push origin v1.0.1
gh release create v1.0.1 art-theme.zip --repo artbashlykov/art-theme --title "v1.0.1" --notes "Initial public release."
gh release upload v1.0.1 art-theme.zip --repo artbashlykov/art-theme --clobber
```

Внутри архива должна быть папка `art-theme/`, не плоский список файлов.

## Лицензия

GPL v2 or later. См. [LICENSE](LICENSE).

Шрифты Manrope и Rubik — SIL Open Font License 1.1. См. [assets/fonts/LICENSE.txt](assets/fonts/LICENSE.txt).

## Производительность

См. [PERFORMANCE.md](PERFORMANCE.md) — split CSS, условный `wp-block-library`, кэш архива. **Обязательно** при новых шаблонах и стилях.
