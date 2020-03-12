function redirect(url) {
    self.location.href=url;
}

function suppress(code) {
    if ( confirm("Voulez vous vraiment supprimer ce type d'événement?") ) {
        url="del_type_evenement.php?TE_CODE="+code;
        self.location.href=url;
    }
}

function delete_stat(id,code) {
    if ( confirm("Voulez vous vraiment supprimer cette statistique? Tous les enregistrements saisis sur les événements de type "+code+" pour cette statistique seront aussi effacés?") ) {
        url="delete_statistique.php?TB_ID="+id;
        self.location.href=url;
    }
}

function orderfilter(p1){
    self.location.href="type_evenement.php?order="+p1;
    return true
}
function displaymanager(p1){
    self.location.href="upd_type_evenement.php?TE_CODE="+p1;
    return true
}

function bouton_redirect(cible) {
    self.location.href = cible;
}

function goback(operation,code) {
    if (operation == 'insert' ) {
        url="upd_type_evenement.php?operation=insert";
    }
    else {
        url="upd_type_evenement.php?TE_CODE=" + code;
    }
    self.location.href=url;
}

function changedRapport() {
    var checkBox = document.getElementById("TE_MAIN_COURANTE");
    if ( checkBox.checked ) {
        $("#TE_VICTIMES").removeAttr('disabled');
        $(".statRow").show("slow");
    }
    else {
        $("#TE_VICTIMES").attr('disabled','disabled');
        $("#TE_VICTIMES").attr('checked', false);
        $(".statRow").hide("fast");
    }
}