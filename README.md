HTPL 
=====

HTPL is a PHP template engine that uses HTML5 tags. Here is a simple example:

```html
<ul>
    <w-loop items="entries" var="v" key="k">
        <w-if cond="k=='name' || k=='id'">
            <li><strong>{k}:</strong> {v}</li>
        </w-if>
    </w-loop>
</ul>
```

We wrote this template engine because we had a need for an engine that can be easily plugged-in to, for example, 
to retrieve the source templates from a cloud storage and write compiled (cache) templates into memcache for faster execution,
and that at the same time it doesn't scare off designers, with all the weird symbols and annotations.
 
## Main features

1. It's secure, all values are automatically escaped before the output
2. Supports layout inheritance
3. Easy to extend, no need to write any lexers
4. Very fast (in some cases it outperforms Smarty, Twig and Blade) 
5. Simple and intuitive syntax

# The Basics

The engine uses an instance of a `TemplateProvider` to retrieve the source template, and it uses a `Cache` instance
to store the compiled template, for faster execution.

```php
$provider = new \Webiny\Htpl\TemplateProviders\FilesystemProvider([__DIR__ . '/template']);
$cache = new \Webiny\Htpl\Cache\FilesystemCache(__DIR__ . '/temp/compiled');

$htpl = new \Webiny\Htpl\Htpl($provider, $cache);

$htpl->display('template.htpl');
```

There are a couple of built in template providers and cache providers. If you wish to build your own, just create a class
 and implement `\Webiny\Htpl\TemplateProviders\TemplateProviderInterface` for a template provider, or `\Webiny\Htpl\Cache\CacheInterface` for the cache.

See more:
- [Functions](#functions)
- [Variables and modifiers](#variables-and-modifiers)
- [Template inheritance](#template-inheritance)
- [License and Contributions](#license-and-contributions)
- [Resources](#resources)

 
## Variables and modifiers

The variable values are printed using `{varName}` syntax. To the variable, you can attach different modifiers, for example:

```
{someVar|lower|replace({"john":"doe", "bird":"fish:})}
```

The upper code takes the value of `someVar` variable, makes it lowercase and replaces the word `john` with `doe`, and 
the work `bird` with `fish`.

As you can see, the modifiers are very easy to apply, and they can be chained together.

### Modifiers

The following modifiers are built in:

// numbers
- [Abs](#abs)
- [Round](#round)
- [Number format](#number-format)

// strings
- [Capitalize](#capitalize)
- [Lower](#lower)
- [Upper](#upper)
- [First upper](#fist-upper)
- [Format](#format)
- [Length](#length)
- [Nl2br](#nl2br)
- [Raw](#raw)
- [Replace](#replace)
- [Strip tags](#script-tags)
- [Trim](#trim)

// array
- [First](#first)
- [Last](#last)
- [Join](#join)
- [Keys](#keys)
- [Values](#values)
- [Length](#length)
- [Json encode](#json-encode)

// date
- [Date](#date)
- [Time ago](#time-ago)

// other
- [Default](#default)

See also [building a custom modifier](#custom-modifiers).

#### Abs

Absolute value

`someNum = -4`;
```
{someNum|abs} // 4  
```

#### Round

Round the number.

`someNum = 3.555`;
```
{someNum|round} // 4
{someNum|round(2)} // 4.00
{someNum|round(2)} // 3.56
{someNum|round(2, "down")} // 3.549
```



#### Capitalize

Capitalize the string.

`str = "some string"`
```
{str|capitalize} // Some String
```

#### Lower

String to lowercase.

`str = "SOME STRING"`
```
{str|capitalize} // some string
```

#### Upper

String to uppercase.

`str = "some string"`
```
{str|capitalize} // SOME STRING 
```

#### First upper

First letter to upper case.

`str = "some string"`
```
{str|firstUpper} // Some string
```

#### Date

Display the date.

`date = "2015-01-01 14:25"`
```
{date|date("F j, Y, g:i a")} // January 1, 2015, 2:25 pm
```

The `date` modifier uses PHP date internally, meaning you can pass any PHP date format and it will parse it.
 
#### Time ago

This a handy modifier for displaying the date/time in a `time ago` format.

`date = "2015-01-01 14:25"`
```
{date|timeAgo} // 4 months ago
```

#### Default value

Return a default value, of the variable is empty.

`var` is not defined.
```
{var|default("some value")} // some value
```


#### Format

Format a string and replace the placeholders with given values.

`var = "My name is %s"`
```
{var|format({"John Snow"})} // My name is John Snow
```

The modifier takes an array of strings that should be replace in the same order as the placeholders appear in the input string.

#### First

Return the first value from the array.

`arr = ["one", "two", "three"]`
```
{arr|first} // one
```

#### Last

Return the last value from the array.

`arr = ["one", "two", "three"]`
```
{arr|last} // three
```

#### Join

Join the array pieces with the given glue.

`arr = ["one", "two", "three"]`
```
{arr|join(",")} // one,two,three 
```

The modifier takes the glue as the parameter.

#### Keys

Return the array keys.

`arr = ["keyOne"=>"one", "keyTwo"=>"two", "keyThree"=>"three]`
```
{arr|keys} // ["keyOne", "keyTwo", "keyThree"]
```

#### Values

Return the array keys.

`arr = ["keyOne"=>"one", "keyTwo"=>"two", "keyThree"=>"three]`
```
{arr|values} // ["one", "two", "three"]
```

#### Length

Returns the string length or the number or elements inside an array.

`arr = ["one", "two", "three"]`
```
{arr|length} // 3 
```

`str = "some string"`
```
{str|length} // 11 
```

#### Json encode

Json encode the given array.

`arr = ["one", "two", "three"]`
```
{arr|jsonEncode} // {"one", "two", "three"} 
```

#### Nl2br

Converts new lines to HTML `br` tag.

`str = "Some\nString"`
```
{str|nl2br} // Some<br />\nString
```

#### Number format

Format the given number.

`num = 3500.1`
```
{num|numberFormat(2)} // 3,500.10
{num|numberFormat(3, ",", ".")} // 3.500,100
```

The modifier takes three parameters: `decimals`, `decimal point` and `thousand separator`.

#### Raw

Un-escapes the variable output.

`var = "<div><p>string</p></div>"`
```
{var} // &lt;div&gt;&lt;p&gt;string&lt;/p&gt;&lt;/div&gt;
{var|raw} // <div><p>string</p></div>
```

#### Replace

Perform a find and replace on the given string.

`var = "John loves Kalisi"`
```
{var|replace({"Kalisi":"Tyrion"})} // John loves Tyrion
```

The modifier takes an array of key=>value pairs defining what should be replaced.


##### Strip tags

Strips the HTML tags from the string.

`var = "Some <div>HTML</div> string"`
```
{var|stripTags} // Some HTML string
{var|stripTags("<div>")} // Some <div>HTML</div> string
```

The modifier take a comma separated list of allowed tags that shouldn't be replaced.


#### Trim

Trims the given character from the beginning, end or from both sides of the string.

`str = "|Some string|"`
```
{str|trim("|")} // Some string
{str|trim("|", "left")} // Some string|
{str|trim("|", "right")} // |Some string
```

The modifier takes the char that should be trimmed as the first parameter, and the trim direction as the second parameter.


#### Custom modifiers

To add a custom modifier, create a class the implements `\Webiny\Htpl\Modifiers\ModifierPackInterface` and assign the class
instance to your Htpl instance:

```php
$myModifierPack = new MockModifierPack();
$htpl->registerModifierPack($myModifierPack);
```

It's worth checking out the built-in [CorePack](src/Webiny/Htpl/Modifiers/CorePack.php) to get a sense of the implementation. 


## Functions (tags)

The template engine provides just a few core functions that are sufficient in about 95% of your needs. 
For the remaining 5%, HTPL provides a simple way to integrate any custom function.

Lets take a look at what is supported.

### If, Else, ElseIf

The `if` function, and its siblings `else` and `elseif` provide a way for executing/showing a particular part of the template,
based on the value of the logical condition.

```html
<w-if cond="someVar=='someString'">
    <li>the value of someVar equals to someString</li>
<w-elseif cond="someVar>100" />
    <li>someVar is larger than 100</li>
<w-else/>
    <li>something else - in case both upper conditions are false</li>
</w-if>
```

### Include a template

An external template can we included using the `w-include` tag. 

```html
<ul>
    <w-include file="myLists.htpl"/>
</ul>
```

If the value of the `file` attribute doesn't have a `.htpl` extension, it will be read as a variable, and the engine will
try to retrieve the template name from the variable and include it. 
**Note:** Only `.htpl` files can be included. The `.htpl` files cannot contain any PHP code.

```html
<ul>
    <w-include file="someVariable"/>
</ul>
```

### Loops

The loop parameter takes the `items` attribute, which is the object you wish to loop, and the `var` attribute, which 
marks the current object value inside the loop. Also an optional attribute `key` can be passed, that holds the object key value.

```html
<w-loop items="entries" var="v" key="k">
    <li><strong>{k}:</strong> {v}</li>
</w-loop>
```

### Literal

The `w-literal` tag marks the content that should not be parsed. This is useful when you are using curly brackets `{}` inside
your JavaScript code, so that the template engine doesn't raise an error.

```html
<w-literal>
    <script>
        var object = {"name":"john"};
    </script>
</w-literal>
```

### Minify

This is handy function that minifies and concatenates all marked JavaScript, or CSS, files into one file and strips out
comments and new lines, making the file much faster to download.

A sample template like this: 

```html
<w-minify>
    <script src="assets/js/skel.js"></script>
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/init.js"></script>
</w-minify>

<w-minify>
    <link rel="stylesheet" href="assets/css/style.css"/>
    <link rel="stylesheet" href="assets/css/navigation.css"/>
    <link rel="stylesheet" href="assets/css/modals.css"/>
</minify>
```

Would output something like this:
```html
<script src="assets/minified/asda1kjh12k3jh1k3jh12k.js"></script>
<link rel="stylesheet" href="assets/minified/klh123iuoi13k1j23lk.css"/>
```

The script automatically tracks when the file was changed and creates a new minified file, with a different name, 
so it's automatically refreshed in the user's browser.
**Note:** Don't place js and css files together inside the same `w-minify` block.

###

#### Configuring minify

The minify function needs to be configured, before it can be used. 

```php
// get your Htpl instance
$htpl = new \Webiny\Htpl\Htpl($provider, $cache);

// define the minify options
$htpl->setOptions([
    'minify' => [
        'driver'    => 'Webiny\Htpl\Functions\WMinify\WMinify',
        'provider'  => $providerInstance,
        'cache'     => $cacheInstance
    ]
]);
```

The `driver` parameter is an optional parameter. If not defined, if will use the internal minifaction class. In case you
wish to use some other minifcation class, you can create your own driver by extending `\Webiny\Htpl\Functions\WMinify\WMinifyAbstract`.

The `provider` parameter is an instance of a template provider, which can be a different instance, then the one used for Htpl instance.
This `provider` tells to the minify where to look for source files.

The `cache` parameter is an instance of a cache, which can also be a different instance then the one used for Htpl instance.
The `cache` tells to the minify where to save the minified files. 

## Template inheritance

Template inheritance is done using layouts and blocks.

For example:

`layout.htpl` content:
```html
<html>
<head>
    <title><w-block="title"></w-block></title>
</head>
<body>
    <w-block="content"></w-block>
</body>
</html>
```

`template.htpl` content:
```html
<w-layout template="layout.htpl">
    <w-block="title">Hello World</w-block>
    
    <w-block="content">
        This is my content
    </w-block>
</w-layout>
```

The output:
```html
<html>
<head>
    <title>Hello World</title>
</head>
<body>
    This is my content
</body>
</html>
```

**Note**: inside `w-layout` tag, all content that is note inside a `w-block` tag will get dropped. 


## License and Contributions

Contributing > Feel free to send PRs.
License > [MIT](LICENSE)

## Resources

To run unit tests, you need to use the following command:
```
$ cd path/to/Htpl/
$ composer install
$ phpunit
```