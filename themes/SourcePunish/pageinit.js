$(document).ready(function() {
    /* Submenus should be the width of the parent */
    $('nav ul li ul').each(function(index) {
        $Parent = $(this).parent().width();
        $(this).find('li').css('min-width', $Parent+'px');
    });
});