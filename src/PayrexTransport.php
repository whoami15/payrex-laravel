<?php

declare(strict_types=1);

namespace LegionHQ\LaravelPayrex;

use Closure;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use LegionHQ\LaravelPayrex\Data\ApiResponseMetadata;
use LegionHQ\LaravelPayrex\Exceptions\AuthenticationException;
use LegionHQ\LaravelPayrex\Exceptions\InvalidRequestException;
use LegionHQ\LaravelPayrex\Exceptions\PayrexApiException;
use LegionHQ\LaravelPayrex\Exceptions\RateLimitException;
use LegionHQ\LaravelPayrex\Exceptions\ResourceNotFoundException;
use Psr\Http\Message\RequestInterface;
use Throwable;

/**
 * @internal Handles HTTP communication with the PayRex API. Not part of the public API.
 */
final class PayrexTransport
{
    protected ?ApiResponseMetadata $lastResponseMetadata = null;

    public function __construct(
        protected readonly string $secretKey,
        protected readonly string $baseUrl,
        protected readonly int $timeout,
        protected readonly int $connectTimeout,
        protected readonly int $retries,
        protected readonly int $retryDelay,
    ) {}

    /**
     * Send an API request.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     */
    public function request(string $method, string $uri, array $data = [], array $headers = []): array
    {
        $pending = $this->newRequest()->withHeaders($headers);

        $response = match (strtoupper($method)) {
            'GET' => $pending->get($uri, $data),
            'POST' => $pending->post($uri, $data),
            'PUT' => $pending->put($uri, $data),
            'DELETE' => $pending->delete($uri, $data),
            default => throw new InvalidArgumentException("Unsupported HTTP method: {$method}"),
        };

        return $this->handleResponse($response);
    }

    /**
     * Get metadata from the most recent API response.
     */
    public function getLastResponse(): ?ApiResponseMetadata
    {
        return $this->lastResponseMetadata;
    }

    /**
     * Build a new HTTP request with authentication and encoding.
     */
    protected function newRequest(): PendingRequest
    {
        $request = Http::baseUrl($this->baseUrl)
            ->withBasicAuth($this->secretKey, '')
            ->asForm()
            ->acceptJson()
            ->withUserAgent('laravel-payrex php/'.PHP_VERSION)
            ->timeout($this->timeout)
            ->connectTimeout($this->connectTimeout)
            ->withRequestMiddleware(self::normalizeFormEncoding());

        if ($this->retries > 0) {
            $request->retry(
                $this->retries,
                $this->retryDelay,
                fn (Throwable $exception, PendingRequest $_pendingRequest): bool => $exception instanceof RequestException
                    && $exception->response->serverError(),
                throw: false,
            );
        }

        return $request;
    }

    /**
     * Create a Guzzle request middleware that replaces numeric array indices
     * in form-encoded bodies with empty brackets.
     *
     * PHP's http_build_query encodes arrays as field[0]=a&field[1]=b, but
     * the PayRex API expects field[]=a&field[]=b.
     */
    protected static function normalizeFormEncoding(): Closure
    {
        return static function (RequestInterface $request): RequestInterface {
            $body = (string) $request->getBody();

            if ($body === '') {
                return $request;
            }

            $normalized = preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $body) ?? $body;

            return $request->withBody(Utils::streamFor($normalized));
        };
    }

    /**
     * Handle the API response and throw typed exceptions for error status codes.
     *
     * @return array<string, mixed>
     */
    protected function handleResponse(Response $response): array
    {
        $this->lastResponseMetadata = new ApiResponseMetadata(
            headers: array_map(
                fn (array $values): string => $values[0] ?? '',
                $response->headers(),
            ),
            statusCode: $response->status(),
        );

        if ($response->successful()) {
            return $response->json() ?? [];
        }

        $body = $response->json() ?? [];

        $status = $response->status();

        throw match ($status) {
            400 => InvalidRequestException::fromResponse($body, $status),
            401 => AuthenticationException::fromResponse($body, $status),
            404 => ResourceNotFoundException::fromResponse($body, $status),
            429 => RateLimitException::fromResponse($body, $status),
            default => PayrexApiException::fromResponse($body, $status),
        };
    }
}
