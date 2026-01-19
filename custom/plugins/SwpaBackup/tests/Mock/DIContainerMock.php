<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Test\Mock;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * DI Container Mock
 *
 * @package   Swpa\SwpaBackup\Test\Mock
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class DIContainerMock implements ContainerInterface
{

    public function set($id, $service): void
    {
    }

    public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE): ?object
    {
    }

    public function has($id): bool
    {
    }

    public function initialized($id): bool
    {
    }

    public function getParameter($name)
    {
    }

    public function hasParameter($name): bool
    {
    }

    public function setParameter($name, $value): void
    {
    }
}
