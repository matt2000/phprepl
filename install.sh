#!/bin/bash
if [ "$(id -u)" != "0" ]; then
    echo "This script should be run as 'root'"
    exit 1
fi

mkdir -p /usr/share/php/phprepl
chmod a+x phprepl
chmod a+x drepl
cp * /usr/share/php/phprepl
ln -s /usr/share/php/phprepl/phprepl /usr/bin/phprepl
ln -s /usr/share/php/phprepl/drepl /usr/bin/drepl