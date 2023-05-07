<?php
declare(strict_types=1);

namespace Translation;

use Translation\Domain\Service\SymfonyLocaleValidator;
use Translation\Presentation\TranslateTextJsonPresenter;
use WP_REST_Server;

final readonly class TranslationPlugin
{
    const OPENAI_TRANSLATION_ACTIVATED = 'openai_translation_activated';
    const NAMESPACE = 'openai-translation/v1';

    public function __construct(
        private string $file,
        private string $apiKey
    )
    {
        register_activation_hook($this->file, [$this, 'plugin_activation']);
        add_action('admin_notices', [$this, 'notices_activation']);
        add_action('rest_api_init', [$this, 'register_routes']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('print_media_templates', [$this, 'add_tpl_translation_button']);
    }

    public function plugin_activation(): void
    {
        set_transient(self::OPENAI_TRANSLATION_ACTIVATED, true);
    }

    public function notices_activation(): void
    {
        if (get_transient(self::OPENAI_TRANSLATION_ACTIVATED)) {
            if (empty($this->apiKey)) {
                $this->render('notices-empty-openaikey');
                return;
            }

            $this->render('notices', [
                'message' => 'Translation plugin is now activated!',
            ]);

            delete_transient(self::OPENAI_TRANSLATION_ACTIVATED);
        }
    }

    public function register_routes(): void
    {
        register_rest_route(self::NAMESPACE, '/translate', [
            'methods' => WP_REST_Server::CREATABLE,
            'permission_callback' => [$this, 'privileged_permission_callback'],
            'callback' => [$this, 'translate_text'],
        ]);
    }

    public static function privileged_permission_callback(): bool
    {
        return current_user_can('edit_posts') || current_user_can('edit_pages');
    }

    public function translate_text(\WP_REST_Request $request): \WP_REST_Response
    {
        $response = new \WP_REST_Response();

        $translator = new Domain\Service\OpenAITranslator($this->apiKey);
        $validator = new SymfonyLocaleValidator();
        $translateText = new Domain\UseCase\TranslateText\TranslateText($translator, $validator);
        $translateTextRequest = new Domain\UseCase\TranslateText\TranslateTextRequest();
        $translateTextRequest->title = $request->get_param('title');
        $translateTextRequest->blocks = $request->get_param('blocks');
        $translateTextRequest->targetLanguage = $request->get_param('language');
        $presenter = new TranslateTextJsonPresenter();

        $translateText->execute($translateTextRequest, $presenter);

        $response->set_data($presenter->json());
        if ($presenter->hasErrors()) {
            $response->set_status(400);
        }

        return $response;
    }

    public function enqueue_assets(): void
    {
        global $pagenow;
        if (!in_array($pagenow, ['post.php', 'page.php'])) {
            return;
        }

        if ($pagenow === 'post.php' && !current_user_can('edit_posts')) {
            return;
        }

        if ($pagenow === 'page.php' && !current_user_can('edit_pages')) {
            return;
        }

        wp_register_script('translation.js', plugin_dir_url($this->file) . 'assets/js/translation.js', ['jquery'], '1.0.0');
        wp_enqueue_script('translation.js');

        // Add font awesome
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css', [], '5.15.3');
    }

    public function add_tpl_translation_button(): void
    {
        $this->render('translation-button');
    }

    public function render(string $name, array $args = []): void
    {
        extract($args);
        $file = plugin_dir_path($this->file) . 'views/' . $name . '.php';
        ob_start();
        include_once $file;
        echo ob_get_clean();
    }
}
