<?php

namespace App\Application\Actions\Payment;

use App\Application\Actions\Action;
use App\Application\UseCases\PayUserUseCase;
use App\Domain\Exceptions\DomainException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class PayUserAction extends Action
{
    public function __construct(LoggerInterface $logger, private readonly PayUserUseCase $useCase)
    {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        /** @var array{
         * value: float,
         * payer: string,
         * payee: string
         * } $data */
        $data = $this->getFormData();

        try {
            $transaction = $this->useCase->handle(
                $data['payer'],
                $data['payee'],
                intval($data['value'] * 100),
            );
        } catch (DomainException $e) {
            return $this->respondWithData([
                'error' => $e->getMessage(),
            ], 400);
        }

        return $this->respondWithData($transaction);
    }
}
