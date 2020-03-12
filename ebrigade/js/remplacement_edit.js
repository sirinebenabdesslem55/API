function update(rid, evenement, action) {
    var substitute = document.getElementById('substitute').value;
    var periode = $('input[name=periode]:checked').val();
    var param1 = "?action="+ action + "&rid=" + rid + "&evenement=" + evenement;
    var param2 = "";
    if ( action == 'update' || action == 'validate' || action == 'accept' ) {
        param2 ="&substitute=" + substitute + "&periode=" + periode;
    }
    url = "remplacement_edit.php" + param1 + param2;
    self.location = url;
}

function create(evenement, status) {
    var replaced = document.getElementById('replaced').value;
    var substitute = document.getElementById('substitute').value;
    var periode = $('input[name=periode]:checked').val();
    if ( status == 'demande' ) {
        url = "remplacement_edit.php?action=create&evenement=" + evenement + "&replaced=" + replaced + "&substitute=" + substitute + "&periode=" + periode;
    }
    else {
        url = "remplacement_edit.php?action=create_validate&evenement=" + evenement + "&replaced=" + replaced + "&substitute=" + substitute + "&periode=" + periode;
    }
    self.location = url;    
}

function reload(rid, evenement, action) {
    var replaced = document.getElementById('replaced').value;
    var periode = $('input[name=periode]:checked').val();
    var param1 = "?action="+ action + "&rid=" + rid + "&evenement=" + evenement;
    var param2 = "&replaced=" + replaced + "&periode=" + periode;
    url = "remplacement_edit.php" + param1 + param2;
    self.location = url;
}