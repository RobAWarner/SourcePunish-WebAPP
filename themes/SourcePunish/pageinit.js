$(document).ready(function() {
    /* Submenus should be the width of the parent */
    $('nav ul li ul').each(function(index) {
        $Parent = $(this).parent().width();
        $(this).find('li').css('min-width', $Parent+'px');
    });
    /* Submenus should be there full width on the right of the page */
    $('#nav-user ul li ul').each(function(index) {
        
        /*$Margin = $(this).width()-$(this).parent().width();
        $(this).css('margin-left', '-'+$Margin+'px');*/
        
        /*---*/
        /*$Width = $(this).outerWidth()+10;
        $MenuWidth = $(this).parent().parent().outerWidth();
        $ItemWidth = $(this).parent().outerWidth();
        $MenuPos = $(this).parent().position().left + (($(this).parent().outerWidth() - $ItemWidth));
        $Space = ($MenuWidth - $MenuPos) - $ItemWidth;

        if($Width > $Space)
            $(this).css('margin-left', '-'+($Width-$Space)+'px');*/
            
        /*$MainMenuWidth = $(this).parent().parent().outerWidth();
        $MenuPos = $(this).parent().position().left;
        $ItemWidth = $(this).outerWidth() + 10;
        $Space = ($MainMenuWidth - $MenuPos) - $(this).parent().outerWidth();
        
        if($ItemWidth > $Space)
            $(this).css('margin-left', ''+($Space-$ItemWidth)+'px');*/
    });

    $NavMain = $('#nav-main > ul:first-child').menu({position: { my: "left top", at: "left-1 bottom" }});
    $NavUser = $('#nav-user > ul:first-child').menu({position: { my: "right top", at: "right+1 bottom" }});

    $NavMain.mouseleave(function() {$NavMain.menu('collapseAll');});
    $NavUser.mouseleave(function() {$NavUser.menu('collapseAll');});
});