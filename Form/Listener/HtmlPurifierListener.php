<?php

namespace Exercise\HTMLPurifierBundle\Form\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class HtmlPurifierListener implements EventSubscriberInterface
{
    private $purifier;
    private $config;

    public function __construct(\HTMLPurifier $purifier, ?array $config = null)
    {
        $this->purifier = $purifier;
    }

    public function purifySubmittedData(FormEvent $event): void
    {
        $submittedData = (string) $event->getData();

        if (empty($submittedData)) {
            return;
        }

        $event->setData($this->purifier->purify($submittedData, $this->config));
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SUBMIT => ['purifySubmittedData', -1024],
        ];
    }
}
