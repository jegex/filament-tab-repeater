<?php

namespace Jegex\FilamentTabRepeater;

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
}
