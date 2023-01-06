# Query Helper functions

We've included helper functions to make creating certain queries quicker and easier when using the Prismic PHP development kit. This page provides the description and examples for each of the helper functions.

## getByUID

```css
getByUID( custom-type, uid, options )
```

The `getByUID` function is used to query the specified custom type by a certain UID value. This requires that the custom type of the document contains the UID field.

This function will only ever retrieve one document as there can only be one instance of a given UID value for each custom type & language.

| Property                                             | Description                                                           |
| ---------------------------------------------------- | --------------------------------------------------------------------- |
| <strong>custom-type</strong><br/><code>string</code> | <p>(required) The API-ID of the custom type you are searching for</p> |
| <strong>uid</strong><br/><code>string</code>         | <p>(required) The UID of the document you want to retrieve</p>        |
| <strong>options</strong><br/><code>array</code>      | <p>(optional) An array with option parameters and values</p>          |

Here is an example that queries a document of the type "page" by its uid "about-us".

```
<?php
$document = $api->getByUID('page', 'about-us');
// $document contains the document content
```

Here is an example with options that specifies a particular language to query.

```
<?php
$options = [ 'lang' => 'en-us' ];
$document = $api->getByUID('page', 'about-us', $options);
// $document contains the document content
```

## getByID

```css
getByID( id, options )
```

The `getByID` function is used to query a certain document by its document id. Every document is automatically assigned a unique id when it is created. The id will look something like this: ‘WAjgAygABN3B0a-a’.

This function will only ever retrieve one document as each document has a unique id value.

| Property                                        | Description                                                   |
| ----------------------------------------------- | ------------------------------------------------------------- |
| <strong>id</strong><br/><code>string</code>     | <p>(required) The id of the document you want to retrieve</p> |
| <strong>options</strong><br/><code>array</code> | <p>(optional) An array with option parameters and values</p>  |

Here is an example that queries a document by its id “WAjgAygABN3B0a-a”.

```
<?php
$document = $api->getByID('WAjgAygABN3B0a-a');
// $document contains the document content
```

Here is an example that adds options.

```
<?php
$options = [ 'fetch' => 'product.title' ];
$document = $api->getByID('WAjgAygABN3B0a-a', $options);
// $document contains the document content
```

## getByIDs

```css
getByIDs( ids, options )
```

The `getByIDs` function is used to query multiple documents by their ids.

This will return the documents in the same order specified in the array, unless options are added to sort them otherwise.

| Property                                        | Description                                                                              |
| ----------------------------------------------- | ---------------------------------------------------------------------------------------- |
| <strong>ids</strong><br/><code>array</code>     | <p>(required) An array of strings with the ids of the documents you want to retrieve</p> |
| <strong>options</strong><br/><code>array</code> | <p>(optional) An array with option parameters and values</p>                             |

Here is an example that queries multiple documents by their ids.

```
<?php
$ids = ['WAjgAygAAN3B0a-a', 'WC7GECUAAHBHQd-Y', 'WEE_gikAAC2feA-z'];
$response = $api->getByIDs($ids);
// $response contains the response object
```

Here is an example with options that sort the documents by their titles.

```
<?php
$ids = ['WAjgAygAAN3B0a-a', 'WC7GECUAAHBHQd-Y', 'WEE_gikAAC2feA-z'];
$options = [ 'orderings' => '[my.page.title]' ];
$response = $api->getByIDs($ids);
// $response contains the response object
```

## getSingle

```css
getSingle( custom-type, options )
```

The `getSingle` function is used to query the document of a Single custom type. Single custom types only allow for the creation of one document of that type.

This will only ever retrieve one document.

| Property                                             | Description                                                                 |
| ---------------------------------------------------- | --------------------------------------------------------------------------- |
| <strong>custom-type</strong><br/><code>string</code> | <p>(required) The API ID of the single custom type you want to retrieve</p> |
| <strong>options</strong><br/><code>array</code>      | <p>(optional) An array with option parameters and values</p>                |

Here is an example that retrieves the document of the Single type "navigation".

```
<?php
$document = $api->getSingle('navigation');
// $document contains the document content
```
