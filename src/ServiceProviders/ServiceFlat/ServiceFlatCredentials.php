<?php

namespace Mitrik\Shipping\ServiceProviders\ServiceFlat;

use Mitrik\Shipping\ServiceProviders\Credentials\Credentials;
use Mitrik\Shipping\ServiceProviders\Credentials\CredentialsInterface;

class ServiceFlatCredentials extends Credentials implements CredentialsInterface
{
    /**
     *
     */
    public function __construct(
        private readonly bool $test = false
    )
    {

    }

    public function test(): bool
    {
        return $this->test;
    }

    /**
     * @return array
     */
    public static function credentialKeys(): array
    {
        return [];
    }
}
