#!/usr/bin/env php
<?php

set_include_path(__DIR__ . "/src/");
spl_autoload_register();
spl_autoload_extensions('.class.php');

function myassert(string $message, bool $condition) {
    if (!$condition) {
        error_log("failed: {$message}");
        exit(1);
    }
}

$app = new App;
$app->import("test/gallery.json", "test/gallery.html");
$app->import("test/shop.json", "test/shop.csv");

myassert("exists", $app->filters->exists(["X"]) == 1);
myassert("not_null", $app->filters->not_null(1));
myassert("max_one", $app->filters->max_one([5]) == 5);
myassert("first", $app->filters->first([5,6,7]) == 5);
myassert("one", $app->filters->one([5]) == 5);

myassert("remove_root", $app->filters->remove_root("<a><b></b></a>") == "<b></b>");
myassert("floatval", $app->filters->floatval("123 xyz") == 123);
myassert("nl2br", $app->filters->nl2br("\n") == "<br>\n");
myassert("implode_rightarrow", $app->filters->implode_rightarrow([1,2,3]) == "1→2→3");
myassert("implode_semicolon", $app->filters->implode_semicolon([1,2,3]) == "1;2;3");
myassert("dmy_date", $app->filters->dmy_date("23-07-2016") == "2016-07-23 12:00:00");
myassert("ymd_date", $app->filters->ymd_date("2016-07-23") == "2016-07-23 12:00:00");
myassert("url_path_and_query", $app->filters->url_path_and_query("http://example.com/ex.html?query=123") == "/ex.html?query=123");
