RiverwayGridBundle
================

Installation
------------
# Step 1: Download the Bundle

```composer install --prefer-dist riverway/grid-bundle```

# Step 2: Enable the Bundle

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

            new RiverwayGrid\RiverwayGridBundle(),
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
```php
$query = $this->getDoctrine()->getRepository('AppBundle:Order')->createQueryBuilder('o')->getQuery();
$grid = $this->get('riverway.grid');
$grid->setConfig($query, [
        'id' => [
            'sortable' => true,
        ],
        'status' => [
            'report_only' => true,
            'value' => function (Order $row) {
                return $this->translator->trans($row->getHumanStatus());
            },
        ],
        'created_at' => [
            'value' => function (Order $row) {
                return $row->getCreatedAt()->format('Y-m-d H:i');
            },
            'sortable' => true,
        ],
        'updated_at' => [
            'value' => function (Order $row) {
                return $row->getUpdatedAt()->format('Y-m-d H:i');
            },
            'sortable' => true,
        ],
        'price' => [
            'sortable' => true,
        ],
        'number',
        'comment',
        'fio' => [
            'value' => function (Order $row, $index, $asReport) {
                if ($asReport) {
                    return $row->getCustomer()->getFio();
                } else {
                    return $this->engine->render('@App/Order/_order_grid_fio_template.html.twig',
                        ['object' => $row]);
                }
            },
        ],
        'phone' => [
            'value' => function (Order $row, $index, $asReport) {
                if ($asReport) {
                    return $this->helperExtension->protect($row->getCustomer()->getFullPhone());
                } else {
                    return $this->engine->render('@App/Order/_order_grid_phone_template.html.twig',
                        ['object' => $row]);
                }
            },
            'is_visible' => function (Order $row) {
                return $this->authorizationChecker->isGranted('ROLE_VIEW_PHONE_CUSTOMER', $row);
            },
        ],
        'region' => [
            'sortable' => true,
        ],
        'last_operator' => [
            'value' => function (Order $row) {
                return $row->getOrderMeta()->getLastChangedStatusOperator();
            },
            'is_visible' => function (Order $row) {
                return $this->authorizationChecker->isGranted('ROLE_ORDER_DASHBOARD_SEE_LAST_OPERATOR');
            },
            'report_only' => true,
        ],
        'web_id' => [
            'value' => function (Order $row) {
                return $row->getOrderMeta()->getWebId();
            },
            'is_visible' => function (Order $row) {
                return $this->authorizationChecker->isGranted('ROLE_ORDER_DASHBOARD_SEE_WEB_ID');
            },
            'report_only' => true,
        ],
        'city' => [
            'value' => function (Order $row) {
                return $row->getCustomer()->getCity();
            },
            'is_visible' => function (Order $row) {
                return $this->tokenStorage->getToken()->getUser()->getRole() === UserRole::ROLE_LOGISTIC;
            },
            'report_only' => true,
        ],
        'export_try' => [
            'value' => function (Order $row) {
                return $row->getExportOrderStatus() ? 'Yes' : 'No';
            },
            'is_visible' => function (Order $row) use ($recheck) {
                return $recheck;
            },
        ],
        'logistic' => [
            'value' => function (Order $row) {
                return $row->getExportOrderStatus() ? $row->getOrderExportStatus()->getHumanType() : null;
            },
            'is_visible' => function (Order $row) use ($recheck) {
                return $this->authorizationChecker->isGranted('ROLE_STAT_EXPORT_LOGISTICS');
            },
            'report_only' => true,
        ],
        'actions' => [
            'value' => function (Order $row) use ($actionEditName, $isBuyout) {
                return $this->engine->render(
                    'AppBundle:Order:_actions.html.twig',
                    [
                        'object' => $row,
                        'actionEditName' => $actionEditName,
                        'isBuyout' => $isBuyout,
                    ]
                );
            },
            'no_report' => true,
        ],
    ]
);

return $this->render('index.html.twig', [
    'grid' => $grid->getGrid(),
]);

```

Template:
```
{{ grid|raw }}
```
