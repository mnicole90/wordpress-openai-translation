# OpenAI Translation plugin for Wordpress

This plugin uses the [OpenAI API](https://beta.openai.com/) to translate your posts and pages into other languages.

This plugin requires PHP 8.1 or greater to run.

Current version: `1.0.0`

## Pre-requisites

You need to add your OpenAI API key to the environment variable `OPENAI_SECRET` in order to use this plugin.

## Installation

1. Download the plugin into your `wp-content/plugins` directory.
2. Activate the plugin in your Wordpress admin panel.

### Using SymfonyLocaleValidator component

If you want to use the Symfony Locale Validator, in your terminal run `composer install` in the plugin directory and set
the environment variable `OPENAI_TRANSLATION_VALIDATOR`to `symfony`.

## Usage

1. Go on the page or post you want to translate.
2. Click on the "Translate" button in the top right corner.
3. Click on the language you want to translate to.
4. Wait for the translation to be generated.
5. Click on the "Save" button to save the translation.

## Authors

* **Maxime Nicole** - <https://linkedin.com/in/mnicole>

## License

Copyright 2023 Maxime Nicole

Licensed under the MIT License: http://opensource.org/licenses/MIT
