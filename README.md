# ExerciseHTMLPurifierBundle

This bundle integrates [HTMLPurifier][] into Symfony.

  [HTMLPurifier]: http://htmlpurifier.org/

## Installation

## Symfony 2.8, 3.4 and above (using Composer)

Require the bundle in your composer.json file:

```json
{
    "require": {
        "exercise/htmlpurifier-bundle": "*"
    }
}
```

Install the bundle:

```bash
$ composer require heahprod/htmlpurifier-bundle
```

Register the bundle in Symfony 2 & 3:

```php
// app/AppKernel.php

public function registerBundles()
{
    return array(
        new Exercise\HTMLPurifierBundle\ExerciseHTMLPurifierBundle(),
        // ...
    );
}
```

## Configuration in Symfony 3

If you do not explicitly configure this bundle, an HTMLPurifier service will be
defined as `exercise_html_purifier.default`. This behavior is the same as if you
had specified the following configuration:

```yaml
# app/config.yml

exercise_html_purifier:
    default:
        Cache.SerializerPath: '%kernel.cache_dir%/htmlpurifier'
```

The `default` profile is special in that it is used as the configuration for the
`exercise_html_purifier.default` service as well as the base configuration for
other profiles you might define.

```yaml
# app/config.yml

exercise_html_purifier:
    default:
        Cache.SerializerPath: '%kernel.cache_dir%/htmlpurifier'
    custom:
        Core.Encoding: 'ISO-8859-1'
```

In this example, a `exercise_html_purifier.custom` service will also be defined,
which includes both the cache and encoding options. Available configuration
options may be found in HTMLPurifier's [configuration documentation][].

**Note:** If you define a `default` profile but omit `Cache.SerializerPath`, it
will still default to the path above. You can specify a value of `null` for the
option to suppress the default path.

  [configuration documentation]: http://htmlpurifier.org/live/configdoc/plain.html

## Configuration in Symfony 4

If you do not explicitly configure this bundle, an HTMLPurifier service will be
defined as `exercise_html_purifier.default`. This behavior is the same as if you
had specified the following configuration:

```yaml
# config/packages/exercise_html_purifier.yaml

heah_html_purifier:
    default:
        Cache.SerializerPath: '%kernel.cache_dir%/htmlpurifier'
```

The `default` profile is special in that it is used as the configuration for the
`exercise_html_purifier.default` service as well as the base configuration for
other profiles you might define.

```yaml
# config/packages/exercise_html_purifier.yaml

exercise_html_purifier:
    default:
        Cache.SerializerPath: '%kernel.cache_dir%/htmlpurifier'
    custom:
        Core.Encoding: 'ISO-8859-1'
```
  
## Autowiring

By default type hinting `\HtmlPurifier` in your services will autowire
the `exercise_html_purifier.default` service.
To override it and use your own config as default autowired services just add
this in you `app/config/services.yml` in you use symfony 3 or `config/services.yaml`
if you use symfony 4:

```yaml
services:
    #...
    
    exercise_html_purifier.custom:
        alias: \HTMLPurifier
```

## Form Type Extension

This bundles provides a form type extension for filtering form fields with
HTMLPurifier. Purification is done during the PRE_SUBMIT event, which
means that client data will be filtered before binding to the form.

The following example demonstrates one possible way to integrate an HTMLPurifier
transformer into a form by way of a custom field type:

```php
<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', TextareaType::class, ['purify_html' => 'true', 'purify_html_profile' => 'custom'])
            ->add('sneek_peak', TextType::class, [
                'purify_html' => 'true',
                // will be merged with default or any custom profile when set
                'purify_html_config' => [
                    'AutoFormat.Linkify' => true,
                    'AutoFormat.RemoveEmpty' => true,
                ],
            ])
            // ...
        ;
    }
    
    // ...
}
```

Every type extending `TextType` (i.e: `SearchType`) inherit these options.

## Twig Filter

This bundles registers a `purify` filter with Twig. Output from this filter is
marked safe for HTML, much like Twig's built-in escapers. The filter may be used
as follows:

``` jinja
{# Filters text's value through the "default" HTMLPurifier service #}
{{ text|purify }}

{# Filters text's value through the "custom" HTMLPurifier service #}
{{ text|purify('custom') }}
```

## Purifiers Registry

A `Heah\HtmlPurifierBundle\HtmlPurifiersRegistry` class is registered by default
as a service. To add your custom instance of purifier, and make it available to
the form type and Twig extensions through its profile name, you can use the tag
`exercise.html_purifier` as follow:

```yaml
# config/services.yaml

services:
    # ...
    
    App\HtmlPurifier\CustomPurifier:
        tags:
            - name: exercise.html_purifier
              profile: custom
```

Now your purifier can be used when:

```php
// In a form type
$builder
    ->add('content', TextareaType::class, [
        'purify_html' => 'true',
        'purify_html_profile' => 'custom',
    ])
    // ...
```

```jinja
{# in a template #}
{{ html_string|purify('custom') }}
```
