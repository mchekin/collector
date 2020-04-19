<?php

namespace Collector\Test\Util;

use Collector\Test\TestCase;
use Collector\Util\CollectorMirror;

class CollectorMirrorTest extends TestCase
{
    /**
     * TODO: understand the solution: what are the expected values for each parameter
     */
    public function testWithReadyParameters()
    {
        $mirrorUrl = 'https://api.github.com/repos/%package%/%version%/%reference%/%type%';
        $packageName = 'mchekin/collector';
        $version = '1.0.1';
        $reference = 'b46bfffbe2a0b675cd77b9c58fb29702';
        $type = 'dist';

        $this->assertSame(
            $this->getExpectedProcessedUrl($packageName, $version, $reference, $type, $mirrorUrl),
            CollectorMirror::processUrl($mirrorUrl, $packageName, $version, $reference, $type)
        );
    }

    /**
     * TODO: understand the solution: why is it acceptable having %reference% in the processed url (result)
     */
    public function testWithPlaceholderReference()
    {
        $mirrorUrl = 'https://api.github.com/repos/%package%/%version%/%reference%/%type%';
        $packageName = 'mchekin/collector';
        $version = '1.0.1';
        $reference = '%reference%';
        $type = 'dist';

        $this->assertSame(
            $this->getExpectedProcessedUrl($packageName, $version, $reference, $type, $mirrorUrl),
            CollectorMirror::processUrl($mirrorUrl, $packageName, $version, $reference, $type)
        );
    }

    /**
     * TODO: understand the solution: when is reference is neither md5 hash nor %reference% placeholder
     */
    public function testWithReferenceNeitherMd5HashNorPlaceholder()
    {
        $mirrorUrl = 'https://api.github.com/repos/%package%/%version%/%reference%/%type%';
        $packageName = 'mchekin/collector';
        $version = '1.0.1';
        $reference = 'plain';
        $type = 'dist';

        $this->assertSame(
            $this->getExpectedProcessedUrl($packageName, $version, md5($reference), $type, $mirrorUrl),
            CollectorMirror::processUrl($mirrorUrl, $packageName, $version, $reference, $type)
        );
    }

    /**
     * TODO: understand the solution: why empty reference is ignored, possibly resulting in // inside the url
     */
    public function testWithEmptyReference()
    {
        $mirrorUrl = 'https://api.github.com/repos/%package%/%version%/%reference%/%type%';
        $packageName = 'mchekin/collector';
        $version = '1.0.1';
        $reference = '';
        $type = 'dist';

        $this->assertSame(
            $this->getExpectedProcessedUrl($packageName, $version, $reference, $type, $mirrorUrl),
            CollectorMirror::processUrl($mirrorUrl, $packageName, $version, $reference, $type)
        );
    }

    /**
     * TODO: understand the solution: why when version contains / it is being md5 hashed
     */
    public function testWithVersionContainingSlash()
    {
        $mirrorUrl = 'https://api.github.com/repos/%package%/%version%/%reference%/%type%';
        $packageName = 'mchekin/collector';
        $version = '/1.0.1/';
        $reference = 'b46bfffbe2a0b675cd77b9c58fb29702';
        $type = 'dist';

        $this->assertSame(
            $this->getExpectedProcessedUrl($packageName, md5($version), $reference, $type, $mirrorUrl),
            CollectorMirror::processUrl($mirrorUrl, $packageName, $version, $reference, $type)
        );
    }

    /**
     * @param string $packageName
     * @param string $version
     * @param string $reference
     * @param string $type
     * @param string $mirrorUrl
     *
     * @return string
     */
    private function getExpectedProcessedUrl($packageName, $version, $reference, $type, $mirrorUrl)
    {
        return str_replace(
            array('%package%', '%version%', '%reference%', '%type%'),
            array($packageName, $version, $reference, $type),
            $mirrorUrl
        );
    }
}
