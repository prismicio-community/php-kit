# How to Query the API

In order to retrieve the content from your repository, you will need to query the repository API. When you create your query you will specify exactly what it is you are looking for. You could query the repository for all the documents of certain type or retrieve the one specific document you need.

Let’s take a look at how to put together queries for whatever case you need.

> Check out the [Integrating with an existing PHP project](../01-getting-started/02-integrating-with-existing-project.md) page to learn how to get set up to query documents.

## The Basics

In order to do any query, you'll first need to get the API object.

**php (sdk v4 or later)**:

```php
<?php
use Prismic\Api;
use Prismic\LinkResolver;
use Prismic\Predicates;

$api = Api::get('https://your-repo-name.cdn.prismic.io/api/v2');
```

**php (sdk v3 or earlier)**:

```php
<?php
use Prismic\Api;
use Prismic\LinkResolver;
use Prismic\Predicates;

$api = Api::get('https://your-repo-name.cdn.prismic.io/api');
```

Once you have the API object, you'll be able to retrieve content from your Prismic repository.

Here's what a typical query looks like.

```php
<?php
$response = $api->query(
    Predicates::at('document.type', 'blog-post'),
    [ 'orderings' => '[my.blog-post.date desc]' ]
);
// $response contains the response object
```

This is the basic format of a query. In the query you have two parts, the Predicates and the Query Options.

### Predicates

In the above example we had the following predicate.

```
Predicates::at('document.type', 'blog-post')
```

The predicate(s) will define which documents are retrieved from the content repository. This particular example will retrieve all of the documents of the type "blog-post".

The first part, "document.type" is the **path**, or what the query will be looking for. You will find a list and description of the available paths on the [Query Predicates Reference](../02-query-the-api/02-query-predicate-reference.md) page. The second part the predicate in the above example is "blog-post" this is the **value** that the query is looking for.

You can combine more than one predicate together to refine your query. You just need to put all your predicates into a comma-separated array like the following example.

```php
[ Predicates::at('document.type', 'blog-post'),
  Predicates::at('document.tags', ['featured']) ]
```

This particular query will retrieve all the documents of the "blog-post" type that also have the tag "featured".

You will find a list and description of all the available predicates on the [Query Predicates Reference](../02-query-the-api/02-query-predicate-reference.md) page.

### Options

In the second part of the query, you can include the options needed for that query. In the above example we had the following option.

```
[ 'orderings' => '[my.blog-post.date desc]' ]
```

The above specifies how the returned list of documents will be ordered. You can include more than one option, by comma separating them as shown below.

```
[ 'pageSize' => 10, 'page' => 2 ]
```

You will find a list and description of all the available options on the [Query Options Reference](../02-query-the-api/04-query-options-reference.md) page.

Here’s another example of a more advanced query with multiple predicates and multiple options.

```php
<?php
$response = $api->query(
    [ Predicates::at('document.type', 'blog-post'),
      Predicates::at('document.tags', ['featured']) ],
    [ 'pageSize' => 25, 'page' => 1, 'orderings' => '[my.blog-post.date desc]' ]
);
// $response contains the response object
```

Whenever you query your content, you end up with the response object stored in the defined variable. In this case, the response object is stored in the `$response` variable.

## Pagination of API Results

When querying a Prismic repository, your results will be paginated. By default, there are 20 documents per page in the results. You can read more about how to manipulate the pagination in the [Pagination for Results](../02-query-the-api/18-pagination-for-results.md) page.
