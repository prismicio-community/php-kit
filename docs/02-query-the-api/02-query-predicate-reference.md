# Query Predicate reference

Here you will find everything you need to learn how the query predicates work. You'll find the descriptions first for all the predicate paths and then for all the available predicates.

## Predicate Path Reference

Here are the different paths available when creating your query predicates.

### document.type

The `document.type` path is the custom type of the document. You would search this path with the API ID of the custom type you are looking for.

In the example below, the API ID of the custom type is "product". This would retrieve all the documents of that custom type.

```
Predicates::at('document.type', 'product')
```

### document.id

The `document.id` path is the id of the document. This is a unique id automatically assigned to every document when it is created. It looks something like this: WGvVEDIAAAcuB3lJ

The following example retrieves the one document with that id.

```
Predicates::at('document.id', 'WGvVEDIAAAcuB3lJ')
```

### document.tags

The `document.tags` path looks at the tags associated with the document. You can add tags to a document from the document edit screen.

The following predicate would retrieve all of the documents with the tag "featured".

```
Predicates::at('document.tags', ['featured'])
```

> Note that even when querying for one single tag, you need to put the string for the tag inside of an array.

### document.first_publication_date

The `document.first_publication_date` path is the date and time that the document was first published. The value for this field path is filled the first time the document is published and it never changes.

The following predicate would retrieve all the documents that were first published in the year 2020.

```
Predicates::year('document.first_publication_date', 2020)
```

### document.last_publication_date

The `document.last_publication_date` path is the date and time that the document was last published. The value for this field path is updated every time the document is edited and re-published.

The following predicate would retrieve all the documents that were published or updated in the year 2020.

```
Predicates::year('document.last_publication_date', 2020)
```

### document

The `document` path is used only with the `fulltext` predicate described below. This allows you to search an entire document for a defined word, such as "football".

```
Predicates::fulltext('document', 'football')
```

### my.{custom-type}.{field}

This path allows you to query a specific field in a custom type. For `{custom-type}` you need to use the API ID of the custom type you want to query. For `{field}` you need to use the API ID of the specific field in the custom type that you need.

The example below searches the "price" field in the "product" custom type.

```
Predicates::at('my.product.price', 100)
```

## Predicate Reference

To use the predicates as shown below, you must make sure to include the Predicates class.

```
use Prismic/Predicates;
```

Here are descriptions of all the available predicates.

### at

The `at` predicate checks that the path matches the described value exactly. It takes a single value for a field or an array (only for tags).

```
Predicates::at( $path, $value )
```

| Property                                                                   | Description                                                                                    |
| -------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------- |
| <strong>$path</strong><br/><code>accepted paths</code>                     | <p>document.type</p><p>document.tags</p><p>document.id</p><p>my.{custom-type}.{field}</p>      |
| <strong>$value</strong><br/><code>accepted values</code>                   | <p>single value (for all but document.tags)</p><p>array of values (only for document.tags)</p> |
| <strong>my.{custom-type}.{field}</strong><br/><code>accepted fields</code> | <p>UID</p><p>Key Text</p><p>Select</p><p>Number</p><p>Date</p><p>Boolean</p>                   |

Examples:

```
Predicates::at('document.type', 'product')
Predicates::at('document.tags', ['Macaron', 'Cupcake'])
Predicates::at('my.product.price', 50)
```

### not

The `not` predicate checks that the path doesn't match the provided value exactly. It takes a single value as the argument.

```
Predicates::not( $path, $value )
```

| Property                                                                   | Description                                                                               |
| -------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------- |
| <strong>$path</strong><br/><code>accepted paths</code>                     | <p>document.type</p><p>document.tags</p><p>document.id</p><p>my.{custom-type}.{field}</p> |
| <strong>$value</strong><br/><code>accepted value</code>                    | <p>Single value</p>                                                                       |
| <strong>my.{custom-type}.{field}</strong><br/><code>accepted fields</code> | <p>UID</p><p>Key Text</p><p>Select</p><p>Number</p><p>Date</p><p>Boolean</p>              |

Example:

```
Predicates::not('document.type', 'product')
```

### any

The `any` predicate takes an array of values. It works exactly the same way as the `at` operator, but checks whether the fragment matches **any** of the values in the array.

When using this predicate with the ID or UID field, it will not necessarily return the documents in the same order as the passed array.

```
Predicates::any( $path, $values )
```

| Property                                                                   | Description                                                                               |
| -------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------- |
| <strong>$path</strong><br/><code>accepted paths</code>                     | <p>document.type</p><p>document.tags</p><p>document.id</p><p>my.{custom-type}.{field}</p> |
| <strong>$values</strong><br/><code>array</code>                            | <p>Array of values</p>                                                                    |
| <strong>my.{custom-type}.{field}</strong><br/><code>accepted fields</code> | <p>UID</p><p>Key Text</p><p>Select</p><p>Number</p><p>Date</p>                            |

Example:

```
Predicates::any('document.type', ['product', 'blog-post'])
```

### in

The `in` predicate is used specifically to retrieve an array of documents by their IDs or UIDs. This predicate is much more efficient at this than the `any` predicate.

This returns the documents in the same order as the passed array.

```
Predicates::in( $path, $values )
```

| Property                                               | Description                                   |
| ------------------------------------------------------ | --------------------------------------------- |
| <strong>$path</strong><br/><code>accepted paths</code> | <p>document.id</p><p>my.{custom-type}.uid</p> |
| <strong>$values</strong><br/><code>array</code>        | <p>Array of IDs or UIDs</p>                   |

Examples:

```
Predicates::in('document.id', ['V9rIvCQAAB0ACq6y', 'V9ZtvCcAALuRUzmO']) // IDs
Predicates::in('my.page.uid', ['myuid1', 'myuid2']) // UIDs
```

### fulltext

The `fulltext` predicate provides two capabilities

1. Checking if a certain string is anywhere inside a document (this is what you should use to make your project's search engine feature)
1. Checking if the string is contained inside a specific custom type’s Rich Text or Key Text fragment.

You can search a document or a text fragment for either just one term or for multiple terms. To search for more than one term, put all the terms into a string with spaces between them as shown in the examples below.

The full document search and specific field search works on the following fields:

- Rich Text
- Title
- Key Text
- UID
- Select

The fulltext search is not case sensitive.

```
Predicates::fulltext( $path, $value )
```

| Property                                               | Description                                    |
| ------------------------------------------------------ | ---------------------------------------------- |
| <strong>$path</strong><br/><code>accepted paths</code> | <p>document</p><p>my.{custom-type}.{field}</p> |
| <strong>$value</strong><br/><code>string</code>        | <p>Search terms</p>                            |

Examples:

```
Predicates::fulltext('document', 'banana') // one term
Predicates::fulltext('document', 'banana apple') // two terms
Predicates::fulltext('my.product.title', 'phone') // specific field
```

### has

The `has` predicate checks whether a fragment has a value. It will return all the documents of the specified type that contain a value for the specified field.

Note that this predicate will restrict the results to the custom type implied in the path.

```
Predicates::has( $path )
```

| Property                                                                   | Description                                    |
| -------------------------------------------------------------------------- | ---------------------------------------------- |
| <strong>$path</strong><br/><code>accepted path</code>                      | <p>my.{custom-type}.{field}</p>                |
| <strong>my.{custom-type}.{field}</strong><br/><code>accepted fields</code> | <p>All fields except for Groups and Slices</p> |

Example:

```
Predicates::has('my.product.price')
```

### missing

The `missing` predicate checks if a fragment doesn't have a value. It will return all the documents of the specified type that **do not** contain a value for the specified field.

Note that this predicate will restrict the results to the custom type implied in the path.

```
Predicates::missing( $path )
```

| Property                                                                   | Description                                    |
| -------------------------------------------------------------------------- | ---------------------------------------------- |
| <strong>$path</strong><br/><code>accepted path</code>                      | <p>my.{custom-type}.{field}</p>                |
| <strong>my.{custom-type}.{field}</strong><br/><code>accepted fields</code> | <p>All fields except for Groups and Slices</p> |

Example:

```
Predicates::missing('my.product.price')
```

### similar

The `similar` predicate takes the ID of a document, and returns a list of documents with similar content. This allows you to build an automated content discovery feature (for example, a "Related posts" section).

Also, remember that you can combine it with other predicates. Therefore not only could you search for "similar blog posts," but you could refine that to search for "similar blog posts that mention chocolate".

```
Predicates::similar( $id, $value )
```

| Property                                         | Description                                                                                      |
| ------------------------------------------------ | ------------------------------------------------------------------------------------------------ |
| <strong>$id</strong><br/><code>string</code>     | <p>The document ID</p>                                                                           |
| <strong>$value</strong><br/><code>integer</code> | <p>The maximum number of documents that a term may appear in to still be considered relevant</p> |

Example:

```
Predicates::similar('VkRmhykAAFA6PoBj', 10)
```

### near

The `near` predicate checks that the value in the path is within the radius of the given coordinates.

This predicate will only work for a GeoPoint field.

> Note that the measure of distance used for the radius is in km.

The near predicate will order the results from nearest to farthest from the given coordinates.

```
Predicates::near( $path, $latitude, $longitude, $radius )
```

| Property                                              | Description                                       |
| ----------------------------------------------------- | ------------------------------------------------- |
| <strong>$path</strong><br/><code>accepted path</code> | <p>my.{custom-type}.{field}</p>                   |
| <strong>$latitude</strong><br/><code>float</code>     | <p>Latitude of the center of the search area</p>  |
| <strong>$longitude</strong><br/><code>float</code>    | <p>Longitude of the center of the search area</p> |
| <strong>$radius</strong><br/><code>float</code>       | <p>Radius of search in kilometers</p>             |

Example:

```
Predicates::near('my.restaurant.location', 9.656896299, -9.77508544, 10)
```

## Number based Predicates

There are a few predicates that are specifically used for the Number field.

Note that when using any of these predicates, you will limit the results of the query to the specified custom type.

### lt (Less than)

The `lt` predicate checks that the value in the number field is less than the value passed into the predicate.

This is a strict less-than operator, it will not give values equal to the specified value.

```
Predicates::lt( $path, $value )
```

| Property                                              | Description                     |
| ----------------------------------------------------- | ------------------------------- |
| <strong>$path</strong><br/><code>accepted path</code> | <p>my.{custom-type}.{field}</p> |
| <strong>$value</strong><br/><code>float</code>        | <p>Number value</p>             |

Examples:

```
Predicates::lt('my.instructions.numberOfSteps', 10)
Predicates::lt('my.product.price', 49.99)
```

### gt (Greater than)

The `gt` predicate checks that the value in the number field is greater than the value passed into the predicate.

This is a strict greater-than operator, it will not give values equal to the specified value.

```
Predicates::gt( $path, $value )
```

| Property                                              | Description                     |
| ----------------------------------------------------- | ------------------------------- |
| <strong>$path</strong><br/><code>accepted path</code> | <p>my.{custom-type}.{field}</p> |
| <strong>$value</strong><br/><code>float</code>        | <p>Number value</p>             |

Examples:

```
Predicates::gt('my.rental.numberOfBedrooms', 2)
Predicates::gt('my.product.price', 9.99)
```

### inRange

The `inRange` predicate checks that the value in the path is within the two values passed into the predicate.

This is an inclusive search, it will include values equal to the upper and lower limits.

```
Predicates::inRange( $path, $lowerLimit, $upperLimit )
```

| Property                                               | Description                     |
| ------------------------------------------------------ | ------------------------------- |
| <strong>$path</strong><br/><code>accepted paths</code> | <p>my.{custom-type}.{field}</p> |
| <strong>$lowerLimit</strong><br/><code>float</code>    | <p>Lower limit of the range</p> |
| <strong>$upperLimit</strong><br/><code>float</code>    | <p>Upper limit of the range</p> |

Examples:

```
Predicates::inRange('my.album.track-count', 7, 10)
Predicates::inRange('my.product.price', 9.99, 49.99)
```

## Date & Time based Predicates

There are a number of predicates that are specifically used for the Date and Timestamp fields. You can read more about them on the [Date & Time based Predicate Reference](../02-query-the-api/03-date-and-time-based-predicate-reference.md) page.
