# Date & Time based Predicate Reference

This page describes and gives examples for all the date and time based predicates you can use when creating queries with the prismic.io PHP development kit.

To use the predicates as shown below, you must make sure to include the Predicates class.

```
use Prismic/Predicates;
```

All of these predicates will work when used with either the Date or Timestamp fields, as well as the first and last publication dates.

Note that when using any of these predicates with either a Date or Timestamp field, you will limit the results of the query to the specified custom type.

## dateAfter

The `dateAfter` predicate checks that the value in the path is after the date value passed into the predicate.

This will NOT include anything with a date equal to the input value.

```
Predicates::dateAfter( $path, $date )
```

| Property                                                     | Description                                                                                                |
| ------------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------- |
| <strong>$path</strong><br/><code>accepted paths</code>       | <p>document.first_publication_date</p><p>document.last_publication_date</p><p>my.{custom-type}.{field}</p> |
| <strong>$date</strong><br/><code>accepted date values</code> | <p>DateTime object</p><p>String in the following format: “YYYY-MM-DD”</p>                                  |

Examples:

```
Predicates::dateAfter('document.first_publication_date', new DateTime('2017-05-18'))
Predicates::dateAfter('document.last_publication_date', '2017-05-18')
Predicates::dateAfter('my.article.release-date', '2017-01-22')
```

## dateBefore

The `dateBefore` predicate checks that the value in the path is before the date value passed into the predicate.

This will NOT include anything with a date equal to the input value.

```
Predicates::dateBefore( $path, $date )
```

| Property                                                     | Description                                                                                                |
| ------------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------- |
| <strong>$path</strong><br/><code>accepted paths</code>       | <p>document.first_publication_date</p><p>document.last_publication_date</p><p>my.{custom-type}.{field}</p> |
| <strong>$date</strong><br/><code>accepted date values</code> | <p>DateTime object</p><p>String in the following format: “YYYY-MM-DD”</p>                                  |

Examples:

```
Predicates::dateBefore('document.first_publication_date', '2016-09-19')
Predicates::dateBefore('document.last_publication_date', new DateTime('2016-10-15'))
Predicates::dateBefore('my.post.date', new DateTime('2017-08-24'))
```

## dateBetween

The `dateBetween` predicate checks that the value in the path is within the date values passed into the predicate.

```
Predicates::dateBetween( $path, $startDate, $endDate )
```

| Property                                                          | Description                                                                                                |
| ----------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------- |
| <strong>$path</strong><br/><code>accepted paths</code>            | <p>document.first_publication_date</p><p>document.last_publication_date</p><p>my.{custom-type}.{field}</p> |
| <strong>$startDate</strong><br/><code>accepted date values</code> | <p>DateTime object</p><p>String in the following format: &quot;YYYY-MM-DD&quot;</p>                        |
| <strong>$endDate</strong><br/><code>accepted date values</code>   | <p>DateTime object</p><p>String in the following format: &quot;YYYY-MM-DD&quot;</p>                        |

Examples:

```
Predicates::dateBetween('document.first_publication_date', '2017-01-16', '2017-01-20')
Predicates::dateBetween('document.last_publication_date', new DateTime('2017-01-16'), new DateTime('2017-01-20'))
Predicates::dateBetween('my.blog-post.post-date', '2017-01-16', '2017-01-20')
```

## dayOfMonth

The `dayOfMonth` predicate checks that the value in the path is equal to the day of the month passed into the predicate.

```
Predicates::dayOfMonth( $path, $day )
```

| Property                                               | Description                                                                                                |
| ------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------- |
| <strong>$path</strong><br/><code>accepted paths</code> | <p>document.first_publication_date</p><p>document.last_publication_date</p><p>my.{custom-type}.{field}</p> |
| <strong>$day</strong><br/><code>integer</code>         | <p>Day of the month</p>                                                                                    |

Examples:

```
Predicates::dayOfMonth('document.first_publication_date', 22)
Predicates::dayOfMonth('document.last_publication_date', 30)
Predicates::dayOfMonth('my.post.date', 14)
```

## dayOfMonthAfter

The `dayOfMonthAfter` predicate checks that the value in the path is after the day of the month passed into the predicate.

Note that this will return only the days after the specified day of the month. It will not return any documents where the day is equal to the specified day.

```
Predicates::dayOfMonthAfter( $path, $day )
```

| Property                                               | Description                                                                                                |
| ------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------- |
| <strong>$path</strong><br/><code>accepted paths</code> | <p>document.first_publication_date</p><p>document.last_publication_date</p><p>my.{custom-type}.{field}</p> |
| <strong>$day</strong><br/><code>integer</code>         | <p>Day of the month</p>                                                                                    |

Examples:

```
Predicates::dayOfMonthAfter('document.first_publication_date', 1)
Predicates::dayOfMonthAfter('document.last_publication_date', 10)
Predicates::dayOfMonthAfter('my.event.date-and-time', 15)
```

## dayOfMonthBefore

The `dayOfMonthBefore` predicate checks that the value in the path is before the day of the month passed into the predicate.

Note that this will return only the days before the specified day of the month. It will not return any documents where the date is equal to the specified day.

```
Predicates::dayOfMonthBefore( $path, $day )
```

| Property                                               | Description                                                                                                |
| ------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------- |
| <strong>$path</strong><br/><code>accepted paths</code> | <p>document.first_publication_date</p><p>document.last_publication_date</p><p>my.{custom-type}.{field}</p> |
| <strong>$day</strong><br/><code>integer</code>         | <p>Day of the month</p>                                                                                    |

Examples:

```
Predicates::dayOfMonthBefore('document.first_publication_date', 30)
Predicates::dayOfMonthBefore('document.last_publication_date', 10)
Predicates::dayOfMonthBefore('my.blog-post.release-date', 23)
```

## dayOfWeek

The `dayOfWeek` predicate checks that the value in the path is equal to the day of the week passed into the predicate.

```
Predicates::dayOfWeek( $path, $day )
```

| Property                                                   | Description                                                                                                |
| ---------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------- |
| <strong>$path</strong><br/><code>accepted paths</code>     | <p>document.first_publication_date</p><p>document.last_publication_date</p><p>my.{custom-type}.{field}</p> |
| <strong>$day</strong><br/><code>string\* or integer</code> | <pre>&#39;monday&#39;, &#39;mon&#39;, or 1                                                                 |

&#39;tuesday&#39;, &#39;tue&#39;, or 2
&#39;wednesday&#39;, &#39;wed&#39;, or 3
&#39;thursday&#39;, &#39;thu&#39;, or 4
&#39;friday&#39;, &#39;fri&#39;, or 5
&#39;saturday&#39;, &#39;sat&#39;, or 6
&#39;sunday&#39;, &#39;sun&#39;, or 7</pre>|

- For any of the string input values you can use either first letter capitalized, all lowercase, or all uppercase. For example, "Monday", "monday", and "MONDAY" are all accepted values.

Examples:

```
Predicates::dayOfWeek('document.first_publication_date', 'monday')
Predicates::dayOfWeek('document.last_publication_date', 'sun')
Predicates::dayOfWeek('my.concert.show-date', 'Friday')
```

## dayOfWeekAfter

The `dayOfWeekAfter` predicate checks that the value in the path is after the day of the week passed into the predicate.

This predicate uses Monday as the beginning of the week:

1. Monday
1. Tuesday
1. Wednesday
1. Thursday
1. Friday
1. Saturday
1. Sunday

Note that this will return only the days after the specified day of the week. It will not return any documents where the day is equal to the specified day.

```
Predicates::dayOfWeekAfter( $path, $day )
```

| Property                                                   | Description                                                                                                |
| ---------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------- |
| <strong>$path</strong><br/><code>accepted paths</code>     | <p>document.first_publication_date</p><p>document.last_publication_date</p><p>my.{custom-type}.{field}</p> |
| <strong>$day</strong><br/><code>string\* or integer</code> | <pre>&#39;monday&#39;, &#39;mon&#39;, or 1                                                                 |

&#39;tuesday&#39;, &#39;tue&#39;, or 2
&#39;wednesday&#39;, &#39;wed&#39;, or 3
&#39;thursday&#39;, &#39;thu&#39;, or 4
&#39;friday&#39;, &#39;fri&#39;, or 5
&#39;saturday&#39;, &#39;sat&#39;, or 6
&#39;sunday&#39;, &#39;sun&#39;, or 7</pre>|

- For any of the string input values you can use either first letter capitalized, all lowercase, or all uppercase. For example, "Monday", "monday", and "MONDAY" are all accepted values.

Examples:

```
Predicates::dayOfWeekAfter('document.first_publication_date', 'fri')
Predicates::dayOfWeekAfter('document.last_publication_date', 'Thu')
Predicates::dayOfWeekAfter('my.blog-post.date', 'tuesday')
```

## dayOfWeekBefore

The `dayOfWeekBefore` predicate checks that the value in the path is before the day of the week passed into the predicate.

This predicate uses Monday as the beginning of the week:

1. Monday
1. Tuesday
1. Wednesday
1. Thursday
1. Friday
1. Saturday
1. Sunday

Note that this will return only the days before the specified day of the week. It will not return any documents where the day is equal to the specified day.

```
Predicates::dayOfWeekBefore( $path, $day )
```

| Property                                                   | Description                                                                                                |
| ---------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------- |
| <strong>$path</strong><br/><code>accepted paths</code>     | <p>document.first_publication_date</p><p>document.last_publication_date</p><p>my.{custom-type}.{field}</p> |
| <strong>$day</strong><br/><code>string\* or integer</code> | <pre>&#39;monday&#39;, &#39;mon&#39;, or 1                                                                 |

&#39;tuesday&#39;, &#39;tue&#39;, or 2
&#39;wednesday&#39;, &#39;wed&#39;, or 3
&#39;thursday&#39;, &#39;thu&#39;, or 4
&#39;friday&#39;, &#39;fri&#39;, or 5
&#39;saturday&#39;, &#39;sat&#39;, or 6
&#39;sunday&#39;, &#39;sun&#39;, or 7</pre>|

- For any of the string input values you can use either first letter capitalized, all lowercase, or all uppercase. For example, "Monday", "monday", and "MONDAY" are all accepted values.

Examples:

```
Predicates::dayOfWeekBefore('document.first_publication_date', 'Wed')
Predicates::dayOfWeekBefore('document.last_publication_date', 'saturday')
Predicates::dayOfWeekBefore('my.page.release-date', 'Saturday')
```

## month

The `month` predicate checks that the value in the path occurs in the month value passed into the predicate.

```
Predicates::month( $path, $month )
```

| Property                                                     | Description                                                                                                |
| ------------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------- |
| <strong>$path</strong><br/><code>accepted paths</code>       | <p>document.first_publication_date</p><p>document.last_publication_date</p><p>my.{custom-type}.{field}</p> |
| <strong>$month</strong><br/><code>string\* or integer</code> | <pre>&#39;january&#39;, &#39;jan&#39;, or 1                                                                |

&#39;february&#39;, &#39;feb&#39;, or 2
&#39;march&#39;, &#39;mar&#39;, or 3
&#39;april&#39;, &#39;apr&#39;, or 4
&#39;may&#39; or 5
&#39;june&#39;, &#39;jun&#39;, or 6
&#39;july&#39;, &#39;jul&#39;, or 7
&#39;august&#39;, &#39;aug&#39;, or 8
&#39;september&#39;, &#39;sep&#39;, or 9
&#39;october&#39;, &#39;oct&#39;, or 10
&#39;november&#39;, &#39;nov&#39;, or 11
&#39;december&#39;, &#39;dec&#39;, or 12</pre>|

- For any of the string input values you can use either first letter capitalized, all lowercase, or all uppercase. For example, "January", "january", and "JANUARY" are all accepted values.

Examples:

```
Predicates::month('document.first_publication_date', 'august')
Predicates::month('document.last_publication_date', 'Sep')
Predicates::month('my.blog-post.date', 1)
```

## monthAfter

The `monthAfter` predicate checks that the value in the path occurs in any month after the value passed into the predicate.

Note that this will only return documents where the date is after the specified month. It will not return any documents where the date is within the specified month.

```
Predicates::monthAfter( $path, $month )
```

| Property                                                     | Description                                                                                                |
| ------------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------- |
| <strong>$path</strong><br/><code>accepted paths</code>       | <p>document.first_publication_date</p><p>document.last_publication_date</p><p>my.{custom-type}.{field}</p> |
| <strong>$month</strong><br/><code>string\* or integer</code> | <pre>&#39;january&#39;, &#39;jan&#39;, or 1                                                                |

&#39;february&#39;, &#39;feb&#39;, or 2
&#39;march&#39;, &#39;mar&#39;, or 3
&#39;april&#39;, &#39;apr&#39;, or 4
&#39;may&#39; or 5
&#39;june&#39;, &#39;jun&#39;, or 6
&#39;july&#39;, &#39;jul&#39;, or 7
&#39;august&#39;, &#39;aug&#39;, or 8
&#39;september&#39;, &#39;sep&#39;, or 9
&#39;october&#39;, &#39;oct&#39;, or 10
&#39;november&#39;, &#39;nov&#39;, or 11
&#39;december&#39;, &#39;dec&#39;, or 12</pre>|

- For any of the string input values you can use either first letter capitalized, all lowercase, or all uppercase. For example, "January", "january", and "JANUARY" are all accepted values.

Examples:

```
Predicates::monthAfter('document.first_publication_date', 'February')
Predicates::monthAfter('document.last_publication_date', 6)
Predicates::monthAfter('my.article.date', 'oct')
```

## monthBefore

The `monthBefore` predicate checks that the value in the path occurs in any month before the value passed into the predicate.

Note that this will only return documents where the date is before the specified month. It will not return any documents where the date is within the specified month.

```
Predicates::monthBefore( $path, $month )
```

| Property                                                     | Description                                                                                                |
| ------------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------- |
| <strong>$path</strong><br/><code>accepted paths</code>       | <p>document.first_publication_date</p><p>document.last_publication_date</p><p>my.{custom-type}.{field}</p> |
| <strong>$month</strong><br/><code>string\* or integer</code> | <pre>&#39;january&#39;, &#39;jan&#39;, or 1                                                                |

&#39;february&#39;, &#39;feb&#39;, or 2
&#39;march&#39;, &#39;mar&#39;, or 3
&#39;april&#39;, &#39;apr&#39;, or 4
&#39;may&#39; or 5
&#39;june&#39;, &#39;jun&#39;, or 6
&#39;july&#39;, &#39;jul&#39;, or 7
&#39;august&#39;, &#39;aug&#39;, or 8
&#39;september&#39;, &#39;sep&#39;, or 9
&#39;october&#39;, &#39;oct&#39;, or 10
&#39;november&#39;, &#39;nov&#39;, or 11
&#39;december&#39;, &#39;dec&#39;, or 12</pre>|

- For any of the string input values you can use either first letter capitalized, all lowercase, or all uppercase. For example, "January", "january", and "JANUARY" are all accepted values.

Examples:

```
Predicates::monthBefore('document.first_publication_date', 8)
Predicates::monthBefore('document.last_publication_date', 'june')
Predicates::monthBefore('my.blog-post.release-date', 'Sep')
```

## year

The `year` predicate checks that the value in the path occurs in the year value passed into the predicate.

```
Predicates::year( $path, $year )
```

| Property                                               | Description                                                                                                |
| ------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------- |
| <strong>$path</strong><br/><code>accepted paths</code> | <p>document.first_publication_date</p><p>document.last_publication_date</p><p>my.{custom-type}.{field}</p> |
| <strong>$year</strong><br/><code>integer</code>        | <p>Year</p>                                                                                                |

Examples:

```
Predicates::year('document.first_publication_date', 2016)
Predicates::year('document.last_publication_date', 2017)
Predicates::year('my.employee.birthday', 1986)
```

## hour

The `hour` predicate checks that the value in the path occurs within the hour value passed into the predicate.

This uses the 24 hour system, starting at 0 and going through 23.

Note that this predicate will technically work for a Date field, but won’t be very useful. All date field values are automatically given an hour of 0.

```
Predicates::hour( $path, $hour )
```

| Property                                               | Description                                                                                                |
| ------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------- |
| <strong>$path</strong><br/><code>accepted paths</code> | <p>document.first_publication_date</p><p>document.last_publication_date</p><p>my.{custom-type}.{field}</p> |
| <strong>$hour</strong><br/><code>integer</code>        | <p>Hour between 0 and 23</p>                                                                               |

Examples:

```
Predicates::hour('document.first_publication_date', 12)
Predicates::hour('document.last_publication_date', 8)
Predicates::hour('my.event.date-and-time', 19)
```

## hourAfter

The `hourAfter` predicate checks that the value in the path occurs after the hour value passed into the predicate.

This uses the 24 hour system, starting at 0 and going through 23.

> Note that this will only return documents where the timestamp is after the specified hour. It will not return any documents where the timestamp is within the specified hour.

This predicate will technically work for a Date field, but won’t be very useful. All date field values are automatically given an hour of 0.

```
Predicates::hourAfter( $path, $hour )
```

| Property                                               | Description                                                                                                |
| ------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------- |
| <strong>$path</strong><br/><code>accepted paths</code> | <p>document.first_publication_date</p><p>document.last_publication_date</p><p>my.{custom-type}.{field}</p> |
| <strong>$hour</strong><br/><code>integer</code>        | <p>Hour between 0 and 23</p>                                                                               |

Examples:

```
Predicates::hourAfter('document.first_publication_date', 21)
Predicates::hourAfter('document.last_publication_date', 8)
Predicates::hourAfter('my.blog-post.releaseDate', 16)
```

## hourBefore

The `hourBefore` predicate checks that the value in the path occurs before the hour value passed into the predicate.

This uses the 24 hour system, starting at 0 and going through 23.

> Note that this will only return documents where the timestamp is before the specified hour. It will not return any documents where the timestamp is within the specified hour.

This predicate will technically work for a Date field, but won’t be very useful. All date field values are automatically given an hour of 0.

```
Predicates::hourBefore( $path, $hour )
```

| Property                                               | Description                                                                                                |
| ------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------- |
| <strong>$path</strong><br/><code>accepted paths</code> | <p>document.first_publication_date</p><p>document.last_publication_date</p><p>my.{custom-type}.{field}</p> |
| <strong>$hour</strong><br/><code>integer</code>        | <p>Hour between 0 and 23</p>                                                                               |

Examples:

```
Predicates::hourBefore('document.first_publication_date', 10)
Predicates::hourBefore('document.last_publication_date', 12)
Predicates::hourBefore('my.event.dateAndTime', 12)
```
