# Query all your Documents

If you need to query all of the documents in your repository, you can just run a query with an empty string.

## Without query options

Here is an example that will query your repository for all documents. By default, the API will paginate the results, with 20 documents per page.

```
<?php
$response = $api->query('');
// $response contains the response object
```

## With query options

You can add options to this query. In the following example we allow 100 documents per page for the query response.

```
<?php
$response = $api->query('', [ 'pageSize' => 100 ]);
// $response contains the response object
```
