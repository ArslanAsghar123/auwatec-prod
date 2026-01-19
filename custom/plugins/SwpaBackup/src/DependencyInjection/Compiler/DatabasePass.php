<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DatabasePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('swpa.backup.databases')) {
            return;
        }
        $definition = $container->findDefinition('swpa.backup.databases');
        $databases = $container->findTaggedServiceIds('swpa.backup.databases');
        foreach ($databases as $id => $attributes) {
            $definition->addMethodCall('add', [new Reference($id)]);
        }
    }
}
