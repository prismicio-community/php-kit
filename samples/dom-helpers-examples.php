<?php

include_once __DIR__.'/../vendor/autoload.php';

use Prismic\Api;
use Prismic\LinkResolver;
use Prismic\Dom\Link;
use Prismic\Dom\RichText;
use Prismic\Dom\Date;

$api = Api::get('https://your-repository-name.prismic.io/api/v2');
$document = $api->getSingle('example');

class ExampleLinkResolver extends LinkResolver
{
    public function resolve($link) : string
    {
        if ($link->isBroken) {
            return '/404';
        }
        if ($link->type === 'about') {
            return '/about';
        }
        return '/';
    }
}

$linkResolver = new ExampleLinkResolver();

?>

<!-- Link asUrl -->
<a href="<?php echo Link::asUrl($document->data->example_link, $linkResolver); ?>">example link</a>

<!-- RichText asText -->
<button>
    <?php echo RichText::asText($document->data->example_button_rich_text); ?>
</button>

<!-- RichText asHtml -->
<article>
    <?php echo RichText::asHtml($document->data->example_article_rich_text, $linkResolver); ?>
</article>

<!-- Date asDate -->
<?php $date = Date::asDate($document->data->example_date); ?>
<time>Date: <?php echo $date->format('Y-m-d H:i:s'); ?></time>
