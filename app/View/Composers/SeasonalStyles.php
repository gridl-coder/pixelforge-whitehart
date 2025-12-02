<?php

namespace PixelForge\View\Composers;

use Roots\Acorn\View\Composer;
use function is_front_page;
use function PixelForge\CMB2\get_theme_option;

class SeasonalStyles extends Composer
{
    protected static $views = [
        'layouts.app',
        'layouts.front-page',
    ];

    public function with(): array
    {
        $theme = (string) get_theme_option('seasonal_theme', 'none');
        $isChristmas = $theme === 'christmas';
        $isEnabled = $isChristmas && is_front_page();

        return [
            'seasonalStyles' => [
                'theme' => $theme,
                'christmas' => $isChristmas,
                'enabled' => $isEnabled,
            ],
        ];
    }
}
