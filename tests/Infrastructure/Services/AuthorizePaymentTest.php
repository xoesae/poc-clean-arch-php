<?php

namespace Tests\Infrastructure\Services;

use App\Infrastructure\Services\AuthorizePayment;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;

class AuthorizePaymentTest extends TestCase
{
    private function getMockedService(int $code, array $data = []): AuthorizePayment
    {
        $mock = new MockHandler([
            new Response($code, ['Content-Type' => 'application/json'], json_encode($data)),
        ]);

        $client = new HttpClient([
            'handler' => HandlerStack::create($mock),
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
        ]);

        /** @var AuthorizePayment $authorizePaymentService */
        $authorizePaymentService = $this->getAppInstance()->getContainer()->get(AuthorizePayment::class);
        $authorizePaymentService->setHttpClient($client);

        return $authorizePaymentService;
    }

    public function testAuthorizedPayment()
    {
        $authorizePaymentService = $this->getMockedService(200, [
            'status' => 'success',
            'data'   => [
                'authorization' => true
            ],
        ]);

        $authorized = $authorizePaymentService->isAuthorized();

        $this->assertTrue($authorized);
    }

    public function testNotAuthorizedPayment()
    {
        $authorizePaymentService = $this->getMockedService(403, [
            'status' => 'fail',
            'data'   => [
                'authorization' => false
            ],
        ]);

        $authorized = $authorizePaymentService->isAuthorized();

        $this->assertFalse($authorized);
    }

    public function testServerErrorOnAuthorizePayment()
    {
        $authorizePaymentService = $this->getMockedService(500);

        $authorized = $authorizePaymentService->isAuthorized();

        $this->assertFalse($authorized);
    }
}
