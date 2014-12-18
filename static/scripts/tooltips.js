//Tool Tips
function DoToolTip() {
    $('[title][title!=]:not(.notooltip):not(.notooltip *)').each(function(index) {
        $ToolTipItem = $(this);
        $ToolTipItem.on('mousemove', function(mouse) {
            $ToolDiv = $('#tooltip');
            $(this).css('cursor', 'help');
            $ToolDiv.find('#tooltip-content').html($(this).attr('title'));
            $(this).attr('data-title', $(this).attr('title')).removeAttr('title');
            $newy = (mouse.pageY + $(this).height() + 5);
            if(($(this).offset().top + $(this).outerHeight() + $ToolDiv.outerHeight()) > $('#wrapper-main').height()) {
                $newy = (mouse.pageY - $ToolDiv.outerHeight()) - 5;
                $ToolDiv.find('#tooltip-arrow').hide(0);
                $ToolDiv.find('#tooltip-arrow-bottom').show(0);
            }
            $newx = mouse.pageX;
            if((mouse.pageX + $ToolDiv.outerWidth()) > $('#wrapper-main').outerWidth()+(($(document).width()-$('#wrapper-main').outerWidth())/2)) {
                $newx = ($('#wrapper-main').outerWidth()+(($(document).width()-$('#wrapper-main').outerWidth())/2))-$ToolDiv.outerWidth();
            }
            $ToolDiv.css({ top: $newy, left: $newx });
            $ToolDiv.fadeIn('fast');
        });
        $ToolTipItem.on('mouseleave mouseout', function() {
            $(this).attr('title', $(this).attr('data-title'));
            $('#tooltip').stop().fadeOut('fast', function() {
                $('#tooltip').find('#tooltip-arrow').show(0);
                $('#tooltip').find('#tooltip-arrow-bottom').hide(0);
            }); 
        });
    });
}
$(document).ready(function() {
    /* Add the tooltip container to the page */
    $('body').prepend('<div id="tooltip"><div id="tooltip-arrow"></div><div id="tooltip-content"></div><div id="tooltip-arrow-bottom"></div></div>');
    /* Initiate the tooltips */
    DoToolTip();
    /* Hide the title for elements with .notooltip */
    $('.notooltip').each(function(index) {
        $(this).attr('data-title', $(this).attr('title')).removeAttr('title');
        $(this).find('[title]').each(function(index) {
            $(this).attr('data-title', $(this).attr('title')).removeAttr('title');
        });
    });
});
