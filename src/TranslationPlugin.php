<?php
declare(strict_types=1);

namespace Translation;

use Translation\Wordpress\ApiKeyManager;
use Translation\Wordpress\Endpoints;
use Translation\Wordpress\Installation;
use Translation\Wordpress\ManageAssets;
use Translation\Wordpress\SettingsPage;

final readonly class TranslationPlugin
{
    const NAMESPACE = 'wp-translation/v1';

    public function __construct(private string $file)
    {
        new Installation($this->file);
        new SettingsPage($this->file);
        new Endpoints();
        new ManageAssets($this->file);
    }

    public static function getAvailableBackends(): array
    {
        return ApiKeyManager::getAvailableBackends();
    }

    public static function getSupportedBlockTypes(): array
    {
        // Call static method directly from TranslateText use case
        return \Translation\Domain\UseCase\TranslateText\TranslateText::getSupportedBlockTypes();
    }

    public static function getLanguageList(): array
    {
        $languageNames = [
            'en_GB' => __('English (GB)', 'wordpress-openai-translation'),
            'en_US' => __('English (US)', 'wordpress-openai-translation'),
            'fr_FR' => __('French', 'wordpress-openai-translation'),
            'es_ES' => __('Spanish', 'wordpress-openai-translation'),
            'de_DE' => __('German', 'wordpress-openai-translation'),
            'it_IT' => __('Italian', 'wordpress-openai-translation'),
            'ja_JP' => __('Japanese', 'wordpress-openai-translation'),
            'nl_NL' => __('Dutch', 'wordpress-openai-translation'),
            'ro_RO' => __('Romanian', 'wordpress-openai-translation'),
            'pt_PT' => __('Portuguese', 'wordpress-openai-translation'),
            'cs_CZ' => __('Czech', 'wordpress-openai-translation'),
        ];

        $languagesString = defined('TRANSLATION_LANGUAGES') ? TRANSLATION_LANGUAGES : 'en_GB,en_US,fr_FR,es_ES,de_DE,it_IT,ja_JP';
        $locales = array_map('trim', explode(',', $languagesString));

        // Consider languages setup in PolyLang if enabled (and if TRANSLATION_LANGUAGES contains "pll")
        if (function_exists('pll_languages_list') && in_array('pll', $locales)) {
            $pllLocales = array_map(fn($e) => str_replace('_formal', '', $e->locale), pll_languages_list(['fields' => null]));
            $pllLanguagesNames  = array_combine(
                $pllLocales,
                array_map(fn($e) => $e->name, pll_languages_list(['fields' => null]))
            );
            $languageNames = array_merge($pllLanguagesNames, $languageNames);
            $locales = array_merge($locales, $pllLocales);
        }

        $languages = [];
        foreach ($locales as $locale) {
            if ($locale === 'pll') continue;
            $languages[] = [
                'code' => $locale,
                'label' => $languageNames[$locale] ?? $locale,
            ];
        }

        return $languages;
    }

    public static function localeToLanguage($locale)
    {
        $l = TranslationPlugin::getLanguageList();
        $keys = array_combine(
            array_map(fn($e) => $e['code'], $l),
            array_map(fn($e) => $e['label'], $l)
        );
        return $keys[$locale] ?? $locale;
    }
}
