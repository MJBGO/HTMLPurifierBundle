<?php

namespace Exercise\HTMLPurifierBundle;

class HtmlPurifiersRegistry
{
    private $purifiers = [];

    public function has(string $profile): bool
    {
        return isset($this->purifiers[$profile]);
    }

    public function get(string $profile): \HTMLPurifier
    {
        if (!isset($this->purifiers[$profile])) {
            throw new \InvalidArgumentException(sprintf('The profile "%s" is not registered.', $profile));
        }

        return $this->purifiers[$profile];
    }

    public function add(string $profile, \HTMLPurifier $purifier): void
    {
        $this->purifiers[$profile] = $purifier;
    }
}
