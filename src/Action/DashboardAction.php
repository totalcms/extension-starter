<?php

declare(strict_types=1);

namespace Acme\Starter\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TotalCMS\Renderer\TwigRenderer;

/**
 * Example admin page action.
 *
 * Accessible at /admin/ext/acme/starter/dashboard (requires login).
 * Renders inside the admin dashboard layout.
 */
class DashboardAction
{
	public function __construct(
		private readonly TwigRenderer $twigRenderer,
	) {
	}

	/**
	 * @param array<string,string> $args
	 */
	public function __invoke(
		ServerRequestInterface $request,
		ResponseInterface $response,
		array $args,
	): ResponseInterface {
		return $this->twigRenderer->template($response, 'admin/ext-page.twig', [
			'url' => [
				'path'   => $request->getUri()->getPath(),
				'query'  => $request->getUri()->getQuery(),
				'params' => $args,
				'page'   => 'ext-starter',
			],
			'extTitle'   => 'Starter Extension',
			'extContent' => '<p>This is an example admin page registered by the starter extension.</p>'
				. '<p>Replace this action with your own Twig template for a real extension.</p>'
				. '<h3>What you can do here</h3>'
				. '<ul>'
				. '<li>Display extension settings</li>'
				. '<li>Show extension-specific data</li>'
				. '<li>Provide management tools</li>'
				. '</ul>',
		]);
	}
}
