# GF Bot Notifier

Send [Gravity Forms](https://www.gravityforms.com/) submissions to **Telegram** and **Rubika** bots, reliably.

Notifications are queued and sent in the background, so visitors never have to wait for a bot API response. Failed deliveries are retried automatically, and every delivery is recorded in logs that you can filter and resend from.

> **Status:** Version **2.0.0** — First public release.

## Features

- **Telegram & Rubika integration:** Separate enable switches, bot tokens, chat IDs, connection checks, test messages, and connection status indicators for each service.
- **Find chats:** Automatically discover chat IDs from recent bot updates.
- **Form selection:** Send notifications from all forms or selected forms.
- **Field selection:** Choose which fields to send for each form.
- **Custom message templates:** Use custom templates for individual forms, with preview and testing options.
- **Merge Tags support:** Includes `{form_title}`, `{form_id}`, `{entry_id}`, `{entry_url}`, `{date}`, `{all_fields}`, `{site_name}`, `{ip}`, and Gravity Forms Merge Tags (e.g., `{Name:1}`).
- **Service-specific templates:** Configure separate message templates for Telegram and Rubika.
- **Background processing:** Notifications are processed using WP-Cron without blocking form submissions.
- **Automatic retries:** Exponential back-off, separate handling of temporary and permanent errors, and support for HTTP 429 `retry_after`.
- **Duplicate protection:** Prevent duplicate notification deliveries.
- **Delivery logs:** Filter logs by date, form, service, or status; resend failed notifications; delete individual, multiple, or matching records; and automatically retain logs according to configured settings.
- **Security:** Nonce and capability checks, sanitized input, escaped output, prepared SQL queries, HTTPS-only and SSRF-protected API addresses, masked tokens, and redacted error messages.
- **Multilingual support:** English and Persian (`fa_IR`), including full RTL support.

No external services are used other than the Telegram and Rubika bot APIs.

## Requirements

- WordPress 5.8+
- PHP 7.4+
- Gravity Forms (any recent 2.x version)
- Outgoing HTTPS access to `api.telegram.org` and/or `botapi.rubika.ir` (or a public HTTPS proxy for Telegram)

## Installation

1. Download `gf-bot-notifier-2.0.0.zip` from the **Releases** page. Do not use GitHub's **Download ZIP** option for the source repository, as that archive is not packaged for direct plugin installation.
2. In WordPress, go to **Plugins → Add New → Upload Plugin**.
3. Select the downloaded ZIP file, install it, and activate the plugin.
4. Open **Bot Notifier** from the WordPress admin menu.

### Upgrading from 1.x

Install the new version over the existing plugin. Your settings are preserved, and the previous notification history is imported into the new log table.

## Telegram Setup

1. Open [@BotFather](https://t.me/BotFather), run `/newbot`, and copy the bot token.
2. Start a conversation with your bot and send it a message.
   - For groups: add the bot to the group and send a message.
   - For channels: add the bot as an administrator.
3. In the plugin's **Telegram** tab, paste the token and click **Find chats**. Select the desired chat or manually enter a numeric chat ID or `@channelusername`.
4. Click **Check connection** and **Send test message**.
5. Enable **Send notifications to Telegram** and click **Save changes**.

If Telegram is blocked on your server, you can configure a public HTTPS proxy under **API address**. Private, local, and non-HTTPS addresses are intentionally rejected.

## Rubika Setup

1. Create a bot using Rubika's bot creation service and copy its token.
2. Send a message to your bot.
3. In the plugin's **Rubika** tab, paste the token and click **Find chats**.
4. Select the desired chat.
5. Click **Check connection** and **Send test message**.
6. Enable notifications and click **Save changes**.

## Configuring Forms and Messages

- **Message tab:** Edit the default message template, insert available tags by clicking them, and preview messages using real form entries.
- **Forms tab:** Choose all forms or selected forms. Click **Customize** on a form to select fields and/or define a custom template.
- **Template priority:** Form template → Service template → Default template.
- **Delivery & Logs tab:** Configure retry behavior, timeout, log retention, and the level of API error details stored.

## Keeping Tokens Out of the Database

You can define bot tokens in `wp-config.php` instead of storing them in the database:

```php
define( 'GFBN_TELEGRAM_TOKEN', '123456:ABC...' );
define( 'GFBN_RUBIKA_TOKEN', '...' );
```

## Hooks

| Filter | Default | Purpose |
|---|---|---|
| `gfbn_capability` | `manage_options` | Capability required to access and manage the plugin |

## Privacy

The plugin sends form submission content to the bot services you configure.

It does not store submitted form data or bot tokens in its logs. The log table contains form ID and title, entry ID, service, status, attempt counts, and redacted error messages.

Messages are rebuilt from the Gravity Forms entry on every delivery attempt. Spam entries and password or credit-card fields are never sent.

All plugin data is removed when the plugin is deleted.

## How Delivery Works

1. A Gravity Forms submission creates one notification job per enabled service. A unique key prevents duplicate jobs, and a WP-Cron event is scheduled.
2. The job is claimed using a database lock, the message is built, and the request is sent to the configured bot API.
3. Temporary errors (network errors, HTTP 5xx, and HTTP 429) are retried with increasing delays. Permanent errors (invalid token, chat not found, or deleted entry) are marked as **Failed** immediately and can be manually resent from the logs page.
4. An hourly safety-net task processes missed jobs and purges old logs.

If `DISABLE_WP_CRON` is enabled, configure a real server cron job to call `wp-cron.php` every minute or two.

## Screenshots

### Settings — Telegram

![Settings, Telegram tab](docs/screenshots/settings-telegram.png)

### Delivery Logs

![Delivery logs](docs/screenshots/logs.png)

## Development

```text
gf-bot-notifier.php   Plugin bootstrap
uninstall.php          Removes plugin data
includes/             Plugin classes (settings, HTTP, services, queue, logs, admin, etc.)
views/                Admin page templates
assets/               Admin CSS and JavaScript (no build step)
languages/             Translation files (.pot and fa_IR)
```

The translation template is located at `languages/gf-bot-notifier.pot`. Compile `.po` files into `.mo` files using `msgfmt`.

### Releases

To prepare a new release:

1. Update the plugin version in `gf-bot-notifier.php` (plugin header and `GFBN_VERSION`).
2. Update `CHANGELOG.md`.
3. Create a new version tag (e.g., `vX.Y.Z`).
4. Attach the installable ZIP to the GitHub Release.

The installable ZIP must contain the `gf-bot-notifier/` plugin directory and exclude development files such as `docs/` and `.gitignore`.

## License

Copyright © Mahan Banibayrami. Licensed under **GPL-2.0-or-later**. See [LICENSE](LICENSE) for details.
