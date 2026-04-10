<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\Session\Storage\Handler;

/**
 * Native session handler using PHP's built in file storage.
 *
 * @author Drak <drak@zikula.org>
 */
class NativeFileSessionHandler extends NativeSessionHandler
{
    /**
     * @param string $savePath Path of directory to save session files
     *                         Default null will leave setting as defined by PHP.
     *                         '/path', 'N;/path', or 'N;octal-mode;/path
     *
     * @see https://php.net/session.configuration#ini.session.save-path for further details.
     *
     * @throws \InvalidArgumentException On invalid $savePath
     * @throws \RuntimeException         When failing to create the save directory
     */
    public function __construct($savePath = null)
    {
        if (null === $savePath) {
            $savePath = ini_get('session.save_path');
        }

        $baseDir = $savePath;

        if ($count = substr_count($savePath, ';')) {
            if ($count > 2) {
                throw new \InvalidArgumentException(sprintf('Invalid argument $savePath \'%s\'.', $savePath));
            }

            // characters after last ';' are the path
            $baseDir = ltrim(strrchr($savePath, ';'), ';');
        }

        if ($baseDir && !is_dir($baseDir) && !@mkdir($baseDir, 0777, true) && !is_dir($baseDir)) {
            throw new \RuntimeException(sprintf('Session Storage was not able to create directory "%s".', $baseDir));
        }

        // PHP 8.1+ raises a warning (converted to exception by Symfony's error handler) when
        // session ini settings are changed after the session has already started or after output
        // has been sent (e.g. when the legacy kernel runs during a CLI command like cache:clear).
        if (\PHP_SESSION_ACTIVE !== session_status() && !headers_sent()) {
            ini_set('session.save_path', $savePath);
            ini_set('session.save_handler', 'files');
        }
    }
}
