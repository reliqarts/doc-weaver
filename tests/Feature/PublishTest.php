<?php

namespace ReliQArts\Docweaver\Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use ReliQArts\Docweaver\Tests\TestCase as TestCase;

/**
 * @internal
 * @coversNothing
 */
final class PublishTest extends TestCase
{
    /**
     * Test the ability to publish documentation.
     *
     * @covers \ReliQArts\Docweaver\Console\Commands\Publish
     * @covers \ReliQArts\Docweaver\Services\Documentation\Publisher::publish
     */
    public function testPublishDoc()
    {
        $docIndex = $this->configProvider->getRoutePrefix();
        $productName = 'Test Product';

        // publish Docweaver docs
        Artisan::call('docweaver:publish', [
            'product' => $productName,
            'source' => 'https://github.com/reliqarts/docweaver-docs.git',
            '--y' => true,
        ]);

        // check existence
        $this->visit($docIndex)
            ->see($productName)
            ->see('master');
    }
}
