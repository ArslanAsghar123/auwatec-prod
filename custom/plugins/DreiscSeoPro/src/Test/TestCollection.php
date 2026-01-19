<?php declare(strict_types=1);

namespace DreiscSeoPro\Test;

use DreiscSeoPro\Test\Behaviour\Context\SalesChannelContextTestBehaviour;
use DreiscSeoPro\Test\Behaviour\Entity\CategoryEntityTestBehaviour;
use DreiscSeoPro\Test\Behaviour\Entity\LandingpageEntityTestBehaviour;
use DreiscSeoPro\Test\Behaviour\Entity\ProductEntityTestBehaviour;
use DreiscSeoPro\Test\Behaviour\Helper\MockHelper;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait TestCollection
{
    use IntegrationTestBehaviour;
    use SalesChannelContextTestBehaviour;
    use CategoryEntityTestBehaviour;
    use LandingpageEntityTestBehaviour;
    use ProductEntityTestBehaviour;
    use MockHelper;
}
