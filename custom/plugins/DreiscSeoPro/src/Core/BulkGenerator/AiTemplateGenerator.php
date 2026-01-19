<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\BulkGenerator;

use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkEnum;
use DreiscSeoPro\Core\CustomSetting\CustomSettingLoader;
use DreiscSeoPro\Core\Seo\SeoUrlSlugify;

class AiTemplateGenerator
{
    /**
     * @var CustomSettingLoader
     */
    protected CustomSettingLoader $customSettingLoader;

    /**
     * @var SeoUrlSlugify
     */
    protected $seoUrlSlugify;

    /**
     * @param CustomSettingLoader $customSettingLoader
     * @param SeoUrlSlugify $seoUrlSlugify
     */
    public function __construct(CustomSettingLoader $customSettingLoader, SeoUrlSlugify $seoUrlSlugify)
    {
        $this->customSettingLoader = $customSettingLoader;
        $this->seoUrlSlugify = $seoUrlSlugify;
    }

    public function generate(string $prompt, string $seoOption): string
    {
        $customSettingStruct = $this->customSettingLoader->load();
        $openAiConfig  = $customSettingStruct->getAi()->getOpenAi();

        if (empty($openAiConfig->getApiKey())) {
            throw new \RuntimeException('The OpenAI key has not yet been stored in the SEO Professional settings. You can find further information at: https://de.dreischild.com/docs/seo-professional/modules/settings/seo-settings#kuenstliche-intelligenz---openai');
        }

        if (empty($openAiConfig->getModel())) {
            throw new \RuntimeException('The OpenAI model has not yet been stored in the SEO Professional settings. You can find further information at: https://de.dreischild.com/docs/seo-professional/modules/settings/seo-settings#kuenstliche-intelligenz---openai');
        }

        if (!class_exists(\OpenAI::class)) {
            throw new \RuntimeException('OpenAI not installed. To install, please run “composer require openai-php/client” in the Shopware root directory.');
        }

        $client = \OpenAI::client($openAiConfig->getApiKey());

        $result = $client->chat()->create([
            'model' => $openAiConfig->getModel(),
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        $renderedTemplate = $result->choices[0]->message->content;

        /** Slugify the template result, if it's an url */
        if (DreiscSeoBulkEnum::SEO_OPTION__URL === $seoOption) {
            $renderedTemplate = $this->seoUrlSlugify->convert($renderedTemplate);
        }

        return $renderedTemplate;
    }
}
