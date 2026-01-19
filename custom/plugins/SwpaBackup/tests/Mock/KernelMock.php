<?php declare(strict_types=1);

namespace Swpa\SwpaBackup\Test\Mock;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Kernel Mock
 *
 * @package   Swpa\SwpaBackup\Test\Mock
 * @copyright See COPYING.txt for license details
 * @author    swpa <info@swpa.dev>
 */
class KernelMock implements KernelInterface
{


    public function getProjectDir()
    {
        return '/var/www/';
    }

    public function handle(Request $request, int $type = self::MASTER_REQUEST, bool $catch = true)
    {

    }

    /**
     * Returns an array of bundles to register.
     *
     * @return iterable|BundleInterface[] An iterable of bundle instances
     */
    public function registerBundles()
    {
        // TODO: Implement registerBundles() method.
    }

    /**
     * Loads the container configuration.
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        // TODO: Implement registerContainerConfiguration() method.
    }

    /**
     * Boots the current kernel.
     */
    public function boot()
    {
        // TODO: Implement boot() method.
    }

    /**
     * Shutdowns the kernel.
     *
     * This method is mainly useful when doing functional testing.
     */
    public function shutdown()
    {
        // TODO: Implement shutdown() method.
    }

    /**
     * Gets the registered bundle instances.
     *
     * @return BundleInterface[] An array of registered bundle instances
     */
    public function getBundles()
    {
        // TODO: Implement getBundles() method.
    }

    /**
     * Returns a bundle.
     *
     * @param string $name Bundle name
     *
     * @return BundleInterface A BundleInterface instance
     *
     * @throws \InvalidArgumentException when the bundle is not enabled
     */
    public function getBundle($name)
    {
        // TODO: Implement getBundle() method.
    }

    /**
     * Returns the file path for a given bundle resource.
     *
     * A Resource can be a file or a directory.
     *
     * The resource name must follow the following pattern:
     *
     *     "@BundleName/path/to/a/file.something"
     *
     * where BundleName is the name of the bundle
     * and the remaining part is the relative path in the bundle.
     *
     * @param string $name A resource name to locate
     *
     * @return string|array The absolute path of the resource or an array if $first is false (array return value is deprecated)
     *
     * @throws \InvalidArgumentException if the file cannot be found or the name is not valid
     * @throws \RuntimeException         if the name contains invalid/unsafe characters
     */
    public function locateResource($name)
    {
        // TODO: Implement locateResource() method.
    }

    /**
     * Gets the name of the kernel.
     *
     * @return string The kernel name
     *
     * @deprecated since Symfony 4.2
     */
    public function getName()
    {
        // TODO: Implement getName() method.
    }

    /**
     * Gets the environment.
     *
     * @return string The current environment
     */
    public function getEnvironment()
    {
        // TODO: Implement getEnvironment() method.
    }

    /**
     * Checks if debug mode is enabled.
     *
     * @return bool true if debug mode is enabled, false otherwise
     */
    public function isDebug()
    {
        // TODO: Implement isDebug() method.
    }

    /**
     * Gets the application root dir (path of the project's Kernel class).
     *
     * @return string The Kernel root dir
     *
     * @deprecated since Symfony 4.2
     */
    public function getRootDir()
    {
        // TODO: Implement getRootDir() method.
    }

    /**
     * Gets the current container.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        // TODO: Implement getContainer() method.
    }

    /**
     * Gets the request start time (not available if debug is disabled).
     *
     * @return float The request start timestamp
     */
    public function getStartTime()
    {
        // TODO: Implement getStartTime() method.
    }

    /**
     * Gets the cache directory.
     *
     * @return string The cache directory
     */
    public function getCacheDir()
    {
        // TODO: Implement getCacheDir() method.
    }

    /**
     * Gets the log directory.
     *
     * @return string The log directory
     */
    public function getLogDir()
    {
        // TODO: Implement getLogDir() method.
    }

    /**
     * Gets the charset of the application.
     *
     * @return string The charset
     */
    public function getCharset()
    {
        // TODO: Implement getCharset() method.
    }
}
