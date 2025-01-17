<?php

/*
 * This file is part of Flarum.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace Flarum\PackageManager;

use Carbon\Carbon;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Str;

class LastUpdateCheck
{
    public const KEY = 'flarum-package-manager.last_update_check';

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function save(array $updates): array
    {
        $lastUpdateCheck = [
            'checkedAt' => Carbon::now(),
            'updates' => $updates,
        ];

        $this->settings->set(self::KEY, json_encode($lastUpdateCheck));

        return $lastUpdateCheck;
    }

    public function get(): array
    {
        return json_decode($this->settings->get(self::KEY, '{}'), true);
    }

    public function forget(string $name, bool $wildcard = false): void
    {
        $lastUpdateCheck = json_decode($this->settings->get(self::KEY, '{}'), true);

        if (isset($lastUpdateCheck['updates']) && ! empty($lastUpdateCheck['updates']['installed'])) {
            $updatesListChanged = false;
            $pattern = preg_quote(str_replace('*', '.*', $name));

            foreach ($lastUpdateCheck['updates']['installed'] as $k => $package) {
                if (($wildcard && Str::of($package['name'])->test("/($pattern)/")) || $package['name'] === $name) {
                    unset($lastUpdateCheck['updates']['installed'][$k]);
                    $updatesListChanged = true;

                    if (! $wildcard) {
                        break;
                    }
                }
            }

            if ($updatesListChanged) {
                $lastUpdateCheck['updates']['installed'] = array_values($lastUpdateCheck['updates']['installed']);
                $this->settings->set(self::KEY, json_encode($lastUpdateCheck));
            }
        }
    }
}
