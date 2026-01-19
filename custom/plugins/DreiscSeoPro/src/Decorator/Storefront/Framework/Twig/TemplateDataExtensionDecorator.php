<?php declare(strict_types=1);

namespace DreiscSeoPro\Decorator\Storefront\Framework\Twig;

use DreiscSeoPro\Core\CustomSetting\CustomSettingLoader;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Storefront\Framework\Twig\TemplateDataExtension;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class TemplateDataExtensionDecorator extends AbstractExtension implements GlobalsInterface
{
    public function __construct(private readonly GlobalsInterface $decorated, private readonly CustomSettingLoader $customSettingLoader)
    {
    }

    /**
     * @return array
     * @throws InconsistentCriteriaIdsException
     */
    public function getGlobals(): array
    {
        $globals = $this->decorated->getGlobals();

        /** We add the custom setting of the plugin to the globals */
        $globals['dreiscSeoCustomSettings'] = $this->customSettingLoader->load()->toArray();

        return $globals;
    }
}
