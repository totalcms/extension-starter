<?php

declare(strict_types=1);

namespace Acme\Starter\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Example protected API endpoint.
 *
 * Accessible at /ext/acme/starter/api/hello (requires session or API key).
 */
class ApiHelloAction
{
	public function __invoke(
		ServerRequestInterface $request,
		ResponseInterface $response,
	): ResponseInterface {
		$data = json_encode([
			'message' => 'Hello from the Starter extension API!',
			'version' => '1.0.0',
		]);

		$response->getBody()->write((string) $data);

		return $response->withHeader('Content-Type', 'application/json');
	}
}
