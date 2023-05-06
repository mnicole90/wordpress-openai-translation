<?php
declare(strict_types=1);

namespace TranslationTest\Domain\UseCase\TranslateText;

use PHPUnit\Framework\TestCase;
use Translation\Domain\UseCase\TranslateText\TranslateText;
use Translation\Domain\UseCase\TranslateText\TranslateTextPresenterInterface;
use Translation\Domain\UseCase\TranslateText\TranslateTextResponse;
use TranslationTest\_Mock\Domain\Service\FakeLocaleValidator;
use TranslationTest\_Mock\Domain\Service\FakeTranslator;

class TranslateTextTest extends TestCase implements TranslateTextPresenterInterface
{
    private TranslateText $translateText;
    private TranslateTextResponse $response;

    public function present(TranslateTextResponse $response): void
    {
        $this->response = $response;
    }

    public function setUp(): void
    {
        $translator = new FakeTranslator();
        $validator = new FakeLocaleValidator();
        $this->translateText = new TranslateText(
            $translator,
            $validator
        );
    }

    public function test_fail_when_language_is_not_provided()
    {
        $request = TranslateTextRequestBuilder::frenchRequest()->withTargetLanguage('')->build();
        $this->translateText->execute($request, $this);

        $shouldBe = ['targetLanguage' => 'form.targetLanguage.required'];
        $this->assertEquals($shouldBe, $this->response->getErrors());
    }

    public function test_fail_when_title_is_not_provided()
    {
        $request = TranslateTextRequestBuilder::frenchRequest()->withTitle('')->build();
        $this->translateText->execute($request, $this);

        $shouldBe = ['title' => 'form.title.required'];
        $this->assertEquals($shouldBe, $this->response->getErrors());
    }

    public function test_fail_when_blocks_is_not_provided()
    {
        $request = TranslateTextRequestBuilder::frenchRequest()->withBlocks([])->build();
        $this->translateText->execute($request, $this);

        $shouldBe = ['blocks' => 'form.blocks.required'];
        $this->assertEquals($shouldBe, $this->response->getErrors());
    }

    public function test_fail_when_empty_request()
    {
        $request = TranslateTextRequestBuilder::empty()->build();
        $this->translateText->execute($request, $this);

        $shouldBe = [
            'title' => 'form.title.required',
            'blocks' => 'form.blocks.required',
            'targetLanguage' => 'form.targetLanguage.required'
        ];
        $this->assertEquals($shouldBe, $this->response->getErrors());
    }

    public function test_fail_if_language_is_not_supported()
    {
        $request = TranslateTextRequestBuilder::frenchRequest()->withTargetLanguage('toto')->build();
        $this->translateText->execute($request, $this);

        $shouldBe = ['targetLanguage' => 'form.targetLanguage.not_supported'];
        $this->assertEquals($shouldBe, $this->response->getErrors());
    }

    public function test_translation_in_french_is_working()
    {
        $request = TranslateTextRequestBuilder::frenchRequest()->build();
        $this->translateText->execute($request, $this);

        $shouldBe = 'Bonjour';
        $this->assertEquals($shouldBe, $this->response->getTitle());
    }

    public function test_translation_in_spanish_is_working()
    {
        $request = TranslateTextRequestBuilder::spanishRequest()->build();
        $this->translateText->execute($request, $this);

        $shouldBe = 'Hola';
        $this->assertEquals($shouldBe, $this->response->getTitle());
    }
}
