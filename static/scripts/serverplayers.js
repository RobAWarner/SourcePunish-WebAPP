$(document).ready(function() {
    /* Load the server query ajax */
    $PlayerContainer = $('#player-list-container');
    $ServerID = $PlayerContainer.attr('data-sid');
    if($ServerID.length) {
        $.ajax({
            url:$(location).attr('pathname')+'?q=serverquery&ajax=1&type=playerlist&id='+$ServerID+'&nocache='+$.now(),
            type:'get',
            success: function(response, textStatus, jqXHR){
                $JSONOut = response;
                if($JSONOut.success == true){
                    if($PlayerContainer.length) {
                        if($JSONOut.players != 'undefined') {
                            $PlayerContainer.html($JSONOut.players);
                        }
                    }
                }
            }
        });
    }
});