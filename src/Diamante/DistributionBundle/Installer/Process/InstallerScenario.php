<?php

namespace Diamante\DistributionBundle\Installer\Process;

use Symfony\Component\DependencyInjection\ContainerAware;
use Sylius\Bundle\FlowBundle\Process\Builder\ProcessBuilderInterface;
use Sylius\Bundle\FlowBundle\Process\Scenario\ProcessScenarioInterface;
use Oro\Bundle\InstallerBundle\Process\Step as OroInstallerStep;
use Diamante\DistributionBundle\Installer\Process\Step as DiamanteInstallerStep;

class InstallerScenario extends ContainerAware implements ProcessScenarioInterface
{
    public function build(ProcessBuilderInterface $builder)
    {
        $builder
            ->add('configure', new OroInstallerStep\ConfigureStep())
            ->add('schema', new DiamanteInstallerStep\InitializationStep())
            ->add('setup', new DiamanteInstallerStep\SetupStep())
            ->add('final', new DiamanteInstallerStep\FinalStep())
            ->setRedirect('oro_dashboard_view');
    }
}
