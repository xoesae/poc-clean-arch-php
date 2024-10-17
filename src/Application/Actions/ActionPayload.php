<?php

declare(strict_types=1);

namespace App\Application\Actions;

use JsonSerializable;

class ActionPayload implements JsonSerializable
{
    private int $statusCode;

    /**
     * @var array<string, mixed>|object|null
     */
    private array|object|null $data;

    private ?ActionError $error;

    /**
     * @param int $statusCode
     * @param array<string, mixed>|object|null $data
     * @param ActionError|null $error
     */
    public function __construct(
        int $statusCode = 200,
        array|object|null $data = null,
        ?ActionError $error = null
    ) {
        $this->statusCode = $statusCode;
        $this->data       = $data;
        $this->error      = $error;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, mixed>|null|object
     */
    public function getData(): object|array|null
    {
        return $this->data;
    }

    public function getError(): ?ActionError
    {
        return $this->error;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $payload = [
            'statusCode' => $this->statusCode,
        ];

        if ($this->data !== null) {
            $payload['data'] = $this->data;
        } elseif ($this->error !== null) {
            $payload['error'] = $this->error;
        }

        return $payload;
    }
}
