<?php

/*
 * This file is part of Flarum.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

use Flarum\Testing\integration\Setup\SetupScript;
use Flarum\PackageManager\Tests\integration\SetupComposer;

require __DIR__.'/../../vendor/autoload.php';

putenv('FLARUM_TEST_TMP_DIR_LOCAL='.realpath('../tmp'));

$setup = new SetupScript();

$setup->run();

$setupComposer = new SetupComposer();

$setupComposer->run();
