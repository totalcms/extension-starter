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
		$context->addTwigFunction(
			new TwigFunction('starter_greet', function (string $name): string {
				return "Hello, {$name}!";
			})
		);

		// ── Twig Filters ────────────────────────────────────────────────
		// Available in templates: {{ text|reverse_words }}
		$context->addTwigFilter(
			new TwigFilter('reverse_words', function (string $text): string {
				return implode(' ', array_reverse(explode(' ', $text)));
			})
		);

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
		// Adds a widget to the admin home screen
		$context->addDashboardWidget(new DashboardWidget(
			id: 'starter-widget',
			label: 'Starter Widget',
			template: 'widgets/starter.twig',
			position: 'sidebar',
			priority: 50,
		));

		// ── Event Listeners ─────────────────────────────────────────────
		// React to content changes
		$context->addEventListener('object.created', function (array $payload): void {
			// Called after any object is created in any collection
			// $payload contains: 'collection' and 'id'
			error_log("[Starter] Object created: {$payload['collection']}/{$payload['id']}");
		});

		$context->addEventListener('object.updated', function (array $payload): void {
			error_log("[Starter] Object updated: {$payload['collection']}/{$payload['id']}");
		});

		// ── Custom Field Types ──────────────────────────────────────────
		// Register a new field type usable in schemas (class must extend FormField)
		// $context->addFieldType('colorpicker', \Acme\Starter\Fields\ColorPickerField::class);

		// ── API Routes ──────────────────────────────────────────────────
		// Protected API at /ext/acme/starter/... (requires session or API key)
		$context->addRoutes(function (\Slim\Routing\RouteCollectorProxy $group): void {
			$group->get('/api/hello', Action\ApiHelloAction::class);
		});

		// ── Public Routes ───────────────────────────────────────────────
		// Unauthenticated routes at /ext/acme/starter/... (no auth)
		// Use for webhooks, embeds, and endpoints accessible without credentials.
		$context->addPublicRoutes(function (\Slim\Routing\RouteCollectorProxy $group): void {
			$group->get('/status', Action\PublicStatusAction::class);
		});

		// ── Admin Routes ────────────────────────────────────────────────
		// Admin pages at /admin/ext/acme/starter/... (requires login)
		// Templates can extend admin-dashboard.twig for the admin layout.
		$context->addAdminRoutes(function (\Slim\Routing\RouteCollectorProxy $group): void {
			$group->get('/dashboard', Action\DashboardAction::class);
		});
	}

	public function boot(ExtensionContext $context): void
	{
		// The boot phase runs after ALL extensions have registered.
		// Use $context->get() to resolve services from the DI container.
		//
		// Example: read a setting configured by the admin
		$greeting = $context->setting('greeting', 'Hello');

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
