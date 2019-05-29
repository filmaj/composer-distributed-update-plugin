<?php

namespace Magento\Composer\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RequireCommand extends \Composer\Command\RequireCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('distributed-require');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getIO();

        $io->writeError('<info>Merge configuration from instance composer.json files and install requested packages.</info>');
        
        $result = parent::execute($input, $output);

        if ($result !== 0) {
            return $result;
        }

        $composer = $this->getComposer(true, $input->getOption('no-plugins'));
        $vendor  = $composer->getConfig()->get('vendor-dir');

        $packages = $input->getArguments()['packages'];

        foreach ($packages as $package) {
            $extensionParts = explode('/', $package);
            $extensionComposerJson = $vendor . '/' . $extensionParts[0] . '/' . $extensionParts[1] . '/composer.json';
            $extensionComposerData = json_decode(file_get_contents($extensionComposerJson), true);

            $packageVersion = $extensionComposerData['version'];

            // If metapackage, check for packages that has composer.json with custom configuration of where this extension should be added
            foreach ($extensionComposerData['require'] as $dependentPackage => $version) {
                $dependentPackageParts = explode('/', $dependentPackage);
                $extensionDependencyComposerData = json_decode(
                    file_get_contents(
                        $vendor . '/' . $dependentPackageParts[0] . '/' . $dependentPackageParts[1] . '/composer.json'
                    ),
                    true
                );
                
                if (!isset($extensionDependencyComposerData['extra']['magento-plugin']['instances'])
                    || empty($extensionDependencyComposerData['extra']['magento-plugin']['instances'])
                ) {
                    $io->writeError('<info>' . $dependentPackage . ' of ' . $package . ' doesn\'t contain instance configuration.</info>');
                    return 1;
                }
                
                $instanceDependencies[$dependentPackage] = [
                    'version' => $version,
                    'instances' => $extensionDependencyComposerData['extra']['magento-plugin']['instances']
                ];
            }

            foreach ($instanceDependencies as $instanceDependency => $instanceDependencyInfo) {
                foreach ($instanceDependencyInfo['instances'] as $instance) {
                    $instancesData[$instance][] = [
                        'dependency' => $instanceDependency,
                        'version' => $instanceDependencyInfo['version']
                    ];
                }
            }

            $instances = $composer->getPackage()->getExtra()['magento-plugin']['instances'];

            foreach ($instancesData as $instance => $instanceData) {
                $instanceComposerData = json_decode(file_get_contents($instances[$instance]), true);
                foreach ($instanceData as $extension) {
                    if (!isset($instanceComposerData['extra']['magento-plugin']['require'][$package])) {
                        // Need to handle extension versions
                        $instanceComposerData['require'][$extension['dependency']] = $extension['version'];
                        // Need to check if package already exists
                        $instanceComposerData['extra']['magento-plugin']['require'][$package] = $packageVersion;

                        $io->writeError('<info>' . $extension['dependency'] . ' has been added as dependency to ' . $instance . ' instance.</info>');
                    }
                }
                // Need to use composer API, handle file doesn't exist and not writable errors, etc
                file_put_contents($instances[$instance], json_encode($instanceComposerData));
            }
        }
    }
}
