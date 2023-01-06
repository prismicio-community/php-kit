# Query Documents by ID or UID

You can retrieve either multiple documents or a single one by their document ID or UID.

> **Querying by Language**
>
> Note that if you are trying to query a document that isn't in the master language of your repository, you will need to specify the language code or wildcard language value. You can read how to do this on the [Query by Language page](../02-query-the-api/19-query-by-language.md).
>
> If you are using one of the query helper functions below, you do not need to do this. The only exception is the `getByUID` helper which is explained below.

## Query a document by ID

We've created a helper function that makes it easy to query by ID, but it is also possible to do this without the helper function.

### getByID helper function

Here is an example that shows how to query a document by its ID using the `getByID` helper function.

```
<?php
$document = $api->getByID('WAjgAygABN3B0a-a');
// $document contains the document content
```

### Without the helper function

Here we perform the same query for a document by its ID without using the helper function.

**php (sdk v4 or later)**:

```
<?php
$response = $api->query(
    Predicates::at('document.id', 'WAjgAygAAN3B0a-a'),
    [ 'lang' => '*' ]
);
$document = $response->results[0];
// $document contains the document content
```

**php (sdk v3 or earlier)**:

```
<?php
$response = $api->query(
    Predicates::at('document.id', 'WAjgAygAAN3B0a-a'),
    [ 'lang' => '*' ]
);
$document = $response->getResults()[0];
// $document contains the document content
```

## Query multiple documents by IDs

We've created a helper function that makes it easy to query multiple documents by IDs.

### getByIDs helper function

Here is an example of querying multiple documents by their ids using the `getByIDs` helper function.

```
<?php
$ids = ['WAjgAygAAN3B0a-a', 'WC7GECUAAHBHQd-Y', 'WEE_gikAAC2feA-z'];
$response = $api->getByIDs($ids);
// $response contains the response object
```

### Without the helper function

Here is an example of how to perform the same query as above, but this time without using the helper function.

```
<?php
$ids = ['WAjgAygAAN3B0a-a', 'WC7GECUAAHBHQd-Y', 'WEE_gikAAC2feA-z'];
$response = $api->query(
    Predicates::in('document.id', $ids),
    [ 'lang' => '*' ]
);
// $response contains the response object
```

## Query a document by its UID

If you have added the UID field to a custom type, you can query a document by its UID.

### getByUID helper function

Here is an example showing how to query a document of the type "page" by its UID "about-us" using the `getByUID` helper function.

```
<?php
$document = $api->getByUID('page', 'about-us');
// $document contains the document content
```

### Query by language

It's possible that you may have documents in different languages with the same UID value. In that case, you will need to specify the language code in order to retrieve the correct document.

```
<?php
$options = [ 'lang' => 'fr-fr' ];
$document = $api->getByUID('page', 'about-us', $options);
// $document contains the fr-fr document content
```

> Note that if you don't specify the language or if you specify the wildcard value `'*'`, the oldest document with this UID value will be returned.

### Query all language versions by UID

The `getByUID` function will always return a single document. If you need to query all the language versions that share the same UID, then you can use the following method (without the helper function) to retrieve them all at the same time.

### Without the helper function

Here is an example of the same query without using the helper function. It will query the document(s) of the type "page" that contains the UID "about us".

```
<?php
$response = $api->query(
    Predicates::at('my.page.uid', 'about-us'),
    [ 'lang' => '*' ]
);
// $response contains the response object
```
