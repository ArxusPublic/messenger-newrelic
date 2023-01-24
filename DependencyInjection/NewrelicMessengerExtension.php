<?php declare(strict_types=1);

namespace Arxus\NewrelicMessengerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class NewrelicMessengerExtension extends Extension
{
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $locator = new FileLocator(__DIR__.'/../Resources/config/');
        $loader = new YamlFileLoader($container, $locator);
        $loader->load('services.yaml');
    }
}
