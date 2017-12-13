<?php

namespace Exercise\HTMLPurifierBundle\DependencyInjection;

use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistry;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ExerciseHTMLPurifierExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        if (!method_exists($container, 'getReflectionClass')) {
            $loader->load('legacy_html_purifier.xml');
        } else {
            $loader->load('html_purifier.xml');
        }

        /* Prepend the default configuration. This cannot be defined within the
         * Configuration class, since the root node's children are array
         * prototypes.
         *
         * This cache path may be suppressed by either unsetting the "default"
         * configuration (relying on canBeUnset() on the prototype node) or
         * setting the "Cache.SerializerPath" option to null.
         */
        array_unshift($configs, [
            'default' => [
                'Cache.SerializerPath' => '%kernel.cache_dir%/htmlpurifier',
            ],
        ]);

        $configs = $this->processConfiguration(new Configuration(), $configs);
        $configs = array_map([$this, 'resolveServices'], $configs);

        $purifiers = [];
        $purifiersLocator = null;
        $registry = $container->register(HTMLPurifiersRegistry::class);

        if (class_exists(ServiceLocator::class)) {
            $purifiersLocator = $container
                ->register('exercise_html_purifier.purifiers_locator', ServiceLocator::class)
                ->addTag('container.service_locator')
            ;
            $registry->setArguments([$purifiersLocator]);
        }

        $serializerPaths = [];

        foreach ($configs as $name => $config) {
            $configId = 'exercise_html_purifier.config.'.$name;

            $container->register($configId, \HTMLPurifier_Config::class)
                ->setFactory([\HTMLPurifier_Config::class, 'create'])
                ->setArguments([$config])
                ->setPublic(false)
            ;

            $purifierId = 'exercise_html_purifier.'.$name;

            $container->setDefinition($purifierId, new Definition(\HTMLPurifier::class, [new Reference($configId)]));

            if ($purifiersLocator) {
                $purifiers[$name] = new Reference($purifierId);
            } else {
                $registry->addMethodCall('add', [$name, new Reference($purifierId)]);
            }

            if (isset($config['Cache.SerializerPath'])) {
                $serializerPaths[] = $config['Cache.SerializerPath'];
            }
        }

        if ($purifiersLocator) {
            $purifiersLocator->setArguments([$purifiers]);
        }

        $container->setParameter('exercise_html_purifier.cache_warmer.serializer.paths', array_unique($serializerPaths));
        $container->setAlias(\HTMLPurifier::class, 'exercise_html_purifier.default');
    }

    public function getAlias()
    {
        return 'exercise_html_purifier';
    }

    private function resolveServices($value)
    {
        if (is_array($value)) {
            $value = array_map([$this, 'resolveServices'], $value);
        } elseif (is_string($value) &&  0 === strpos($value, '@')) {
            if (0 === strpos($value, '@?')) {
                $value = substr($value, 2);
                $invalidBehavior = ContainerInterface::IGNORE_ON_INVALID_REFERENCE;
            } else {
                $value = substr($value, 1);
                $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
            }

            $value = new Reference($value, $invalidBehavior);
        }

        return $value;
    }
}
