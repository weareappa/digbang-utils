<?php

namespace Digbang\Utils;

use Digbang\Utils\Doctrine\Mappings\Embeddables\EnumFlagMapping;
use Digbang\Utils\Doctrine\Mappings\Embeddables\EnumMapping;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Config\Repository as Config;
use Illuminate\Support\ServiceProvider;
use LaravelDoctrine\Fluent\FluentDriver;
use LaravelDoctrine\ORM\Configuration\MetaData\MetaDataManager;

class UtilsServiceProvider extends ServiceProvider
{
    public function boot(EntityManagerInterface $entityManager, MetaDataManager $metadata)
    {
        $this->registerDoctrineMappings($entityManager, $metadata);
    }

    public function register(Config $config)
    {
    }

    protected function registerDoctrineMappings(EntityManagerInterface $entityManager, MetaDataManager $metadata): void
    {
        /** @var FluentDriver $fluentDriver */
        $fluentDriver = $metadata->driver('fluent', [
            'mappings' => [
                EnumMapping::class,
                EnumFlagMapping::class,
            ],
        ]);

        /** @var MappingDriverChain $chain */
        $chain = $entityManager->getConfiguration()->getMetadataDriverImpl();
        $chain->addDriver($fluentDriver, __NAMESPACE__);
    }
}
