<?php

namespace Mitrik\Shipping\ServiceProviders\ServiceFedEx;

use Illuminate\Support\Str;
use Mitrik\Shipping\ServiceProviders\Credentials\Credentials;
use Mitrik\Shipping\ServiceProviders\Credentials\CredentialsInterface;

class ServiceFedExCredentials extends Credentials implements CredentialsInterface
{

    /**
     * @param string $clientId
     * @param string $clientSecret
     * @param string $accountNumber
     * @param bool $test
     */
    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $accountNumber,
        private readonly bool $test = false,
    )
    {

    }

    /**
     * @return string|null
     */
    public function clientId(): ?string
    {
        return $this->clientId;
    }

    /**
     * @return string|null
     */
    public function clientSecret(): ?string
    {
        return $this->clientSecret;
    }

    /**
     * @return string|null
     */
    public function accountNumber(): ?string
    {
        return $this->accountNumber;
    }

    /**
     * @return bool
     */
    public function test(): bool
    {
        return $this->test;
    }

    /**
     * @return array
     */
    public static function credentialKeys(): array
    {
        $className = self::class;
        $className = explode('\\', $className);
        $className = end($className);

        $className = strtoupper(Str::snake(str_replace('Credentials', '', $className)));
        $className = str_replace('SERVICE_', '', $className);
        $className = str_replace('U_S_P_S', 'USPS', $className);
        $className = str_replace('U_P_S', 'UPS', $className);
        $className = str_replace('FED_EX', 'FEDEX', $className);
        $result = [];

        foreach (get_class_vars(self::class) as $property => $propertyValue) {
            $result[] = $className . '_' . strtoupper(Str::snake($property));
        }

        return $result;
    }
}
