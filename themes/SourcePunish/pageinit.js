$(document).ready(function() {
    /* Submenus should be the width of the parent */
    $('nav ul li ul').each(function(index) {
        $Parent = $(this).parent().width();
        $(this).find('li').css('min-width', $Parent+'px');
    });

    /* Dropdown Menus */
    $NavMain = $('#nav-main > ul:first-child').menu({position: { my: "left top", at: "left-1 bottom" }});
    $NavUser = $('#nav-user > ul:first-child').menu({position: { my: "right top", at: "right+1 bottom" }});

    $NavMain.mouseleave(function() {$NavMain.menu('collapseAll');});
    $NavUser.mouseleave(function() {$NavUser.menu('collapseAll');});
});