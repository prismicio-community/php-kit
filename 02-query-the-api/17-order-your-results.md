# Order your results

This page shows how to order the results of your query for prismic.io. It explains how to use the orderings and after predicate options.

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

### Specifying multiple orderings

You can specify more than one field to order your results by. To do so, simply add more than one field in the array.

The results will be ordered by the first field in the array. If any of the results have the same value for that initial sort, they will then be sorted by the next specified field.

Here is an example that first sorts the products by price from lowest to highest. If any of the products have the same price, then they will be sorted by their titles.

```css
[ 'orderings' => '[my.product.price, my.product.title]' ]
```

### Order by publication dates

It is also possible to order documents by their first or last publication dates.

The **first publication date** is the date that the document was originally published for the first time. The **last publication date** is the most recent date that the document has been published after editing.

```css
[ 'orderings' => '[document.first_publication_date]' ]
[ 'orderings' => '[document.last_publication_date]' ]
```

## after

The `after` option can be used along with the orderings option. It will remove all the documents except for those after the specified document in the list.

To clarify, let’s say you have a query that return the following documents in this order:

- `V9Zt3icAAAl8Uzob (Page 1)`
- `PqZtvCcAALuRUzmO (Page 2)`
- `VkRmhykAAFA6PoBj (Page 3)`
- `V4Fs8rDbAAH9Pfow (Page 4)`
- `G8ZtxQhAALuSix6R (Page 5)`
- `Ww9yuAvdAhl87wh6 (Page 6)`

If you add the `after` option and specify page 3, “VkRmhykAAFA6PoBj”, your query will return the following:

- `V4Fs8rDbAAH9Pfow (Page 4)`
- `G8ZtxQhAALuSix6R (Page 5)`
- `Ww9yuAvdAhl87wh6 (Page 6)`

By reversing the orderings in your query, you can use this same method to retrieve all the documents before the specified document. Simply use the `after` parameter in your query options as shown below.

```css
[ 'after' => 'VkRmhykAAFA6PoBj' ]
```
