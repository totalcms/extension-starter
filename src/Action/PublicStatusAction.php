<?php

declare(strict_types=1);

namespace Acme\Starter\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Example public endpoint — no authentication required.
 *
 * Accessible at /ext/acme/starter/status
 * Use for webhooks, health checks, embeds, or anything that
 * must be accessible without credentials.
 */
class PublicStatusAction
{
	public function __invoke(
		ServerRequestInterface $request,
		ResponseInterface $response,
	): ResponseInterface {
		$data = json_encode([
			'status'    => 'ok',
			'extension' => 'acme/starter',
			'version'   => '1.0.0',
		]);

		$response->getBody()->write((string) $data);

		return $response->withHeader('Content-Type', 'application/json');
	}
}
