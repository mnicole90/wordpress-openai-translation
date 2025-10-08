<?php

/*
 * Plugin Name: OpenAI Translation
 * Description: Translate content of a post with OpenAI
 * Author: Maxime Nicole
 * Version: 1.1.0
 */

// Make sure we don't expose any info if called directly

if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

if (!defined('OPENAI_SECRET')) {
    define('OPENAI_SECRET', getenv('OPENAI_SECRET', ''));
}

if (!defined('OPENAI_TRANSLATION_VALIDATOR')) {
    define('OPENAI_TRANSLATION_VALIDATOR', getenv('OPENAI_TRANSLATION_VALIDATOR', 'custom'));
}

$autoload = plugin_dir_path(__FILE__) . 'vendor/autoload.php';
if (file_exists($autoload)) {
    require $autoload;
}
require plugin_dir_path(__FILE__) . 'vendor/autoload.php';

$plugin = new Translation\TranslationPlugin(__FILE__, OPENAI_API_KEY, OPENAI_TRANSLATION_VALIDATOR);
