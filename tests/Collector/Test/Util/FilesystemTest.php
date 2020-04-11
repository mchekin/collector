<?php

/*
 * This file is part of Collector.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Collector\Test\Util;

use Collector\Test\TestCase;
use Collector\Util\Filesystem;

class FilesystemTest extends TestCase
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $workingDirectory;

    /**
     * @var string
     */
    private $testFile;

    public function setUp()
    {
        $this->filesystem = new Filesystem;
        $this->workingDirectory = $this->getUniqueTmpDirectory();
        $this->testFile = $this->getUniqueTmpDirectory() . '/composer_test_file';
    }

    public function tearDown()
    {
        if (is_dir($this->workingDirectory)) {
            $this->filesystem->removeDirectory($this->workingDirectory);
        }
        if (is_file($this->testFile)) {
            $this->filesystem->removeDirectory(dirname($this->testFile));
        }
    }

    /**
     * @dataProvider providePathCouplesAsCode
     *
     * @param $from
     * @param $to
     * @param $directory
     * @param $expected
     * @param bool $static
     */
    public function testFindShortestPathCode($from, $to, $directory, $expected, $static = false)
    {
        $filesystem = new Filesystem;
        $this->assertEquals($expected, $filesystem->findShortestPathCode($from, $to, $directory, $static));
    }

    public function providePathCouplesAsCode()
    {
        return array(
            array('/foo/bar', '/foo/bar', false, '__FILE__'),
            array('/foo/bar', '/foo/baz', false, "__DIR__.'/baz'"),
            array('/foo/bin/run', '/foo/vendor/acme/bin/run', false, "dirname(__DIR__).'/vendor/acme/bin/run'"),
            array('/foo/bin/run', '/bar/bin/run', false, "'/bar/bin/run'"),
            array('c:/bin/run', 'c:/vendor/acme/bin/run', false, "dirname(__DIR__).'/vendor/acme/bin/run'"),
            array('c:\\bin\\run', 'c:/vendor/acme/bin/run', false, "dirname(__DIR__).'/vendor/acme/bin/run'"),
            array('c:/bin/run', 'd:/vendor/acme/bin/run', false, "'d:/vendor/acme/bin/run'"),
            array('c:\\bin\\run', 'd:/vendor/acme/bin/run', false, "'d:/vendor/acme/bin/run'"),
            array('/foo/bar', '/foo/bar', true, '__DIR__'),
            array('/foo/bar/', '/foo/bar', true, '__DIR__'),
            array('/foo/bar', '/foo/baz', true, "dirname(__DIR__).'/baz'"),
            array('/foo/bin/run', '/foo/vendor/acme/bin/run', true, "dirname(dirname(__DIR__)).'/vendor/acme/bin/run'"),
            array('/foo/bin/run', '/bar/bin/run', true, "'/bar/bin/run'"),
            array('/bin/run', '/bin/run', true, '__DIR__'),
            array('c:/bin/run', 'c:\\bin/run', true, '__DIR__'),
            array('c:/bin/run', 'c:/vendor/acme/bin/run', true, "dirname(dirname(__DIR__)).'/vendor/acme/bin/run'"),
            array('c:\\bin\\run', 'c:/vendor/acme/bin/run', true, "dirname(dirname(__DIR__)).'/vendor/acme/bin/run'"),
            array('c:/bin/run', 'd:/vendor/acme/bin/run', true, "'d:/vendor/acme/bin/run'"),
            array('c:\\bin\\run', 'd:/vendor/acme/bin/run', true, "'d:/vendor/acme/bin/run'"),
            array('C:/Temp/test', 'C:\Temp', true, 'dirname(__DIR__)'),
            array('C:/Temp', 'C:\Temp\test', true, "__DIR__ . '/test'"),
            array('/tmp/test', '/tmp', true, "dirname(__DIR__)"),
            array('/tmp', '/tmp/test', true, "__DIR__ . '/test'"),
            array('C:/Temp', 'c:\Temp\test', true, "__DIR__ . '/test'"),
            array('/tmp/test/./', '/tmp/test/', true, '__DIR__'),
            array('/tmp/test/../vendor', '/tmp/test', true, "dirname(__DIR__).'/test'"),
            array('/tmp/test/.././vendor', '/tmp/test', true, "dirname(__DIR__).'/test'"),
            array('C:/Temp', 'c:\Temp\..\..\test', true, "dirname(__DIR__).'/test'"),
            array('C:/Temp/../..', 'd:\Temp\..\..\test', true, "'d:/test'"),
            array('/foo/bar', '/foo/bar_vendor', true, "dirname(__DIR__).'/bar_vendor'"),
            array('/foo/bar_vendor', '/foo/bar', true, "dirname(__DIR__).'/bar'"),
            array('/foo/bar_vendor', '/foo/bar/src', true, "dirname(__DIR__).'/bar/src'"),
            array('/foo/bar_vendor/src2', '/foo/bar/src/lib', true, "dirname(dirname(__DIR__)).'/bar/src/lib'"),

            // static use case
            array('/tmp/test/../vendor', '/tmp/test', true, "__DIR__ . '/..'.'/test'", true),
            array('/tmp/test/.././vendor', '/tmp/test', true, "__DIR__ . '/..'.'/test'", true),
            array('C:/Temp', 'c:\Temp\..\..\test', true, "__DIR__ . '/..'.'/test'", true),
            array('C:/Temp/../..', 'd:\Temp\..\..\test', true, "'d:/test'", true),
            array('/foo/bar', '/foo/bar_vendor', true, "__DIR__ . '/..'.'/bar_vendor'", true),
            array('/foo/bar_vendor', '/foo/bar', true, "__DIR__ . '/..'.'/bar'", true),
            array('/foo/bar_vendor', '/foo/bar/src', true, "__DIR__ . '/..'.'/bar/src'", true),
            array('/foo/bar_vendor/src2', '/foo/bar/src/lib', true, "__DIR__ . '/../..'.'/bar/src/lib'", true),
        );
    }

    /**
     * @dataProvider providePathCouples
     * @param $a
     * @param $b
     * @param $expected
     * @param bool $directory
     */
    public function testFindShortestPath($a, $b, $expected, $directory = false)
    {
        $fs = new Filesystem;
        $this->assertEquals($expected, $fs->findShortestPath($a, $b, $directory));
    }

    public function providePathCouples()
    {
        return array(
            array('/foo/bar', '/foo/bar', './bar'),
            array('/foo/bar', '/foo/baz', './baz'),
            array('/foo/bar/', '/foo/baz', './baz'),
            array('/foo/bar', '/foo/bar', './', true),
            array('/foo/bar', '/foo/baz', '../baz', true),
            array('/foo/bar/', '/foo/baz', '../baz', true),
            array('C:/foo/bar/', 'c:/foo/baz', '../baz', true),
            array('/foo/bin/run', '/foo/vendor/acme/bin/run', '../vendor/acme/bin/run'),
            array('/foo/bin/run', '/bar/bin/run', '/bar/bin/run'),
            array('/foo/bin/run', '/bar/bin/run', '/bar/bin/run', true),
            array('c:/foo/bin/run', 'd:/bar/bin/run', 'd:/bar/bin/run', true),
            array('c:/bin/run', 'c:/vendor/acme/bin/run', '../vendor/acme/bin/run'),
            array('c:\\bin\\run', 'c:/vendor/acme/bin/run', '../vendor/acme/bin/run'),
            array('c:/bin/run', 'd:/vendor/acme/bin/run', 'd:/vendor/acme/bin/run'),
            array('c:\\bin\\run', 'd:/vendor/acme/bin/run', 'd:/vendor/acme/bin/run'),
            array('C:/Temp/test', 'C:\Temp', './'),
            array('/tmp/test', '/tmp', './'),
            array('C:/Temp/test/sub', 'C:\Temp', '../'),
            array('/tmp/test/sub', '/tmp', '../'),
            array('/tmp/test/sub', '/tmp', '../../', true),
            array('c:/tmp/test/sub', 'c:/tmp', '../../', true),
            array('/tmp', '/tmp/test', 'test'),
            array('C:/Temp', 'C:\Temp\test', 'test'),
            array('C:/Temp', 'c:\Temp\test', 'test'),
            array('/tmp/test/./', '/tmp/test', './', true),
            array('/tmp/test/../vendor', '/tmp/test', '../test', true),
            array('/tmp/test/.././vendor', '/tmp/test', '../test', true),
            array('C:/Temp', 'c:\Temp\..\..\test', '../test', true),
            array('C:/Temp/../..', 'c:\Temp\..\..\test', './test', true),
            array('C:/Temp/../..', 'D:\Temp\..\..\test', 'd:/test', true),
            array('/tmp', '/tmp/../../test', '/test', true),
            array('/foo/bar', '/foo/bar_vendor', '../bar_vendor', true),
            array('/foo/bar_vendor', '/foo/bar', '../bar', true),
            array('/foo/bar_vendor', '/foo/bar/src', '../bar/src', true),
            array('/foo/bar_vendor/src2', '/foo/bar/src/lib', '../../bar/src/lib', true),
            array('C:/', 'C:/foo/bar/', 'foo/bar', true),
        );
    }

    /**
     * @group GH-1339
     */
    public function testRemoveDirectoryPhp()
    {
        @mkdir($this->workingDirectory . '/level1/level2', 0777, true);
        file_put_contents($this->workingDirectory . '/level1/level2/hello.txt', 'hello world');

        $fs = new Filesystem;
        $this->assertTrue($fs->removeDirectoryPhp($this->workingDirectory));
        $this->assertFileNotExists($this->workingDirectory . '/level1/level2/hello.txt');
    }

    public function testFileSize()
    {
        file_put_contents($this->testFile, 'Hello');

        $fs = new Filesystem;
        $this->assertGreaterThanOrEqual(5, $fs->size($this->testFile));
    }

    public function testDirectorySize()
    {
        @mkdir($this->workingDirectory, 0777, true);
        file_put_contents($this->workingDirectory. '/file1.txt', 'Hello');
        file_put_contents($this->workingDirectory. '/file2.txt', 'World');

        $fs = new Filesystem;
        $this->assertGreaterThanOrEqual(10, $fs->size($this->workingDirectory));
    }

    /**
     * @dataProvider provideNormalizedPaths
     *
     * @param $expected
     * @param $actual
     */
    public function testNormalizePath($expected, $actual)
    {
        $fs = new Filesystem;
        $this->assertEquals($expected, $fs->normalizePath($actual));
    }

    public function provideNormalizedPaths()
    {
        return array(
            array('../foo', '../foo'),
            array('c:/foo/bar', 'c:/foo//bar'),
            array('C:/foo/bar', 'C:/foo/./bar'),
            array('C:/foo/bar', 'C://foo//bar'),
            array('C:/foo/bar', 'C:///foo//bar'),
            array('C:/bar', 'C:/foo/../bar'),
            array('/bar', '/foo/../bar/'),
            array('phar://c:/Foo', 'phar://c:/Foo/Bar/..'),
            array('phar://c:/Foo', 'phar://c:///Foo/Bar/..'),
            array('phar://c:/', 'phar://c:/Foo/Bar/../../../..'),
            array('/', '/Foo/Bar/../../../..'),
            array('/', '/'),
            array('/', '//'),
            array('/', '///'),
            array('/Foo', '///Foo'),
            array('c:/', 'c:\\'),
            array('../src', 'Foo/Bar/../../../src'),
            array('c:../b', 'c:.\\..\\a\\..\\b'),
            array('phar://c:../Foo', 'phar://c:../Foo'),
        );
    }

    /**
     * @link https://github.com/composer/composer/issues/3157
     * @requires function symlink
     */
    public function testUnlinkSymlinkedDirectory()
    {
        $basepath = $this->workingDirectory;
        $symlinked = $basepath . '/linked';
        @mkdir($basepath . '/real', 0777, true);
        touch($basepath . '/real/FILE');

        $result = @symlink($basepath . '/real', $symlinked);

        if (!$result) {
            $this->markTestSkipped('Symbolic links for directories not supported on this platform');
        }

        if (!is_dir($symlinked)) {
            $this->fail('Precondition assertion failed (is_dir is false on symbolic link to directory).');
        }

        $fs = new Filesystem();
        $result = $fs->unlink($symlinked);
        $this->assertTrue($result);
        $this->assertFileNotExists($symlinked);
    }

    /**
     * @link https://github.com/composer/composer/issues/3144
     * @requires function symlink
     */
    public function testRemoveSymlinkedDirectoryWithTrailingSlash()
    {
        @mkdir($this->workingDirectory . '/real', 0777, true);
        touch($this->workingDirectory . '/real/FILE');
        $symlinked = $this->workingDirectory . '/linked';
        $symlinkedTrailingSlash = $symlinked . '/';

        $result = @symlink($this->workingDirectory . '/real', $symlinked);

        if (!$result) {
            $this->markTestSkipped('Symbolic links for directories not supported on this platform');
        }

        if (!is_dir($symlinked)) {
            $this->fail('Precondition assertion failed (is_dir is false on symbolic link to directory).');
        }

        if (!is_dir($symlinkedTrailingSlash)) {
            $this->fail('Precondition assertion failed (is_dir false w trailing slash).');
        }

        $fs = new Filesystem();

        $result = $fs->removeDirectory($symlinkedTrailingSlash);
        $this->assertTrue($result);
        $this->assertFileNotExists($symlinkedTrailingSlash);
        $this->assertFileNotExists($symlinked);
    }

    public function testJunctions()
    {
        @mkdir($this->workingDirectory . '/real/nesting/testing', 0777, true);

        //$processExecutor = $this
        //    ->getMockBuilder('Collector\Util\ProcessExecutor')
        //    ->disableOriginalConstructor()
        //    ->getMock()
        //;
        $filesystem = new Filesystem();

        // Non-Windows systems do not support this and will return false on all tests, and an exception on creation
        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            $this->assertFalse($filesystem->isJunction($this->workingDirectory));
            $this->assertFalse($filesystem->removeJunction($this->workingDirectory));
            $this->setExpectedException('LogicException', 'not available on non-Windows platform');
        }

        $target = $this->workingDirectory . '/real/../real/nesting';
        $junction = $this->workingDirectory . '/junction';

        // Create and detect junction
        $filesystem->junction($target, $junction);
        $this->assertTrue($filesystem->isJunction($junction), $junction . ': is a junction');
        //$this->assertFalse($filesystem->isJunction($target), $target . ': is not a junction');
        //$this->assertTrue($filesystem->isJunction($target . '/../../junction'), $target . '/../../junction: is a junction');
        //$this->assertFalse($filesystem->isJunction($junction . '/../real'), $junction . '/../real: is not a junction');
        //$this->assertTrue($filesystem->isJunction($junction . '/../junction'), $junction . '/../junction: is a junction');
        //
        //// Remove junction
        //$this->assertTrue(is_dir($junction), $junction . ' is a directory');
        //$this->assertTrue($filesystem->removeJunction($junction), $junction . ' has been removed');
        //$this->assertFalse(is_dir($junction), $junction . ' is not a directory');
    }

    public function testCopy()
    {
        @mkdir($this->workingDirectory . '/foo/bar', 0777, true);
        @mkdir($this->workingDirectory . '/foo/baz', 0777, true);
        file_put_contents($this->workingDirectory . '/foo/foo.file', 'foo');
        file_put_contents($this->workingDirectory . '/foo/bar/foobar.file', 'foobar');
        file_put_contents($this->workingDirectory . '/foo/baz/foobaz.file', 'foobaz');
        file_put_contents($this->testFile, 'testfile');

        $fs = new Filesystem();

        $result1 = $fs->copy($this->workingDirectory . '/foo', $this->workingDirectory . '/foop');
        $this->assertTrue($result1, 'Copying directory failed.');
        $this->assertTrue(is_dir($this->workingDirectory . '/foop'), 'Not a directory: ' . $this->workingDirectory . '/foop');
        $this->assertTrue(is_dir($this->workingDirectory . '/foop/bar'), 'Not a directory: ' . $this->workingDirectory . '/foop/bar');
        $this->assertTrue(is_dir($this->workingDirectory . '/foop/baz'), 'Not a directory: ' . $this->workingDirectory . '/foop/baz');
        $this->assertTrue(is_file($this->workingDirectory . '/foop/foo.file'), 'Not a file: ' . $this->workingDirectory . '/foop/foo.file');
        $this->assertTrue(is_file($this->workingDirectory . '/foop/bar/foobar.file'), 'Not a file: ' . $this->workingDirectory . '/foop/bar/foobar.file');
        $this->assertTrue(is_file($this->workingDirectory . '/foop/baz/foobaz.file'), 'Not a file: ' . $this->workingDirectory . '/foop/baz/foobaz.file');

        $result2 = $fs->copy($this->testFile, $this->workingDirectory . '/testfile.file');
        $this->assertTrue($result2);
        $this->assertTrue(is_file($this->workingDirectory . '/testfile.file'));
    }

    public function testCopyThenRemove()
    {
        @mkdir($this->workingDirectory . '/foo/bar', 0777, true);
        @mkdir($this->workingDirectory . '/foo/baz', 0777, true);
        file_put_contents($this->workingDirectory . '/foo/foo.file', 'foo');
        file_put_contents($this->workingDirectory . '/foo/bar/foobar.file', 'foobar');
        file_put_contents($this->workingDirectory . '/foo/baz/foobaz.file', 'foobaz');
        file_put_contents($this->testFile, 'testfile');

        $fs = new Filesystem();

        $fs->copyThenRemove($this->testFile, $this->workingDirectory . '/testfile.file');
        $this->assertFalse(is_file($this->testFile), 'Still a file: ' . $this->testFile);

        $fs->copyThenRemove($this->workingDirectory . '/foo', $this->workingDirectory . '/foop');
        $this->assertFalse(is_file($this->workingDirectory . '/foo/baz/foobaz.file'), 'Still a file: ' . $this->workingDirectory . '/foo/baz/foobaz.file');
        $this->assertFalse(is_file($this->workingDirectory . '/foo/bar/foobar.file'), 'Still a file: ' . $this->workingDirectory . '/foo/bar/foobar.file');
        $this->assertFalse(is_file($this->workingDirectory . '/foo/foo.file'), 'Still a file: ' . $this->workingDirectory . '/foo/foo.file');
        $this->assertFalse(is_dir($this->workingDirectory . '/foo/baz'), 'Still a directory: ' . $this->workingDirectory . '/foo/baz');
        $this->assertFalse(is_dir($this->workingDirectory . '/foo/bar'), 'Still a directory: ' . $this->workingDirectory . '/foo/bar');
        $this->assertFalse(is_dir($this->workingDirectory . '/foo'), 'Still a directory: ' . $this->workingDirectory . '/foo');
    }
}
