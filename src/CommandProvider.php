<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
            new \Magento\Composer\Command\RequireCommand(),
            new \Magento\Composer\Command\RemoveCommand()
        ];
    }
}
