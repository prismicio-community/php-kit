# Templating the UID field

The UID field is a unique identifier that can be used specifically to create SEO-friendly website URLs.

## Get the UID value

To get the UID value, you just need to access UID property on the retrieved document object as shown below.

**php (sdk v4 or later)**:

```
<?php
$uid = $document->uid;
```

**php (sdk v3 or earlier)**:

```
<?php
$uid = $document->getUid();
```
