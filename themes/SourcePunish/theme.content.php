<?php
function Theme_Content($Data) {
    global $Theme;
    $Theme->AddAttr($Data, 'class', 'section-content');

    $Build = '<section'.$Theme->PrintAttr($Data).'>'.PHP_EOL;
    if(isset($Data['title']))
        $Build .= '<header><h1>'.$Data['title'].'</h1></header>'.PHP_EOL;
    $Build .= '<div class="section-content-inner">'.$Data['text'].'</div>'.PHP_EOL;
    $Build .= '</section>'.PHP_EOL;
    return $Build;
}
?>