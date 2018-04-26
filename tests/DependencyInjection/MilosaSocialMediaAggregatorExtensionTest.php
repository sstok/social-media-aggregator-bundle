<?php

declare(strict_types=1);

namespace Milosa\SocialMediaAggregatorTests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Milosa\SocialMediaAggregatorBundle\DependencyInjection\MilosaSocialMediaAggregatorExtension;

class MilosaSocialMediaAggregatorExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            new MilosaSocialMediaAggregatorExtension(),
        ];
    }

    /**
     * @test
     */
    public function after_loading_the_correct_parameter_has_been_set()
    {
        $this->load();

        $this->assertContainerBuilderHasParameter('milosa_social_media_aggregator.twitter_consumer_key', 'test_key');
        $this->assertContainerBuilderHasParameter('milosa_social_media_aggregator.twitter_consumer_secret', 'test_secret');
        $this->assertContainerBuilderHasParameter('milosa_social_media_aggregator.twitter_oauth_token', 'test_token');
        $this->assertContainerBuilderHasParameter('milosa_social_media_aggregator.twitter_oauth_token_secret', 'test_secret_token');
        $this->assertContainerBuilderHasParameter('milosa_social_media_aggregator.twitter_numtweets', 10);
        $this->assertContainerBuilderHasParameter('milosa_social_media_aggregator.twitter_account', 'realDonaldTrump');
    }

    /**
     * @test
     */
    public function after_loading_the_correct_services_have_been_defined()
    {
        $this->load();
        $this->assertContainerBuilderHasService('Abraham\TwitterOAuth\TwitterOAuth');
        $this->assertContainerBuilderHasService('Milosa\SocialMediaAggregatorBundle\Sites\TwitterFetcher');
        $this->assertContainerBuilderHasService('twig.extension.date');
        $this->assertContainerBuilderHasService('Milosa\SocialMediaAggregatorBundle\SocialMediaAggregator');
    }

    protected function getMinimalConfiguration()
    {
        return ['twitter' => [
            'auth_data' => [
                'consumer_key' => 'test_key',
                'consumer_secret' => 'test_secret',
                'oauth_token' => 'test_token',
                'oauth_token_secret' => 'test_secret_token',
            ],
            'fetch_interval' => 720,
            'template' => 'twitter.twig',
            'show_images' => true,
            'hashtag_links' => true,
            'account_to_fetch' => 'realDonaldTrump',
        ],
            ];
    }
}