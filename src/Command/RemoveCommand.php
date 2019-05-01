<?php

namespace Magento\Composer\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class RemoveCommand extends \Composer\Command\RemoveCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('distributed-remove');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('require');

        $arguments = [
            'command' => 'require',
            'packages' => $input->getArguments()['packages'],
        ];

        $greetInput = new \Symfony\Component\Console\Input\ArrayInput($arguments);
        $returnCode = $command->run($greetInput, $output);

        if ($returnCode !== 0) {
            // Can't install packages that are currently installed on distributed Magento
        }

        $composer = $this->getComposer(true, $input->getOption('no-plugins'));
        $extraBack = $composer->getPackage()->getExtra();
        $composer->getPackage()->setExtra([]);

        $vendor  = $composer->getConfig()->get('vendor-dir');
        $file = \Composer\Factory::getComposerFile();
        $jsonFile = new \Composer\Json\JsonFile($file);
        $composerDefinition = $jsonFile->read();

        foreach ($composer->getPackage()->getRequires() as $package) {
            $packages[$package->getTarget()] = $package->getPrettyConstraint();
        }

        $packagesToRemoveFromInstances = [];
        foreach ($input->getArguments()['packages'] as $package) {
            $extensionParts = explode('/', $package);

            $extensionComposerJson = $vendor . '/' . $extensionParts[0] . '/' . $extensionParts[1] . '/composer.json';

            $extensionComposerData = json_decode(file_get_contents($extensionComposerJson), true);

            // Need to include only packages that have extra section and abort if there are any packages with out extra section
            $packagesToRemoveFromInstances[$package] = $extensionComposerData['require'];

            foreach ($packages as $key => $value) {
                if (in_array($key, array_keys($extensionComposerData['require']))) {
                    unset($packages[$key]);
                }
            }
            foreach ($input->getArguments()['packages'] as $key => $value) {
                if (in_array($value, array_keys($packages))) {
                    unset($packages[$value]);
                }
            }

            $composerDefinition['require'] = $packages;
            $extraBack = $composerDefinition['extra'] ?: [];
            unset($composerDefinition['extra']);
            $composer->getPackage()->setRequires($packages);
            $jsonFile->write($composerDefinition);

            if ($extensionComposerData['type'] == 'magento2-module') {
                // If metapackage, check recursively for packages that has composer.json with custom configuration of where this extension should be added
                foreach ($extensionComposerData['require'] as $dependentPackage => $version) {
                    $dependentPackageParts = explode('/', $dependentPackage);
                    $extensionDependencyComposerData = json_decode(
                        file_get_contents(
                            $vendor . '/' . $dependentPackageParts[0] . '/' . $dependentPackageParts[1] . '/composer.json'
                        ),
                        true
                    );
                    $instanceDependencies[$dependentPackage] = $extensionDependencyComposerData['instances'];
                }
            } else {
                // If not metapackage don't check recursively, just add
            }

            foreach ($instanceDependencies as $instanceDependency => $instances) {
                foreach ($instances as $instance) {
                    $instancesData[$instance][] = $instanceDependency;
                }
            }


            //$composer = $this->getComposer(true, $input->getOption('no-plugins'));

            $composer = \Composer\Factory::create(new \Composer\IO\NullIO(), null, null);


            $vendor  = $composer->getConfig()->get('vendor-dir');

            $extensionParts = explode('/', $package);

            echo $extensionComposerJson = $vendor . '/' . $extensionParts[0] . '/' . $extensionParts[1] . '/composer.json';

            $extensionComposerData = json_decode(file_get_contents($extensionComposerJson), true);

            //print_r($extensionComposerData);

            $dependentPackages = [];
            if ($extensionComposerData['type'] == 'magento2-module') {
                // If metapackage, check recursively for packages that has composer.json with custom configuration of where this extension should be added
                foreach ($extensionComposerData['require'] as $dependentPackage => $version) {
                    $dependentPackages[] = $dependentPackage;
                }
            } else {
                // If not metapackage don't check recursively, just add
            }

        }

        $command = $this->getApplication()->find('remove');

        $arguments = [
            'command' => 'remove',
            'packages' => $input->getArguments()['packages'],
        ];

        $greetInput = new \Symfony\Component\Console\Input\ArrayInput($arguments);
        $returnCode = $command->run($greetInput, $output);

        $composerDefinition['extra'] = $extraBack;
        $composer->getPackage()->setExtra($extraBack);
        $jsonFile->write($composerDefinition);


        if ($returnCode !== 0) {
            // Were not able to remove packages
        }

        // Need to check which packages were removed

        $instances = $composer->getPackage()->getExtra()['magento-plugin']['instances'];
        foreach ($instancesData as $instance => $instanceData) {
            $instanceComposerData = json_decode(file_get_contents($instances[$instance]), true);
            foreach ($instanceData as $extension) {
                if (isset($instanceComposerData['require'][$extension])) {
                    unset($instanceComposerData['require'][$extension]);
                    unset($instanceComposerData['extra']['magento-plugin']['require'][$package]);
                }
            }
            file_put_contents($instances[$instance], json_encode($instanceComposerData));
        }
    }
}
