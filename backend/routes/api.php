<?php

foreach (glob(__DIR__.'/apis/*.php') as $routeFile) {
    require $routeFile;
}
