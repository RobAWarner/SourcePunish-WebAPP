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
});