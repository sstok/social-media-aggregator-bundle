<?php

declare(strict_types=1);

namespace Milosa\SocialMediaAggregatorBundle\DependencyInjection;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class MilosaSocialMediaAggregatorExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $configuration = new Configuration();
        $loader->load('milosa_social.xml');
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('milosa_social_media_aggregator.twitter_consumer_key', $config['twitter']['auth_data']['consumer_key']);
        $container->setParameter('milosa_social_media_aggregator.twitter_consumer_secret', $config['twitter']['auth_data']['consumer_secret']);
        $container->setParameter('milosa_social_media_aggregator.twitter_oauth_token', $config['twitter']['auth_data']['oauth_token']);
        $container->setParameter('milosa_social_media_aggregator.twitter_oauth_token_secret', $config['twitter']['auth_data']['oauth_token_secret']);
        $container->setParameter('milosa_social_media_aggregator.twitter_numtweets', $config['twitter']['number_of_tweets']);
        $container->setParameter('milosa_social_media_aggregator.twitter_account', $config['twitter']['account_to_fetch']);

        if ($config['twitter']['enable_cache'] === true) {
            $this->configureCaching($container, $config['twitter']['cache_lifetime']);
        }
    }

    protected function configureCaching(ContainerBuilder $container, int $lifetime): void
    {
        $cacheDefinition = new Definition(FilesystemAdapter::class, [
            'milosa_social',
            $lifetime,
            '%kernel.root_dir%/../var/tmp',
            ]);

        $container->setDefinition('milosa_social_media_aggregator.cache', $cacheDefinition);
        $fetcherDefinition = $container->getDefinition('Milosa\SocialMediaAggregatorBundle\Sites\TwitterFetcher');
        $fetcherDefinition->addMethodCall('setCache', [new Reference('milosa_social_media_aggregator.cache')]);
    }
}
