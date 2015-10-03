<?php
/*{{BOILER}}*/

/*{{CORE_REQUIRED}}*/

function Theme_Page($Array) {
    $Build = '<!DOCTYPE html>'.PHP_EOL;
    $Build .= '<html lang="en">'.PHP_EOL;
    $Build .= '<head>'.PHP_EOL;
    $Build .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.PHP_EOL;
    $Build .= '<meta charset="utf-8">'.PHP_EOL;
    $Build .= $Array['headers'];
    $Build .= '</head>'.PHP_EOL;
    $Build .= '<body>'.PHP_EOL;
    $Build .= '<div id="wrapper-main">'.PHP_EOL;
    $Build .= '<header id="header-main">'.PHP_EOL;
    $Build .= '<div id="top-bar">'.PHP_EOL;
    $Build .= '<div id="top-bar-stats">'.$Array['stats'].'</div>'.PHP_EOL;
    $Build .= '<nav id="nav-user" class="notooltip">'.PHP_EOL;
    $Build .= $Array['usernav'];
    $Build .= '</nav>'.PHP_EOL;
    $Build .= '</div>'.PHP_EOL;
    $Build .= '<div id="logo-main">'.PHP_EOL;
    $Build .= $Array['header'];
    $Build .= '</div>'.PHP_EOL;
    $Build .= '<div id="bottom-bar">'.PHP_EOL;
    $Build .= '<nav id="nav-main" class="notooltip">'.PHP_EOL;
    $Build .= $Array['mainnav'];
    $Build .= '</nav>'.PHP_EOL;
    $Build .= '</div>'.PHP_EOL;
    $Build .= '</header>'.PHP_EOL;
    $Build .= '<div class="wrapper-content">'.PHP_EOL;
    $Build .= $Array['content'];
    $Build .= '</div>'.PHP_EOL;
    $Build .= '<footer id="footer">'.PHP_EOL;
    $Build .= $Array['footer'];
    $Build .= '</footer>'.PHP_EOL;
    $Build .= '</div>'.PHP_EOL;
    $Build .= $Array['late-load'];
    $Build .= '</body>'.PHP_EOL;
    $Build .= '</html>';
    return $Build;
}
?>