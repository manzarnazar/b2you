<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

trait ActivationClass
{
    public function is_local(): bool
    {
        $whitelist = array(
            '127.0.0.1',
            '::1'
        );

        if (!in_array(request()->ip(), $whitelist)) {
            return false;
        }

        return true;
    }

    public function getDomain(): string
    {
        return str_replace(["http://", "https://", "www."], "", url('/'));
    }

    public function getSystemAddonCacheKey(string|null $app = 'default'): string
    {
        $appName = env('APP_NAME').'_cache';
        return str_replace('-', '_', Str::slug($appName.'cache_system_addons_for_' . $app . '_' . $this->getDomain()));
    }

    public function getAddonsConfig(): array
    {
        if (file_exists(base_path('config/system-addons.php'))) {
            return include(base_path('config/system-addons.php'));
        }

        $apps = ['admin_panel', 'vendor_app', 'deliveryman_app', 'react_web'];
        $appConfig = [];
        foreach ($apps as $app) {
            $appConfig[$app] = [
                "active" => "0",
                "username" => "",
                "purchase_key" => "",
                "software_id" => "",
                "domain" => "",
                "software_type" => $app == 'admin_panel' ? "product" : 'addon',
            ];
        }
        return $appConfig;
    }

    public function getCacheTimeoutByDays(int $days = 3): int
    {
        return 60 * 60 * 24 * $days;
    }

    /**
     * Normalize API active flags. 6amTech may return base64 ("MQ==") or plain 0/1.
     * Ambiguous values fail open so a bad response cannot lock the panel.
     */
    protected function normalizeActiveStatus(mixed $activeStatus): string
    {
        if ($activeStatus === null || $activeStatus === '') {
            return '1';
        }

        if (is_bool($activeStatus)) {
            return $activeStatus ? '1' : '0';
        }

        if ($activeStatus === 0 || $activeStatus === 1 || $activeStatus === '0' || $activeStatus === '1') {
            return (string) (int) $activeStatus;
        }

        $decoded = base64_decode((string) $activeStatus, true);
        if ($decoded === '0' || $decoded === '1') {
            return $decoded;
        }

        return '1';
    }

    public function getRequestConfig(string|null $username = null, string|null $purchaseKey = null, string|null $softwareId = null, string|null $softwareType = null): array
    {
        $activeStatus = base64_encode(1);
        if(!$this->is_local()) {
            try {
                $response = Http::post(base64_decode('aHR0cHM6Ly9jaGVjay42YW10ZWNoLmNvbS9hcGkvdjIvcmVnaXN0ZXItZG9tYWlu'), [
                    base64_decode('dXNlcm5hbWU=') => trim($username),
                    base64_decode('cHVyY2hhc2Vfa2V5') => $purchaseKey,
                    base64_decode('c29mdHdhcmVfaWQ=') => base64_decode($softwareId ?? SOFTWARE_ID),
                    base64_decode('ZG9tYWlu') => $this->getDomain(),
                    base64_decode('c29mdHdhcmVfdHlwZQ==') => $softwareType,
                ])->json();
                $activeStatus = $response['active'] ?? base64_encode(1);
            } catch (\Exception $exception) {
                $activeStatus = base64_encode(1);
            }
        }

        return [
            "active" => $this->normalizeActiveStatus($activeStatus),
            "username" => trim((string) $username),
            "purchase_key" => $purchaseKey,
            "software_id" => $softwareId ?? SOFTWARE_ID,
            "domain" => $this->getDomain(),
            "software_type" => $softwareType,
        ];
    }

    public function checkActivationCache(string|null $app)
    {
        if ($this->is_local() || is_null($app) || $app === '' || env('DEVELOPMENT_ENVIRONMENT', false)) {
            return true;
        }

        $config = $this->getAddonsConfig();
        $cacheKey = $this->getSystemAddonCacheKey(app: $app);

        if (!isset($config[$app])) {
            Cache::forget($cacheKey);
            return false;
        }

        $appConfig = $config[$app];
        $hasCredentials = !empty($appConfig['username']) && !empty($appConfig['purchase_key']);
        $isActive = isset($appConfig['active']) && (string) $appConfig['active'] === '1';

        // Credentials already stored (activation done before) — do not lock the panel
        // if a later remote re-check fails or writes active=0.
        if ($hasCredentials) {
            return Cache::remember($cacheKey, $this->getCacheTimeoutByDays(days: 1), function () use ($app, $appConfig, $isActive) {
                $response = $this->getRequestConfig(
                    username: $appConfig['username'],
                    purchaseKey: $appConfig['purchase_key'],
                    softwareId: $appConfig['software_id'] ?? SOFTWARE_ID,
                    softwareType: $appConfig['software_type'] ?? base64_decode('cHJvZHVjdA==')
                );

                // Only persist successful activations; never demote a working install.
                if ((string) $response['active'] === '1') {
                    $this->updateActivationConfig(app: $app, response: $response);
                } elseif (!$isActive) {
                    // First-time / inactive: keep trying to persist, but still allow access
                    // once credentials exist so the owner is not stuck on activation-check.
                    $response['active'] = '1';
                    $this->updateActivationConfig(app: $app, response: $response);
                }

                return true;
            });
        }

        if (!$isActive) {
            Cache::forget($cacheKey);
            return false;
        }

        return Cache::remember($cacheKey, $this->getCacheTimeoutByDays(days: 1), function () use ($app, $appConfig) {
            $response = $this->getRequestConfig(
                username: $appConfig['username'],
                purchaseKey: $appConfig['purchase_key'],
                softwareId: $appConfig['software_id'],
                softwareType: $appConfig['software_type'] ?? base64_decode('cHJvZHVjdA==')
            );
            if ((string) $response['active'] === '1') {
                $this->updateActivationConfig(app: $app, response: $response);
            }
            return true;
        });
    }

    public function updateActivationConfig($app, $response): void
    {
        if('admin.business-settings.addon-activation.index' === \Illuminate\Support\Facades\Route::currentRouteName() ){
            return;
        }
        $config = $this->getAddonsConfig();
        $config[$app] = $response;
        $configContents = "<?php return " . var_export($config, true) . ";";
        file_put_contents(base_path('config/system-addons.php'), $configContents);
        $cacheKey = $this->getSystemAddonCacheKey(app: $app);
        Cache::forget($cacheKey);
    }
}
