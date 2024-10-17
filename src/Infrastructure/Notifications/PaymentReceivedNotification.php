<?php

namespace App\Infrastructure\Notifications;

use App\Application\Notifications\PaymentReceivedNotification as PaymentReceivedNotificationInterface;
use App\Application\Settings\SettingsInterface;
use App\Domain\Entities\Transaction;
use App\Domain\Entities\User;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

class PaymentReceivedNotification implements PaymentReceivedNotificationInterface
{
    private HttpClient $client;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly SettingsInterface $settings,
    ) {
        /** @var array{
         * notifications: array{
         *     url: string,
         * },
         * } $paymentSettings */
        $paymentSettings = $this->settings->get('payment');
        $this->client    = new HttpClient([
            'base_uri' => $paymentSettings['notifications']['url'],
            'headers'  => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
        ]);
    }

    public function notify(User $user, Transaction $transaction): void
    {
        try {
            $this->client->request('POST', 'notify');
        } catch (GuzzleException $e) {
            $this->logger->error('Error on notify payment', [$e->getMessage()]);
        }
    }
}
