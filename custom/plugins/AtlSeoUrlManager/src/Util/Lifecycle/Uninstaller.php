<?php declare(strict_types=1);

namespace Atl\SeoUrlManager\Util\Lifecycle;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;

class Uninstaller
{
    /**
     * @var EntityRepository
     */
    private EntityRepository $customFieldSetRepository;

    public function __construct(EntityRepository $customFieldSetRepository)
    {
        $this->customFieldSetRepository = $customFieldSetRepository;
    }

    public function uninstall(Context $context): void
    {
        $this->removeCustomFields($context);
    }

    private function removeCustomFields(Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new ContainsFilter('name', Installer::SEO_URL_MANAGER_PRODUCT_SET));

        $result = $this->customFieldSetRepository->search($criteria, $context);
        $ids = $result->getIds();

        if (empty($ids)) {
            return;
        }

        $data = [];
        foreach ($ids as $id) {
            $data[] = [
                'id' => $id
            ];
        }

        $this->customFieldSetRepository->delete($data, $context);
    }
}
