# Templating the Color field

The Color field allows content writers to select a color through a color picker or to manually write a hexadecimal value.

## Get the color value

Color value is a string representing a color in hexadecimal format such as "#1e89ce".

Here is an example of how to retrieve the value from a Color field. In this case, the API ID of the field is `color`.

**php (sdk v4 or later)**:

```html
<h2 style="color: <?= $document->data->color ?>;">Colorful Title</h2>
// Outputs:
<h2 style="color: #1e89ce;">Colorful Title</h2>
```

**php (sdk v3 or earlier)**:

```html
<h2 style="color: <?= $document->getColor('page.color')->getHexValue() ?>;">
  Colorful Title
</h2>
// Outputs:
<h2 style="color: #1e89ce;">Colorful Title</h2>
```
