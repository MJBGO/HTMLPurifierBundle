<?php

namespace Exercise\HTMLPurifierBundle\Tests\Form;

use Exercise\HTMLPurifierBundle\Form\Listener\HtmlPurifierListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormEvent;

class HTMLPurifierListenerTest extends TestCase
{
    public function testShouldPurifyOnlyDuringReverseTransform()
    {
        $input = 'text';
        $purifiedInput = '<p>text</p>';

        $purifier = $this->getMockBuilder('HTMLPurifier')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $purifier->expects($this->once())
            ->method('purify')
            ->with($input, null)
            ->will($this->returnValue($purifiedInput))
        ;

        $listener = new HtmlPurifierListener($purifier);

        $event = $this->getMockBuilder(FormEvent::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($input))
        ;

        $event->expects($this->once())
            ->method('setData')
            ->with($purifiedInput)
        ;

        $listener->purifySubmittedData($event);
    }
}
