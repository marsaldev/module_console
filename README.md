[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.2-8892BF.svg?style=flat-square)](https://php.net/)
[![PHP tests](https://github.com/marsaldev/module_console_manager/actions/workflows/phpstan.yml/badge.svg)](https://github.com/marsaldev/module_console_manager/blob/dev/.github/workflows/phpstan.yml)
[![GitHub release](https://img.shields.io/github/v/release/marsaldev/module_console_manager)](https://github.com/marsaldev/module_console_manager/releases)

# Module Console Manager

Module console is a module which provides a set a commands to extend PrestaShop 1.7 commands managing the modules.

Since version 1.7.5.0 [Prestashop provides some terminal commands](https://devdocs.prestashop.com/1.7/modules/concepts/commands/) using the [Symfony console tool](https://symfony.com/doc/3.4/console.html).

This repository provides a base Command with better support for PrestaShop legacy classes and useful commands to easy the development on Prestashop or manage a shop.
These commands are mainly for developers, just some basic knowledge of command line processing is needed.

## Install from release (recommended)

[Donwload a zip release](https://github.com/marsaldev/module_console_manager/releases) and install it like any other module.

Alternatively, run this in a shell :

```bash
#!/bin/bash
wget WIP--output-document /tmp/module_console_manager.zip && unzip /tmp/module_console_manager.zip -d modules && ./bin/console pr:mo install module_console_manager
```

## Install from sources

If you want use the dev branch, you can install from github.
If you want to contribute, first create a fork and follow the same steps using your forked repository url instead of the original one.

```
cd modules 
git clone https://github.com/marsaldev/module_console_manager.git
cd module_console_manager
composer install
```
Install the module in the backoffice or in command line like this :
```
cd ../../
php bin/console pr:mo install module_console_manager
```

## Current commands

* `mcm:about:version`                  Display the Module Console Manager version (on disk, on database, latest available release)
* `mcm:hook:add`                       Create hook in database
* `mcm:module:generate`                Scaffold new PrestaShop module
* `mcm:module:hook`                    Attach one module on specific hook
* `mcm:module:hooks`                   Get modules list
* `mcm:module:non-essential`           Manage non essential modules
* `mcm:module:rename`                  Rename a module
* `mcm:module:unhook`                  Detach module from hook

## Create your owns Commands

The official documentation from PrestaShop and Symfony Core teams are still right, but you needs
to extends our class.

```php
<?php

// psr-4 autoloader

namespace MCM\Console\Commands\Domain; // e.g. namespace MCM\Console\Commands\Configuration

use MCM\Console\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DomainAction extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mcm:domain') // e.g 'mcm:export'
            // or
            ->setName('mcm:domain:action') // e.g 'mcm:configuration:export' 
            ->setDescription('Describe the command on a user perspective.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->text('Hello Module Console Manager!');

        return 0; // return 0 on success or 1 on failure.
    }
}
```

## Getting started

In a shell (call it shell, console or terminal), at the root of a Prestashop installation, type this command to list all available commands.
You'll see commands provided by Symfony, Prestashop and installed modules.

```shell
./bin/console list
```

To list only mcm commands :
```shell
./bin/console list mcm
```

You are ready to go !

## Contribute

Any contributions are very welcome :)
First [install from sources](/README.md#install-from-sources) and see [Contributing](/CONTRIBUTING.md) for details.

[Current contributors](https://github.com/marsaldev/module_console_manager/graphs/contributors) or [contributors](/CONTRIBUTORS.md).

## Compatibility

| Prestashop Version | Compatible |
| ------------------ | -----------|
| 1.7.4.x and below | :x: |
| 1.7.5.x | :heavy_check_mark: |
| 1.7.6.x | :heavy_check_mark: |
| 1.7.7.x | :heavy_check_mark: |
| 1.7.8.x | :heavy_check_mark: |

| Php Version | Compatible |
| ------ | -----------|
| 7.1 and below | :x: |
| 7.2 | :heavy_check_mark: |
| 7.3| :heavy_check_mark: |
| 7.4 | :heavy_check_mark: |
| 8.0 | :interrobang: Not yet tested |

## License

This module is released under AFL license.
See [License](/docs/licenses/LICENSE.txt) for details.
