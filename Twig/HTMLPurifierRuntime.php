<?php

namespace Exercise\HTMLPurifierBundle\Twig;

use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistry;
use Twig\Extension\RuntimeExtensionInterface;

class HTMLPurifierRuntime implements RuntimeExtensionInterface
{
    private $purifiersRegistry = [];

    public function __construct(HTMLPurifiersRegistry $registry)
    {
        $this->purifiersRegistry = $registry;
    }

    /**
     * Filters the input through an \HTMLPurifier service.
     *
     * @param string $string  The html string to purify
     * @param string $profile A configuration profile name
     *
     * @return string The purified html string
     */
    public function purify(string $string, string $profile = 'default')
    {
        return $this->getHTMLPurifierForProfile($profile)->purify($string);
    }

    /**
     * Gets the HTMLPurifier service corresponding to the given profile.
     *
     * @param string $profile
     *
     * @return \HTMLPurifier
     *
     * @throws \InvalidArgumentException If the profile does not exist
     */
    private function getHTMLPurifierForProfile(string $profile): \HTMLPurifier
    {
        return $this->purifiersRegistry->get($profile);
    }
}
