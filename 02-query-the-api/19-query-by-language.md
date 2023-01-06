# Query by language

When querying the API, you can now query by language.

## Query a specific language

You simply need to add the `lang` query option and set it to the language code you are querying (example, "en-us" for American English).

> If you don't specify a `lang` the query will automatically query the documents in your master language.

Here is an example of how to query for all the documents of the type "blog-post" in French (language code "fr-fr").

```
<?php
$response = $api->query(
    Predicates::at('document.type', 'blog-post'),
    [ 'lang' => 'fr-fr' ]
);
// $response contains the response object
```

## Query for all languages

If you want to query all the document in all languages you can add the wildcard, `'*'`, as your lang option.

This example shows this by querying all documents of the type "blog-post" in all languages.

```
<?php
$response = $api->query(
    Predicates::at('document.type', 'blog-post'),
    [ 'lang' => '*' ]
);
// $response contains the response object
```

## Query by UID & language

To learn more about how to query your documents by UID and language, check out the [Query by ID and UID](../02-query-the-api/07-query-by-id-and-uid.md) page.
