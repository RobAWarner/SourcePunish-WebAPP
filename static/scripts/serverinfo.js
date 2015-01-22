$(document).ready(function() {
    $('.table-servers > tbody tr').each(function() {
        if($(this).attr('data-sid') !== undefined) {
            $(this).addClass('clickable');
            $(this).click(function() {
                window.location.href = $(location).attr('pathname')+'?q=servers&id='+$(this).attr('data-sid');
            });
        }
    });
    /* Load the server query ajax */
    $.ajax({
		url:$(location).attr('pathname')+'?q=serverquery&ajax=1&nocache='+$.now(),
		type:'get',
		success: function(response, textStatus, jqXHR){
			$JSONOut = response;
			if($JSONOut.success == true){
				$.each($JSONOut.servers, function(ID, Info) {
                    $ServerRow = $('.table-servers-update').find('tr[data-sid=\''+ID+'\']');
                    if($ServerRow.length) {
                        if(Info.vac != 'undefined') {
                            if(Info.vac == 1) {
                                if($ServerRow.find('.s-info-vac').find('img').css('visibility') == 'hidden')
                                    $ServerRow.find('.s-info-vac').find('img').css('visibility', 'visible');
                            } else {
                                if($ServerRow.find('.s-info-vac').find('img').css('visibility') == 'visible')
                                    $ServerRow.find('.s-info-vac').find('img').css('visibility', 'hidden');
                            }
                        }
                        if(Info.hostname != 'undefined') {
                            if($ServerRow.find('.s-info-name').html() != Info.hostname)
                                $ServerRow.find('.s-info-name').html(Info.hostname);
                        }
                        if(Info.numplayers != 'undefined' && Info.maxplayers != 'undefined') {
                            $Players = ''+Info.numplayers;
                            $Players += '/'+Info.maxplayers;
                            if(Info.bots != 'undefined' && Info.bots > 0) {
                                $Players += ' <span title="bots">('+Info.bots+')</span>'
                            }
                            if($ServerRow.find('.s-info-players').html() != $Players) {
                                $ServerRow.find('.s-info-players').html($Players);
                                DoToolTip();
                            }
                        }
                        if(Info.map !== 'undefined') {
                            if($ServerRow.find('.s-info-map').html() != Info.map)
                                $ServerRow.find('.s-info-map').html(Info.map);
                        }
                    }
                });
			}
		}
		});
});