<?php
declare(strict_types=1);

namespace Prismic\Test;

use RuntimeException;
use function file_exists;
use function file_get_contents;
use function sprintf;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    public function getJsonFixture(string $fileName) : string
    {
        $file = sprintf('%s/../fixtures/%s', __DIR__, $fileName);
        if (! file_exists($file)) {
            throw new RuntimeException(sprintf(
                'The JSON Fixture %s does not exist on disk',
                $fileName
            ));
        }

        return file_get_contents($file);
    }
}
