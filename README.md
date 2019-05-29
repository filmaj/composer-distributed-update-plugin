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

## Example

The following example shows how you can use the plugin to modify `composer.json` files of admin UI and storefront UI instances.

1. Clone the plugin into `artifact-repo/composer-distributed-update-plugin` directory.
2. Create a composer package for the plugin
    ```
    cd artifact-repo
    zip -r composer-distributed-update-plugin.zip composer-distributed-update-plugin/*
    ```
3. Create composer packages for the extension we going to install
    ```
    zip -r extension-ui.zip example/extension-ui/*
    zip -r extension-admin-ui.zip example/extension-admin-ui/*
    zip -r extension-metapackage.zip example/extension-metapackage/*
    ```
4. Please admin UI and storefront UI instances by following `https://github.com/magento-architects/modularity-refactoring-tools`
5. Create a directory `plugin-test` with `composer.json` file that would contain configuration of artifact repositories with plugin, sample packages and Magento packages

    ```
    {
        "require": {
            "magento/composer-plugin": "^1.0",
        },
        "prefer-stable": true,
        "minimum-stability": "dev",
        "repositories": [
            {
                "type": "artifact",
                "url": "/path/to/artifact-repo"
            },
            {
                "type": "path",
                "url": "/path/to/magento2ce-admin-ui/magento/app/code/Magento/*/*"
            },
            {
                "type": "path",
                "url": "/path/to/magento2ce-admin-ui/magento/app/code/Magento/*"
            },
            {
                "type": "path",
                "url": "/path/to/magento2ce-admin-ui/magento/lib/internal/Magento/Framework/*/*"
            },
            {
                "type": "path",
                "url": "/path/to/magento2ce-admin-ui/magento/lib/internal/Magento/Framework/*"
            },
            {
                "type": "path",
                "url": "/path/to/magento2ce-admin-ui/magento/lib/internal/Magento/*"
            }
        ],
        "extra": {
            "magento-plugin": {
                "instances": {
                    "ui": "/path/to/magento2ce-storefront-ui/composer.json",
                    "admin-ui": "/path/to/magento2ce-admin-ui/composer.json"
                }
            }
        }
    }
    ```
6. Add `app/etc/vendor_path.php` to `plugin-test` (copy from existing project)
7. Run `composer update`
7. Run `composer distributed-require magento/module-extension-metapackage`

`composer.json` files of UI instances should be updated.
