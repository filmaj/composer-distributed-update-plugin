<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Composer\Merge;

use Composer\Composer;

class PluginState extends \Wikimedia\Composer\Merge\PluginState
{
    /**
     * Load plugin settings
     */
    public function loadSettings()
    {
        $extra = $this->composer->getPackage()->getExtra();
        $config = array_merge(
            array(
                'include' => array(),
                'require' => array(),
                'recurse' => true,
                'replace' => false,
                'ignore-duplicates' => false,
                'merge-dev' => true,
                'merge-extra' => false,
                'merge-extra-deep' => false,
                'merge-scripts' => false,
            ),
            isset($extra['magento-plugin']) ? $extra['magento-plugin'] : array()
        );

        $this->includes = (is_array($config['include'])) ?
            $config['include'] : array($config['include']);
        $this->requires = (is_array($config['require'])) ?
            $config['require'] : array($config['require']);
        $this->recurse = (bool)$config['recurse'];
        $this->replace = (bool)$config['replace'];
        $this->ignore = (bool)$config['ignore-duplicates'];
        $this->mergeDev = (bool)$config['merge-dev'];
        $this->mergeExtra = (bool)$config['merge-extra'];
        $this->mergeExtraDeep = (bool)$config['merge-extra-deep'];
        $this->mergeScripts = (bool)$config['merge-scripts'];
    }
}
