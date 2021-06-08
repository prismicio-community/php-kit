<?php
declare(strict_types=1);

namespace Prismic\Test;

use Prismic\Utils;

class UtilsTest extends TestCase
{
    public function testBuildUrl(): void
    {
        $url = 'https://test.prismic.io/api/v2?accessToken=1234';
        $parameters = [ 'accessToken' => '1234'];
        $this->assertEquals(Utils::buildUrl('https://test.prismic.io/api/v2', $parameters), $url);
    }

    public function testBuildUrl2(): void
    {
        $url = 'https://test.prismic.io/api/v2/documents/search?integrationFieldsRef=1234&page=2&pageSize=3&graphQuery=%7B%20blogpost%20%7B%20title%20%7D%20%7D';
        $parameters = [
            'page' => '2',
            'pageSize' => '3',
            'graphQuery' => '{ blogpost { title } }'
        ];
        $this->assertEquals(Utils::buildUrl('https://test.prismic.io/api/v2/documents/search?integrationFieldsRef=1234', $parameters), $url);
    }
}
