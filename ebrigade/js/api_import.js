function redirect(cible) {
    self.location.href = cible;
}

function run_import() {
    var number=document.getElementById('number').value;
    var pid=document.getElementById('pid').value;
    var start=document.getElementById('start').value;
    if ( number == 1 ) {
        if ( parseInt(pid) == 'NaN' || pid == '') alert('Numero de fiche invalide');
        else {
            url='import_api.php?pid='+pid;
        }
    }
    else {
        if ( parseInt(start) == 'NaN' || start == '') alert('Numero de départ invalide');
        url='import_api.php?number='+number+'&start='+start;
    }
    self.location.href = url;
}

function changenumber() {
    var divpid = document.getElementById('divpid');
    var divstart = document.getElementById('divstart');
    var number = document.getElementById('number').value;
    if ( number == 1 ) {
        divpid.style.display = '';
        divstart.style.display = 'none';
    }
    else {
        divpid.style.display = 'none';
        divstart.style.display = '';
    }
}