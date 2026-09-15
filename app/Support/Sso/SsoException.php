<?php

namespace App\Support\Sso;

use App\Http\Middleware\EnsureUserIsStaffMember;
use RuntimeException;

/**
 * A Microsoft sign-in that stopped short of logging someone in.
 *
 * The exception message and context are for the log only — they name the
 * failing step, an AADSTS code, the address that was refused — and never
 * reach the browser. userMessage() is the plain sentence the login page shows.
 */
class SsoException extends RuntimeException
{
    public const FAILED = 'Microsoft sign-in could not be completed. Please try again, or contact the system administrator if the problem continues.';

    public const CANCELLED = 'Microsoft sign-in was cancelled.';

    public const EXPIRED = 'Your sign-in attempt has expired. Please try again.';

    public function __construct(
        string $logMessage,
        private readonly string $userMessage = self::FAILED,
        private readonly array $context = [],
    ) {
        parent::__construct($logMessage);
    }

    /** Microsoft sign-in worked, but this is not someone the admin panel lets in. */
    public static function notAuthorised(string $reason, array $context = []): self
    {
        return new self($reason, EnsureUserIsStaffMember::NOT_AUTHORISED_MESSAGE, $context);
    }

    public function userMessage(): string
    {
        return $this->userMessage;
    }

    public function context(): array
    {
        return $this->context;
    }
}
