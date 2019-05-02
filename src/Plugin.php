<?php

namespace Magento\Composer;

use Composer\Plugin\PluginInterface;
use Composer\Plugin\Capable;

class Plugin extends \Wikimedia\Composer\MergePlugin implements PluginInterface, \Composer\EventDispatcher\EventSubscriberInterface, Capable
{

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return parent::getSubscribedEvents() + [
            \Composer\Installer\PackageEvents::PRE_PACKAGE_UPDATE =>
                array('prePackageUpdate', self::CALLBACK_PRIORITY),
        ];
    }

    public function prePackageUpdate()
    {
        $a = 1;
    }

    /**
     * @inheritdoc
     */
    public function getCapabilities()
    {
        return [
            \Composer\Plugin\Capability\CommandProvider::class => 'Magento\Composer\CommandProvider',
        ];
    }

    /**
     * @inheritdoc
     */
    public function onInit(\Composer\EventDispatcher\Event $event)
    {
        /*
        $this->state->loadSettings();
        if (!isset($this->composer->getPackage()->getExtra()['magento-plugin']['instances'])) {
            throw new \Magento\Composer\Merge\MissingConfigurationException(
                "Required configuration is missing in root composer.json for Magento distributed update plugin."
            );
        }
        */
        parent::onInit($event);
    }

    /**
     * @inheritdoc
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