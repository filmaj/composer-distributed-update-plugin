<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Composer\Merge;

use Composer\Json\JsonFile;
use Composer\Package\RootPackageInterface;

class ExtraPackage extends \Wikimedia\Composer\Merge\ExtraPackage
{

    /**
     * Get list of additional packages to include if precessing recursively.
     *
     * @return array
     */
    public function getIncludes()
    {
        return isset($this->json['extra']['magento-plugin']['include']) ?
            $this->json['extra']['magento-plugin']['include'] : array();
    }

    /**
     * Get list of additional packages to require if precessing recursively.
     *
     * @return array
     */
    public function getRequires()
    {
        return isset($this->json['extra']['magento-plugin']['require']) ?
            $this->json['extra']['magento-plugin']['require'] : array();
    }

    /**
     * Read the contents of a composer.json style file into an array.
     *
     * The package contents are fixed up to be usable to create a Package
     * object by providing dummy "name" and "version" values if they have not
     * been provided in the file. This is consistent with the default root
     * package loading behavior of Composer.
     *
     * @param string $path
     * @return array
     */
    protected function readPackageJson($path)
    {
        $file = new JsonFile($path);
        $json = $file->read();
        if (!isset($json['name'])) {
            $json['name'] = 'magento-plugin/' .
                strtr($path, DIRECTORY_SEPARATOR, '-');
        }
        if (!isset($json['version'])) {
            $json['version'] = '1.0.0';
        }
        return $json;
    }

    /**
     * Merge extra config into a RootPackageInterface
     *
     * @param RootPackageInterface $root
     * @param PluginState $state
     */
    public function mergeExtra(RootPackageInterface $root, PluginState $state)
    {
        $extra = $this->package->getExtra();
        unset($extra['magento-plugin']);
        if (!$state->shouldMergeExtra() || empty($extra)) {
            return;
        }

        $rootExtra = $root->getExtra();
        $unwrapped = self::unwrapIfNeeded($root, 'setExtra');

        if ($state->replaceDuplicateLinks()) {
            $unwrapped->setExtra(
                self::mergeExtraArray($state->shouldMergeExtraDeep(), $rootExtra, $extra)
            );
        } else {
            if (!$state->shouldMergeExtraDeep()) {
                foreach (array_intersect(
                    array_keys($extra),
                    array_keys($rootExtra)
                ) as $key) {
                    $this->logger->info(
                        "Ignoring duplicate <comment>{$key}</comment> in ".
                        "<comment>{$this->path}</comment> extra config."
                    );
                }
            }
            $unwrapped->setExtra(
                self::mergeExtraArray($state->shouldMergeExtraDeep(), $extra, $rootExtra)
            );
        }
    }
}
