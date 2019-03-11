<?php

declare(strict_types=1);

namespace ReliQArts\Docweaver\Contracts;

use Symfony\Component\Process\Exception\ProcessFailedException;

interface VCSCommandRunner
{
    /**
     * @param string $source
     * @param string $branch
     * @param string $workingDirectory
     */
    public function clone(string $source, string $branch, string $workingDirectory): void;

    /**
     * @param string $workingDirectory
     *
     * @throws ProcessFailedException
     */
    public function pull(string $workingDirectory): void;

    /**
     * @param string $workingDirectory
     *
     * @throws ProcessFailedException
     *
     * @return array
     */
    public function getTags(string $workingDirectory): array;
}