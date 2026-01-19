<?php declare(strict_types=1);

namespace DreiscSeoPro\Test\Behaviour\Helper;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\ContainerInterface;
trait MockHelper
{
    private $mocks = [];

    abstract protected static function getContainer(): ContainerInterface;
    abstract protected function createMock(string $originalClassName): MockObject;

    protected function _createMock($class): MockObject
    {
        $this->mocks[$class] = $this->createMock($class);

        return $this->_getMock($class);
    }

    /**
     * @return MockObject
     */
    protected function _getMock($class): mixed
    {
        return $this->mocks[$class] ?? $this->getContainer()->get($class);
    }
}
