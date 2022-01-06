# Query by Content Relationship field

To query by a particular Content Relationship / Document link value, you must use the ID of the document you are looking for.

> **You must use the document ID**
>
> Note that you must use the document ID to make this query. It does not work if you try to query using a UID value.

## By a Content Relationship field

The following example queries all the "blog_post" custom type documents with the "category_link" field (a Content Relationship) equal to the category with a document ID of "WNje3SUAAEGBu8bc".

```
<?php
$response = $api->query(
  [ Predicates::at('document.type', 'blog_post'),
    Predicates::at('my.blog_post.category_link', 'WNje3SUAAEGBu8bc') ]
);
// $response contains the response object
```

## By a Content Relationship field in a Group

If your Content Relationship field is inside a group, you just need to specify the Group, then the Content Relationship field.

Here is an example that queries all the "blog_post" custom type documents with the "category_link" field (a Content Relationship) equal to the category with a document ID of "WNje3SUAAEGBu8bc". In this case, the Content Relationship field is inside a Group field with the API ID of "categories".

```
<?php
$response = $api->query(
  [ Predicates::at('document.type', 'blog_post'),
    Predicates::at('my.blog_post.categories.category_link', 'WNje3SUAAEGBu8bc') ]
);
// $response contains the response object
```
