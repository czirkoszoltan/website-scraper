#!/bin/bash

if [ "$1" = "" ]; then
    echo "Mirrors a web page to be used with the scraper. Usage:"
    echo "  $0 http://example.com/"
    echo "All extra arguments will be forwarded to wget."
    echo "Examples: --exclude-directories '/forum,/whatever', --reject 'filename,something'"
    exit 1
fi

wget -e 'robots=off' --no-verbose --mirror --wait=0.1 --restrict-file-names=unix,ascii --convert-links --adjust-extension $*
