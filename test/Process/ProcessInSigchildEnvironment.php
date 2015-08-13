<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Oxygen\Tests\Process;

use Oxygen_Process_Process;

class ProcessInSigchildEnvironment extends Oxygen_Process_Process
{
    protected function isSigchildEnabled()
    {
        return true;
    }
}
