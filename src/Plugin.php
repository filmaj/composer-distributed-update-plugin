<?php

namespace Magento\Composer;

use Composer\Plugin\PluginInterface;
use Composer\Plugin\Capable;
use Magento\Composer\Merge\MissingFileException;

class Plugin extends \Wikimedia\Composer\MergePlugin implements PluginInterface, \Composer\EventDispatcher\EventSubscriberInterface, Capable
{

    public function getCapabilities()
    {
        return [
            \Composer\Plugin\Capability\CommandProvider::class => 'Magento\Composer\CommandProvider',
        ];
    }

    /**
     * Find configuration files matching the configured glob patterns and
     * merge their contents with the master package.
     *
     * @param array $patterns List of files/glob patterns
     * @param bool $required Are the patterns required to match files?
     * @throws MissingFileException when required and a pattern returns no
     *      results
     */
    protected function mergeFiles(array $patterns, $required = false)
    {
        $root = $this->composer->getPackage();

        $instances = [];
        if (isset($this->composer->getPackage()->getExtra()['magento-plugin']['instances'])) {
            foreach ($this->composer->getPackage()->getExtra()['magento-plugin']['instances'] as $instance) {
                $instances[] = [$instance];
            }
        }

        foreach (array_reduce($instances, 'array_merge', array()) as $path) {
            $this->mergeFile($root, $path);
        }
    }
}