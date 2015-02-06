# evePath
evePath is a simple PHP pathfinding library for EVE Online

# Requirements
* PHP
* Nice to have: memcache reduces library init time by a lot

There is no static database needed, all the data is loaded from json files.

# Usage
include evePath.php in your script first.

$evePath = new evePath();

$route = $evePath->getPath(30003591, 30003870, 0.5);
echo join(",", $route);

# Functions
getPath($fromSystemID, $toSystemID, $minSecurity = 0.5)

retruns the systemIDs as an array if there is a path, otherwise the array is empty.

# Performance
It takes my server aprox 0.2 seconds to initialize the library (if it is cached in memcache 0.02 seconds) and aprox 0.004 seconds to get a path.

# Based upon
This code is based upon https://github.com/fuzzysteve/Eve-Route-Plan
I mostly made it a bit more streamlined and added the minSecurity option.
