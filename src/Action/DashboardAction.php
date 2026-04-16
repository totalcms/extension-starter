<?php

declare(strict_types=1);

namespace Acme\Starter\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Example admin page action.
 *
 * Accessible at /ext/acme/starter/dashboard
 */
class DashboardAction
{
	public function __invoke(
		ServerRequestInterface $request,
		ResponseInterface $response,
	): ResponseInterface {
		$html = <<<'HTML'
		<!DOCTYPE html>
		<html>
		<head><title>Starter Extension</title></head>
		<body>
			<h1>Starter Extension Dashboard</h1>
			<p>This is an example admin page registered by the starter extension.</p>
			<p>Replace this with your own Twig template rendering.</p>
		</body>
		</html>
		HTML;

		$response->getBody()->write($html);

		return $response;
	}
}
