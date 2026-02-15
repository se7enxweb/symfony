<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Monolog\Formatter;

use Monolog\Formatter\FormatterInterface;
use Symfony\Component\VarDumper\Cloner\VarCloner;

/**
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class VarDumperFormatter implements FormatterInterface
{
    private $cloner;
    private $clonerError = false;

    public function __construct(?VarCloner $cloner = null)
    {
        try {
            $this->cloner = $cloner ?: new VarCloner();
        } catch (\Throwable $e) {
            // VarCloner might not be loadable during error handling
            $this->clonerError = true;
            $this->cloner = null;
        }
    }

    public function format(array $record)
    {
        // If cloner initialization failed, skip var dumping
        if ($this->clonerError || !$this->cloner) {
            return $record;
        }

        try {
            $record['context'] = $this->cloner->cloneVar($record['context']);
            $record['extra'] = $this->cloner->cloneVar($record['extra']);
        } catch (\Throwable $e) {
            // Fallback if cloning fails
        }

        return $record;
    }

    public function formatBatch(array $records)
    {
        foreach ($records as $k => $record) {
            $record[$k] = $this->format($record);
        }

        return $records;
    }
}
