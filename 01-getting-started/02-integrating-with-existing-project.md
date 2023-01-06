# Integrating with an existing project

If you already have an existing PHP project that you want to integrate with Prismic, then you simply need to add the Prismic PHP development kit library as a dependency. Here we will show you all the steps needed to get Prismic integrated into your site.

## 1. Create a content repository

A content repository is where you can define, edit, and publish your website content.

[**Create a new repository**](https://prismic.io/dashboard/new-repository/)

Next you will need to model your content, create your custom types, and publish some documents to your content repository.

Now, let’s take a look at how to retrieve and integrate this new content with your project.

## 2. Add the PHP kit as a dependency

Now let’s add the Prismic PHP kit as a dependency to your project. Launch the terminal (command prompt or similar on Windows), and point it to your project location.

> Note that you will need to make sure to first have [Composer](https://getcomposer.org/) installed before running this command. Check out the [Composer Getting Started](https://getcomposer.org/doc/00-intro.md) page for installation instructions.

Run the following command.

```bash
composer require prismic/php-sdk
```

## 3. Include the dependency

To use the Prismic PHP library, you will need to include an instance of it. Simply add the following code.

```php
<?php
include_once __DIR__.'/vendor/autoload.php';

use Prismic\Api;
use Prismic\LinkResolver;
use Prismic\Predicates;
```

## 4. Get the API and query your content

Now we can query your Prismic repository and retrieve the content as shown below.

**php (sdk v4 or later)**:

```php
<?php
$api = Api::get("https://your-repo-name.cdn.prismic.io/api/v2");
$response = $api->query(Predicates::at('document.type', 'page'));
```

**php (sdk v3 or earlier)**:

```php
<?php
$api = Api::get("https://your-repo-name.cdn.prismic.io/api");
$response = $api->query(Predicates::at('document.type', 'page'));
```

If you are using a private repository, then you’ll need to [generate an access token](https://intercom.help/prismicio/api-application-and-token-management/generating-an-access-token) and then include it like this:

**php (sdk v4 or later)**:

```php
<?php
$url = "https://your-repo-name.cdn.prismic.io/api/v2";
$token = "MC5XUkgtxelvOGEBdVViSEla.E--_xe-_qe-vUXvv7...1r77-9FgXv";
$api = Api::get($url, $token);
$response = $api->query(Predicates::at('document.type', 'page'));
```

**php (sdk v3 or earlier)**:

```php
<?php
$url = "https://your-repo-name.cdn.prismic.io/api";
$token = "MC5XUkgtxelvOGEBdVViSEla.E--_xe-_qe-vUXvv7...1r77-9FgXv";
$api = Api::get($url, $token);
$response = $api->query(Predicates::at('document.type', 'page'));
```

To learn more about querying the API, check out the [How to Query the API](../02-query-the-api/01-how-to-query-the-api.md) page.

### Pagination of API Results

When querying a Prismic repository, your results will be paginated. By default, there are 20 documents per page in the results. You can read more about how to manipulate the pagination in the [Pagination for Results](../02-query-the-api/18-pagination-for-results.md) page.

## 5. Add the content to your templates

Once you have retrieved your content, you can include the content in your template using the helper functions in the PHP development kit. Here is a simple example.

**php (sdk v4 or later)**:

```php
<?php
use Prismic\Dom\RichText;

$document = $response->results[0];
?>

<section>
  <h1><?= RichText::asText($document->data->title) ?></h1>
  <img src="<?= $document->data->image->url ?>" alt="<?= $document->data->image->alt ?>" />
  <div>
    <?= RichText::asHtml($document->data->description) ?>
  </div>
</section>
```

**php (sdk v3 or earlier)**:

```php
<?php
use Prismic\Dom\RichText;

$document = $response->getResults()[0];
?>

<section>
  <h1><?= $document->getStructuredText('page.title')->asText() ?></h1>
  <img
      src="<?= $document->getImage('page.image')->getUrl() ?>"
      alt="<?= $document->getImage('page.image')->getAlt() ?>"
  />
  <div>
    <?= $document->getStructuredText('page.description')->asHtml() ?>
  </div>
</section>
```

You can read more about templating your content in the Templating section of the documentation.

## 6. Take advantage of Previews and the Prismic Toolbar

In order to take advantage of all that Prismic has to offer, check out the [Previews and the Prismic Toolbar](../04-beyond-the-api/02-previews-and-the-prismic-toolbar.md) page to learn how to add these great features to your project!

## And your Prismic journey begins!

You should now have all the tools to really get going with your project. We invite you to further explore the documentation to learn how to define your Custom Types, query the API, and add your content to your templates.
