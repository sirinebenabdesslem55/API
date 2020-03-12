function deletefile(event, file) {
   if ( confirm ("Voulez vous vraiment supprimer le fichier " + file +  "?" )) {
         self.location = "delete_event_file.php?number=" + event + "&file=" + file + "&type=evenement";
   }
}

function warning_cancel(checkbox) {
  if (checkbox.checked) {
     alert("Attention : vous devez renseigner la raison de cette annulation dans la case ci contre.\n(manque de secouristes, probl�me de mat�riel, annul� par l'organisateur ...)" );
    }
}

function validateMax()
{
    stagiaires=document.getElementById('stagiaires');
    nombre=document.getElementById('nombre');
    if ( parseInt(stagiaires.value, 10) > 99 ) {
        nombre.value=0;
    }
    else if ( parseInt(nombre.value, 10) > 0 && parseInt(stagiaires.value, 10) > parseInt(nombre.value, 10) ) {
        nombre.value=parseInt(stagiaires.value, 10);
    }
}

function decreaseMax()
{
    stagiaires=document.getElementById('stagiaires');
    nombre=document.getElementById('nombre');
    if ( parseInt(nombre.value, 10) > 0 && parseInt(nombre.value, 10) < parseInt(stagiaires.value, 10) ) {
        stagiaires.value=parseInt(nombre.value, 10);
    }
}
    
function change(previous_type)  {
    what=document.getElementById('type').value;
    display_or_not_rowcolonne();
    save=document.getElementById("sauver");
    vo=document.getElementById('visible_outside');
    rt=document.getElementById('rowtarif');
    rc2=document.getElementById('rowcomment2');
    rf1=document.getElementById('rowflag1');
    rf=document.getElementById('rowtarif');
    ru=document.getElementById('rowurl');
    rnbs=document.getElementById('rownbstagiaires');
    rfp=document.getElementById('rowforpour');
    rntf=document.getElementById('rowntypefor');
    fl1=document.getElementById('flag1');
    tarif=document.getElementById('tarif');
    ps=document.getElementById('ps');
    
    
    if  ( what == 'ALL' ) {
        save.disabled=true;
    }
    else {
        save.disabled=false;
    }
    if  (what == 'DPS' ) {
        vo.checked = false;
        rc2.style.display = 'none';
        rf1.style.display = '';
        rt.style.display = 'none';
        ru.style.display = 'none';
        rnbs.style.display = 'none';
        rfp.style.display = 'none';
        rntf.style.display = 'none';
        tarif.value='';
    }
    else if  (what == 'FOR' ) {
        change_ps();
        rf1.style.display = 'none';
        rt.style.display = '';
        ru.style.display = '';
        rnbs.style.display = '';
        rfp.style.display = '';
        rntf.style.display = '';
        fl1.checked = false;
    }
    else  {
        vo.checked = false;
        rc2.style.display = 'none';
        rf1.style.display = 'none';
        rt.style.display = 'none';
        ru.style.display = 'none';
        rnbs.style.display = 'none';
        rfp.style.display = 'none';
        rntf.style.display = 'none';
        fl1.checked = false;
        tarif.value='';
    }
}

function change_ps()  {
    type=document.getElementById('type');
    ps=document.getElementById('ps');
    vo=document.getElementById('visible_outside');
    rc2=document.getElementById('rowcomment2');
    lib=document.getElementById('libelle');
    var show_ext = [ '51', '33' ];
    if ( type.value == 'FOR' && show_ext.includes(ps.value) ) {
        if ( lib.value == '' ) {
            vo.checked = true;
            rc2.style.display = '';
        }
    }
    else {
        vo.checked = false;
        rc2.style.display = 'none';
    }
}

function showNextRow(debrow,finrow,prevplusrow,plusrow,flag,nextdeb) {
    document.getElementById(prevplusrow).style.display = 'none';
    document.getElementById(debrow).style.display = '';
    document.getElementById(finrow).style.display = '';
    if ( flag === 0 ) {
        if ( document.getElementById(nextdeb).style.display == 'none' ) {
            document.getElementById(plusrow).style.display = '';
        }
    }
}

function hideRow(debrow,finrow,prevplusrow,plusrow,date1,date2,debut,fin,duree) {
    document.getElementById(date1).value = '';
    document.getElementById(date2).value = '';
    document.getElementById(debut).value = '';
    document.getElementById(fin).value = '';
    document.getElementById(duree).value = '';
    document.getElementById(prevplusrow).style.display = '';
    document.getElementById(debrow).style.display = 'none';
    document.getElementById(finrow).style.display = 'none';
    document.getElementById(plusrow).style.display = 'none';
}

function makeHidden(checkbox) {
    vo=document.getElementById('visible_outside');
    r2=document.getElementById('rowcomment2');
    if (checkbox.checked) {
        vo.checked = false;
        vo.disabled = true;
        r2.style.display = 'none';
    }
    else {
        vo.disabled = false;
    }
}

function makeVisibleExternal(checkbox) {
    if (checkbox.checked) {
       document.getElementById('rowcomment2').style.display = '';
    }
    else {
       document.getElementById('rowcomment2').style.display = 'none';
    }
}

function updfin(dtdebut,dtfin) {
    checkDate2(dtdebut);
    if ( dtfin.value === '' ) {
        dtfin.value = dtdebut.value;
    }
    else {
        verifyDateRange(dtdebut,dtfin)
    }
}

function attacher_renfort() {
  if ( confirm("ATTENTION: Les �quipes �ventuellement cr��es sur cet �v�nement vont �tre perdues.\nVoulez vous quand m�me rattacher cet �v�nement en tant que renfort?\n")) {
        return true;
  }
  document.getElementById('parent').value="null";
  return false;
}

function checkURL(current) {
    url = document.getElementById('url');
    var res = url.value.substring(0,4);
    if ( res == 'http' ) {
        alert("L'adresse URL ne doit pas avoir un pr�fixe http ou https");
        url.value = current;
   }
}
