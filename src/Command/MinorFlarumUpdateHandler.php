<?php

/*
 * This file is part of Flarum.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace Flarum\PackageManager\Command;

use Composer\Console\Application;
use Illuminate\Contracts\Events\Dispatcher;
use Flarum\PackageManager\Event\FlarumUpdated;
use Flarum\PackageManager\Exception\ComposerUpdateFailedException;
use Flarum\PackageManager\LastUpdateCheck;
use Flarum\PackageManager\OutputLogger;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class MinorFlarumUpdateHandler
{
    /**
     * @var Application
     */
    protected $composer;

    /**
     * @var LastUpdateCheck
     */
    protected $lastUpdateCheck;

    /**
     * @var Dispatcher
     */
    protected $events;

    /**
     * @var OutputLogger
     */
    protected $logger;

    public function __construct(Application $composer, LastUpdateCheck $lastUpdateCheck, Dispatcher $events, OutputLogger $logger)
    {
        $this->composer = $composer;
        $this->lastUpdateCheck = $lastUpdateCheck;
        $this->events = $events;
        $this->logger = $logger;
    }

    /**
     * @throws \Flarum\User\Exception\PermissionDeniedException
     * @throws ComposerUpdateFailedException
     */
    public function handle(MinorFlarumUpdate $command)
    {
        $command->actor->assertAdmin();

        $output = new BufferedOutput();
        $input = new ArrayInput([
            'command' => 'update',
            'packages' => ["flarum/*"],
            '--prefer-dist' => true,
            '--no-dev' => true,
            '-a' => true,
            '--with-all-dependencies' => true,
        ]);

        $exitCode = $this->composer->run($input, $output);
        $output = $output->fetch();

        $this->logger->log($input->__toString(), $output, $exitCode);

        if ($exitCode !== 0) {
            throw new ComposerUpdateFailedException('flarum/*', $output);
        }

        $this->lastUpdateCheck->forget('flarum/*', true);

        $this->events->dispatch(
            new FlarumUpdated(FlarumUpdated::MINOR)
        );

        return true;
    }
}
