# Query Options reference

Here you will find a complete reference about the various query options.

## after

The `after` option can be used along with the orderings option. It will remove all the documents except for those after the specified document in the list.

To clarify, let’s say you have a query that return the following documents in this order:

- `V9Zt3icAAAl8Uzob (Page 1)`
- `PqZtvCcAALuRUzmO (Page 2)`
- `VkRmhykAAFA6PoBj (Page 3)`
- `V4Fs8rDbAAH9Pfow (Page 4)`
- `G8ZtxQhAALuSix6R (Page 5)`
- `Ww9yuAvdAhl87wh6 (Page 6)`

If you add the `after` option and specify page 3, “VkRmhykAAFA6PoBj”, your query will return the following:

- `V4Fs8rDbAAH9Pfow (Page 4)`
- `G8ZtxQhAALuSix6R (Page 5)`
- `Ww9yuAvdAhl87wh6 (Page 6)`

By reversing the orderings in your query, you can use this same method to retrieve all the documents before the specified document.

This option is useful when creating a navigation for a blog.

```css
[ 'after' => 'VkRmhykAAFA6PoBj' ]
```

## fetch

The `fetch` option is used to make queries faster by only retrieving the specified field(s).

To retrieve a single field, simply specify the field as shown below.

```css
[ 'fetch' => 'product.title' ]
```

To retrieve more than one field, you just need to comma separate all the fields you wish included in the response.

```css
[ 'fetch' => 'product.title, product.price']
```

## fetchLinks

The `fetchLinks` option allows you to retrieve a specific content field from a linked document and add it to the document response object.

Note that this will only retrieve content of the following field types:

- Color
- Content Relationship
- Date
- Image
- Key Text
- Number
- Rich Text (but only the first element)
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

```css
[ 'fetchLinks' => '{custom-type}.{field}' ]
```

| Property                            | Description                                                                  |
| ----------------------------------- | ---------------------------------------------------------------------------- |
| <strong>{custom-type}</strong><br/> | <p>The custom type API-ID of the linked document</p>                         |
| <strong>{field}</strong><br/>       | <p>The API-ID of the field you wish to retrieve from the linked document</p> |

To view a complete example of how this option works, view the example on the [Fetch Linked Document Fields](../02-query-the-api/16-fetch-linked-document-fields.md) page.

```css
[ 'fetchLinks' => 'author.full_name' ]
[ 'fetchLinks' => 'author.first-name, author.last-name' ]
```

## lang

The `lang` option defines the language code for the results of your query.

### Specify a particular language/region

You can use the *lang* option to specify a particular language/region you wish to query by. You just need to set the lang value to the desired language code, for example "en-us" for American English.

If no *lang* option is provided, then the query will default to the master language of the repository.

```css
[ 'lang' => 'en-us' ]
```

### Query all languages

You can also use the *lang* option to specify that you want to query documents in all available languages. Simply set the *lang* option to the wildcard value `*`.

```css
[ 'lang' => '*' ]
```

To view a complete example of how this option works, view the examples on the [Query by Language](../02-query-the-api/19-query-by-language.md) page.

## orderings

The `orderings` option orders the results by the specified field(s). You can specify as many fields as you want.

| Property                                | Description                                                                                    |
| --------------------------------------- | ---------------------------------------------------------------------------------------------- |
| <strong>lowest to highest</strong><br/> | <p>It will automatically order the field from lowest to highest</p>                            |
| <strong>highest to lowest</strong><br/> | <p>Use &quot;desc&quot; next to the field name to instead order it from greatest to lowest</p> |

```css
[ 'orderings' => '[my.product.price]' ] // lowest to highest
[ 'orderings' => '[my.product.price desc]' ] // highest to lowest
```

### Multiple orderings

You can specify more than one field to order your results by. To do so, simply add more than one field in the array.

The results will be ordered by the first field in the array. If any of the results have the same value for that initial sort, they will then be sorted by the next specified field.

Here is an example that first sorts the products by price from lowest to highest. If any of the products have the same price, then they will be sorted by their titles.

```css
[ 'orderings' => '[my.product.price, my.product.title]' ]
```

### Sort by publication date

It is also possible to order documents by their first or last publication dates.

| Property                                     | Description                                                                    |
| -------------------------------------------- | ------------------------------------------------------------------------------ |
| <strong>first_publication_date</strong><br/> | <p>The date that the document was originally published for the first time</p>  |
| <strong>last_publication_date</strong><br/>  | <p>The most recent date that the document has been published after editing</p> |

```css
[ 'orderings' => '[document.first_publication_date]' ]
[ 'orderings' => '[document.last_publication_date]' ]
```

## page

The `page` option defines the pagination for the result of your query.

Defaults to "1", corresponding to the first page.

| Property                                        | Description                                         |
| ----------------------------------------------- | --------------------------------------------------- |
| <strong>value</strong><br/><code>integer</code> | <p>page index (1 = 1st page, 2 = 2nd page, ...)</p> |

```css
[ 'page' => 2 ]
```

## pageSize

The `pageSize` option defines the maximum number of documents that the API will return for your query.

Default is 20, max is 100.

| Property                                        | Description                          |
| ----------------------------------------------- | ------------------------------------ |
| <strong>value</strong><br/><code>integer</code> | <p>page size (between 1 and 100)</p> |

```css
[ 'pageSize' => 100 ]
```

## ref

The `ref` option defines which version of your content to query.

By default the Prismic PHP development kit will use the master ref to retrieve the currently published documents.

| Property                                       | Description                                        |
| ---------------------------------------------- | -------------------------------------------------- |
| <strong>value</strong><br/><code>string</code> | <p>Master, Release, Experiment, or Preview ref</p> |

```css
[ 'ref' => 'Wst7PCgAAHUAvviX' ]
```
