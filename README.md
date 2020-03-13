# Unofficial PHP development kit for prismic.io

![Build Status](https://github.com/netglue/prismic-php-kit/workflows/PHPUnit%20Test%20Suite/badge.svg)
[![codecov](https://codecov.io/gh/netglue/prismic-php-kit/branch/master/graph/badge.svg)](https://codecov.io/gh/netglue/prismic-php-kit)


## Introduction

This fork of the [official Prismic.io library](https://github.com/prismicio/php-kit) differs in a few ways and is more similar to the 3.x version of the original kit than the 4.x version that's currently in beta.

## Installation

[Composer](https://getcomposer.org) is the only 'supported' installation method:

```bash
$ composer require netglue/prismic-php-kit
```

## Usage

You'll need a content repository all setup and ready to go at [Prismic.io](https://prismic.io), if you don’t know anything about prismic.io yet, take a [look at the website](https://prismic.io) and the [documentation about content modelling](https://user-guides.prismic.io/content-modeling-and-custom-types) and [sign up for an account](https://prismic.io/signup).

Prismic content repositories expose a url to direct your API queries to in the form of `https://<REPO-NAME>.prismic.io/api`. Your repository may or may not require an access token depending on how you have set it up. There are currently 2 versions of the content API which are accessible at different URLs. V1 of the API is available at `/api` or `/api/v1` whereas V2 of the API is available at `/api/v2`. This kit attempts to support both versions of the API transparently. The V1 api appears to be the same as V2 with the exception that the responses in V1 are more verbose. There's no date for deprecation of the v1 api and I’m not aware of any functional differences between the two versions.

Once you have the details for your repository, you can create an API client in the following way _(Assuming you have setup [composer autoloading](https://getcomposer.org/doc/01-basic-usage.md#autoloading))_:

```php
use Prismic\Api;
$accessToken = 'AccessTokenString Or null';
$api = Api::get('https://<REPO-NAME>.prismic.io/api/v1', $accessToken); 
```

## Results from the API

In most cases, you'll probably be retrieving either a single document or a result set from the API, for example, using the api client to retrieve a single document by it's identifier goes something like this: `$api->getById('SomeIdentifier');`, whereas a query to get all the documents of a specific type may look like…
```php
<?php
use Prismic\Predicates;
$query = [
    Predicates::at('document.type', 'blog-post'),
];
$response = $api->query($query);
```

For a single document, you'll be getting a `\Prismic\DocumentInterface` instance. Result sets will be an instance of `\Prismic\Response`. Calling `$response->getResults()` will yield an array of Document instances.

## Resolving Application Links

In most cases, before you can render content as HTML, you will need to provide a 'Link Resolver' to the API client so that it knows how to generate URLs for your content. Your link resolver needs to implement the `\Prismic\LinkResolver` interface. There's an abstract class to inherit from in `\Prismic\LinkResolverAbstract` so that you only have to implement a single method and you can find an concrete example Link Resolver in `./samples/LinkResolverExample.php`. Once you have a link resolver instance, provide it to the API with
```php
<?php
/** @var \Prismic\LinkResolver $linkResolver **/
$linkResolver = $myDiContainer->get('My-Link-Resolver');
$api->setLinkResolver($linkResolver);
```
## Rendering Content

Generally speaking, once you've figured out how to query the api to get some data, you'll probably want to render this as HTML or plain text. Calling `$document->asHtml()` will give you a bucket of html but it likely won't be structured in the way you want, therefore you'll be using some sort of templating library.

Each document retrieved from the API will contain the fragments you have defined at Prismic, these fragments will be contained in a `\Prismic\Document\Fragment\FragmentCollection` and within that collection, there might be any number of single or composite `\Prismic\Document\Fragment\FragmentInterface` instances. Because _(Apart from the UID, if present)_ no content fields are required by the CMS, you will generally want to check for non nulls in your template before attempting to render anything:

```php
<?php
/** @var \Prismic\Document\Fragment\RichText|null $titleFragment **/
$titleFragment = $document->get('title');
if ($titleFragment) {
    printf('<div class="page-title-block">%s</div>', $titleFragment->asHtml());
}
``` 

## Hydrating Results into Concrete Objects

By default, the kit will create a `\Prismic\Document` instance for each document retrieved from the API. You can change this behaviour by mapping prismic document types to FQCN's, for example:

```php
<?php
$hydrator = $api->getHydrator();
$hydrator->mapType('blog-post', \My\BlogPostDocument::class);
$response = $api->query([Predicates::at('document.type', 'blog-post')]);
/** @var \My\BlogPostDocument[] $posts */
$posts = $response->getResults();
```

Any class you define to represent a document must implement `\Primic\DocumentInterface`, but normally, you should be able to just extend `\Prismic\Document`

## Caching

Caching differs in this fork in that it consumes any `\Psr\Cache` implementation so you can swap out caching for any adapter from any Psr compatible library that floats your boat. By default the Symfony implementation is used with an APC adapter if APC is installed, otherwise, it's an in-memory array cache.

Your custom cache needs to be provided to the named constructor of the Api instance:

```php
<?php
/** @var \Psr\Cache\CacheItemPoolInterface $cache **/
$cache = $myDIContainer->get('SomeRedisCache');
$httpClient = null;
$api = Api::get($repoApiUrl, $accessToken, $httpClient, $cache);
```

## Webhooks

Webhooks are sent to your app/website when individual documents or releases are published. Just so you know, these are the other conditions for when a webhook might be posted.

| Event Type | Webhook | Partial Payload |
|:---|:---:|:---|
| New Document Type Created | No ||
| Document Type Disabled | No ||
| Document Type Deleted | No ||
| Bookmark Created | Yes | `{ "bookmarks" : { "addition" : [ { "id" : "example", "docId" : "" } ] } }` |
| Bookmark Target Set | Yes | `{ "bookmarks" : { "update" : [ { "id" : "example", "docId" : "SomeID" } ] } }` |
| Bookmark Target Changed | Yes | `{ "bookmarks" : { "update" : [ { "id" : "example", "docId" : "DifferentID" } ] } }` |
| Bookmark Deleted | Yes | `{ "bookmarks" : { "deletion" : [ { "id" : "example", "docId" : "LastKnownDocumentId" } ] } }` |
| Collection Created | Yes | `{ "collection" : { "addition" : [ { "id" : "example", "label" : "Example" } ] } }` |
| Collection Filters Changed | No ||
| Collection Name Changed | Yes | `{ "collection" : { "update" : [ { "id" : "example", "label" : "Example Changed" } ] } }` |
| Collection Deleted | Yes | `{ "collection" : { "deletion" : [ { "id" : "example", "label" : "Example" } ] } }` |
| Create new release | Yes | `{"releases" : { "addition" : [ { "id" : "RELEASE-ID", "ref" : "RELEASE-REF", "label" : "Example" } ] }}` |
| Docs Added to Release | Yes | `{"releases" : { "update" : [ { "id" : "RELEASE-ID", "ref" : "RELEASE-REF", "label" : "Example" } ] }}` |
| Experiment Created | Yes | `{ "experiments" : { "addition" : [ { "id" : "Some ID", "name" : "Testing Experiment", "variations" : [ { "id" : "Some ID", "ref" : "Some Ref", "label" : "Base" } ] } ] } }` |
| Documents added to Experiment Before Starting It | Yes | `{ "experiments" : { "update" : [ { "id" : "Some ID", "name" : "Testing Experiment", "variations" : [ { "id" : "Some ID", "ref" : "Some Ref", "label" : "Base" }, { "id" : "Some Id", "ref" : "Some Ref", "label" : "Variation 1" } ] } ] } }` |
| Start Experiment | No _(FFS)_ | |
| Experiment Stopped | Yes | `{ experiments" : { "deletion" : [ { "id" : "Some ID", "name" : "Experiment Name", "variations" : [ { "id" : "Some ID", "ref" : "Some Ref", "label" : "Base" }, { "id" : "Some ID", "ref" : "Some Ref", "label" : "First Variation" } ] } ] } }` 
| Experiment Deleted | No | |

The [docs here](https://user-guides.prismic.io/webhooks/webhooks) say that webhooks are dispatched when tags are added, changed and removed. This is not accurate. At the time of writing, you cannot edit or delete tags. Any new tags that are created as part of working with documents will be posted in the next payload but only if something else occurs that triggers a webhook.

The only way that you can determine whether a release or a document has been published is the presence of the `masterRef` property in the body of the webhook payload. 

## Built-In Document Explorer

There's a naive document explorer ready to use if you have an existing repo you'd like to fire up:

```bash
$ export PRISMIC_API="https://somerepo.prismic.io/api"
$ export PRISMIC_TOKEN="Some-Access-Token"
$ composer serve
> php -S 0.0.0.0:8080 -t samples samples/document-explorer.php
```

Visit [http://localhost:8080](http://localhost:8080) in your browser and you should see the content in the repo.

## Testing

Tests are written with PHPUnit. To run the tests locally:

```bash
$ composer install
$ vendor/bin/phpunit # or composer check
```

## Contributions

…and pull requests are most welcome, but please run `composer check` to make sure that CS is maintained and include additional unit tests if appropriate.

## License

This software is licensed under the Apache 2 license, quoted below.

Copyright 2018 Prismic.io (https://prismic.io).

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this project except in compliance with the License. You may obtain a copy of the License at http://www.apache.org/licenses/LICENSE-2.0.

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
