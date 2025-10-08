<?php
declare(strict_types=1);

namespace Translation\Wordpress;

use Translation\Domain\Service\CustomLocaleValidator;
use Translation\Domain\Service\OpenAITranslator;
use Translation\Domain\Service\SymfonyLocaleValidator;
use Translation\Domain\UseCase\TranslateText\TranslateText;
use Translation\Domain\UseCase\TranslateText\TranslateTextRequest;
use Translation\Presentation\TranslateTextJsonPresenter;
use Translation\TranslationPlugin;

final class Endpoints
{
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(): void
    {
        register_rest_route(TranslationPlugin::NAMESPACE, '/translate', [
            'methods' => \WP_REST_Server::CREATABLE,
            'permission_callback' => [$this, 'privileged_permission_callback'],
            'callback' => [$this, 'translate_text'],
        ]);
    }

    public static function privileged_permission_callback(): bool
    {
        return current_user_can('edit_posts') || current_user_can('edit_pages');
    }

    private function createValidator(): \Translation\Domain\Service\LocaleValidatorInterface
    {
        if (
            get_option('openai_translation_validator_name') === 'symfony' &&
            class_exists('Symfony\Component\Validator\Constraints\Locale') &&
            class_exists('Symfony\Component\Validator\Validation')
        ) {
            return new SymfonyLocaleValidator();
        }

        return new CustomLocaleValidator();
    }

    public function translate_text(\WP_REST_Request $request): \WP_REST_Response
    {
        $response = new \WP_REST_Response();

        // Init use case
        $translator = new OpenAITranslator(get_option('openai_translation_api_key'));
        $translateText = new TranslateText($translator, $this->createValidator());
        $presenter = new TranslateTextJsonPresenter();

        // Create request
        $translateTextRequest = new TranslateTextRequest();
        $translateTextRequest->title = $request->get_param('title');
        $translateTextRequest->blocks = $request->get_param('blocks');
        $translateTextRequest->targetLanguage = $request->get_param('language');

        // Execute use case
        $translateText->execute($translateTextRequest, $presenter);

        // Generate response
        $response->set_data($presenter->json());
        if ($presenter->hasErrors()) {
            $response->set_status(400);
        }

        return $response;
    }
}
