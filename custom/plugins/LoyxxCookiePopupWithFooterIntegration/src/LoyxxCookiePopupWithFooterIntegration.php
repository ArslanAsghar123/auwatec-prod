<?php declare(strict_types=1);
namespace LoyxxCookiePopupWithFooterIntegration;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class LoyxxCookiePopupWithFooterIntegration extends Plugin
{
    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);
        $this->createConfigurationCategory($installContext);
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);
        $this->removeConfigurationCategory($uninstallContext);
    }

    private function createConfigurationCategory(InstallContext $installContext)
    {
        /** @var EntityRepository $categoryRepository */
        $categoryRepository = $this->container->get('category.repository');
        $context = $installContext->getContext();
        $categoryId = md5('LoyxxCookiePopupWithFooterIntegration');

        $existingCategory = $categoryRepository->search(new Criteria([$categoryId]), $installContext->getContext())->first();

        if($existingCategory === null) {
            $categoryRepository->create(
                [
                    [
                        'id' => $categoryId,
                        'name' => [
                            'en-GB' => 'Change cookie settings',
                            'de-DE' => 'Cookie Einstellungen ändern',
                            Defaults::LANGUAGE_SYSTEM => 'Change cookie settings',
                        ],
                        'description' => [
                            'en-GB' => 'Cookie Configuration category will load the update,configuration popup again to change the permissions.',
                            'de-DE' => 'Cookie Konfigurationskategorie wird das Popup-Fenster zur Aktualisierung der Konfiguration erneut geladen, um die Berechtigungen zu ändern.',
                            Defaults::LANGUAGE_SYSTEM => 'Cookie Configuration category will load the update configuration popup again to change the permissions.'
                        ],
                        'active' => true,
                    ]
                ],
                $context
            );
        }
    }


    private function removeConfigurationCategory(UninstallContext $uninstallContext)
    {
        $categoryId = md5('LoyxxCookiePopupWithFooterIntegration');
        $categoryRepository = $this->container->get('category.repository');
        $context = $uninstallContext->getContext();

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('id', $categoryId));

        $categoryEntity = $categoryRepository->searchIds($criteria, $context);
        if ($categoryEntity->getTotal()) {
            $categoryRepository->delete(array_values($categoryEntity->getData()), $context);
        }
    }
}