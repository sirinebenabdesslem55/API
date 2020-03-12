//-- Global Variables
var RowsInForm = 5

function fillmenu(frm, menu1,menu2,person) {
year=frm.menu1.options[frm.menu1.selectedIndex].value;
month=frm.menu2.options[frm.menu2.selectedIndex].value;
url = "dispo.php?month="+month+"&year="+year+"&person="+person;
self.location.href = url;
}

//=====================================================================
// Mise à jour des totaux
//=====================================================================

//-- Updates the totals in the lower part of table.
function updateTotal(mybox,totalbox) {
    var V = parseInt(totalbox.value);
    if ( mybox.checked ) {
          totalbox.value = V + 1;
    }
    else {
        totalbox.value = V - 1;
    }
}

//=====================================================================
// choix personne
//=====================================================================
function redirect(p1,p2,p3,p4,p5,p6) {
     if ( p4 == 'saisie' ) {
         url="dispo.php?person="+p1+"&month="+p2+"&year="+p3;
         self.location.href=url;
     }
     if ( p4 == 'ouvrir' ) {
        if ( confirm ("Attention : Vous allez permettre la saisie des disponibilités pour le mois "+p2+"/"+p3+" par le personnel de "+p6+".\nLes agents pourront de nouveau modifier leur disponibilités.\nConfirmer ?" )) {
          cible="tableau_garde_status.php?month="+p2+"&year="+p3+"&action=ouvrir&filter="+p5+"&person="+p1;
          self.location.href = cible;
        }
     }
      if ( p4 == 'fermer' ) {
        if ( confirm ("Attention : Vous allez bloquer la saisie des disponibilités pour le mois "+p2+"/"+p3+" par le personnel de "+p6+".\nLes agents ne pourront plus saisir ou modifier leur disponibilités pour le mois suivant.\nConfirmer ?" )) {
            cible="tableau_garde_status.php?month="+p2+"&year="+p3+"&action=fermer&filter="+p5+"&person="+p1;
            self.location.href = cible;
        }
     }
     
}

//=====================================================================
// check all
//=====================================================================
function CheckAll(field,checkValue){
    var dForm = document.dispo;
    var F = 'total'+field;
    var V = document.getElementById(F).value;
    
    // Vérif du compteur
    document.getElementById(F).value = ((checkValue!=true)? V:0 );

    // Parcours des jours et mise à jour des cases à cocher
    for (i=0;i<dForm.length;i++)
    {
        var element = dForm[i];
        if (element.type=='checkbox'){
            var G = 'total'+element.name.substring(0,1);
            var B = document.getElementById(G);
            if (element.name.substring(0,1)==field){
                if ( element.disabled == false ) {
                    element.checked = ((checkValue!=true)?false:true);
                    updateTotal(element,B);
                }
            }    
        }
    }
}