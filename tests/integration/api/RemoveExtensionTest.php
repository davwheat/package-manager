<?php

/*
 * This file is part of Flarum.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace Flarum\PackageManager\Tests\integration\api;

use Flarum\PackageManager\Tests\integration\TestCase;

class RemoveExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function extension_installed_by_default()
    {
        $this->assertExtensionExists('flarum-tags');
    }

    /**
     * @test
     */
    public function removing_an_extension_works()
    {
        $response = $this->send(
            $this->request('DELETE', '/api/package-manager/extensions/flarum-tags', [
                'authenticatedAs' => 1
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertExtensionNotExists('flarum-tags');

        $this->requireExtension('flarum/tags');
    }
}
