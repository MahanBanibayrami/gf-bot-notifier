# GF Bot Notifier

Send [Gravity Forms](https://www.gravityforms.com/) submissions to **Telegram** and **Rubika** bots, reliably.

Notifications are queued and sent in the background (your visitors never wait for a bot API), failures are retried automatically, and every delivery is recorded in a log you can filter and resend from.

> Status: version **2.0.0** is the first public release.

## Features

- Telegram and Rubika, each with its own switch, token, chat ID, **Check connection** and **Send test message** buttons and a connection status badge.
- "Find chats" helper that lists chat IDs from the bot's recent updates.
- Choose **all forms** or **selected forms**; per form choose the **fields** to send and an optional **custom template**; preview and test any form.
- Message templates with `{form_title}`, `{form_id}`, `{entry_id}`, `{entry_url}`, `{date}`, `{all_fields}`, `{site_name}`, `{ip}` and any Gravity Forms merge tag (e.g. `{Name:1}`). Optional separate templates for Telegram and Rubika.
- Background queue (WP-Cron), duplicate protection, retry with exponential back-off, separate handling of temporary and permanent errors, HTTP 429 `retry_after` support.
- Delivery logs page: filter by date / form / service / status, resend failed items, delete one / many / all matching, automatic retention.
- Security: nonces and capability checks, sanitized input and escaped output, prepared SQL, HTTPS-only and SSRF-protected API addresses, tokens masked in the UI and redacted from errors and logs.
- Works in English and Persian (fa_IR) with full RTL support. No external services other than the two bot APIs.

## Requirements

- WordPress 5.8+
- PHP 7.4+
- Gravity Forms (any recent 2.x version)
- Outgoing HTTPS access to `api.telegram.org` and/or `botapi.rubika.ir` (or a public HTTPS proxy for Telegram)

## Installation

1. Download `gf-bot-notifier-2.0.0.zip` from the **Releases** page (do not use GitHub's "Download ZIP" of the source; its folder name is not installable as-is).
2. In WordPress go to **Plugins → Add New → Upload Plugin**, choose the ZIP, install and activate.
3. Open **Bot Notifier** in the admin menu.

Upgrading from 1.x: install over the old plugin. Settings are kept; the history is imported into the new log table.

## Telegram setup

1. Talk to [@BotFather](https://t.me/BotFather), run `/newbot` and copy the **token**.
2. Open a chat with your bot and send it any message (for a group: add the bot to the group and post a message; for a channel: add the bot as an administrator).
3. In the **Telegram** tab paste the token, click **Find chats** and pick the chat (or type the numeric ID / `@channelusername`).
4. Click **Check connection** and **Send test message**, tick **Send notifications to Telegram** and **Save changes**.

If Telegram is blocked on your server, enter the address of a public HTTPS proxy in **API address**. Private, local and non-HTTPS addresses are rejected on purpose.

## Rubika setup

1. Create a bot with Rubika's bot father and copy the **token**.
2. Send a message to the bot, then in the **Rubika** tab paste the token, click **Find chats** and pick the chat.
3. **Check connection**, **Send test message**, enable, **Save changes**.

## Configuring forms and messages

- **Message** tab: edit the default template; click a tag to insert it; use **Preview** to see the result for a real entry.
- **Forms** tab: choose all/selected forms. Click **Customize** on a form to pick fields and/or set a custom template. Template priority: *form template → service template → default template*.
- **Delivery & logs** tab: retries, timeout, log retention and the amount of API error detail stored.

### Keeping tokens out of the database

Add to `wp-config.php`:

```php
define( 'GFBN_TELEGRAM_TOKEN', '123456:ABC...' );
define( 'GFBN_RUBIKA_TOKEN', '...' );
```

### Hooks

| Filter | Default | Purpose |
|---|---|---|
| `gfbn_capability` | `manage_options` | Capability needed to use the plugin |

## Privacy

The plugin sends the content of form submissions to the bot services you configure. It stores **no** submitted form data and **no** tokens in its logs: the log table contains form ID/title, entry ID, service, status, attempt counts and a (redacted) error text. Messages are rebuilt from the Gravity Forms entry on every attempt. Spam entries and password / credit-card fields are never sent. All plugin data is removed when the plugin is deleted.

## How delivery works

1. A Gravity Forms submission creates one job per enabled service (a unique key prevents duplicates) and schedules a WP-Cron event.
2. The job is claimed with a database lock, the message is built from the entry and sent.
3. Temporary errors (network, HTTP 5xx, HTTP 429) are retried with a growing delay; permanent errors (bad token, chat not found, deleted entry) are marked **Failed** immediately and can be resent from the logs page.
4. An hourly safety-net task picks up anything missed and purges old logs.

If `DISABLE_WP_CRON` is set, run a real server cron that calls `wp-cron.php` every minute or two.

## Screenshots

**Settings (Telegram tab)**

![Settings, Telegram tab](docs/screenshots/settings-telegram.png)

**Delivery logs**

![Delivery logs](docs/screenshots/logs.png)

## Development

```
gf-bot-notifier.php   bootstrap
uninstall.php         removes all data
includes/             classes (settings, http, services, queue, logs, admin, ...)
views/                admin page templates
assets/               admin CSS/JS (no build step)
languages/            .pot and fa_IR translation
```

Translations: `languages/gf-bot-notifier.pot` is the template; compile `.po` to `.mo` with `msgfmt`.
Releases: bump the version in `gf-bot-notifier.php` (header and `GFBN_VERSION`), update `CHANGELOG.md`, tag `vX.Y.Z`, and attach the installable ZIP (folder `gf-bot-notifier/` without `docs/` and `.gitignore`).

## License

Copyright © Mahan Banibayrami. Licensed under GPL-2.0-or-later, see [`LICENSE`](LICENSE).
