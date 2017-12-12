<?php

namespace Exercise\HTMLPurifierBundle\Twig;

use Exercise\HTMLPurifierBundle\HtmlPurifiersRegistry;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HTMLPurifierExtension extends AbstractExtension
{
    private $purifiersRegistry = [];

    public function __construct(HtmlPurifiersRegistry $registry)
    {
        $this->purifiersRegistry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new TwigFilter('purify', array($this, 'purify'), array('is_safe' => array('html'))),
        );
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
