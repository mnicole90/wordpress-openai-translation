<?php

/*
 * Plugin Name: OpenAI Translation
 * Description: Translate content of a post with OpenAI
 * Author: Maxime Nicole
 * Version: 1.0
 */

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

define('OPENAI_SECRET', getenv_docker('OPENAI_SECRET', ''));

require plugin_dir_path(__FILE__) . 'vendor/autoload.php';

$plugin = new Translation\TranslationPlugin(__FILE__, OPENAI_SECRET);
