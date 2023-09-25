<?php

namespace Mitrik\Shipping\ServiceProviders\ServiceUSPS;

use Illuminate\Support\Str;
use Mitrik\Shipping\ServiceProviders\Credentials\Credentials;
use Mitrik\Shipping\ServiceProviders\Credentials\CredentialsInterface;

class ServiceUSPSCredentials extends Credentials implements CredentialsInterface
{
    /**
     * @param string $username
     * @param string $password
     * @param bool $test
     */
    public function __construct(
        private readonly string $username,
        private readonly string $password,
        private readonly bool $test = false
    )
    {

    }

    /**
     * @return string
     */
    public function username(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function password(): string
    {
        return $this->password;
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
