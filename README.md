RiverwayGridBundle
================

Installation
------------
#### Step 1: Download the Bundle
Add private repo:
```
"repositories":[
    {
        "type": "git",
        "url" : "git@bitbucket.org:riverway/riverway-grid-bundle.git"
    }
]
```
Install:
```composer require --prefer-dist riverway/grid-bundle```

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Riverway\Grid\RiverwayGridBundle(),
        );

        // ...
    }

    // ...
}
```

Configuration
-------------
There are no configuration yet : )

Usage
-----
Controller:
```php
$query = $this->getDoctrine()->getRepository('AppBundle:Order')->createQueryBuilder('o')->getQuery();

return $this->render('index.html.twig', [
    'query' => $query->getQuery(),
]);

```

Template:
```
 {{ riverway_grid_render([
    'id',
    'name',
    {'type.key': {'humanize': true, 'label': 'Type'}}
    ], query) }}
```
