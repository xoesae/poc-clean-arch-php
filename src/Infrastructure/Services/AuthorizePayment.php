<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Application\Contracts\AuthorizePayment as AuthorizePaymentInterface;
use App\Application\Settings\SettingsInterface;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;

class AuthorizePayment implements AuthorizePaymentInterface
{
    private HttpClient $client;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly SettingsInterface $settings,
    ) {
        /** @var array{
         * authorization: array{
         *     url: string,
         * },
         * } $paymentSettings */
        $paymentSettings = $this->settings->get('payments');
        $this->client    = new HttpClient([
            'base_uri' => $paymentSettings['authorization']['url'],
            'headers'  => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
        ]);
    }

    public function isAuthorized(): bool
    {
        try {
            $response = $this->client->request('GET', 'authorize');
        } catch (RequestException) {
            return false;
        } catch (GuzzleException $e) {
            $this->logger->error($e->getMessage());

            return false;
        }

        /** @var object $contents */
        $contents = json_decode($response->getBody()->getContents());

        return $contents->data->authorization ?? false;
    }

    public function setHttpClient(HttpClient $httpClient): void
    {
        $this->client = $httpClient;
    }
}
