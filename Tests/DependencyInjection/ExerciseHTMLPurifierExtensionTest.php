<?php

namespace Exercise\HTMLPurifierBundle\Tests\DependencyInjection;

use Exercise\HTMLPurifierBundle\DependencyInjection\ExerciseHTMLPurifierExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ExerciseHTMLPurifierExtensionTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var ExerciseHTMLPurifierExtension
     */
    private $extension;

    private $defaultConfig;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new ExerciseHTMLPurifierExtension();

        $this->defaultConfig = [
            'Cache.SerializerPath' => '%kernel.cache_dir%/htmlpurifier',
        ];
    }

    public function testShouldLoadDefaultConfiguration()
    {
        $this->extension->load([], $this->container);

        $this->assertDefaultConfigDefinition($this->defaultConfig);
    }

    public function testShouldAllowOverridingDefaultConfigurationCacheSerializerPath()
    {
        $config = [
            'default' => [
                'AutoFormat.AutoParagraph' => true,
                'Cache.SerializerPath' => null,
            ],
        ];

        $this->extension->load([$config], $this->container);

        $this->assertDefaultConfigDefinition($config['default']);
    }

    public function testShouldNotDeepMergeOptions()
    {
        $configs = [
            ['default' => [
                'Core.HiddenElements' => ['script' => true],
                'Cache.SerializerPath' => null,
            ]],
            ['default' => [
                'Core.HiddenElements' => ['style' => true],
            ]],
        ];

        $this->extension->load($configs, $this->container);

        $this->assertDefaultConfigDefinition([
            'Core.HiddenElements' => ['style' => true],
            'Cache.SerializerPath' => null,
        ]);
    }

    public function testShouldLoadCustomConfiguration()
    {
        $config = [
            'default' => [
                'AutoFormat.AutoParagraph' => true,
            ],
            'simple' => [
                'Cache.DefinitionImpl' => null,
                'Cache.SerializerPath' => '%kernel.cache_dir%/htmlpurifier-simple',
                'AutoFormat.Linkify' => true,
                'AutoFormat.RemoveEmpty' => true,
                'AutoFormat.RemoveEmpty.RemoveNbsp' => true,
                'HTML.Allowed' => "a[href],strong,em,p,li,ul,ol",
            ],
            'advanced' => [
                'Cache.DefinitionImpl' => null,
            ],
        ];

        $this->extension->load([$config], $this->container);

        $this->assertDefaultConfigDefinition(array_replace($this->defaultConfig, $config['default']));
        $this->assertConfigDefinition('simple', $config['simple']);
        $this->assertConfigDefinition('advanced', $config['advanced']);
    }

    public function testShouldResolveServices()
    {
        $config = [
            'simple' => [
                'AutoFormat.Custom' => ['@service_container'],
            ],
        ];

        $this->extension->load([$config], $this->container);

        $definition = $this->container->getDefinition('exercise_html_purifier.config.simple');

        $args = $definition->getArguments();

        $this->assertInstanceOf(Reference::class, $args[0]['AutoFormat.Custom'][0]);
    }

    /**
     * Assert that the named config definition extends the default profile and
     * loads the given options.
     *
     * @param string $name
     * @param array  $config
     */
    private function assertConfigDefinition($name, array $config)
    {
        $this->assertTrue($this->container->hasDefinition('exercise_html_purifier.config.' . $name));

        $definition = $this->container->getDefinition('exercise_html_purifier.config.' . $name);
        $this->assertEquals([\HTMLPurifier_Config::class, 'create'], $definition->getFactory());

        $args = $definition->getArguments();
        $this->assertCount(1, $args);
        $this->assertEquals([$config], $args);
    }

    /**
     * Assert that the default config definition loads the given options.
     *
     * @param array $config
     */
    private function assertDefaultConfigDefinition(array $config)
    {
        $this->assertTrue($this->container->hasDefinition('exercise_html_purifier.config.default'));

        $definition = $this->container->getDefinition('exercise_html_purifier.config.default');
        $this->assertEquals([\HTMLPurifier_Config::class, 'create'], $definition->getFactory());
        $this->assertEquals([$config], $definition->getArguments());
    }
}
