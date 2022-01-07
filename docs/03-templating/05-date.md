# Templating the Date field

The Date field allows content writers to add a date that represents a calendar day.

## Get the date value

Date value is a string formatted as YYYY-MM-DD (example: 2020-02-19).

Here is an example that retrieves the value of a Date field with the API ID of `date`.

**php (sdk v4 or later)**:

```
<time><?= $document->data->date ?></time>
// Outputs: 2020-02-19
```

**php (sdk v3 or earlier)**:

```
<time><?= $document->getDate('page.date')->asText() ?></time>
// Outputs: 2020-02-19
```

## Format the Date

You can also customize the output of the Date field.

**php (sdk v4 or later)**:

```
<?php
use Prismic\Dom\Date;
$date = Date::asDate($document->data->date);
?>

<time><?= $date->format('l M jS, Y') ?></time>
// Outputs: Sunday Feb 19th, 2020
```

**php (sdk v3 or earlier)**:

```
<?php
$date = $document->getDate('page.date')->asDateTime();
?>

<time><?= $date->format('l M jS, Y') ?></time>
// Outputs: Sunday Feb 19th, 2020
```
