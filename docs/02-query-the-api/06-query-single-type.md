# Query a Single Type Document

Here we discuss how to retrieve the content for a Single type document.

## getSingle helper function

In this example we use the `getSingle` helper function to query the single instance of the custom type "navigation".

```
<?php
$document = $api->getSingle('navigation');
// $document contains the document content
```

## Without the helper

You can perform the same query without using the helper function. Here we again query the single document of the type "navigation".

**php (sdk v4 or later)**:

```
<?php
$response = $api->query(Predicates::at('document.type', 'navigation'));
$document = $response->results[0];
// $document contains the document content
```

**php (sdk v3 or earlier)**:

```
<?php
$response = $api->query(Predicates::at('document.type', 'navigation'));
$document = $response->getResults()[0];
// $document contains the document content
```

> **Querying by Language**
>
> Note that if you are trying to query a document that isn't in the master language of your repository this way, you will need to specify the language code or wildcard language value. You can read how to do this on the [Query by Language page](../02-query-the-api/19-query-by-language.md).
>
> If you are using the query helper function above, you do not need to do this.
