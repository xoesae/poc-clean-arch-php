<?php

namespace App\Application\Actions\User;

use App\Application\Actions\Action;
use App\Application\UseCases\User\CreateUserUseCase;
use App\Domain\Exceptions\DomainException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class CreateUserAction extends Action
{
    public function __construct(protected LoggerInterface $logger, private readonly CreateUserUseCase $useCase)
    {
        parent::__construct($this->logger);
    }

    protected function action(): Response
    {
        /** @var array{
         * name: string,
         * email: string,
         * password: string,
         * document_number: string,
         * type: string
         * } $data */
        $data = $this->getFormData();

        try {
            $user = $this->useCase->handle(
                name: $data['name'],
                email: $data['email'],
                password: $data['password'],
                documentNumber: $data['document_number'],
                type: $data['type'],
            );
        } catch (DomainException $e) {
            $this->logger->info('User with invalid document number: ' . $data['document_number'] . '.');

            return $this->respondWithData([
                'error' => $e->getMessage(),
            ], 400);
        }

        $this->logger->info('User created with document number: ' . $data['document_number'] . '.');

        return $this->respondWithData($user, 201);
    }
}
