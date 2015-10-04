<?php
/*{{BOILER}}*/

/*{{CORE_REQUIRED}}*/

/* Available:
    type - What the image represents. E.G: 'flag',
    for - A known parent/container for the image. E.G: '#table-punish-list' / 'button',
    attr -
        src - The image source
        alt - Alternate text for the image
        * - Can also container class, id, data etc
    tooltip (optional) - Optional text to be used for a title/tooltip
*/
function Theme_Nav_Main($Data, $Level = 1) {
    global $Theme;
    $Theme->AddAttr($Data, 'class', 'nav-level-'.$Level);
    $Build = '<ul'.$Theme->PrintAttr($Data).'>'.PHP_EOL;
    foreach($Data['items'] as $Nav) {
        if($Nav['active'])
            $Theme->AddAttr($Nav, 'class', 'active');
        $Build .= '<li'.$Theme->PrintAttr($Nav).'>'.PHP_EOL;
        $Build .= '<a'.$Theme->PrintAttr($Nav['link']).'>'.$Nav['link']['text'].'</a>'.PHP_EOL;
        if(isset($Nav['children']))
            $Build .= Theme_Nav_Main($Nav['children'], ($Level + 1));
        $Build .= '</li>'.PHP_EOL;
    }
    $Build .= '</ul>'.PHP_EOL;
    return $Build;
}

?>
