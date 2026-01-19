<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Product;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;
use DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Product\Review\AuthorStruct;

class ReviewStruct extends AbstractCustomSettingStruct
{
    final const DEFAULT__AUTHOR_COMPLILATION = AuthorStruct::COMPILATION__STATIC_SNIPPET;

    /**
     * @var AuthorStruct
     */
    protected $author;

    /**
     * @param array $reviewSettings
     * @param string $settingContext
     */
    public function __construct(array $reviewSettings, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->author = new AuthorStruct(
            !empty($reviewSettings['author']['compilation']) ? $reviewSettings['author']['compilation'] : $this->setDefault(self::DEFAULT__AUTHOR_COMPLILATION),
            $settingContext
        );
    }

    public function getAuthor(): AuthorStruct
    {
        return $this->author;
    }

    public function setAuthor(AuthorStruct $author): ReviewStruct
    {
        $this->author = $author;

        return $this;
    }
}
