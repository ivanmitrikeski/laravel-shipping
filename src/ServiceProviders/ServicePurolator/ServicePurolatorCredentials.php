<?php

namespace Mitrik\Shipping\ServiceProviders\ServicePurolator;

use Illuminate\Support\Str;
use Mitrik\Shipping\ServiceProviders\Credentials\Credentials;
use Mitrik\Shipping\ServiceProviders\Credentials\CredentialsInterface;

class ServicePurolatorCredentials extends Credentials implements CredentialsInterface
{
    /**
     * @param string $key
     * @param string $password
     * @param string $billingAccount
     * @param string $registeredAccount
     * @param string $userToken
     * @param bool $test
     */
    public function __construct(
        private readonly string $key,
        private readonly string $password,
        private readonly string $billingAccount,
        private readonly string $registeredAccount,
        private readonly string $userToken,
        private readonly bool   $test = false)
    {

    }

    /**
     * @return string
     */
    public function key(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function password(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function billingAccount(): string
    {
        return $this->billingAccount;
    }

    /**
     * @return string
     */
    public function registeredAccount(): string
    {
        return $this->registeredAccount;
    }

    /**
     * @return string
     */
    public function userToken(): string
    {
        return $this->userToken;
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
