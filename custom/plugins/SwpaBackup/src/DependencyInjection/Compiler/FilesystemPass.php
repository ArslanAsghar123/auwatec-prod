<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FilesystemPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('swpa.backup.filesystems')) {
            return;
        }
        $definition = $container->findDefinition('swpa.backup.filesystems');
        $filesystems = $container->findTaggedServiceIds('swpa.backup.filesystems');
        foreach ($filesystems as $id => $attributes) {
            $definition->addMethodCall('add', [new Reference($id)]);
        }
    }
}
