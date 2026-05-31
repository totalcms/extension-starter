# Total CMS Extension Starter

A template for building [Total CMS](https://totalcms.co) extensions. Clone this repo, rename it, and start building.

## What's Included

This starter demonstrates every extension point:

| Feature | File | Description |
|---|---|---|
| Twig function | `Extension.php` | `starter_greet()` function |
| Twig filter | `Extension.php` | `reverse_words` filter |
| Twig global | `Extension.php` | Example in register() (commented out) |
| CLI command | `src/Command/GreetCommand.php` | `tcms acme:greet` |
| API route | `src/Action/ApiHelloAction.php` | Protected API at `/ext/acme/starter/api/hello` |
| Public route | `src/Action/PublicStatusAction.php` | Unauthenticated at `/ext/acme/starter/status` |
| Admin page | `src/Action/DashboardAction.php` | Admin page at `/admin/ext/acme/starter/dashboard` |
| Admin nav | `Extension.php` | Sidebar link with custom SVG icon |
| Dashboard widget | `templates/widgets/starter.twig` | Widget on admin home |
| Event listener | `Extension.php` | Logs object create/update events |
| Custom field type | `src/Field/ColorPickerField.php` | Native HTML color picker field |
| MCP tool | `Extension.php` | `acme_search_inventory` — query the inventory from an AI agent |
| MCP tool with progress | `Extension.php` | Bulk-reindex example with SSE progress notifications (commented) |
| MCP resource | `Extension.php` | `acme://message/of-the-day` — addressable content for AI agents |
| MCP resource template | `Extension.php` | URI template `acme://inventory/{sku}` for unbounded resource sets (commented) |
| Admin CSS asset | `assets/colorpicker.css` | Styles for the color picker field |
| Admin JS asset | `Extension.php` | Example in register() (commented out) |
| Frontend CSS/JS assets | `Extension.php` | Examples in register() (commented out) |
| Container definition | `Extension.php` | Example in register() (commented out) |
| Page middleware | `Extension.php` | Example in register() (commented out) |
| Read-only schema | `schemas/starter-items.json` | Example collection schema (Pro+) |
| Installable schema | `Extension.php` | Example in boot() (commented out, Pro+) |
| Settings | `settings-schema.json` | Configurable greeting message |
| Icon | `icon.svg` | Displayed on the extension card in the admin UI |
| Manifest links | `extension.json` | "Documentation" and "Dashboard" links on the admin card |
| Review note | `extension.json` | Plain-language note shown on the pre-enable review screen |

## Getting Started

1. Clone this repo into your T3 extensions directory:

```bash
cd tcms-data/extensions/
mkdir your-vendor
cd your-vendor
git clone https://github.com/totalcms/extension-starter.git your-extension
cd your-extension
```

2. Update `extension.json` with your extension's ID, name, and details:

```json
{
    "id": "your-vendor/your-extension",
    "name": "Your Extension"
}
```

3. Update the PHP namespace in `composer.json` and all PHP files:

```json
"autoload": {
    "psr-4": {
        "YourVendor\\YourExtension\\": "src/"
    }
}
```

4. Install dependencies and generate the autoloader:

```bash
composer install
```

5. Enable the extension:

```bash
tcms extension:enable your-vendor/your-extension
```

6. Delete the parts you don't need and start building.

## Directory Structure

```
your-extension/
    extension.json          # Manifest (required)
    Extension.php           # Entry point (required)
    icon.svg                # Extension icon (optional)
    composer.json           # Dependencies and autoloading
    settings-schema.json    # Settings form definition
    src/
        Action/             # HTTP action handlers
        Command/            # CLI commands
    schemas/                # Read-only schemas (Pro+)
    templates/              # Twig templates
```

## Manifest Links

Add a `links` array to `extension.json` to surface buttons on the admin Extensions card:

```json
"links": [
    {"label": "Documentation", "url": "https://docs.totalcms.co/extensions/"},
    {"label": "Dashboard", "url": "/admin/ext/acme/starter/dashboard"}
]
```

Each entry needs a `label` and a `url`.

- URLs starting with `http://` or `https://` are treated as external and open in a new tab.
- Relative URLs (admin pages your extension registers) only show when the extension is **enabled** — they wouldn't resolve otherwise. External links are always shown so users can read your docs before enabling.

## Review Note

When a user enables an extension that touches sensitive capabilities — public (unauthenticated) routes, listening to all content changes, registering services, or exposing tools/resources to AI agents over MCP — Total CMS shows a short **pre-enable review screen** before the extension turns on. (Extensions that use none of those just enable in one click.) The screen also lists any high-risk patterns a static source scan finds in your code (`shell_exec`, raw network calls, etc.).

The `reviewNote` field in `extension.json` is **your message at the top of that screen** — your chance to explain, in plain language, what your extension does and why it needs the access it asks for:

```json
"reviewNote": "Receives Stripe webhooks at a public endpoint to confirm payments, and watches new orders to send notifications. No data leaves your site except the Stripe calls you configure."
```

Guidelines:

- **Write for the site owner, not a developer.** Say what the extension *does* and *why* it needs each sensitive capability.
- **Be specific about public endpoints and data access** — those are what users worry about.
- **Keep it short** — a sentence or two. It's a reassurance, not a manual.
- You'll see this exact screen when you enable your own extension during testing, so you can preview how it reads.

Two behaviors worth knowing as you build:

- **Capabilities you add in a later version default to *off*** for users who already had your extension enabled. Total CMS surfaces the new capability in the extension's settings as a toggle, so the user opts in deliberately — adding a capability in an update won't silently start using it.
- **An update that introduces high-risk code patterns** (caught by the source scan) **disables the extension** until the user reviews and re-enables it. Clean updates install without interruption. So keep risky calls intentional and explainable.

`reviewNote` is optional — but on an extension that uses any sensitive capability, a good note is the difference between a confident "Enable" and a nervous one.

## Schemas

Two ways to ship schemas with an extension. Both require the **Pro** edition.

| Approach | Where it lives | Editable by user? | Use when |
|---|---|---|---|
| Read-only | `schemas/yourname.json` in the extension | No — owned by the extension | The schema is core to the extension's behavior and shouldn't be modified |
| Installable | `$context->installSchema([...])` in `boot()` | Yes — copied once into `tcms-data/.schemas/` | The schema is a starting point the user is expected to customize |

Read-only schemas are auto-discovered. They show up in the admin schemas view alongside built-in schemas, and can be referenced by collections. Disabling the extension's `Schemas` capability hides them.

`installSchema()` is a one-shot copy: it skips silently if a schema with the same `id` already exists, so subsequent boots don't overwrite the user's edits.

## Version Requirements

The `requires` block in `extension.json` is enforced before the extension can be enabled:

```json
"requires": {
    "totalcms": ">=3.3.0",
    "php": ">=8.2"
}
```

If the running Total CMS or PHP version doesn't satisfy these constraints, the extension is **still listed** on the Extensions admin page but the Enable button is disabled and a yellow warning panel explains why. This means users can see your extension exists and read its docs, but they can't enable it on an unsupported runtime.

## Documentation

- [Extensions Overview](https://docs.totalcms.co/extensions/overview/)
- [Manifest Reference](https://docs.totalcms.co/extensions/manifest/)
- [Extension Points](https://docs.totalcms.co/extensions/extension-points/)
- [Events](https://docs.totalcms.co/extensions/events/)
- [Schemas](https://docs.totalcms.co/extensions/schemas/)

## License

MIT
