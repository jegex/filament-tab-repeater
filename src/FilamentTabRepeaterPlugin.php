<?php

namespace Jegex\FilamentTabRepeater;

use Filament\Contracts\Plugin;
use Filament\Panel;

/**
 * Compatibility plugin retained for panels that registered the package
 * explicitly before v2. The field itself does not require a panel plugin.
 */
class FilamentTabRepeaterPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-tab-repeater';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function register(Panel $panel): void
    {
        $panel
            ->discoverResources(
                in: __DIR__.'/Filament/Resources',
                for: 'Jegex\\FilamentTabRepeater\\Filament\\Resources',
            )
            ->discoverPages(
                in: __DIR__.'/Filament/Pages',
                for: 'Jegex\\FilamentTabRepeater\\Filament\\Pages',
            )
            ->discoverWidgets(
                in: __DIR__.'/Filament/Widgets',
                for: 'Jegex\\FilamentTabRepeater\\Filament\\Widgets',
            );
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
