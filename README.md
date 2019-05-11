# HTML/CSV scraper

This tool is a command line website scraper / CSV reader / postprocessor, which can read data from HTML or a CSV file, and convert it to PHP objects, JSON data or CSV data.

For example, given this input file:

```html
<html>   
  <body>
    <h1 class="title">Gallery title</h1>
    <a href="first-image.png">First image title</a>
    <a href="second-image.png">Second image title</a>
  </body>
</html>
```

And this configuration:

```json
{
    "name": ["h1[@class='title']", "one", "innerText"],
    "images": ["a", "attrib_href"],
    "captions": ["a", "innerText"]
}
```

The output will be:

```json
{
    "name": "Gallery title",
    "images": [
        "first-image.png",
        "second-image.png"
    ],
    "captions": [
        "First image title",
        "Second image title"
    ]
}
```

The tool has various *readers*, which can process input files in different formats, like reading HTML DOM elements specified with XPaths, and reading columns of CSV files specified with their column index.

After reading, *filters* help to postprocess data, eg. extracting text from HTML elements and convert between date formats. The result of the scraping is copied to *result* objects, which can validate the input.

Finally, the result is passed to a *writer* object, that stores the data in a file, or maybe inserts it into a database.

## Usage and configuration

Run *import.php* from the command line. Arguments are:

```shell
./import.php config.json files_to_import...
```

To configure the scraper, you write a JSON file which specifies the reader, the writer and the result format. An example configuration file looks like this:

```json
{
    "reader": "Reader_HTML",
    "result": "Result_Gallery",
    "writer": "Writer_JSON",
    "imports": {
        "name": ["h1[@class='title']", "one", "innerText"],
        "images": ["a", "attrib_href"],
        "captions": ["a", "innerText"]
    }
}
```

- `reader` is the name of the class that will read the input file. The repository contains a reader for HTML and CSV, but you can implement your own.
- `result` is the name of the result class which stores scraped data. You can use the generic `Result` class, or implement your own to have validation.
- `writer` is the name of the class that generates output. Writers are available for JSON, PHP and CSV formats, but you can subclass `Writer` to create your own.
- The `imports` field specifies the data items that will be loaded from the file. The keys denote the attribute names in the `Result` object. The values, ie. the arrays specify what data to load and which filters to apply. The first element of the array is always the path, and all other elements are zero or more filters. The interpretation of the path string depends on the `Reader` object used; for HTML, it is an XPath, and for CSV, a column index.

## Readers

### Reader_HTML

The HTML reader can open HTML and XHTML files, any format that can be understood by Tidy and SimpleXmlElement. Extra configuration for this reader:

- `main` (optional): an XPath which specifies an element to select the node for all relative XPaths used in imports.

XPath examples:

- `./div[@class="product"]`: all div elements that are direct descendants of `main`.
- `.//img`: all image elements that are descendants of `main`.
- `//a[@href]`: all anchors in the document.

Some pre- and postprocessing is made by this reader at string and at DOM level. If you have some HTML code with syntax errors to process, you can subclass this reader to fix the problems. See the examples in the protected methods of this class, and overload them as needed.

### Reader_CSV

This reader will process CSV files. Paths for this reader are column indexes starting at 0. Extra configuration:

- `separator` (optional): separator character between fields. By default, the delimiter is a semicolon.

## Filters

See the `Filters` class. It contains a bunch of functions, which serve different purposes:

- Filters: reduce the number of elements in an array.
- Reduce functions: to reduce an array of elements to a single element, for example to concatenate all strings in an array.
- Map functions: to convert data, for example to get the `src` attribute of images. These work on single elements and on arrays as well. In the latter case, the function is applied element by element.

Example filters for an image gallery:

```json
"imports": {
    "name": ["h1[@class='title']", "one", "innerText"],
    "images": ["a", "attrib_href"],
    "captions": ["a", "innerText"]
}
```

- The *name* of the gallery is read from the `h1` element. The result of the XPath search is an array of elements, but it is reduced by `Filters::one` to a single SimpleXmlElement. (If the XPath matched zero or more elements, `one` will throw an exception.) Then the text is extracted from the XmlElement using the `Filters::innerText` method.
- To find the *images*, the next XPath query looks for `a` tags in the document. The result is an array, but in this case it is not reduced, as more images are expected. The `href` attributes of the anchors point to the image filenames, therefore the attributes are extracted using `Filters::attrib_href`.
- The same happens for the image `captions`.

## Writers

Writers are called after extracting the data and validating the result.

### Writer_CSV

Appends data to a CSV file. The filename can be specified in the configuration file with `csvoutfile`, otherwise the standard output is used. If the result contains arrays, they will be concatenated as strings, delimited by newline characters.

### Writer_JSON, Writer_PHP

These are similar to the CSV writer, but append data to a JSON or a PHP file. They expect the file to contain an array; new data will be appended to that array. The name of the file can be specified using `jsonoutfile` an `phpoutfile`. Without this filename, they dump data to the standard output.

Be careful, `Writer_PHP` uses `eval`.

### Writer_NOP, Writer_Print

These writers are intended to help with experimenting and debugging.

## Mirroring a web page

See the small script in the `util` folder.

## License

MIT.
