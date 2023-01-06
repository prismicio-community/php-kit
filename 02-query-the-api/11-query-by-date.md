# Query by Date

This page shows multiple ways to query documents based on a date field.

Here we use a few predicates that can query based on Date or Timestamp fields. Feel free to explore the [Date & Time based Predicate Reference](../02-query-the-api/03-date-and-time-based-predicate-reference.md) page to learn more about this.

## Query by an exact date

The following is an example that shows how to query for all the documents of the type "article" with the release-date field ("date") equal to January 22, 2020. Note that this type of query will only work for the Date Field, not the Time Stamp field.

```
<?php
$response = $api->query(
    Predicates::at('my.article.release-date', '2020-01-22')
);
// $response contains the response object
```

## Query by month and year

Here is an example of a query for all documents of the type "blog-post" whose release-date is in the month of May in the year 2020. This might be useful for a blog archive.

```
<?php
$response = $api->query(
    [ Predicates::month('my.blog-post.release-date', 'May'),
      Predicates::year('my.blog-post.release-date', 2020) ]
);
// $response contains the response object
```

## Query by publication date

You can also query documents by their first or last publication dates.

Here is an example of a query for all documents of the type "blog-post" whose original publication date is in the month of May in the year 2020.

```
<?php
$response = $api->query(
    [ Predicates::at('document.type', 'blog-post'),
      Predicates::month('document.first_publication_date', 'May'),
      Predicates::year('document.first_publication_date', 2020) ]
);
// $response contains the response object
```
