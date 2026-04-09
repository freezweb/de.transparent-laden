<?php

namespace App\Libraries\Provider;

use App\Models\ProviderModel;

class ProviderFactory
{
    private static array $instances = [];

    public static function create(array $providerRow): ProviderAdapterInterface
    {
        $providerId = $providerRow['id'];

        if (isset(self::$instances[$providerId])) {
            return self::$instances[$providerId];
        }

        $adapterClass = $providerRow['adapter_class'];

        if (! class_exists($adapterClass)) {
            throw new \RuntimeException("Provider adapter class not found: {$adapterClass}");
        }

        $config = [];
        if (! empty($providerRow['config_encrypted'])) {
            $encrypter = service('encrypter');
            $decrypted = $encrypter->decrypt(base64_decode($providerRow['config_encrypted']));
            $config = json_decode($decrypted, true) ?? [];
        }

        $adapter = new $adapterClass($config);

        if (! ($adapter instanceof ProviderAdapterInterface)) {
            throw new \RuntimeException("Adapter {$adapterClass} does not implement ProviderAdapterInterface");
        }

        self::$instances[$providerId] = $adapter;
        return $adapter;
    }

    public static function getForProvider(int $providerId): ProviderAdapterInterface
    {
        $providerModel = model(ProviderModel::class);
        $provider = $providerModel->find($providerId);

        if (! $provider) {
            throw new \RuntimeException("Provider not found: {$providerId}");
        }

        return self::create($provider);
    }

    public static function clearInstances(): void
    {
        self::$instances = [];
    }
}
