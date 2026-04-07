<?php

namespace Jegex\FilamentTabRepeater;

use Filament\Panel;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentTabRepeaterServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-tab-repeater')
            ->hasViews();
    }

    public function packageBooted(): void
    {
        FilamentAsset::register([
            Css::make('filament-tab-repeater', __DIR__.'/../resources/css/filament-tab-repeater.css'),
        ], 'jegex/filament-tab-repeater');
    }

    public function packageRegistered(): void
    {
        Panel::configureUsing(function (Panel $panel) {
            return $panel->plugin(FilamentTabRepeaterPlugin::make());
        });
    }
}
