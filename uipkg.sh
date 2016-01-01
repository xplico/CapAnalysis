#!/bin/bash

WUI=www

# dir temporanea
mkdir -p tmp
rm -rf tmp/*

# copy the WUI
cp -a $WUI tmp/www

# min javascript and css
java -jar ./yuicompressor-2.4.8pre.jar tmp/www/app/webroot/css/bar-stack-chart.css -o tmp/www/app/webroot/css/bar-stack-chart.css
java -jar ./yuicompressor-2.4.8pre.jar tmp/www/app/webroot/css/capana.css -o tmp/www/app/webroot/css//capana.css
java -jar ./yuicompressor-2.4.8pre.jar tmp/www/app/webroot/css/jui.capana.css -o tmp/www/app/webroot/css/jui.capana.css
java -jar ./yuicompressor-2.4.8pre.jar tmp/www/app/webroot/css/infowhois.css -o tmp/www/app/webroot/css/infowhois.css
java -jar ./yuicompressor-2.4.8pre.jar tmp/www/app/webroot/js/bar-stack-chart.js -o tmp/www/app/webroot/js/bar-stack-chart.js
java -jar ./yuicompressor-2.4.8pre.jar tmp/www/app/webroot/js/capana.js -o tmp/www/app/webroot/js/capana.js
java -jar ./yuicompressor-2.4.8pre.jar tmp/www/app/webroot/js/sankey.js -o tmp/www/app/webroot/js/sankey.js
java -jar ./yuicompressor-2.4.8pre.jar tmp/www/app/webroot/js/world-countries.js -o tmp/www/app/webroot/js/world-countries.js


# tgz of WUI
cd tmp/ 
tar czf pkginstall.tgz www

mv pkginstall.tgz pkginstall

# wui as C array
xxd -i pkginstall > ../include/pkgbin.h

# clean
cd ..
rm -rf tmp/*


