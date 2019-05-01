<?php

namespace Magento\Composer;

class CommandProvider implements \Composer\Plugin\Capability\CommandProvider
{
    /**
     * @inheritdoc
     */
    public function getCommands()
    {
        return [
            new \Magento\Composer\Command\UpdateCommand(),
            new \Magento\Composer\Command\InstallCommand(),
            new \Magento\Composer\Command\RequireCommand(),
            new \Magento\Composer\Command\RemoveCommand()
        ];
    }
}