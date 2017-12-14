<?php

namespace Exercise\HTMLPurifierBundle\Form\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class HTMLPurifierListener implements EventSubscriberInterface
{
    private $purifier;
    private $config;

    public function __construct(\HTMLPurifier $purifier, ?array $config = null)
    {
        $this->purifier = $purifier;
        $this->config = $config;
    }

    public function purifySubmittedData(FormEvent $event): void
    {
        if (!is_scalar($event->getData())) {
            // Hope there is a view transformer, otherwise an error might happen
            return; // because we don't want to handle it here
        }

        $submittedData = trim($event->getData());

        if (0 === strlen($submittedData)) {
            return;
        }

        $event->setData($this->purifier->purify($submittedData, $this->config));
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => ['purifySubmittedData', /* as soon as possible */ 1000000],
        ];
    }
}
