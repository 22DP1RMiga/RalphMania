<?php

namespace App\Helpers;

use Illuminate\Support\Facades\App;

class LocaleHelper
{
    /**
     * Iestata aplikācijas lokalizāciju pirms e-pasta sūtīšanas.
     * Izmanto lietotāja saglabāto locale vai pēc noklusējuma 'lv'.
     */
    public static function setForUser($user): void
    {
        $locale = $user?->locale ?? 'lv';
        App::setLocale(in_array($locale, ['lv', 'en']) ? $locale : 'lv');
    }

    /**
     * Iestata lokalizāciju pēc locale stringa.
     */
    public static function set(string $locale): void
    {
        App::setLocale(in_array($locale, ['lv', 'en']) ? $locale : 'lv');
    }
}
