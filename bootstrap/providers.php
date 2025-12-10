<?php

return [
    App\Providers\AppServiceProvider::class,
    // Register Filament admin panel provider only if Filament is installed
    ...(class_exists(\Filament\PanelProvider::class)
        ? [App\Providers\Filament\AdminPanelProvider::class]
        : []),
    App\Providers\FortifyServiceProvider::class,
    App\Providers\VoltServiceProvider::class,
];
