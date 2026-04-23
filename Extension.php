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
		// Adds a link to the admin sidebar
		$context->addAdminNavItem(new AdminNavItem(
			label: 'Starter',
			icon: 'starter',
			url: '/ext/acme/starter/dashboard',
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

		// ── Routes ──────────────────────────────────────────────────────
		// Register admin pages at /ext/acme/starter/...
		$context->addRoutes(function (\Slim\Routing\RouteCollectorProxy $group): void {
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
