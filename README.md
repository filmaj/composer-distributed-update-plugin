# Magento distributed update composer plugin

## Overview

Plugin allows to manage composer packages dependencies on application that consists from multiple instances (admin, storefront, cron, webapi).

## Usage

To use plugin, install it in new directory

`comnposer require magento/composer-distributed-update-plugin`

and add configuration to to `composer.json`

```
"extra": {
    "magento-plugin": {
        "instances": {
            "ui": "path/to/ui/instance/composer.json",
            "admin-ui": "path/to/admin-ui/instance/composer.json"
        }
    }
}
```

where instances is the list of instances that we want to manage.

Extension must be a metapackage that should consists with packages that have configuration of where they should be installed.

```
"extra": {
    "instances": [
        "ui"
    ]
}
```

The following commands available to manage dependencies
* `composer distributed-require`
* `composer distributed-remove`
