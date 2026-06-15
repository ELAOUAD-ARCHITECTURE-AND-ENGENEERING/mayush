<?php

namespace Mayush\Shipping\Onessta\Exceptions;

class RemoteApiException extends OnesstaException
{
    protected int $httpStatus;
    protected ?array $responseBody;

    public function __construct(
        string $message = 'Remote API error',
        int $httpStatus = 500,
        ?array $responseBody = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 'REMOTE_API_ERROR', $httpStatus, $previous);
        $this->httpStatus = $httpStatus;
        $this->responseBody = $responseBody;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    public function getResponseBody(): ?array
    {
        return $this->responseBody;
    }
}
