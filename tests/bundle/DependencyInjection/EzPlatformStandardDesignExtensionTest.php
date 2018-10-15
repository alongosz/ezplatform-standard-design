<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\Tests\EzPlatformStandardDesignBundle\DependencyInjection;

use EzSystems\EzPlatformStandardDesignBundle\DependencyInjection\EzPlatformStandardDesignExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

class EzPlatformStandardDesignExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return [
            new EzPlatformStandardDesignExtension(),
        ];
    }

    /**
     * Test that Extension prepends eZ Design Engine configuration.
     */
    public function testExtensionPrependsStandardDesignSettings()
    {
        $this->load();

        self::assertContains(
            [
                'design_list' => [
                    'standard' => ['standard'],
                ],
            ],
            $this->container->getExtensionConfig('ezdesign')
        );
    }

    /**
     * Test that Extension prepends SA-aware configuration for layout-related templates.
     */
    public function testSiteAccessAwareTemplatesHaveEzDesignPrefix()
    {
        $this->load();
        $config = $this->container->getExtensionConfig('ezpublish')[0];

        self::assertStringStartsWith('@ezdesign/', $config['system']['default']['pagelayout']);
        foreach (['field_templates', 'fielddefinition_settings_templates'] as $keyName) {
            foreach ($config['system']['default'][$keyName] as $templateSettings) {
                self::assertStringStartsWith(
                    '@ezdesign/',
                    $templateSettings['template']
                );
            }
        }
    }
}
