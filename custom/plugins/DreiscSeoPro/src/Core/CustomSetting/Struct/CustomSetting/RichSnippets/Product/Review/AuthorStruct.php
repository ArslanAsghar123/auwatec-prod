<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\CustomSetting\Struct\CustomSetting\RichSnippets\Product\Review;

use DreiscSeoPro\Core\CustomSetting\Struct\AbstractCustomSettingStruct;

class AuthorStruct extends AbstractCustomSettingStruct
{
    final const COMPILATION__NOT_DISPLAY = 'notDisplay';
    final const COMPILATION__STATIC_SNIPPET = 'staticSnippet';
    final const COMPILATION__FIRSTNAME = 'firstName';
    final const COMPILATION__FIRSTNAME_AND_FIRST_LETTER_OF_LASTNAME = 'firstNameAndFirstLetterOfLastName';
    final const COMPILATION__FIRSTNAME_AND_LASTNAME = 'firstNameAndLastName';

    /**
     * @var string|null
     */
    protected $compilation;

    /**
     * @param string|null $compilation
     * @param string $settingContext
     */
    public function __construct(?string $compilation, string $settingContext)
    {
        parent::__construct($settingContext);

        $this->compilation = $compilation;
    }

    public function getCompilation(): ?string
    {
        return $this->compilation;
    }

    public function setCompilation(?string $compilation): AuthorStruct
    {
        $this->compilation = $compilation;
        return $this;
    }
}
