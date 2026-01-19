<?php declare(strict_types=1);

namespace Acris\DiscountGroup\Storefront\Subscriber;

use Acris\DiscountGroup\Components\DiscountGroupGateway;
use Acris\DiscountGroup\Custom\DiscountGroupEntity;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Storefront\Page\Account\Overview\AccountOverviewPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AccountPageSubscriber implements EventSubscriberInterface
{
    /**
     * @var DiscountGroupGateway
     */
    private $discountGroupGateway;

    public function __construct(
        DiscountGroupGateway $discountGroupGateway
    ) {
        $this->discountGroupGateway = $discountGroupGateway;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AccountOverviewPageLoadedEvent::class => 'onAccountOverviewPageLoaded'
        ];
    }

    public function onAccountOverviewPageLoaded(AccountOverviewPageLoadedEvent $event): void
    {
        $discountGroupResult = $this->discountGroupGateway->getAllDiscountGroups($event->getSalesChannelContext());

        if ($discountGroupResult->count() === 0) {
            return;
        }

        $discountGroupDisplayList = [];

        /** @var DiscountGroupEntity $discountGroup */
        foreach ($discountGroupResult->getElements() as $discountGroup) {
            if( $discountGroup->getAccountDisplay() && $discountGroup->getTranslation('displayText')  != '' )
                $discountGroupDisplayList[] = $discountGroup->getTranslation('displayText');
        }

        $event->getPage()->addExtension('acris_discount_group', new ArrayEntity([
            'assigned_discount_groups' => $discountGroupDisplayList
        ]));
    }
}
