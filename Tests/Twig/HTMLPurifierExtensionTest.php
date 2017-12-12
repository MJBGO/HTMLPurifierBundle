<?php

namespace Exercise\HTMLPurifierBundle\Tests\Twig;

use Exercise\HTMLPurifierBundle\HtmlPurifiersRegistry;
use Exercise\HTMLPurifierBundle\Twig\HTMLPurifierExtension;
use PHPUnit\Framework\TestCase;

class HTMLPurifierExtensionTest extends TestCase
{
    /**
     * @dataProvider providePurifierProfiles
     */
    public function testPurifyFilter($profile)
    {
        $input = 'text';
        $purifiedInput = '<p>text</p>';

        $purifier = $this->getMockBuilder('HTMLPurifier')
            ->disableOriginalConstructor()
            ->getMock();

        $purifier->expects($this->once())
            ->method('purify')
            ->with($input)
            ->will($this->returnValue($purifiedInput));

        $container = $this->createMock(HtmlPurifiersRegistry::class);

        $container->expects($this->once())
            ->method('get')
            ->with($profile)
            ->will($this->returnValue($purifier))
        ;

        $extension = new HTMLPurifierExtension($container);

        $this->assertEquals($purifiedInput, $extension->purify($input, $profile));
    }

    public function providePurifierProfiles()
    {
        yield ['default'];
        yield ['custom'];
    }
}
