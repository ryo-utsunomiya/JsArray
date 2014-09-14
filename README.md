JsArray
=======

A port of JavaScript Array for PHP

Synopsis
========

```php
<?php
$fruits = new JsArray("Banana", "Orange", "Lemon", "Apple");
$fruits[] = "Mango";
echo $fruits[-1]; // "Mango"
$citrus = $fruits->slice(1, 3);
echo $citrus[0]; // "Orange"
```
