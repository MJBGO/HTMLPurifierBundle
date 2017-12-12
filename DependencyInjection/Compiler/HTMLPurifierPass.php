<?php

namespace Exercise\HtmlPurifierBundle\DependencyInjection\Compiler;

use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistry;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class HTMLPurifierPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(HTMLPurifiersRegistry::class)) {
            return;
        }

        $registry = $container->getDefinition(HTMLPurifiersRegistry::class);

        foreach ($container->findTaggedServiceIds('exercise.html_purifier') as $id => $tags) {
            foreach ($tags as $tag) {
                if (empty($tag['profile'])) {
                    throw new InvalidConfigurationException('Tag "exercise.html_purifier" must define a "profile" attribute.');
                }

                $purifier = $container->getDefinition($id);

                if (empty($purifier->getArguments())) {
                    $configId = 'exercise_html_purifier.config.'.$tag['profile'];
                    $config = $container->hasDefinition($configId) ? $configId : 'exercise_html_purifier.config.default';

                    $purifier->addArgument(new Reference($config));
                }

                $registry->addMethodCall('add', [$tag['profile'], new Reference($id)]);
            }
        }
    }
}
