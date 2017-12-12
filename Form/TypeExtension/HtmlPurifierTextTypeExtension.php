<?php

namespace Exercise\HtmlPurifierBundle\Form\TypeExtension;

use Exercise\HTMLPurifierBundle\Form\Listener\HtmlPurifierListener;
use Exercise\HTMLPurifierBundle\HtmlPurifiersRegistry;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HtmlPurifierTextTypeExtension extends AbstractTypeExtension
{
    private $purifiersRegistry;

    public function __construct(HtmlPurifiersRegistry $registry)
    {
        $this->purifiersRegistry = $registry;
    }

    public function getExtendedType()
    {
        return TextType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'purify_html' => false,
                'purify_html_profile' => 'default',
                'purify_html_config' => null,
            ])
            ->setAllowedTypes('purify_html', 'bool')
            ->setAllowedTypes('purify_html_profile', 'string')
            ->setNormalizer('purify_html_profile', function (Options $options, $profile) {
                if (!$this->purifiersRegistry->has($profile)) {
                    throw new InvalidConfigurationException(sprintf('The profile "%s" is not registered.', $profile));
                }

                return $profile;
            })
            ->setAllowedTypes('purify_html_config', ['null', 'array', '\HtmlPurifer_Config'])
            ->setNormalizer('html_purifier_config', function (Options $options, $config) {
                if (is_array($config) && [] !== $config) {
                    // Ensure the config is valid on build form time
                    return \HTMLPurifier_Config::create($config);
                }

                return $config;
            })
        ;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['purify_html']) {
            $builder->addEventSubscriber(new HtmlPurifierListener(
                $this->purifiersRegistry->get($options['purify_html_profile']),
                $options['purify_html_config']
            ));
        }
    }
}
