var format_usb_internal = function(colNumber, row){
    var col = $('td:eq('+colNumber+')', row),
        colvar = col.text();
    colvar = colvar == '1' ? i18n.t('internal') :
    colvar = (colvar == '0' ? i18n.t('external') : colvar)
    col.text(colvar)
}

var format_usb_yes_no = function(colNumber, row){
    var col = $('td:eq('+colNumber+')', row),
        colvar = col.text();
    colvar = colvar == '0' ? i18n.t('no') :
    colvar = (colvar == '1' ? i18n.t('yes') : colvar)
    col.text(colvar)
}