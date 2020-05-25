<?php
declare(strict_types=1);

namespace Prismic\Test\Document\Fragment;

use PHPUnit\Framework\TestCase;
use Prismic\Document\Fragment\Boolean;
use function json_decode;

class BooleanTest extends TestCase
{
    /** @return mixed[] */
    public function possibleFragmentValues() : iterable
    {
        return [
            'V1 Style, True' => ['{"type": "Boolean","value": true}', true],
            'V1 Style, False' => ['{"type": "Boolean","value": false}', false],
            'V2 Style, True' => ['true', true],
            'V2 Style, False' => ['false', false],
        ];
    }

    /** @dataProvider possibleFragmentValues */
    public function testFactoryWithV1FragmentPayload(string $json, bool $expect) : void
    {
        $data = json_decode($json, false);
        $fragment = Boolean::factory($data);
        $this->assertInstanceOf(Boolean::class, $fragment);
        $this->assertSame($expect, $fragment->asBoolean());
    }
}
