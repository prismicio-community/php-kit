# Query by a Field

There are a number of fields that you can query by. Here we give a examples that show how to query by the Number, Key Text, Document Link, and Select fields.

## Number field

The following example shows how to query all the products with the "price" field (Number) less than 100.

```
<?php
$response = $api->query(
    Predicates::lt('my.product.price', 100)
);
// $response contains the response object
```

You can find more query predicates for Number fields on the [Query Predicate Reference](../02-query-the-api/02-query-predicate-reference.md) page.

## Key Text field

The following example shows how to query all the "employee" custom type documents with the "job-title" field (Key Text) equal to either "Developer" or "Designer".

```
<?php
$response = $api->query(
    Predicates::any('my.employee.job-title', ['Developer', 'Designer'])
);
// $response contains the response object
```

## Document Link / Content Relationship Field

To query by a particular document link value, you must use the ID of the document you are looking for.

The following example queries all the "blog_post" custom type documents with the "category_link" field (a Document Link) equal to the category with a document ID of "WNje3SUAAEGBu8bc".

```
<?php
$response = $api->query(
  [ Predicates::at('document.type', 'blog_post'),
    Predicates::at('my.blog_post.category_link', 'WNje3SUAAEGBu8bc') ]
);
// $response contains the response object
```

## Select field

The following demonstrates how to query all the "book" custom type documents with the "author" field (Select) equal to "John Doe". It also sorts the results by their titles.

```
<?php
$response = $api->query(
    Predicates::at('my.book.author', 'John Doe'),
    [ 'orderings' => '[my.book.title]' ]
);
// $response contains the response object
```

## Select field within a Group

It is also possible to query by a field inside of a group. The following queries all the "book" custom type documents with the "name" field (Select) equal to "John Doe". In this case the "name" field is in a group field named "author-info".

```
<?php
$response = $api->query(
    Predicates::at('my.book.author-info.name', 'John Doe'),
    [ 'orderings' => '[my.book.title]' ]
);
// $response contains the response object
```

### A Boolean Field

The following example queries all the "post" custom type documents with the "switch"** Boolean **field equal to _true_ by using the\*\* \*\*at predicate. It also sorts the results by their title.

```
<?php
$response = $api->query(
    Predicates::at('my.post.switch', true),
    [ 'orderings' => '[my.post.title]' ]
);
// $response contains the response object
```
