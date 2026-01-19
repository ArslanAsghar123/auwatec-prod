<?php declare(strict_types=1);

namespace phpSchmied\LastSeenProducts;

use Exception;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

class phpSchmiedLastSeenProducts extends Plugin {

    const MODUL_NAME = 'phpSchmiedLastSeenProducts';

    /**
     * @param ActivateContext $activateContext
     * @return void
     * @throws Exception
     */
    public function activate(ActivateContext $activateContext): void
    {
        parent::install($activateContext);

        $kernel = $this->container->get('kernel');
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $application->run(new ArrayInput([
            'command' => 'theme:compile'
        ]));
    }
}
