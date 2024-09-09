<div id="usb-tab"></div>
<h2 data-i18n="usb.clienttab"></h2>
<div id="usb-msg" data-i18n="listing.loading" class="col-lg-12 text-center"></div>

<script>
$(document).on('appReady', function(){
    $.getJSON(appUrl + '/module/usb/get_data/' + serialNumber, function(data){

         // Check if we have data
        if( data == "" || ! data || data.length == 0 || data.length == "0"){
            $('#usb-msg').text(i18n.t('usb.nousb'));
        } else {

            // Hide
            $('#usb-msg').text('');
            $('#usb-count-view').removeClass('hide');

            // Set count of USB devices
            $('#usb-cnt').text(data.length);
            $.each(data, function(i,d){

                // Generate rows from data
                var rows = ''
                for (var prop in d){
                    if (d[prop] == null || prop == 'name'){
                        // Do nothing for blank data
                        rows = rows
                    }
                    else if(prop == 'usb_serial_number' && d[prop] == ''){
                       // Do nothing for a blank device serial number
                    }
                    else if((prop == 'internal' || prop == 'media' || prop == 'connected') && d[prop] == 1){
                       rows = rows + '<tr><th>'+i18n.t('usb.'+prop)+'</th><td>'+i18n.t('yes')+'</td></tr>';
                    }
                    else if((prop == 'internal' || prop == 'media' || prop == 'connected') && d[prop] == 0){
                       rows = rows + '<tr><th>'+i18n.t('usb.'+prop)+'</th><td>'+i18n.t('no')+'</td></tr>';
                    }
                    else if(prop == 'timestamp' && d[prop] > 0){
                       var date = new Date(d[prop] * 1000);
                       rows = rows + '<tr><th>'+i18n.t('usb.'+prop)+'</th><td><span title="'+date+'">'+moment(date).fromNow()+'</td></tr>';
                    }
                    else {
                        rows = rows + '<tr><th>'+i18n.t('usb.'+prop)+'</th><td>'+d[prop]+'</td></tr>';
                    }
                }
                $('#usb-tab')
                    .append($('<h4>')
                        .append($('<i>')
                            .addClass('fa fa-usb'))
                        .append(' '+d.name))
                    .append($('<div style="max-width:500px;">')
                        .addClass('table-responsive')
                        .append($('<table>')
                            .addClass('table table-striped table-condensed')
                            .append($('<tbody>')
                                .append(rows))))
            })
        }
    });
});
</script>
