# Templating the Boolean field

The Boolean field will add a switch for a true or false values for the content authors to pick from.

## Output boolean value

Here is an example of how to retrieve the value from a Boolean field which has the API ID switch.

**php (sdk v4 or later)**:

```php
<?php
  $example = $document->data->switch;
   if ($example) {
     echo "This is printed if value is true.<br />";
  }
?>
```

**php (sdk v3 or earlier)**:

```
We do not support the boolean field for V1
```
