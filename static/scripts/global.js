$(document).ready(function() {
    // Make punish table links clickable
    $('.table-punish > tbody tr').each(function() {
        if($(this).attr('data-pid') !== undefined) {
            $(this).addClass('clickable');
            $(this).click(function() {
                window.location.href = $(location).attr('pathname')+'?q=view&id='+$(this).attr('data-pid');
            });
        }
    });
    // Hide title for elements with .notooltip
    $('.notooltip').each(function(index) {
        $(this).attr('data-title', $(this).attr('title')).removeAttr('title');
        $(this).find('[title]').each(function(index) {
            $(this).attr('data-title', $(this).attr('title')).removeAttr('title');
        });
    });
    // Submenus should be the width of the parent
    $('nav ul li ul').each(function(index) {
        $Parent = $(this).parent().width();
        $(this).find('li').css('min-width', $Parent+'px');
    });
});