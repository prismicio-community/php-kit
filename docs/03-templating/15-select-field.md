# Templating the Select field

The Select field will add a dropdown select box of choices for the content writers to pick from.

## Get the Select field value

Here's an example of how to integrate the selected text value. In this case the Select field has the API ID of `category`.

**php (sdk v4 or later)**:

```
<p><?= $document->data->category ?></p>
```

**php (sdk v3 or earlier)**:

```
<p><?= $document->getText('page.category') ?></p>
```
