<?php

namespace Oxygen\Tests\Integrity;

class NamespaceTest extends \PHPUnit_Framework_TestCase
{
    public function testNamespacesMapToFileLocations()
    {
        $dir = realpath(__DIR__.'/../../src');
        $dirLength = strlen($dir) + 1;

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));

        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            if ($file->isDir()) {
                continue;
            }

            if (pathinfo($file->getRealPath(), PATHINFO_EXTENSION) !== 'php') {
                continue;
            }

            $location = substr($file->getRealPath(), $dirLength);
            $location = str_replace('\\', '/', $location); // Windows compatibility.
            $classes = $this->getPhpClasses(file_get_contents($file->getRealPath()));

            if (!isset($classes[0])) {
                $this->fail(sprintf('File "%s" has no class or interface defined', $file->getRealPath()));
            }

            $expectedClass = 'Oxygen_'.substr(str_replace(['/', '\\'], '_', $location), 0, -4);
            $this->assertContains($expectedClass, $classes, sprintf('Class %s not found among class(es): %s', $expectedClass, implode(', ', $classes)));
        }
    }

    private function getPhpClasses($code)
    {
        $classes = array();
        $tokens = token_get_all($code);
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if (($tokens[$i - 2][0] === T_CLASS || $tokens[$i - 2][0] === T_INTERFACE)
                && $tokens[$i - 1][0] === T_WHITESPACE
                && $tokens[$i][0] === T_STRING
            ) {

                $class_name = $tokens[$i][1];
                $classes[] = $class_name;
            }
        }

        return $classes;
    }
}
