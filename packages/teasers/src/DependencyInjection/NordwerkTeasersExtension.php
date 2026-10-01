<?php

declare(strict_types=1);

namespace Nordwerk\TeasersBundle\DependencyInjection;

use Nordwerk\TeasersBundle\Source\TeaserSourceInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class NordwerkTeasersExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(TeaserSourceInterface::class)->addTag('nordwerk.teaser_source');

        (new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config')))->load('services.yaml');
    }
}
