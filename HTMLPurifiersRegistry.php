<?php

namespace Exercise\HTMLPurifierBundle;

use Psr\Container\ContainerInterface;

class HTMLPurifiersRegistry
{
    private $purifiers = [];
    private $purifiersLocator;

    public function __construct(ContainerInterface $purifiersLocator = null)
    {
        $this->purifiersLocator = $purifiersLocator;
    }

    public function has(string $profile): bool
    {
        return isset($this->purifiers[$profile]) || $this->purifiersLocator->has($profile);
    }

    public function get(string $profile): \HTMLPurifier
    {
        if (isset($this->purifiers[$profile])) {
            return $this->purifiers[$profile];
        }

        if ($this->purifiersLocator->has($profile)) {
            return $this->purifiersLocator->get($profile);
        }

        throw new \InvalidArgumentException(sprintf('The profile "%s" is not registered.', $profile));
    }

    public function add(string $profile, \HTMLPurifier $purifier): void
    {
        $this->purifiers[$profile] = $purifier;
    }
}
