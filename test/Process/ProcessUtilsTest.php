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

use Oxygen_Process_ProcessUtils;

class ProcessUtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataArguments
     */
    public function testEscapeArgument($result, $argument)
    {
        $this->assertSame($result, Oxygen_Process_ProcessUtils::escapeArgument($argument));
    }

    public function dataArguments()
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            return array(
                array('"\"php\" \"-v\""', '"php" "-v"'),
                array('"foo bar"', 'foo bar'),
                array('^%"path"^%', '%path%'),
                array('"<|>\\" \\"\'f"', '<|>" "\'f'),
                array('""', ''),
                array('"with\trailingbs\\\\"', 'with\trailingbs\\'),
            );
        }

        return array(
            array("'\"php\" \"-v\"'", '"php" "-v"'),
            array("'foo bar'", 'foo bar'),
            array("'%path%'", '%path%'),
            array("'<|>\" \"'\\''f'", '<|>" "\'f'),
            array("''", ''),
            array("'with\\trailingbs\\'", 'with\trailingbs\\'),
        );
    }
}
