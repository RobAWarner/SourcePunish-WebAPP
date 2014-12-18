$(document).ready(function() {
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
                                if($ServerRow.find('td:nth-child(1)').find('img').css('visibility') == 'hidden')
                                    $ServerRow.find('td:nth-child(1)').find('img').css('visibility', 'visible');
                            } else {
                                if($ServerRow.find('td:nth-child(1)').find('img').css('visibility') == 'visible')
                                    $ServerRow.find('td:nth-child(1)').find('img').css('visibility', 'hidden');
                            }
                        }
                        if(Info.hostname != 'undefined') {
                            if($ServerRow.find('td:nth-child(3)').html() != Info.hostname)
                                $ServerRow.find('td:nth-child(3)').html(Info.hostname);
                        }
                        if(Info.numplayers != 'undefined' && Info.maxplayers != 'undefined') {
                            if($ServerRow.find('td:nth-child(5)').html() != Info.numplayers+'/'+Info.maxplayers)
                                $ServerRow.find('td:nth-child(5)').html(Info.numplayers+'/'+Info.maxplayers);
                        }
                        if(Info.map !== 'undefined') {
                            if($ServerRow.find('td:nth-child(6)').html() != Info.map)
                                $ServerRow.find('td:nth-child(6)').html(Info.map);
                        }
                    }
                });
			}
		}
		});
});