# Changelog

All notable changes to this project are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses [Semantic Versioning](https://semver.org/).

## [2.0.0] - 2026-11-19

First public release. Existing 1.x settings are kept and migrated automatically.

### Added
- Tabbed settings page (Telegram, Rubika, Message, Forms, Delivery & logs) using native WordPress UI, RTL aware, with English and Persian (fa_IR) translations.
- Independent enable switch, "Check connection" and "Send test message" for each service, plus a stored connection status badge.
- Per-form settings: choose which forms notify, which fields are sent, a custom template, preview and test message.
- Separate optional templates per service; priority is form > service > default.
- Background delivery queue (WP-Cron) with duplicate protection, configurable retries (exponential back-off), temporary/permanent error detection and API rate-limit (HTTP 429 `retry_after`) handling.
- Dedicated "Delivery logs" page: filters (date, form, service, status), bulk/single delete, "delete all matching", manual resend, automatic retention clean-up.
- Settings: request timeout, retry count and delay, log retention, optional detailed API errors.
- "Reset to defaults" with confirmation.
- Tokens can be defined in `wp-config.php` (`GFBN_TELEGRAM_TOKEN`, `GFBN_RUBIKA_TOKEN`).
- Dependency checks (PHP/WordPress version, Gravity Forms) with admin notices; works without `mbstring`.
- `uninstall.php` removes all plugin data.

### Changed
- Delivery logs moved from a 30-row option to a dedicated database table (`{prefix}gfbn_logs`). Messages are rebuilt from the Gravity Forms entry on every attempt, so no submitted form data is stored by the plugin.
- Sending no longer happens during form submission; it runs in the background.
- Code split into focused classes (`includes/`), views and assets.

### Security
- Bot tokens are never printed back into the settings page (masked, replace-only) and are redacted from every error message and log.
- Telegram API address must be public HTTPS (SSRF protection); redirects are never followed.
- All admin actions require a nonce and capability; all input is validated/sanitized and all output escaped; database queries use `$wpdb->prepare`.
- Merge-tag injection protection: values typed by visitors are never re-parsed as Gravity Forms merge tags.
- Password and credit-card fields and spam entries are never sent.

### Removed
- The old `gfbn_queue` option (it contained full message texts) is deleted on upgrade; the old `gfbn_log` history is imported read-only.
