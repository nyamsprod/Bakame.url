---
layout: default
title: The Query Parser
---

# The QueryParser

To preserve the query string, the library does not rely on PHP's `parse_str` and `http_build_query` functions.

Instead, the `League\Uri\QueryParser` class provides two public methods that can be used to parse a query string into an array of key value pairs. And conversely creates a valid query string from the resulting array.

## Parsing the query string into an array

- `parse_str` replaces any invalid characters from the query string pair key that can not be included in a PHP variable name by an underscore `_`.
- `parse_str` merges query string values.

These behaviors, specific to PHP, may be considered to be a data loss transformation in other languages.

~~~php
$query_string = 'toto.foo=bar&toto.foo=baz';
parse_str($query_string, $arr);
// $arr is an array containing ["toto_foo" => "baz"]
~~~

To avoid these transformations, the `QueryParser::parse` method returns an `array` representation of the query string which preserve key/value pairs. The method expects at most 3 arguments:

- The query string;
- The query string separator, by default it is set to `&`;
- The query string encryption. It can be:
    - `PHP_QUERY_RFC3986`
    - `PHP_QUERY_RFC1738`
    - `false` if you don't want any encryption.

<p class="message-notice">By default or if the submitted encryption is invalid the encryption is set to PHP constants <code>PHP_QUERY_RFC3986</code></p>

~~~php
use League\Uri\QueryParser;

$parser = new QueryParser();
$query_string = 'toto.foo=bar&toto.foo=baz';
$arr = $parser->parse($query_string, '&', PHP_RFC3986);
// $arr is an array containing ["toto.foo" => [["bar", "baz"]]
~~~

## Building the query string from an array

`http_build_query` always adds array numeric prefix to the query string even when they are not needed

using PHP's `parse_str`

~~~php
$query_string = 'foo[]=bar&foo[]=baz';
parse_str($query_string, $arr);
// $arr = ["foo" => ['bar', 'baz']];

$res = rawurldecode(http_build_query($arr, '', PHP_QUERY_RFC3986));
// $res equals foo[0]=bar&foo[1]=baz
~~~

or using `QueryParser::parse`

~~~php
use League\Uri\QueryParser;

$query_string = 'foo[]=bar&foo[]=baz';
$parser = new Parser();
$arr = $parser->parse($query_string, '&', PHP_RFC3986);
// $arr = ["foo[]" => ['bar', 'baz']];

$res = rawurldecode(http_build_query($arr, '', PHP_QUERY_RFC3986));
// $res equals foo[][0]=bar&oo[][1]=baz
~~~

The `QueryParser::build` method returns and preserves string representation of the query string from the `QueryParser::parse` array result. the method expects at most 3 arguments:

- A valid `array` of data to convert;
- The query string separator, by default it is set to `&`;
- The query string encryption. It can be:
    - `PHP_QUERY_RFC3986`
    - `PHP_QUERY_RFC1738`
    - `false` if you don't want any encryption.

<p class="message-notice">By default or if the submitted encryption is invalid the encryption is set to PHP constants <code>PHP_QUERY_RFC3986</code></p>

~~~php
use League\Uri\QueryParser;

$query_string = 'foo[]=bar&foo[]=baz';
$parser = new Parser();
$arr = $parser->parse($query_string, '&', PHP_RFC3986);
var_export($arr);
// $arr include the following data ["foo[]" => ['bar', 'baz']];

$res =$parser->build($arr, '&', false);
// $res equals 'foo[]=bar&foo[]=baz'
~~~

No key indexes is added and the query string is safely recreated