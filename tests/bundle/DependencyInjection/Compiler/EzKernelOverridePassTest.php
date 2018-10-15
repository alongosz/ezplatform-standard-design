<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\Tests\EzPlatformStandardDesignBundle\DependencyInjection\Compiler;

use EzSystems\EzPlatformStandardDesignBundle\DependencyInjection\Compiler\EzKernelOverridePass;
use EzSystems\EzPlatformStandardDesignBundle\DependencyInjection\EzPlatformStandardDesignExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Test overriding eZ Kernel setup for templates with eZ Design.
 *
 * @see \EzSystems\EzPlatformStandardDesignBundle\DependencyInjection\Compiler\EzKernelOverridePass
 */
class EzKernelOverridePassTest extends AbstractCompilerPassTestCase
{
    public function getTemplatesPathMap()
    {
        return [
            [[]],
            [['standard' => ['/other/path']]],
            [['custom' => ['/another/path']]],
        ];
    }

    /**
     * Register the StandardTheme compiler pass under test.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new EzKernelOverridePass());
    }

    /**
     * @dataProvider getTemplatesPathMap
     *
     * @param array $templatesPathMap
     */
    public function testKernelViewsDirectoryIsMappedToStandardTheme(array $templatesPathMap)
    {
        $this->setParameter('ezdesign.templates_path_map', $templatesPathMap);
        $this->setParameter(
            'kernel.bundles_metadata',
            [
                'EzPublishCoreBundle' => [
                    'path' => '/some/path',
                ],
            ]
        );

        $this->container->compile();

        $templatesPathMap['standard'][] = '/some/path/Resources/views';

        self::assertContainerBuilderHasParameter(
            'ezdesign.templates_path_map',
            $templatesPathMap
        );
    }

    /**
     * Test that default views parameters are overridden and prefixed with ezdesign Twig namespace.
     */
    public function testKernelDefaultViewTemplatesHaveEzDesignPrefix()
    {
        $this->container->setParameter(
            EzPlatformStandardDesignExtension::OVERRIDE_KERNEL_TEMPLATES_PARAM_NAME,
            true
        );
        $this->container->compile();

        $parameters = $this->container->getParameterBag()->all();
        foreach ($parameters as $parameterId => $parameterValue) {
            // check Twig templates only
            if (!is_string($parameterValue) || !$this->endsWith($parameterValue, '.html.twig')) {
                continue;
            }
            self::assertStringStartsWith('@ezdesign/', $parameterValue, "Parameter '{$parameterId}' doesn't start with @ezdesign/ prefix");
        }
    }

    /**
     * Check if the given string ends with the given suffix.
     *
     * @param string $string
     * @param string $suffix
     *
     * @return bool
     */
    private function endsWith(string $string, string $suffix): bool
    {
        return $suffix === substr($string, 0 - \strlen($suffix));
    }
}
