<?php

declare(strict_types=1);

namespace Acme\Starter;

use Acme\Starter\Command\GreetCommand;
use TotalCMS\Domain\Extension\Data\AdminNavItem;
use TotalCMS\Domain\Extension\Data\DashboardWidget;
use TotalCMS\Domain\Extension\ExtensionContext;
use TotalCMS\Domain\Extension\ExtensionInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Starter extension demonstrating every extension point.
 *
 * Use this as a template for building your own extensions.
 * Delete the parts you don't need.
 */
class Extension implements ExtensionInterface
{
	public function register(ExtensionContext $context): void
	{
		// ── Twig Functions ──────────────────────────────────────────────
		// Available in all templates: {{ starter_greet('World') }}
		//
		// Functions and filters must RETURN their output — never echo, print,
		// or ob_start(). Total CMS renders in streaming (yield) mode, so any
		// output written directly bypasses the template stream and is lost.
		$context->addTwigFunction(
			new TwigFunction('starter_greet', function (string $name) use ($context): string {
				$greeting = $context->setting('greeting', 'Hello');

				return "{$greeting}, {$name}!";
			})
		);

		// ── Twig Filters ────────────────────────────────────────────────
		// Available in templates: {{ text|reverse_words }}
		$context->addTwigFilter(
			new TwigFilter('reverse_words', function (string $text): string {
				return implode(' ', array_reverse(explode(' ', $text)));
			})
		);

		// ── Twig Globals ────────────────────────────────────────────────
		// Expose a value (object, array, or scalar) as a global variable in
		// every template. Usage in templates: {{ starter.version }}
		//
		// $context->addTwigGlobal('starter', [
		//     'version' => '1.0.0',
		// ]);

		// ── CLI Commands ────────────────────────────────────────────────
		// Run with: tcms acme:greet --name=World
		$context->addCommand(new GreetCommand());

		// ── Admin Navigation ────────────────────────────────────────────
		// Adds a link to the admin sidebar. Pass raw SVG — it's URL-encoded
		// automatically by the template. Leave icon empty for the default
		// puzzle piece icon.
		$context->addAdminNavItem(new AdminNavItem(
			label: 'Starter',
			icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><g fill="black" stroke-linecap="round" stroke-linejoin="round"><path d="M16 26L17.83 24.17C17.89 24.11 17.85 24 17.76 24H14.24C14.15 24 14.11 24.11 14.17 24.17L16 26Z" stroke="black" stroke-width="2" fill="none"/><path d="M16 26V30.5" stroke="black" stroke-width="2" fill="none"/><path d="M10.67 8L4 2.67V12.67L2 16L4.67 17.33L4 20.67L6.67 21.33L10.9 27.91C12 29.63 13.9 30.67 15.94 30.67H16.06C18.1 30.67 20 29.63 21.1 27.91L25.33 21.33L28 20.67L27.33 17.33L30 16L28 12.67V2.67L21.33 8C21.33 8 18.92 6.67 16 6.67C13.08 6.67 10.67 8 10.67 8Z" stroke="black" stroke-width="2" fill="none"/><circle cx="12" cy="17.5" r="2" fill="black"/><circle cx="20" cy="17.5" r="2" fill="black"/></g></svg>',
			url: '/admin/ext/acme/starter/dashboard',
			permission: 'admin',
			priority: 80,
		));

		// ── Dashboard Widget ────────────────────────────────────────────
		// Adds a widget to the admin home screen.
		// Use @vendor-name/path.twig to reference templates in your templates/ dir.
		$context->addDashboardWidget(new DashboardWidget(
			id: 'starter-widget',
			label: 'Starter Widget',
			template: '@acme-starter/widgets/starter.twig',
			position: 'sidebar', // 'main' or 'sidebar'
			priority: 50,
		));

		// ── Event Listeners ─────────────────────────────────────────────
		// React to content changes. Use $context->logger() to write to the
		// shared extensions.log file (tcms-data/logs/extensions.log) on the
		// 'extensions' channel. Prefix messages with your extension id so
		// multi-extension logs stay readable.
		$logger = $context->logger();

		$context->addEventListener('object.created', function (array $payload) use ($logger): void {
			// Called after any object is created in any collection.
			// $payload contains: 'collection' and 'id'
			$logger->info('[acme/starter] object.created', $payload);
		});

		$context->addEventListener('object.updated', function (array $payload) use ($logger): void {
			$logger->info('[acme/starter] object.updated', $payload);
		});

		$context->addEventListener('object.deleted', function (array $payload) use ($logger): void {
			// PSR-3 levels: debug, info, notice, warning, error, critical, alert, emergency.
			// Pass a context array as the second argument for structured fields.
			$logger->warning('[acme/starter] object.deleted', $payload);
		});

		// ── Automations ─────────────────────────────────────────────────
		// Ship a server-side automation as part of your extension. Like the
		// user-authored automations in the admin, an extension automation runs
		// on a schedule, a webhook, or a content event — but its handler is the
		// closure you register here (held in memory, never written to disk).
		//
		// They appear in the automations admin READ-ONLY (operators can run or
		// toggle them via the 'automations' capability, but can't edit the
		// closure — it lives in your code). Schedule + event triggers dispatch
		// automatically; for HTTP use a public route (see below) instead of a
		// webhook trigger, since an extension automation's id isn't a URL path.
		//
		// The handler receives an AutomationContext ($ctx) — the ONLY API it
		// gets. Pre-injected services + the trigger payload:
		//   $ctx->objectFetcher / objectSaver / objectUpdater / objectRemover
		//   $ctx->indexReader   — read a collection index
		//   $ctx->mailer        — EmailService::sendEmail('mailer-id', [...])
		//   $ctx->config        — core Config
		//   $ctx->logger        — PSR-3 logger (automations.log channel)
		//   $ctx->trigger       — the trigger row that fired this run
		//   $ctx->args          — webhook query+body / manual run args
		//   $ctx->event         — event payload ['collection','id'] (event runs)
		//
		// Schedule cron is evaluated in the site timezone. Return value (if any)
		// is recorded on the run record; throwing marks the run failed.
		$context->addAutomation(
			id: 'prune-drafts',
			label: 'Prune stale drafts',
			triggers: [
				['type' => 'schedule', 'cron' => '0 3 * * *'], // daily at 03:00
			],
			handler: function (\TotalCMS\Domain\Automation\Data\AutomationContext $ctx): array {
				$ctx->logger->info('[acme/starter] prune-drafts tick');

				// ... do work via $ctx->objectFetcher / $ctx->objectRemover ...

				return ['pruned' => 0];
			},
		);

		// ── Automation: react to a content event ────────────────────────
		// Fires after the matching core event. Add a 'collection' key to scope
		// the trigger to one collection; omit it to match every collection.
		//
		// $context->addAutomation(
		//     id: 'welcome-new-member',
		//     label: 'Welcome new members',
		//     triggers: [
		//         ['type' => 'event', 'event' => 'object.created', 'collection' => 'members'],
		//     ],
		//     handler: function (\TotalCMS\Domain\Automation\Data\AutomationContext $ctx): void {
		//         $collection = (string) ($ctx->event['collection'] ?? '');
		//         $id         = (string) ($ctx->event['id'] ?? '');
		//         $member     = $ctx->objectFetcher->fetchObject($collection, $id);
		//         $ctx->mailer->sendEmail('welcome', ['member' => $member->toArray()]);
		//     },
		// );

		// ── Custom Field Types ──────────────────────────────────────────
		// Register a new field type usable in schemas (class must extend FormField)
		$context->addFieldType('colorpicker', \Acme\Starter\Field\ColorPickerField::class);

		// ── Assets (CSS / JS) ───────────────────────────────────────────
		// CSS and JS files from assets/ are served at /ext/acme/starter/assets/
		// with mtime-based cache busting. Admin assets render via
		// {{ cms.adminAssetsHead/Body() }} (already wired in core admin
		// templates); frontend assets render via {{ cms.assetsHead/Body() }}
		// in your public theme template.
		$context->addAdminAsset('css', 'colorpicker.css');
		// $context->addAdminAsset('js', 'admin.js');

		// $context->addFrontendAsset('css', 'widget.css');
		// $context->addFrontendAsset('js', 'widget.js');

		// Both methods accept the same optional arguments:
		//   position: 'head' | 'body' | null   (null = CSS→head, JS→body)
		//   module:   bool                      (JS only — <script type="module">, default true)
		//   preload:  bool                      (emit a preload/modulepreload hint in head)
		//   version:  ?string                   (override mtime cache-bust query string)
		//
		// $context->addFrontendAsset(
		//     type: 'js',
		//     path: 'widget.js',
		//     position: 'body',
		//     module: true,
		//     preload: true,
		// );

		// ── API Routes ──────────────────────────────────────────────────
		// Protected API at /ext/acme/starter/... (requires session or API key)
		//
		// Form Actions are registered alongside API routes — the form
		// processor dispatches to your route automatically. Requires Pro.
		// See totalcms/pushover for a full working example.
		//
		// $context->addFormAction('acme-notify', new \TotalCMS\Domain\Extension\Data\FormAction(
		//     name: 'acme-notify',
		//     route: '/ext/acme/starter/notify',
		//     label: 'Acme Notification',
		// ));
		$context->addRoutes(function ($group): void {
			$group->get('/api/hello', Action\ApiHelloAction::class);
		});

		// ── Public Routes ───────────────────────────────────────────────
		// Unauthenticated routes at /ext/acme/starter/... (no auth)
		// Use for webhooks, embeds, and endpoints accessible without credentials.
		$context->addPublicRoutes(function ($group): void {
			$group->get('/status', Action\PublicStatusAction::class);
		});

		// ── Admin Routes ────────────────────────────────────────────────
		// Admin pages at /admin/ext/acme/starter/... (requires login)
		// Templates can extend admin-dashboard.twig for the admin layout.
		$context->addAdminRoutes(function ($group): void {
			$group->get('/dashboard', Action\DashboardAction::class);
		});

		// ── MCP Tools ───────────────────────────────────────────────────
		// Register PHP-backed tools the MCP server exposes to AI agents.
		// Tool names are global across core + all extensions + all
		// schema-defined tools — vendor-prefix to avoid collisions.
		// Handlers run inside the MCP request lifecycle: return MCP
		// tool envelopes ({ content: [...] } or { isError: true, ... })
		// and never throw past the SDK transport.
		//
		// See docs: extensions/mcp-extensions and mcp/extensions.
		$context->registerMcpTool(
			name: 'acme_search_inventory',
			description: 'Search the acme inventory by SKU or product name.',
			access: 'public', // 'public' | 'admin' | 'authenticated' (Phase 4)
			handler: function (string $query, int $limit = 10): array {
				// Params map by NAME to the inputSchema below — the SDK
				// uses reflection on this closure's parameters. Declare
				// typed args, not a single `array $args`.
				$items = [
					['sku' => 'WIDGET-1', 'name' => 'Demo Widget', 'price' => 19.99],
				];

				return [
					'content' => [[
						'type' => 'text',
						'text' => json_encode($items, JSON_PRETTY_PRINT),
					]],
				];
			},
			inputSchema: [
				'type'     => 'object',
				'required' => ['query'],
				'properties' => [
					'query' => ['type' => 'string', 'description' => 'SKU or product name (case-insensitive).'],
					'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50, 'default' => 10],
				],
			],
		);

		// ── MCP Tool With Progress Notifications ────────────────────────
		// Long-running tools can emit notifications/progress events that
		// stream to the client mid-call. Declare ?\Mcp\Server\RequestContext
		// $ctx = null on your handler; the SDK auto-injects it via
		// reflection. Then call $ctx?->getClientGateway()->progress(...) at
		// meaningful checkpoints. The SDK auto-switches the HTTP response
		// to text/event-stream — no extra wiring needed. If the client
		// didn't send _meta.progressToken in the tools/call request, the
		// progress() calls silently no-op.
		//
		// $context->registerMcpTool(
		//     name: 'acme_bulk_reindex',
		//     description: 'Reindex many inventory records.',
		//     access: 'admin',
		//     handler: function (array $ids, ?\Mcp\Server\RequestContext $ctx = null): array {
		//         $total = count($ids);
		//         foreach ($ids as $i => $id) {
		//             // ... reindex $id ...
		//             if (($i + 1) % 10 === 0) {
		//                 $ctx?->getClientGateway()->progress(
		//                     progress: (float) ($i + 1),
		//                     total:    (float) $total,
		//                     message:  sprintf('reindexed %d of %d', $i + 1, $total),
		//                 );
		//             }
		//         }
		//
		//         return ['content' => [['type' => 'text', 'text' => "Reindexed {$total} records."]]];
		//     },
		//     inputSchema: [
		//         'type'     => 'object',
		//         'required' => ['ids'],
		//         'properties' => [
		//             'ids' => [
		//                 'type'        => 'array',
		//                 'items'       => ['type' => 'string'],
		//                 'description' => 'Inventory SKUs to reindex.',
		//             ],
		//         ],
		//     ],
		// );

		// ── MCP Resources ───────────────────────────────────────────────
		// Resources are addressable content the MCP server exposes via
		// resources/read (and the get_resource tool). Use a vendor-prefixed
		// URI scheme (acme://). The tcms:// scheme is reserved for core
		// collection resources; never register URIs under it from an
		// extension.
		//
		// A concrete resource — one fixed URI. NOTE: the `name` parameter
		// must match [A-Za-z0-9_-]+ (slug form) per MCP SDK validation —
		// no spaces. Despite the docblock describing it as "human-readable",
		// the SDK enforces a strict character set.
		$context->registerMcpResource(
			uri:         'acme://message/of-the-day',
			name:        'message-of-the-day',
			description: "Today's inspirational message — refreshed every 24 hours.",
			handler:     fn (): array => [
				'contents' => [[
					'uri'      => 'acme://message/of-the-day',
					'mimeType' => 'text/plain',
					'text'     => 'Be kind to your future self.',
				]],
			],
		);

		// ── MCP Resource Templates ──────────────────────────────────────
		// Templates declare URI patterns with {placeholders}. The handler
		// receives substituted segment values as named arguments matching
		// the template's variables — the SDK does the parsing. Use
		// templates for unbounded resource sets (one resource per
		// inventory item, one per invoice, etc.) where enumerating every
		// concrete URI in resources/list would be impractical.
		//
		// $context->registerMcpResourceTemplate(
		//     uriTemplate: 'acme://inventory/{sku}',
		//     name:        'inventory-item',
		//     description: 'Acme inventory item by SKU.',
		//     handler:     function (string $sku): array {
		//         $item = ['sku' => $sku, 'name' => 'Sample item', 'price' => 19.99];
		//
		//         return [
		//             'contents' => [[
		//                 'uri'      => "acme://inventory/{$sku}",
		//                 'mimeType' => 'application/json',
		//                 'text'     => json_encode($item, JSON_PRETTY_PRINT),
		//             ]],
		//         ];
		//     },
		// );

		// ── MCP Prompts ─────────────────────────────────────────────────
		// Register prompts the MCP server exposes to AI agents. Prompts
		// appear in prompts/list and are callable via prompts/get.
		// If the name collides with a collection-stored prompt, the
		// collection version wins and this one is skipped.
		//
		// $context->registerMcpPrompt(
		//     new \Mcp\Schema\Prompt(
		//         name: 'acme_audit_links',
		//         description: 'Audit broken links on any page.',
		//         arguments: [
		//             new \Mcp\Schema\PromptArgument('url', 'The URL to audit', required: true),
		//         ],
		//     ),
		//     handler: fn (array $arguments = []) => new \Mcp\Schema\Result\GetPromptResult(
		//         messages: [new \Mcp\Schema\Content\PromptMessage(
		//             \Mcp\Schema\Enum\Role::User,
		//             new \Mcp\Schema\Content\TextContent('Check all links on: ' . ($arguments['url'] ?? '')),
		//         )],
		//     ),
		// );

		// ── Search Providers ────────────────────────────────────────────
		// Register a custom search provider that replaces or supplements
		// T3's built-in text search. The provider handles indexing (on
		// object CRUD events) and querying (from MCP search tools, future
		// REST, site-wide search). Must implement SearchProvider interface.
		// See totalcms/algolia-search for a full working example.
		//
		// $context->registerSearchProvider(new \Acme\Starter\Search\MySearchProvider());

		// ── Container Definitions ───────────────────────────────────────
		// Register a service in the DI container so other code can pull it
		// via constructor injection or $context->get() during boot.
		// The factory receives the Psr\Container\ContainerInterface.
		//
		// $context->addContainerDefinition(
		//     \Acme\Starter\Service\GeoIPService::class,
		//     fn () => new \Acme\Starter\Service\GeoIPService(),
		// );

		// ── Page Middleware ─────────────────────────────────────────────
		// Register a middleware that builder pages can opt into via their
		// `middleware` field — useful for auth gates, rate limits, geo
		// redirects, A/B splits. The class must implement
		// PageMiddlewareInterface (handle() returns ?ResponseInterface —
		// null to continue, a Response to short-circuit) and be resolvable
		// from the container — pair with addContainerDefinition().
		//
		// Names are stable contract: kebab-case, never rename once shipped
		// or sites with the name in their page records will break.
		//
		// $context->addContainerDefinition(
		//     \Acme\Starter\Middleware\GeoRedirect::class,
		//     fn ($c) => new \Acme\Starter\Middleware\GeoRedirect(
		//         $c->get(\Acme\Starter\Service\GeoIPService::class),
		//     ),
		// );
		// $context->addPageMiddleware('geo-redirect', \Acme\Starter\Middleware\GeoRedirect::class);
	}

	public function boot(ExtensionContext $context): void
	{
		// The boot phase runs after ALL extensions have registered.
		// Use $context->get() to resolve services from the DI container.
		//
		// Example: resolve a core service
		// $config = $context->get(\TotalCMS\Support\Config::class);

		// ── Installable Schemas ─────────────────────────────────────────
		// Install a user-editable schema into tcms-data/.schemas/ (Pro+ only).
		// Skips if the schema already exists. For read-only schemas managed
		// by the extension, place them in the schemas/ directory instead.
		//
		// $context->installSchema([
		//     'id' => 'starter-reviews',
		//     'description' => 'Customer reviews',
		//     'properties' => [
		//         'rating' => ['type' => 'number', 'field' => 'number', 'label' => 'Rating'],
		//         'review' => ['type' => 'string', 'field' => 'styledtext', 'label' => 'Review'],
		//     ],
		//     'required' => ['id', 'rating'],
		//     'index' => ['id', 'rating'],
		// ]);
	}
}
