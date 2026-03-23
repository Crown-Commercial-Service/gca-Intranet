function TCMP_btnDeleteClick(id) {
    var success=confirm(delete_data.confirm);
    if(success) {
        var href=delete_data.href + '&tcmp_nonce=' + delete_data.nonce + '&action=delete&id=';
        location.href=href+id;
    }
}