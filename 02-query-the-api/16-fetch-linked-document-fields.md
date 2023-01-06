# Fetch Linked Document Fields

Here we explore an example that fetches a specific content field from a linked document.

## The fetchLinks option

The `fetchLinks` option allows you to retrieve a specific content field from a linked document and add it to the document response object.

Note that this will only retrieve content of the following field types:

- Color
- Content Relationship
- Date
- Image
- Key Text
- Number
- Rich Text (but only returns the first element)
- Select
- Timestamp
- Title

It is **not** possible to retrieve the following content field types:

- Embed
- GeoPoint
- Link
- Link to Media
- Rich Text (anything other than the first element)
- Any field in a Group or Slice

The value you enter for the fetchLinks option needs to take the following format:

```
[ 'fetchLinks' => '{custom-type}.{field}' ]
```

| Property                            | Description                                                                  |
| ----------------------------------- | ---------------------------------------------------------------------------- |
| <strong>{custom-type}</strong><br/> | <p>The custom type API-ID of the linked document</p>                         |
| <strong>{field}</strong><br/>       | <p>The API-ID of the field you wish to retrieve from the linked document</p> |

## A simple example

The following is an example that uses the *fetchLinks* option. We are querying for a "recipe" document with the uid "chocolate-chip-cookies". The "recipe" Custom Type has a Content Relationship (AKA Document Link) field with the API ID `author_link` which links to an "author" document.

Inside the "author" document you have a Key Text field with the API ID `name`.

The following will show you how to retrieve the author name field when querying the recipe.

**php (sdk v4 or later)**:

```
$response = $api->query(
    Predicates::at('my.recipe.uid', 'chocolate-chip-cookies'),
    [ 'fetchLinks' => 'author.name' ]
);

$document = $response->results[0];

$author = $document->data->author_link;
// $author now works like a top-level document

$authorName = $author->data->name;
// $authorName contains the text from the "name" field
```

**php (sdk v3 or earlier)**:

```
$response = $api->query(
    Predicates::at('my.recipe.uid', 'chocolate-chip-cookies'),
    [ 'fetchLinks' => 'author.name' ]
);

$document = $response->getResults()[0];

$author = $document->getLink('recipe.author-link');
// $author now works like a top-level document

$authorName = $author->getText('author.name');
// $authorName contains the text from the "name" field
```

## Fetch multiple fields

In order to fetch more than one field from the linked document, you just need to provide an array of fields. Here is an example that fetches the fields `name` and `picture` from the `author` custom type.

```
[ 'fetchLinks' => 'author.name, author.picture' ]
```
