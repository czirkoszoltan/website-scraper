<?php

/**
 */
class Config {
    public static $DEBUG = true;
    public static $TIDY = [
        'indent' => false,
        'numeric-entities' => true,
        'hide-endtags' => false,
        'enclose-text' => true,
        'enclose-block-text' => true,
        'doctype' => 'omit',
        'wrap' => 0,
        'join-styles' => true,
        'merge-divs' => false,
        'merge-spans' => true,
        'hide-comments' => true,
        'drop-font-tags' => true,
        'drop-proprietary-attributes' => true,
        'new-blocklevel-tags' => 'article aside audio details figcaption figure footer header hgroup nav section source summary temp track video',
        'new-empty-tags' => 'command embed keygen source track wbr',
        'new-inline-tags' => 'audio canvas command datalist embed keygen mark meter output progress time video wbr',
    ];
    public static $JSON_PARAMS = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES |  JSON_UNESCAPED_UNICODE;
}
