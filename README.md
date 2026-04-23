# Total CMS Extension Starter

A template for building [Total CMS](https://totalcms.co) extensions. Clone this repo, rename it, and start building.

## What's Included

This starter demonstrates every extension point:

| Feature | File | Description |
|---|---|---|
| Twig function | `Extension.php` | `starter_greet()` function |
| Twig filter | `Extension.php` | `reverse_words` filter |
| CLI command | `src/Command/GreetCommand.php` | `tcms acme:greet` |
| API route | `src/Action/ApiHelloAction.php` | Protected API at `/ext/acme/starter/api/hello` |
| Public route | `src/Action/PublicStatusAction.php` | Unauthenticated at `/ext/acme/starter/status` |
| Admin page | `src/Action/DashboardAction.php` | Admin page at `/admin/ext/acme/starter/dashboard` |
| Admin nav | `Extension.php` | Sidebar link with custom SVG icon |
| Dashboard widget | `templates/widgets/starter.twig` | Widget on admin home |
| Event listener | `Extension.php` | Logs object create/update events |
| Custom field type | `src/Field/ColorPickerField.php` | Native HTML color picker field |
| Admin CSS asset | `assets/colorpicker.css` | Styles for the color picker field |
| Read-only schema | `schemas/starter-items.json` | Example collection schema (Pro+) |
| Installable schema | `Extension.php` | Example in boot() (commented out, Pro+) |
| Settings | `settings-schema.json` | Configurable greeting message |

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
    composer.json           # Dependencies and autoloading
    settings-schema.json    # Settings form definition
    src/
        Action/             # HTTP action handlers
        Command/            # CLI commands
    schemas/                # Read-only schemas (Pro+)
    templates/              # Twig templates
```

## Documentation

- [Extensions Overview](https://docs.totalcms.co/extensions/overview/)
- [Manifest Reference](https://docs.totalcms.co/extensions/manifest/)
- [Extension Points](https://docs.totalcms.co/extensions/extension-points/)
- [Events](https://docs.totalcms.co/extensions/events/)
- [Schemas](https://docs.totalcms.co/extensions/schemas/)

## License

MIT
