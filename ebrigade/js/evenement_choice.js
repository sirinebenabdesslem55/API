function redirect(type, section, sub, debut, fin, can, company, renforts) {
    var psid = document.getElementById('ps').value;
    url = "evenement_choice.php?type_evenement="+type+"&filter="+section+"&dtdb="+debut+"&subsections="+sub+"&dtfn="+fin+"&canceled="+can+"&company="+company+"&renforts="+renforts+"&competence="+psid;
    self.location.href = url;
}
function redirect2(type, section, sub, debut, fin, can, company, renforts) {
    if (sub.checked) s = 1;
    else s = 0;
    url = "evenement_choice.php?type_evenement="+type+"&filter="+section+"&dtdb="+debut+"&subsections="+s+"&dtfn="+fin+"&canceled="+can+"&company="+company+"&renforts="+renforts;
    self.location.href = url;
}
function redirect3(type, section, sub, debut, fin, can, company, renforts) {
    if (can.checked) c = 1;
    else c = 0;
    url = "evenement_choice.php?type_evenement="+type+"&filter="+section+"&dtdb="+debut+"&subsections="+sub+"&dtfn="+fin+"&canceled="+c+"&company="+company+"&renforts="+renforts;
    self.location.href = url;
}
function redirect4(type, section, sub, debut, fin, can, company, renforts) {
    if (renforts.checked) r = 1;
    else r = 0;
    url = "evenement_choice.php?type_evenement="+type+"&filter="+section+"&dtdb="+debut+"&subsections="+sub+"&dtfn="+fin+"&canceled="+can+"&company="+company+"&renforts="+r;
    self.location.href = url;
}

function bouton_redirect(cible) {
     self.location.href = cible;
}
function impression(){ 
   this.print();
}
function DelCalConfirm(){
    var agree=confirm("Etes-vous s�r de vouloir supprimer ce(s) calendrier(s) de vos pr�f�rences ?");
    if (agree) {
        document.getElementById('delCal').value = '1';
        document.formf.submit();
        return true;
    }
    else
        return false ;
}