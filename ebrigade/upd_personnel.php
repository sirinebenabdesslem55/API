<?php

  # project: eBrigade
  # homepage: http://sourceforge.net/projects/ebrigade/
  # version: 5.1

  # Copyright (C) 2004, 2020 Nicolas MARCHE
  # This program is free software; you can redistribute it and/or modify
  # it under the terms of the GNU General Public License as published by
  # the Free Software Foundation; either version 2 of the License, or
  # (at your option) any later version.
  #
  # This program is distributed in the hope that it will be useful,
  # but WITHOUT ANY WARRANTY; without even the implied warranty of
  # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  # GNU General Public License for more details.
  # You should have received a copy of the GNU General Public License
  # along with this program; if not, write to the Free Software
  # Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

include_once ("config.php");

check_all(0);
writehead();

$id=$_SESSION['id'];

$pompier=$id;
if ( isset($_GET["self"])) $pompier=$id;
else if (isset($_GET["id"])) $pompier=intval($_GET["id"]);
else if (isset ($_GET['pompier'])) $pompier=intval($_GET["pompier"]);
else if (isset ($_SESSION['pompier'])) $pompier=$_SESSION['pompier'];

if ( $pompier > 0 ) $_SESSION['pompier']=$pompier;
else unset ($_SESSION['pompier']);

if ( isset($_GET['ipp'])) $_SESSION["ipp"]=$_GET['ipp'];
if ( isset($_GET['page'])) $_SESSION["page"]=intval($_GET['page']);

if ( isset($_GET["from"])) {
    $from=$_GET["from"]; //qualif, inscriptions
    unset($_SESSION['from_inscriptions']);
    unset($_SESSION['from_cotisation']);
    unset($_SESSION['from_notes_de_frais']);
}
else $from="default";

$SES_NOM=$_SESSION['SES_NOM'];
$SES_PRENOM=$_SESSION['SES_PRENOM'];
$SES_GRADE=$_SESSION['SES_GRADE'];
$browser=$_SESSION['SES_BROWSER'];
$section=$_SESSION['SES_SECTION'];
$mycompany=$_SESSION['SES_COMPANY'];
$myparent=$_SESSION['SES_PARENT'];
$his_section=get_section_of("$pompier");

echo "
<script type='text/javascript' src='js/checkForm.js?version=".$version."'></script>
<script type='text/javascript' src='js/popupBoxes.js?version=".$version."'></script>
<script type='text/javascript' src='js/personnel.js?version=".$version."?patch=".$patch_version."e'></script>
<script type='text/javascript' src='js/zipcode.js?version=".$version."'></script>
<script type='text/javascript' src='js/ddslick.js?version=".$version."'></script>
<script type='text/javascript'>
$(document).ready(function(){
    $('[data-toggle=\"popover\"]').popover();
});
</script>";
echo "
<STYLE type='text/css'>
.categorie{color:".$mydarkcolor."; background-color:".$mylightcolor.";font-size:10pt;}
.type{color:".$mydarkcolor."; background-color:white;font-size:10pt;}
.inputRIB-lg2 { width: 25px; }
.inputRIB-lg4 { width: 41px; }
.inputRIB-lg5 { width: 50px; }
.inputRIB-lg11 { width: 120px; }
tr {background-color:".$mylightcolor.";}
tr.grey {background-color:#b3b3b3;}
.white {background-color:white;}
</STYLE>
";

// test permission visible
if ($id == $pompier) $allowed=true;
else if ( $mycompany == get_company($pompier) and check_rights($id, 45) and $mycompany > 0) {
    $allowed=true;
}
else {
    check_all(56);
    if ( ! check_rights($id,56,$his_section ))
        if ( $his_section <> $myparent and get_section_parent($his_section) <> $myparent )
            check_all(40);
}

if ( isset ( $_GET['order'])) {
    $order = secure_input($dbc,$_GET['order']);
    $tab=3;
    $from = 'formations';
}
else $order='PF_DATE';

// check input parameters
$pompier=intval(secure_input($dbc,$pompier));
if ( $pompier == 0 ) {
    param_error_msg();
    exit;
}

// which tab should we display?
if ( isset ( $_SESSION['from_notes_de_frais'])) {
    $from ='notes_de_frais';
    unset($_SESSION['from_notes_de_frais']);
}

if ( isset ( $_SESSION['from_cotisation'])) {
    $from ='cotisation';
    unset($_SESSION['from_cotisation']);
}

if ( isset ( $_SESSION['from_inscriptions'])) {
    $from ='inscriptions';
    unset($_SESSION['from_inscriptions']);
}

if ( $from <> 'inscriptions'  and $from <> 'cotisation' ) $_SESSION["page"]=1;

if ( isset($_GET["tab"])) $tab=intval($_GET["tab"]);
else if ( $from == 'cotisation' or $from == 'exportcotisation') $tab = 8;
else if ( $from == 'qualif' ) $tab = 2;
else if ( $from == 'formations' ) $tab = 3;
else if ( $from == 'inscriptions' ) $tab = 4;
else if ( $from == 'vehicules' ) $tab = 5;
else if ( $from == 'tenues' ) $tab = 10;
else if ( $from == 'document' )  $tab = 6;
else if ( $from == 'notes_de_frais' ) $tab = 9;
else $tab=1;
if ( intval($tab) == 0 ) $tab = 1;
if ( $tab <> 4 ) unset($_SESSION['from_inscriptions']);
if ( $tab <> 8 ) unset($_SESSION['from_cotisation']);
if ( $tab <> 9 ) unset($_SESSION['from_notes_de_frais']);

// ===========================================
// read permissions
// ===========================================

if (check_rights($id,59,"$his_section") or ($id == $pompier and check_rights($id,77))) $compta_visible=true;
else $compta_visible=false;
if (check_rights($id,4,"$his_section") or $id == $pompier ) $formations_visible=true;
else if (check_rights($id,37,"$his_section") and  get_statut ($pompier) == 'EXT' ) $formations_visible=true;
else $formations_visible=false;
if (check_rights($id,70,"$his_section") or check_rights($id,17,"$his_section") or $id == $pompier ) $materiel_visible=true;
else $materiel_visible=false;
if (check_rights($id,15,"$his_section") or $id == $pompier ) $evenements_visible=true;
else if (check_rights($id,15)){
    // un responsable d'antenne voit les participations du département
    if ( is_children($his_section ,$_SESSION['SES_PARENT']) or $his_section == $_SESSION['SES_PARENT'] ) $evenements_visible=true;
    else $evenements_visible=false;
}
else $evenements_visible=false;
if (check_rights($id,2,"$his_section") or $id == $pompier ) $documents_visible=true;
else $documents_visible=false;

// ===========================================
// counters
// ===========================================
$NB1=count_entities("qualification", "P_ID=".$pompier);
$NB2=count_entities("personnel_formation", "P_ID=".$pompier);
$NB3=count_entities("evenement_participation ep, evenement e, evenement_horaire eh",
   "ep.P_ID=".$pompier." and eh.e_code = e.e_code and ep.eh_id=eh.eh_id and ep.E_CODE=e.E_CODE and e.E_CANCELED = 0 and ep.EP_ABSENT=0 and e.TE_CODE <> 'MC'");
$NB3=$NB3+count_entities("astreinte a", "a.P_ID=".$pompier);
$NB4=count_entities("vehicule v, vehicule_position vp", "v.VP_ID = vp.VP_ID and vp.VP_OPERATIONNEL >= 0  and v.AFFECTED_TO=".$pompier);
$NB4=$NB4+count_entities("materiel m, vehicule_position vp", "m.VP_ID = vp.VP_ID and vp.VP_OPERATIONNEL >= 0
    and m.TM_ID in (select TM_ID from type_materiel where TM_USAGE <> 'Habillement') and m.AFFECTED_TO=".$pompier);
$NB4=$NB4+count_entities("pompier", "P_OLD_MEMBER=0 and P_MAITRE=".$pompier);
$NB41=count_entities("materiel m", "m.TM_ID in (select TM_ID from type_materiel where TM_USAGE = 'Habillement') and m.AFFECTED_TO=".$pompier);
$NB5=count_entities("document", "P_ID=".$pompier);
$NB6=count_entities("compte_bancaire", "CB_TYPE='P' and CB_ID=".$pompier);
$NB7=count_entities("rejet", "REGULARISE=0 and P_ID=".$pompier);
$NB8=count_entities("personnel_cotisation", "REMBOURSEMENT = 0 and P_ID=".$pompier);
$NB9=count_entities("personnel_cotisation", "REMBOURSEMENT = 0 and P_ID=".$pompier." and ANNEE='".date('Y')."'");
$NB10=count_entities("note_de_frais", "P_ID=".$pompier);

$query="select min(ANNEE) from personnel_cotisation
    where REMBOURSEMENT = 0 and P_ID=".$pompier;
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$START = intval($row[0]);
if ( $START > 0 ) $NB11 = date('Y') - $START;
else $NB11 = 0;

$show_carte= false;
$show_courrier_adherent= false;
if ( $syndicate == 1 and $documents_visible ) {
    if ( file_exists($basedir."/images/user-specific/carte_adherent.pdf")) {
        $NB5 = $NB5 + 1;
        $show_carte= true;
    }
    $query_asa=get_asa_query($pompier);
    $result_asa=mysqli_query($dbc,$query_asa);
    $NB12= 2 * mysqli_num_rows($result_asa);
    
    if ( file_exists($basedir."/images/user-specific/courrier_nouvel_adherent_prelevement.pdf") ) {
        $NB5 = $NB5 + 1;
        $show_courrier_adherent= true;
    }
}
else 
    $NB12=0;

// ===========================================
// get all data
// ===========================================

$query="select distinct p.P_CODE ,p.P_ID , p.P_NOM , p.P_NOM_NAISSANCE, p.P_PRENOM, p.P_PRENOM2, p.P_GRADE, p.P_HIDE, p.P_SEXE,
           DATE_FORMAT(p.P_BIRTHDATE, '%d-%m-%Y') as P_BIRTHDATE , p.P_BIRTHDATE RAWBIRTHDATE, p.P_BIRTHPLACE, p.P_BIRTH_DEP, p.P_OLD_MEMBER, tm.TM_CODE,
           g.G_DESCRIPTION as P_DESCRIPTION, DATE_FORMAT(p.P_LAST_CONNECT,'%d-%m-%Y %H:%i') P_LAST_CONNECT, p.P_NB_CONNECT,
           DATE_FORMAT(p.P_ACCEPT_DATE,'le %d-%m-%Y à %H:%i') P_ACCEPT_DATE,
           p.P_STATUT, s1.S_DESCRIPTION as P_DESC_STATUT , DATE_FORMAT(p.P_DATE_ENGAGEMENT, '%d-%m-%Y') P_DATE_ENGAGEMENT, G_TYPE, p.P_SECTION,
           s2.S_DESCRIPTION as P_DESC_SECTION, c.C_NAME,
           g1.GP_DESCRIPTION P_GP_DESCRIPTION, g2.GP_DESCRIPTION P_GP_DESCRIPTION2, p.GP_ID as P_GP_ID, p.GP_ID2 as P_GP_ID2,
           p.P_EMAIL, p.P_PHONE,p.P_PHONE2, p.P_ABBREGE, DATE_FORMAT(p.P_FIN,'%d-%m-%Y') as P_FIN,
           p.P_ADDRESS, p.P_ZIP_CODE, p.P_CITY, DATE_FORMAT(p.P_CREATE_DATE,'%d-%m-%Y' ) P_CREATE_DATE,
           p.TS_CODE, p.TS_HEURES, p.TS_JOURS_CP_PAR_AN, p.TS_HEURES_PAR_AN, p.TS_HEURES_PAR_JOUR, p.TS_HEURES_A_RECUPERER, 
           p.TS_RELIQUAT_CP, p.TS_RELIQUAT_RTT, p.C_ID, p.P_CIVILITE, tc.TC_LIBELLE,
           p.P_RELATION_NOM, p.P_RELATION_PRENOM, p.P_RELATION_PHONE, p.P_RELATION_MAIL, p.P_PHOTO,
           YEAR(CURRENT_DATE) - YEAR(p.P_BIRTHDATE) -( RIGHT(CURRENT_DATE,5)< RIGHT(p.P_BIRTHDATE,5)) AS AGE,
           p.GP_FLAG1, p.GP_FLAG2, p.P_PROFESSION, p.MONTANT_REGUL,
           p.NPAI, date_format(p.DATE_NPAI,'%d-%m-%Y') DATE_NPAI,
           p.SERVICE, p.TP_ID, p.OBSERVATION, ts.TS_LIBELLE,
           p.SUSPENDU, DATE_FORMAT(p.DATE_SUSPENDU, '%d-%m-%Y') DATE_SUSPENDU,
           DATE_FORMAT(p.DATE_FIN_SUSPENDU, '%d-%m-%Y') DATE_FIN_SUSPENDU,
           p.MOTIF_RADIATION, s2.S_CODE,s2.S_PARENT, p.P_PAYS, tp.TP_DESCRIPTION, tp2.TP_DESCRIPTION AS MODE_PAIEMENT,
           p.P_MAITRE, p2.P_NOM NOM_MAITRE, p2.P_PRENOM PRENOM_MAITRE, pp.NAME NOM_PAYS,
           p.P_LICENCE, DATE_FORMAT(p.P_LICENCE_DATE, '%d-%m-%Y') P_LICENCE_DATE, DATE_FORMAT(p.P_LICENCE_EXPIRY, '%d-%m-%Y') P_LICENCE_EXPIRY, p.ID_API,
           p.P_REGIME,trt.TRT_CODE, trt.TRT_DESC
        from pompier p left join pompier p2 on p.P_MAITRE=p2.P_ID
        left join type_profession tp on tp.TP_CODE = p.P_PROFESSION
        left join type_membre tm on ( tm.TM_ID = p.P_OLD_MEMBER and tm.TM_SYNDICAT = ".$syndicate.")
        left join grade g on p.P_GRADE=g.G_GRADE
        left join groupe g1 on g1.GP_ID = p.GP_ID
        left join groupe g2 on g2.GP_ID = p.GP_ID2
        left join statut s1 on s1.S_STATUT=p.P_STATUT
        left join section s2 on s2.S_ID=p.P_SECTION
        left join type_civilite tc on tc.TC_ID = p.P_CIVILITE
        left join groupe gp on gp.GP_ID=p.GP_ID
        left join company c on c.C_ID = p.C_ID
        left join type_salarie ts on ts.TS_CODE = p.TS_CODE
        left join pays pp on p.P_PAYS = pp.ID
        left join type_paiement tp2 on tp2.TP_ID = p.TP_ID
        left join type_regime_travail trt on TRT_CODE = p.P_REGIME
        where p.P_ID=".$pompier;
$result=mysqli_query($dbc,$query);
write_debugbox($query);

// check input parameters
if ( mysqli_num_rows($result) <> 1 ) {
    param_error_msg();
    exit;
}
custom_fetch_array($result);
$P_PRENOM=my_ucfirst($P_PRENOM);
$P_PRENOM2=my_ucfirst($P_PRENOM2);
$P_NOM=strtoupper($P_NOM);
$P_NOM_NAISSANCE=strtoupper($P_NOM_NAISSANCE);
$P_PHONE=phone_display_format($P_PHONE);
$P_PHONE2=phone_display_format($P_PHONE2);
$P_ADDRESS=stripslashes($P_ADDRESS);
$P_RELATION_PHONE=phone_display_format($P_RELATION_PHONE);
$NOM_MAITRE=strtoupper($NOM_MAITRE);
$PRENOM_MAITRE=my_ucfirst($PRENOM_MAITRE);

if ( $P_OLD_MEMBER > 0 ) {
    echo "<STYLE type='text/css'>
    tr.trcolor {background-color :#b3b3b3;}
    </STYLE>";
}

if ( $P_STATUT == 'BEN' ) $NB5++;  //recu adhesion
if ( $P_STATUT == 'BEN' or ($P_STATUT == 'SAL' and $assoc )) $NB5++; // passeport benevole
if ( $syndicate == 1 and $NB8 > 0 ) $NB5= $NB5 + $NB11;  //attestation fiscale années précédentes
$NB5 = $NB5 + $NB12; // ASA/OM

// ===========================================
// update permissions
// ===========================================

// permettre les modifications si je suis habilité sur la fonctionnalité 2
// (et si la personne fait partie de mes sections filles ou alors je suis habilité sur la fonctionnalité 24 )
if ((check_rights($id, 37,"$P_SECTION") or (check_rights($id, 37) and check_rights($id, 24))) and $P_STATUT == 'EXT') $update_allowed=true;
else if (( check_rights($id, 2,"$P_SECTION") or (check_rights($id, 2) and check_rights($id, 24))) and $P_STATUT <> 'EXT') $update_allowed=true;
else $update_allowed=false;

if (check_rights($id, 3,"$P_SECTION")) $delete_allowed=true;
else $delete_allowed=false;

if (check_rights($id, 53,"$P_SECTION")) $cotisations_allowed=true;
else $cotisations_allowed=false;

if (check_rights($id, 53,0)) $cotisations_national_allowed=true;
else $cotisations_national_allowed=false;

if (check_rights($id, 73,"$P_SECTION")) $notes_allowed=true;
else if (check_rights($id, 74,"$P_SECTION")) $notes_allowed=true;
else if (check_rights($id, 75,"$P_SECTION")) $notes_allowed=true;
else $notes_allowed=false;

// permission de modifier les compétences?
$competence_allowed=false;
$query="select distinct F_ID from poste order by F_ID";
$result=mysqli_query($dbc,$query);
while ($row=@mysqli_fetch_array($result)) {
    if (check_rights($id, $row['F_ID'],"$P_SECTION")) {
        $competence_allowed=true;
        break;
    }
}

if (check_rights($id, 4,"$P_SECTION")) $change_formation_allowed=true;
else $change_formation_allowed=false;

if (check_rights($id, 70,"$P_SECTION")) $update_tenues=true;
else $update_tenues=false;

// what is visible or enabled?
if ($update_allowed) $disabled="";
else $disabled="disabled";

if ($update_allowed) $disabled_del="";
else $disabled_del="disabled";

if ( $P_HIDE == 1 
    and ! $update_allowed
    and $pompier <> $id
    and ! check_rights($id, 12,"$P_SECTION")
    and ! check_rights($id, 25,"$P_SECTION")
    and ! check_rights($id, 12, "0")
    and ! is_chef($id, "$P_SECTION")
)
$infos_visible=false;
else $infos_visible=true;

// ne pas afficher au 'public' les infos concernant la personne a prévenir en cas d'urgence
// mais toujours visible dans le code source de la page pour ne pas bloquer les formulaires.
if (    (! $update_allowed )
    and ( $nbsections == 0 )
    and ( $pompier <> $id )
    and (! check_rights($id, 12,"$P_SECTION"))
    and (! check_rights($id, 12, "0")))
$hide_contacturgence=" style=\"display:none;\" ";
else $hide_contacturgence="";

// chacun peut modifier son identifiant
if ( $id == $pompier) $disabled_matricule='';
else $disabled_matricule=$disabled;

// particulier syndicat, il faut permission speciale pour modifier dates, statut, identifiant
$important_update_disabled = $disabled;
if ( $syndicate == 1 and (! check_rights($id, 1))) $important_update_disabled = 'disabled';

// si rôles hors département, tester permissions sur autre départements, rendre infos visibles
if ( $update_allowed == false and $P_STATUT <> 'EXT' and  check_rights($id, 2)) {
    $query="select distinct S_ID EXTERNAL_SECTION from section_role where P_ID=".$pompier." and S_ID <> ".intval($P_SECTION);
    $EXTERNAL_SECTION=0;
    $result=mysqli_query($dbc,$query);
    while (custom_fetch_array($result)) {
        if (check_rights($id, 2, "$EXTERNAL_SECTION")) {
            $infos_visible=true;
            $hide_contacturgence="";
            $documents_visible=true;
            break;
        }
    }
    if (check_rights($id, 4,"$EXTERNAL_SECTION")) {
        $formations_visible=true;
    }
    if ( check_rights($id,70,"$EXTERNAL_SECTION") or check_rights($id,17,"$EXTERNAL_SECTION") ) $materiel_visible=true;
    if (check_rights($id,15,"$EXTERNAL_SECTION") ) $evenements_visible=true;
}

// security verifications
if ( ( $tab == 2 and ! $infos_visible )
  or ( $tab == 3 and ! $formations_visible )
  or ( $tab == 3 and ! $infos_visible )
  or ( $tab == 4 and ! $evenements_visible )
  or ( $tab == 5 and ! $materiel_visible )
  or ( $tab == 6 and ! $documents_visible )
  or ( $tab == 8 and ! $compta_visible )
  or ( $tab == 9 and ! $compta_visible )
  or ( $tab == 10 and ! $materiel_visible )
) {
    $tab = 1;
}

// cas spécial toutes les modifs bloquées
if ( $block_personnel ) $asterisk = '';

// ===========================================
// header
// ===========================================

echo "</head>
<body >";

// message quand on a sauvé
if ( ($id == $pompier or $update_allowed) and isset($_GET['saved'])) {
    $errcode=$_GET['saved'];
    echo "<div id='fadediv' align=center>";
    if ( $errcode == 'nothing' ) echo "<div class='alert alert-info' role='alert'> Aucun changement à sauver.";
    else if ( $errcode == 0 ) echo "<div class='alert alert-success' role='alert'> Fiche personnel sauvée.";
    else echo "<div class='alert alert-danger' role='alert'> Erreur lors de la sauvegarde de la fiche.";
    echo "</div></div><p>";
}

echo "<div max-height='1000'>";

$iphone=is_iphone();
$iphonelink="";
// lien tel iphone
if ($iphone) {  
     if ($P_HIDE == 0 or $pompier == $id or check_rights($id, 2, "$P_SECTION")) {
        if ( $P_PHONE <> "" )
             $iphonelink="<a href=\"tel:".str_replace(" ","",$P_PHONE)."\"><i class='fa fa-phone fa-2x noprint' title=\"Appeler\" style='PADDING-LEFT:3px' class='noprint'></i></a>
                         <a href=\"sms:".str_replace(" ","",$P_PHONE)."\"><i class='far fa-comment fa-2x noprint' title=\"SMS\" style='PADDING-LEFT:3px' class='noprint'></i></a>";
    }
}
if ( $disabled_matricule == '' )
    $lnk="<a href='upd_personnel_photo.php?pompier=".$P_ID."&photo=".$P_PHOTO."' class='noprint' title='Cliquer pour modifier la photo'>";
else 
    $lnk = "<a href='#' title=\"Vous n'avez pas les permissions pour modifier la photo\">";

$photo_found = false;
if( $P_PHOTO != "" ){
    $image=$trombidir."/".$P_PHOTO;
    if( file_exists($image) ) {
        $filedate = date("Y-m-d",filemtime($image));
        if ( $filedate == date("Y-m-d")) $timestamp="?timestamp=".time();
        else $timestamp="";
        $pic = "<img src='".$image.$timestamp."' class='rounded' border='0' width='75' >";
        $photo_found = true;
    }
    else $pic = "<i class='fa fa-user fa-3x' title='Photo non trouvée'>";

}
else {
    if ( $P_CIVILITE >= 4 ) $pic='dog.png';
    else if ( $P_SEXE == 'M') $pic='boy.png';
    else $pic='girl.png';
    $pic="<img src=images/".$pic." class='img-max-50'>";
}
$pic = $lnk.$pic."</a>";

$links="";
if ( check_rights($id, 49) and $log_actions == 1 )
$links .= " <a href='history.php?lccode=P&lcid=$pompier&order=LH_STAMP&ltcode=ALL'><i class='fa fa-search fa-2x noprint' title=\"Historique des modifications\" style='PADDING-LEFT:3px' class='noprint'></i></a>";

$links .= " <a href=#><i class='fa fa-print fa-2x noprint' title=\"imprimer\" style='PADDING-LEFT:3px'  onclick='impression();'></i></a>";

if ( check_rights($id, 2, $his_section) )
$links .= " <a href='vcard.php?pid=$pompier' ><i class='far fa-address-card fa-2x noprint' title=\"Carte de visite\" style='PADDING-LEFT:3px'></i></a>";

if ( $P_STATUT == 'SAL' or $P_STATUT == 'FONC') { 
    if ( check_rights($id, 13,$his_section) or $pompier == $id) {
        if ( $TS_CODE <> 'SNU' ) {
            $week=date('W');
            $year=date('Y');
            // cas particulier, on affiche Y+1 si la derniere semaine est a cheval sur 2 années
            $month=date('m');
            if ( $month == '12' and $week == '01' ) $year = $year + 1;
            $links .= " <a href='horaires.php?view=week&person=$pompier&week=$week&year=$year'><i class='far fa-clock fa-2x noprint' title=\"Horaires travaillés\" style='PADDING-LEFT:3px'></i></a>";
        }
    }
}

if ( $disponibilites == 1 and 
    (( check_rights($id, 38) and $pompier == $id ) or ( check_rights($id, 10 ,$his_section))) 
   )
    $links .= " <a href='dispo.php?person=$pompier'><i class='far fa-calendar-check fa-2x noprint' title=\"Disponibilités\"></i></a>";

if ( check_rights($id, 40,$his_section) and $evenements == 1 and $evenements_visible )
    $links .= " <a href='calendar.php?pompier=$pompier'><i class='far fa-calendar-alt fa-2x noprint' title=\"Calendrier\"></i></a>";

if (  $P_STATUT <> 'EXT' and $P_STATUT <> 'ADH' and $P_OLD_MEMBER == 0 ) {
    if (( check_rights($id,11) and $id == $pompier) or check_rights($id, 12 ,$his_section)) 
        $links .= " <a href='indispo_choice.php?person=$pompier&filter=$his_section'><i class='far fa-calendar-times fa-2x noprint' title=\"Absences\"></i></a>";
}
if ( check_rights($id, 25,$his_section) or check_rights($id, 9 ,$his_section) or $pompier == $id)
    $links .= "<a href='change_password.php?pid=$pompier'><i class='fa fa-key fa-2x noprint' title=\"Changer le mot de passe\" style='PADDING-LEFT:3px'></i></a>";

if ( $show_carte ) {
    $a="<a href=pdf_carte_adherent.php?P_ID=".$pompier." target=_blank title='Voir la carte adhérent'>";
    $links .= $a."<i class='fa fa-id-card fa-2x noprint' title=\"Voir la carte adhérent\" style='PADDING-LEFT:3px; color:green;'></i></a>";
}

if ( $skype_enabled ) {
    $query2="select CONTACT_VALUE as skype from personnel_contact where P_ID=".$pompier." and CT_ID=1";
    $result2=mysqli_query($dbc,$query2);
    $row2=mysqli_fetch_array($result2);
    $skype=$row2["skype"];
    if ( $skype <> "" ) {
        $links .= "<script type='text/javascript' src='http://download.skype.com/share/skypebuttons/js/skypeCheck.js'></script>";
        $links .= "<a href='skype:".$skype."?call'><i class='fab fa-skype fa-2x noprint' title=\"Contacter avec Skype\" style='PADDING-LEFT:3px' class='noprint'></i></a>";
    }
}
if ( $assoc  and ($pompier == $id or check_rights($id, 14)) ) {
    $links .= "<a href='qrcode.php?pid=".$pompier."' ><i class='fa fa-qrcode fa-2x noprint' title=\"Afficher mon code QR Code personnel\" style='PADDING-LEFT:3px' class='noprint'></i></a>";
}

echo "<div align=center><table class=noBorder><tr class='white'><td>".$pic."</td><td>
<font size=3><b>".$P_PRENOM." ".$P_NOM."</b></font><br> ".$iphonelink." ".$links."</td></tr></table>
</div><p>";

if ( ! $photo_found  and $photo_obligatoire and $pompier == $id )
print write_photo_warning($id);

// ===========================================
// tabs
// ===========================================

echo  "<ul class='nav nav-tabs noprint' id='myTab' role='tablist'>";
if ( $tab == '1' ) $class='active';
else $class='';
echo "<li class='nav-item'>
    <a class='nav-link $class' href='upd_personnel.php?from=$from&tab=1&pompier=$pompier' title='Infos' role='tab' aria-controls='tab1' href='#tab1' >
    <i class='fa fa-user'></i> <span>Infos</span></a></li>";
    
// cotisations
if ( $cotisations == 1 and $compta_visible and $P_STATUT <> 'EXT' ) {
    $t="Cot.";
    
    if ( $tab == '8' ) $class='active';
    else $class='';
    
    if ( $NB7 > 0 ) {
        $image="exclamation-triangle";
        $p="Il y a $NB7 rejet(s) non régularisé(s)";
    }
    else if ($NB9 == 0) {
        $image="exclamation-triangle";
        $p="Aucun paiement pour cette année";
    }
    else if ( $bank_accounts == 1 ) {
        if ( $NB6 == 1 ) {
            $image="check-square";
            $p="Un compte bancaire est renseigné";
        }
        else {
            $image="square";
            $p="Pas de compte bancaire renseigné";
        }
    }
    else {
        $image="euro-sign";
        $p="Cotisation, $NB8 paiement(s) enregistré(s), dont $NB9 pour cette année";
    }
    $C="<i class='fa fa-".$image."' title='".$p."'></i> ".$t." ";
    
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"upd_personnel.php?from=$from&tab=8&pompier=$pompier\" role='tab' aria-controls='tab8' href='#tab8' >
            <span>".$C." $NB8</span></a></li>";
}    

// competences
if ( $competences == 1 and $infos_visible ) {
    if ( $tab == '2' ) $class='active';
    else $class='';
    $t="Comp.";
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"upd_personnel.php?from=$from&tab=2&pompier=$pompier\" role='tab' aria-controls='tab2' >
    <span title='Liste des compétences'><i class='fa fa-certificate'></i> $t $NB1</span></a></li>";

    // formations
    if ( $formations_visible ) {
        if ( $tab == 3 ) $class='active';
        else $class='';
        $t="Form.";
        echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"upd_personnel.php?from=$from&tab=3&pompier=$pompier&order=$order\" role='tab' data-toggle='tab3' href='#tab3'>
        <span title='Liste des formations'><i class='fa fa-book'></i> $t $NB2</span></a></li>";
    }
}
// inscriptions
if ( $evenements == 1  and $evenements_visible ){
    if ( $tab == 4 ) $class='active';
    else $class='';
    $t="Particip.";
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"upd_personnel.php?from=$from&tab=4&pompier=$pompier\" role='tab' data-toggle='tab4' href='#tab4'>
    <span title='Liste des participations ou astreintes futures et anciennes'><i class='fa fa-calendar'></i> ".$t." $NB3</span></a></li>";
}
// vehicules/materiel
if ( $materiel_visible ) {
    if ( ($vehicules == 1 or $materiel == 1) and $P_STATUT <> 'EXT') {
        if ( $tab == 5 ) $class='active';
    else $class='';
        $t="Dotation";
        echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"upd_personnel.php?from=$from&tab=5&pompier=$pompier\" role='tab' data-toggle='tab5' href='#tab5' >
        <span title='Liste des véhicules, du matériel affectés et des animaux affectés'><i class='fa fa-car'></i> ".$t." $NB4</span></a></li>";
    }
    // tenues
    if ( $materiel == 1 and $P_STATUT <> 'EXT') {
        if ( $tab == 10 ) $class='active';
        else $class='';
        $t="Tenues";
        if ( $P_SEXE == 'M') $icon='male';
        else $icon='female';
        echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"upd_personnel.php?from=$from&tab=10&pompier=$pompier\" role='tab' data-toggle='tab10' href='#tab10' >
        <span title='Tenues vestimentaires'><i class='fa fa-".$icon."'></i> ".$t." $NB41</span></a></li>";
    }
}

// documents
if ( $documents_visible ) {
    if ( $tab == 6 ) $class='active';
    else $class='';
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"upd_personnel.php?from=$from&tab=6&pompier=$pompier\" role='tab' data-toggle='tab6' href='#tab6'>
    <span title='Liste des documents attachés à cette fiche personnel'><i class='far fa-folder-open'></i> Docs $NB5</span></a></li>";
}

// notes de frais
if ( $notes == 1 and $compta_visible and $P_STATUT <> 'EXT' ) {
    if ( $tab == 9 ) $class='active';
    else $class='';
    echo "\n"."<li class='nav-item'><a class='nav-link $class' href=\"upd_personnel.php?from=$from&tab=9&pompier=$pompier\" role='tab' data-toggle='tab9' href='#tab9' >
    <span title='Liste des notes de frais attachées à cette fiche personnel'><i class='far fa-money-bill-alt'></i> Note. $NB10</span></a></li>";
}
echo "</ul>";
// fin tabs

echo "<div id='export' style='position: relative;' align=center>";

//$infos_visible=false;
//=====================================================================
// table information personnel: block 1
//=====================================================================
    
if ( $tab == 1 ) {
    echo "<p><form name='personnel' action='save_personnel.php' method=POST>";
    print insert_csrf('update_personnel');
    echo "<input type='hidden' name='P_ID' value='$P_ID'>";
    echo "<input type='hidden' name='operation' value='update'>";
    echo "<input type='hidden' name='activite' value='$P_OLD_MEMBER'>";
    echo "<input type='hidden' name='groupe' value='$P_SECTION'>";

    if ( $syndicate == 1 ) $t = 'adhérent';
    else $t = 'membre '.$application_title;

    //=====================================================================
    // container
    //=====================================================================

    if ( $infos_visible ) $w="md-4";
    else $w="lg-12";
    echo "<div class='container'>
        <div class='row'>
        <div class='col-".$w."'>";

    $blockwidth=350;
    echo "<table cellspacing=0 border=0 bgcolor=$mylightcolor>";
    echo "<tr class=TabHeader >
                 <td align=left colspan=2 width=".$blockwidth.">N° ".$t.": ".$P_ID."</td>";

    //=====================================================================
    // ligne profession
    //=====================================================================

    if ( $syndicate == 1 ) {
        echo "<tr class=trcolor>
                <td><b>Profession</b> $asterisk</td>
                <td align=left>";
        if ( $disabled == 'disabled' ) echo $TP_DESCRIPTION;
        else {
            $query2="select TP_CODE, TP_DESCRIPTION from type_profession order by TP_CODE desc";
            $result2=mysqli_query($dbc,$query2);
            echo "<select name='profession' class=smallcontrol>";
            while (custom_fetch_array($result2)) {
                if ( $TP_CODE <> '-' ) $TP_DESCRIPTION=$TP_CODE.' - '.$TP_DESCRIPTION;
                if ( $TP_CODE == $P_PROFESSION ) $selected='selected';
                else $selected='';
                echo "<option value='$TP_CODE' $selected class=smallcontrol>$TP_DESCRIPTION</option>"; 
            }
            echo "</select>";
        }
        echo "</td></tr>";
    }

    //=====================================================================
    // ligne grade
    //=====================================================================

    if ( $grades == 1 ) {
        if ($syndicate == 1 ) $d1=$disabled_matricule;
        else $d1=$disabled;
        echo "<tr class=trcolor>
                <td><b>Grade</b> $asterisk</td>
                <td align=left>";
        
        if ( $d1 == 'disabled') {
            $G_DESCRIPTION="";
            $G_GRADE="-";
            $query2="select G_GRADE, G_DESCRIPTION from grade where G_GRADE='".$P_GRADE."'";
            $result2=mysqli_query($dbc,$query2);
            custom_fetch_array($result2);
            echo "<table class='noBorder'><tr class=trcolor><td width=45><img src='".$grades_imgdir."/".$G_GRADE.".png' width='30'></td><td><b>".$G_DESCRIPTION."</b></td></tr></table>";
        }
        else {
            $query2=query_grades();
            $result2=mysqli_query($dbc,$query2);
            echo "<script type='text/javascript'>
                var ddData = [";
            while (custom_fetch_array($result2)) {
               echo "    {
                    text: \"".wordwrap($G_DESCRIPTION, 28, "<br>")."\",
                    value: '".$G_GRADE."',";
                    if ( $P_GRADE == $G_GRADE ) echo "selected: true,";
                    else echo "selected: false,";
                    $img=$grades_imgdir."/".$G_GRADE.".png";
                    if (! file_exists($img)) 
                        $img=$grades_imgdir."/-.png";
                    echo "description: \"\",
                    imageSrc: \"".$img."\"
                    },";
            }
            echo "];";
            echo "</script><div id='iconSelector' height='20' ></div><input type=hidden name='grade' id='grade' value=\"".$P_GRADE."\">";
            if ( $P_OLD_MEMBER > 0 ) $color='#b3b3b3';
            else $color=$mylightcolor; 
            ?>
            <script type="text/javascript">
            $('#iconSelector').ddslick({
                data:ddData,
                width:225,
                height:400,
                background:"<?php echo $color; ?>",
                selectText: "Choisir le grade",
                imagePosition:"left",
                onSelected: function(data){
                    document.getElementById("grade").value = data.selectedData.imageSrc;
                }
            });
            </script>
            <?php
        }
        echo "</td></tr>";
    }

    //=====================================================================
    // ligne type
    //=====================================================================

    echo "<tr class='pad0 trcolor'>
        <td><b>Statut</b> $asterisk</td>
        <td align=left>";
    if ( $important_update_disabled == 'disabled' ) echo $P_DESC_STATUT;
    else {
        $ext_style="style='background-color:#00ff00;color:black;'";
        $other_style="style='background-color:white;color:black;'";
        if ( $P_STATUT == 'EXT' ) $style = $ext_style;
        else $style = $other_style;
        $query2="select S_STATUT, S_DESCRIPTION from statut";
        if ( $sdis  ) $query2 .= " where S_CONTEXT = 3";
        else $query2 .= " where S_CONTEXT =".$nbsections;
        if ( $army ) $query2 .= " and S_STATUT not in ('BEN','SAL','ADH','FONC')";
        else if ( $syndicate ) $query2 .= " and S_STATUT not in ('BEN','ACT','RES','CIV')";
        else $query2 .= " and S_STATUT not in ('ADH','FONC','ACT','RES','CIV')";
        if (! check_rights($id, 37) or $externes == 0 ){
            $query2 .= " and S_STATUT <> 'EXT'";
        }
        $query2 .= " union select S_STATUT, S_DESCRIPTION from statut where S_STATUT='".$P_STATUT."'";
        $query2 .= " order by S_DESCRIPTION";
        $result2=mysqli_query($dbc,$query2);
        echo "<select name='statut' id='statut' onchange=\"javascript:changedType();\" class=smallcontrol $style>";
        
        while (custom_fetch_array($result2)) {
            if ( $S_STATUT == $P_STATUT ) $selected='selected';
            else $selected = '';
            if ( $S_STATUT == 'EXT' ) $style= $ext_style;
            else $style = $other_style;
            echo "<option value='$S_STATUT' $selected class=smallcontrol $style>$S_DESCRIPTION</option>";
        }
        echo "</select>";
    }
    echo "</td></tr>";
    
    // particularités des SPP
    if ( $P_STATUT == 'SPP') $style="";
    else  $style="style='display:none'";
    echo "<tr id='tsppRow' $style class='pad0 trcolor'>
          <td><b>Régime travail</b> $asterisk</td>
          <td align=left>";
          
     if ( $important_update_disabled == 'disabled' ) echo "<span title=\"".$TRT_DESC."\">".$TRT_CODE."</span>";
        else {
            echo " <select name='regime_travail' id='regime_travail'
                        title='Choisir le régime de travail'>";
            $query2="select TRT_CODE NTRT_CODE, TRT_DESC NTRT_DESC from type_regime_travail order by TRT_ORDER asc";
            $result2=mysqli_query($dbc,$query2);
            while (custom_fetch_array($result2)) {
                if ( $TRT_CODE == $NTRT_CODE ) $selected='selected';
                else $selected='';
                echo "<option value='$NTRT_CODE' $selected title=\"".$NTRT_DESC."\">".$NTRT_CODE."</option>";
            }
            echo "</select>";
        }
    
    // particularités des salariés
    if ( $P_STATUT == 'SAL' or $P_STATUT == 'FONC') $style="";
    else  $style="style='display:none'";

    echo "<tr id='tsRow' $style class='pad0 trcolor'>
          <td><b>Contrat</b> $asterisk</td>
          <td align=left>";
    if ( $nbsections == 0 ) {
        if ( $important_update_disabled == 'disabled' ) echo $TS_LIBELLE;
        else {
            echo " <select name='type_salarie' id='type_salarie'
                        onchange=\"javascript:changedSalarie();\"
                        title='A préciser pour le personnel salarié ou fonctionnaire seulement'>";
            echo "<option value='0'>---choisir---</option>";
            $query2="select TS_CODE, TS_LIBELLE from type_salarie order by TS_LIBELLE asc";
            $result2=mysqli_query($dbc,$query2);
            while ($row2=@mysqli_fetch_array($result2)) {
                $NTS_CODE=$row2["TS_CODE"];
                $NTS_LIBELLE=$row2["TS_LIBELLE"]; 
                if ( $TS_CODE == $NTS_CODE ) $selected='selected';
                else $selected='';
                echo "<option value='$NTS_CODE' $selected>$NTS_LIBELLE</option>";
            }
            echo "</select>";
            
            $url="upd_personnel_salarie.php?person=".$pompier;
            if ( $P_STATUT == 'SAL' or $P_STATUT == 'FONC') {
                echo "<br>";
                print write_modal( $url, "contrat_salarie", "<span title=\"Afficher ou modifier le détail des heures, CP, RTT ...\"><i>Détail du contrat</i></span>");
            }
        }
        echo "</td></tr>";
    }
    else {
         $style="style='display:none'";
         echo "<tr id='tsRow' $style class='pad0 trcolor'></tr>";
    }

    //=====================================================================
    // ligne civilité
    //=====================================================================

    echo "<tr class='pad0 trcolor'>
                <td ><b>Civilité</b></font> $asterisk</td>
                <td align=left>";
    
    if ( $block_personnel or $disabled_matricule == 'disabled' ) 
        echo $TC_LIBELLE;
    else {
        $query2="select TC_ID, TC_LIBELLE from type_civilite" ;
        if ( $syndicate == 1 or $nbsections > 0 ) $query2 .=" where TC_ID < 4 ";
        $query2 .=" order by TC_ID";
        $result2=mysqli_query($dbc,$query2);
        echo "<select name='civilite' id='civilite' onchange=\"javascript:changedCivilite();\">";
        while ($row2=@mysqli_fetch_array($result2)) {
              $TC_ID=$row2["TC_ID"];
              $TC_LIBELLE=$row2["TC_LIBELLE"];
              if ( $P_CIVILITE == $TC_ID ) $selected='selected';
              else $selected='';
              echo "<option value='$TC_ID' $selected>$TC_LIBELLE</option>";
         }
         echo "</select>";
    }
    echo "</td></tr>";

    if ( intval($P_MAITRE) > 0 or $P_CIVILITE > 3) $style="";
    else  $style="style='display:none'";

    $maitre = "<a href=upd_personnel.php?pompier=$P_MAITRE title='Voir la fiche du maître'>".$PRENOM_MAITRE." ".$NOM_MAITRE."</a> ";

    echo "<tr id='maitreRow' $style class='pad0 trcolor'>
                <td align=right><i>Maître</i></td>
                <td align=left>".$maitre."</a>";
    if ( $update_allowed ) {
        $url="personnel_maitre.php?pid=".$P_ID."&maitre=".$P_MAITRE."&civilite=".$P_CIVILITE;
        print write_modal( $url, $P_ID, "<i class='fa fa-user fa-lg' title='choisir le maître' title='choisir le maître'/></i>");
    }
    echo "</td>
    </tr>";

    //=====================================================================
    // ligne nom
    //=====================================================================
    echo "<tr class='pad0 trcolor'>
                <td><b>Nom</b> $asterisk</td>
                <td align=left>";
    if ( $block_personnel or $disabled == 'disabled' )
        echo "<b>".$P_NOM."</b>";
    else
        echo "<input type='text' name='nom' size='24' value=\"$P_NOM\" onchange='isValid3(personnel.nom,\"$P_NOM\");' maxlength='30' ></td>";
    echo "</tr>";

    //=====================================================================
    // ligne prénom
    //=====================================================================
    echo "<TR class='pad0 trcolor'>
                <td><b>Prénom</b> $asterisk</td>
                <td align=left>";
    if ( $block_personnel or $disabled == 'disabled' )
        echo $P_PRENOM;
    else
        echo "<input type='text' name='prenom' size='20' value=\"$P_PRENOM\" onchange='isValid3(personnel.prenom,\"$P_PRENOM\");' maxlength='25'></td>";
    echo "</TR>";
    if ( $P_PRENOM2 == 'None' ) {
        $disabled_prenom2='disabled';
        $checked_no_prenom2='checked';
        $P_PRENOM2='';
    }
    else {
        $disabled_prenom2='';
        $checked_no_prenom2='';
    }
    echo "<TR class='pad0 trcolor' >
                <td align=right><i><small>Deuxième prénom</small></i></td>
                <td align=left>";
    if ( $disabled_matricule == 'disabled' )
        echo $P_PRENOM2;
    else
        echo "<input type='text' id='prenom2' name='prenom2' size='20' value=\"$P_PRENOM2\" onchange='isValid3(personnel.prenom2,\"$P_PRENOM2\");' maxlength='25'
                title='saisissez le 2eme prénom, facultatif');' $disabled_prenom2 >
                <input type='checkbox' id='no_prenom' name='no_prenom' value='1' title=\"Cocher si il n'y a pas de 2ème prénom.\" onchange='no_second_firstname();' $checked_no_prenom2 >";
    echo "</td></TR>";

    //=====================================================================
    // ligne nom naissance
    //=====================================================================

    echo "<TR class='pad0 trcolor' >
              <td  align=right><i><small>Nom de naissance</small></i></td>
              <td align=left>";
    if ( $disabled_matricule == 'disabled' )
        echo $P_NOM_NAISSANCE;
    else
        echo "<input type='text' name='nom_naissance' size='24' value=\"$P_NOM_NAISSANCE\" onchange='isValid4(personnel.nom,\"$P_NOM_NAISSANCE\");' maxlength='30'
                title='saisissez le nom de naissance, ou nom de jeune fille'>";
    echo "</td></TR>";

    //=====================================================================
    // ligne identifiant
    //=====================================================================
    if ( $update_allowed or $pompier == $id ) {
        if ( $army == 0 and $nbsections == 0) $i = "Identifiant";
        else $i = "Matricule";

        if ( $syndicate == 1 ) $disabled2=$important_update_disabled;
        else $disabled2=$disabled_matricule;

        echo "<TR class='pad0 trcolor' id=iRow >";
        echo "<td><b>".$i."</b> $asterisk</td><td align=left>";
        if ( $disabled2 == 'disabled' )
            echo $P_CODE;
        else
            echo "<input type='text' name='matricule' size='20' value=\"$P_CODE\" onchange='isValid(form.matricule);'>";
        echo " <a href='#' title=\"utilisé pour se connecter à ".$application_title."\"><i class='fa fa-question-circle' ></i></a></td></TR>";
    }

    //=====================================================================
    // section
    //=====================================================================

    if ( $syndicate == 1 ) $a = "Département";
    else if ( $nbsections == 0 ) $a = "Affectation";
    else $a = "Section";
    
    $his_section_name = $S_CODE." - ".$P_DESC_SECTION;
        
    if ( check_rights($id, 52) )
        $section_info="<a href=upd_section.php?S_ID=".$P_SECTION." title=\"Voir la fiche de ".$his_section_name."\">".$a."</a></b> $asterisk";
    else 
        $section_info=$a;
    echo "<TR class='pad0 trcolor' >
                <td><b>
                $section_info</td>
                <td align=left>";

    if ( $disabled == 'disabled' ) {
        echo "<div style='max-width:220px;font-size: 12px;'>".$his_section_name."</div>";
    }
    else {
        if ( $update_allowed ) {
            if ( $P_STATUT == 'EXT' ) $mysection=get_highest_section_where_granted($id,37);
            else $mysection=get_highest_section_where_granted($id,2);
            if ( $mysection == '' ) $mysection=$P_SECTION;
            if ( ! is_children($section,$mysection)) $mysection=$section;
            if ( check_rights($id, 24) ) $mysection='0';
        }
        else $mysection=$P_SECTION;
               
        echo "<select id='groupe' name='groupe' class=smallcontrol>";
        $level=get_level($mysection);
        if ( $level == 0 ) $mycolor=$myothercolor;
        elseif ( $level == 1 ) $mycolor=$my2darkcolor;
        elseif ( $level == 2 ) $mycolor=$my2lightcolor;
        elseif ( $level == 3 ) $mycolor=$mylightcolor;
        else $mycolor='white';
        $class="style='background: $mycolor;'";
           
        if ( isset($_SESSION['sectionorder']) ) $sectionorder=$_SESSION['sectionorder'];
        else $sectionorder=$defaultsectionorder;
           
        if ( check_rights($id, 24))
            display_children2(-1, 0, $P_SECTION, $nbmaxlevels, $sectionorder);
        else {
            echo "<option value='$mysection' class=smallcontrol $class >".
                      get_section_code($mysection)." - ".get_section_name($mysection)."</option>";
            if ( "$P_SECTION" <> "$mysection" ) {
                if (! is_children("$P_SECTION","$mysection") )
                    echo "<option value='$P_SECTION' $class selected>".$his_section_name."</option>";
            }
            display_children2($mysection, $level +1, $P_SECTION, $nbmaxlevels);
        }
        echo "</select>";
    }
    echo "</td></TR>";
       
    if (  $syndicate  == 1 ) {
        echo "<TR class='pad0 trcolor' >
            <td align=right><small><i>Service</i></small></td><td>";
        if ( $block_personnel or $disabled == 'disabled' )
            echo $SERVICE;
        else 
            echo "<input name=service type=text size=45 value=\"$SERVICE\" maxlength=60 $disabled class=smallcontrol>";
        echo "</td></TR>";
    }
    //=====================================================================
    // company
    //=====================================================================
    if (  $externes == 1 ) {
        echo "<TR class='pad0 trcolor' id='yRow' ><td><b>";
        if ( $C_ID > 0 and check_rights($id, 37))
            echo "<a href=upd_company.php?C_ID=".$C_ID." title='Voir informations sur cette entreprise'>Entreprise</a></b> $asterisk";
        else
        echo "Entreprise</b> $asterisk";
        echo "</td><td align=left>";
        if ( $disabled == 'disabled' ) {
            if (intval($C_ID) > 0 ) echo $C_NAME;
            else echo "<small>Non précisé</small>";
        }
        else {
            echo "<select id='company' name='company'  style='max-width:220px;font-size: 12px;'>";
            echo companychoice($P_SECTION, $C_ID, true, $P_STATUT);
            echo "</select>";
        }
        echo "</td></TR>";
    }
    else echo "<tr id='yRow' style='display:none' class=trcolor><td colspan=2><input type='hidden' name='company' id='company' value='0'></td>";

    //=====================================================================
    // habilitations appli
    //=====================================================================
    if ( $infos_visible ) {
        # can grant admin only if granted on 9
        $query2="select GP_ID, GP_DESCRIPTION, GP_USAGE from groupe where GP_ID < 100";

        if ( $P_STATUT == 'EXT' ) 
            $query2 .= "  and GP_USAGE in ('all','externes')";
        else 
            $query2 .= "  and GP_USAGE in ('all','internes')";
            
        if (! check_rights($id, 9)) {
            $query2 .="   and not exists (select 1 from habilitation h, fonctionnalite f
                            where f.F_ID = h.F_ID
                            and f.F_TYPE = 2
                            and h.GP_ID= groupe.GP_ID
                            and groupe.GP_ID <> $P_GP_ID";
            if ($P_GP_ID2 <> "" ) $query2 .=" and groupe.GP_ID <> $P_GP_ID2 ";
            $query2 .=" )";
        }

        if (! check_rights($id, 46)) {
            $query2 .="   and not exists (select 1 from habilitation h, fonctionnalite f
                            where f.F_ID = h.F_ID
                            and f.F_TYPE = 3
                            and h.GP_ID= groupe.GP_ID
                            and groupe.GP_ID <> $P_GP_ID
                            and groupe.GP_USAGE = 'externes'";
            if ($P_GP_ID2 <> "" ) $query2 .=" and groupe.GP_ID <> $P_GP_ID2 ";
            $query2 .=" )";
        }

        $query2 .="   order by GP_ORDER, GP_ID asc";
        $result2=mysqli_query($dbc,$query2);

        if ( $update_allowed and (check_rights($id, 9) or check_rights($id, 25))) $disabled2="";
        else $disabled2="disabled";

        if (check_rights($id, 2,"$S_PARENT")) $disabled3='';
        else $disabled3="disabled";
        
        if ( check_rights($id, 52))
            $pic=" <a href=habilitations.php?tab=1 title='Voir les habilitations'><i class='fa fa-question-circle'></i></a>";
        else $pic="";

        echo "<input type='hidden' name='habilitation' value='$P_GP_ID'>";
        echo "<TR class='pad0 trcolor' id=gRow >
            <td nowrap><b>Droit d’accès</b> $pic
            </td>
            <td align=left>";
        
        if ( $disabled2 == 'disabled' ) {
            echo $P_GP_DESCRIPTION;
            if ( $GP_FLAG1 == 1 ) echo " <i class='far fa-check-square' title=\"Et les permissions s'appliquent au niveau supérieur\"></i>";
        }
        else {
            echo "<select name='habilitation'>";
            $found=false;
            while ($row2=@mysqli_fetch_array($result2)) {
                $GP_ID=$row2["GP_ID"];
                $GP_DESCRIPTION=$row2["GP_DESCRIPTION"];
                if ( $P_GP_ID == $GP_ID ) {
                    $selected='selected';
                    $found=true;
                }
                else $selected='';
                echo "<option value='$GP_ID' $selected >".$GP_DESCRIPTION."</option>";
            }
            if (! $found ) 
                echo "<option value='$P_GP_ID' selected>".$P_GP_DESCRIPTION."</option>";
            echo "</select>";
            if ( $GP_FLAG1 == 1 ) $checked="checked";
            else $checked="";
            if ( $P_STATUT == 'EXT' or $P_STATUT == 'ADH') $style="style='display:none'";
            else  $style="";
            echo " <input type=checkbox id='flag1' name='flag1' value='1' $style $disabled3 $checked 
                title=\"Si coché, les droits s'appliquent au niveau supérieur à la section d'appartenance\">";
                  
            if ( $checked == 'checked' and $disabled3 =='disabled' )
                echo " <input type=hidden id='flag1' name='flag1' value='1'>";
        }
        echo "</td></TR>";
        $result2=mysqli_query($dbc,$query2);

        $P_GP_ID2=intval($P_GP_ID2);
        if ( $P_GP_ID2 == 0 ) $P_GP_DESCRIPTION2="aucun";
        if ( $P_STATUT == 'EXT' ) $style="style='display:none'";
        else  $style="";
        $found=false;
        echo "<input type='hidden' name='habilitation2' value='$P_GP_ID2'>";
        echo "<TR class='pad0 trcolor' id=gRow2 $style>
            <td><b>Droit d’accès 2</b></td>
            <td align=left>";
            
        if ( $disabled2 == 'disabled' ) {
            echo $P_GP_DESCRIPTION2;
            if ( $GP_FLAG2 == 1 ) echo " <i class='far fa-check-square' title=\"Et les permissions s'appliquent au niveau supérieur\"></i>";
        }
        else {
            echo "<select name='habilitation2' >";
            while ($row2=@mysqli_fetch_array($result2)) {
                $GP_ID=$row2["GP_ID"];
                $GP_DESCRIPTION=$row2["GP_DESCRIPTION"];
                if ( $P_GP_ID2 == $GP_ID ) {
                    $selected='selected';
                    $found=true;
                }
                else $selected='';
                // ne pas proposer -1 ici, pour les externes réduire les choix
                //if ($GP_ID >= 0 or $P_GP_ID2 == $GP_ID )
                echo "<option value='$GP_ID' $selected>".$GP_DESCRIPTION."</option>";
            }
            if (! $found ) 
                echo "<option value='$P_GP_ID2' selected>".$P_GP_DESCRIPTION2."</option>";
            echo "</select>";

            if ( $P_STATUT == 'EXT' or $P_STATUT == 'ADH') $style="style='display:none'";
            else  $style="";
            if ( $GP_FLAG2 == 1 ) $checked="checked";
            else $checked="";
            echo " <input type=checkbox name='flag2' value='1' $disabled3 $checked $style
                    title=\"Si coché, les droits s'appliquent au niveau supérieur à la section d'appartenance\">";
            if ( $checked == 'checked' and $disabled3 =='disabled')
                echo " <input type=hidden id='flag2' name='flag2' value='1'>";
        }
        echo "</TR></td>";
    }

    //=====================================================================
    // positions éventuelles
    //=====================================================================

        $query2="select s.S_ID _S_ID, s.S_CODE _S_CODE, g.GP_DESCRIPTION _GP_DESCRIPTION
            from section_role sr, section s , groupe g
            where sr.P_ID=".$P_ID." and sr.GP_ID = g.GP_ID and g.TR_CONFIG=2 and sr.S_ID = s.S_ID";
        $result2=mysqli_query($dbc,$query2);
        if ( mysqli_num_rows($result2) > 0 ) {
            echo "<TR class='pad0 trcolor'>
                     <td colspan=2 ><b>Rôles dans l'organigramme</b></td>
              </TR>";
            while (custom_fetch_array($result2)) {
                // cas specifique association, pas de président sur les antennes
                if (( get_level("$_S_ID") + 1 == $nbmaxlevels ) and ( $nbsections == 0 )) {
                    if ( $_GP_DESCRIPTION == "Président (e)" ) $_GP_DESCRIPTION="Responsable d'antenne";
                    if ( $_GP_DESCRIPTION == "Vice président (e)" ) $_GP_DESCRIPTION="Responsable adjoint";
                }
                echo "<tr class=trcolor>
                    <td align=left colspan=2>".$_GP_DESCRIPTION." <a href=upd_section.php?S_ID=$_S_ID><small>".$_S_CODE."</small></a></td>";
                echo "</tr>";
            }
        }
        $query2="select s.S_ID _S_ID, s.S_CODE _S_CODE, g.GP_DESCRIPTION _GP_DESCRIPTION
            from section_role sr, section s , groupe g
            where sr.P_ID=".$P_ID." and sr.GP_ID = g.GP_ID and g.TR_CONFIG=3 and sr.S_ID = s.S_ID";
        $result2=mysqli_query($dbc,$query2);
        if ( mysqli_num_rows($result2) > 0 ) {
            echo "<TR class='pad0 trcolor'>
                     <td colspan=2 ><b>Permissions dans l'organigramme</b></td>
              </TR>";
            while (custom_fetch_array($result2)) {
                echo "<tr class=trcolor>
                    <td align=left colspan=2>".$_GP_DESCRIPTION." <a href=upd_section.php?S_ID=$_S_ID><small>".$_S_CODE."</small></a></td>";
                echo "</tr>";
            }
        }
        // entreprises
        $query2="select c.C_ID, cr.TCR_CODE, tcr.TCR_DESCRIPTION, c.C_NAME
            from company_role cr, company c, type_company_role tcr
            where cr.P_ID=".$P_ID." 
            and cr.TCR_CODE = tcr.TCR_CODE
            and cr.C_ID = c.C_ID";
        $result2=mysqli_query($dbc,$query2);
        if ( mysqli_num_rows($result2) > 0 ) {
            echo "<tr class='pad0 TabHeader'>
                 <td colspan=2 >Rôles dans les entreprises</td>
                </tr>";
            while (custom_fetch_array($result2)) {
                echo "<tr class='pad0'>
                    <td align=left colspan=2><small>".$TCR_DESCRIPTION." <a href=upd_company.php?C_ID=$C_ID>".$C_NAME."</small></a></td>";
                echo "</tr>";
            }
        }
}
echo "</table></div>";


//=====================================================================
// block 2 contact infos
//=====================================================================
if ( $tab == 1 and $infos_visible ) {

    echo "<div class='col-md-4'>";
    echo "<table cellspacing=0 border=0 bgcolor=$mylightcolor>";
    echo "<tr class=TabHeader >
            <td align=left colspan=2 width=".$blockwidth.">Informations personnelles et contact</td>
        </tr>";
    if ( $pompier == $id ) $disabled='';

    //=====================================================================
    // ligne date de naissance
    //=====================================================================
    if ( $AGE <> "") $cmt=" <b>$AGE ans</b>";
    else $cmt="";
    echo "<TR class='pad0 trcolor' >
                <td align=left><b>Date de naissance</b></td>
                <td align=left>";
    if ( $block_personnel or $disabled == 'disabled' )
        echo $P_BIRTHDATE;
    else
        echo "<input type='text' name='birth' size='13' value='".$P_BIRTHDATE."' onchange='checkDate2(personnel.birth)' autocomplete='off' placeholder='JJ-MM-AAAA'>".$cmt;
    echo "</td></TR>";

    //=====================================================================
    // lieu de naissance
    //=====================================================================
    if ( $syndicate == 0 ) {
        echo "<TR class='pad0 trcolor'>
                <td align=left><b>Lieu de naissance</b></td>
                <td align=left>";
        if ( $block_personnel or $disabled == 'disabled' )
            echo $P_BIRTHPLACE;
        else
            echo "<input type='text' name='birthplace' size='24' value=\"$P_BIRTHPLACE\">";
        echo "</td></TR>";
        
        echo "<TR class='pad0 trcolor'>
                <td align=right><i><small>Département</small></i></td>
                <td align=left>";
        if ( $block_personnel or $disabled == 'disabled' )
            echo $P_BIRTH_DEP;
        else
            echo "<input type='text' name='birthdep' size='3' maxlength='3' value=\"$P_BIRTH_DEP\" $disabled onchange=\"checkNumberNullAllowed(form.birthdep,'');\">";
        echo "</td></TR>";
    }
    else 
        echo "<input type='hidden' name='birthplace' value=''><input type='hidden' name='birthdep' value=''>";

    //=====================================================================
    // nationalité
    //=====================================================================
    if ( $syndicate == 0 ) {
        echo "<TR class='pad0 trcolor'><td align='left'><b>Nationalité</b>";
        echo " <td align=left>";
        if ( $block_personnel or $disabled == 'disabled' )
            echo $NOM_PAYS;
        else {
            $query2="select ID, NAME from pays order by ID asc";
            $result2=mysqli_query($dbc,$query2);
            echo "<select name='pays' id='pays' title=\"Choisissez le pays correspondant à la nationalité de la personne\" style='max-width:220px; font-size: 12px;'>";
            echo " <option value='0'>Non renseignée</option>";
            while ($row2=@mysqli_fetch_array($result2)) {
                $_ID=$row2["ID"];
                $_NAME=$row2["NAME"];
                if ( $_ID == $P_PAYS ) $selected='selected';
                else $selected='';
                echo "<option value='$_ID' $selected>".$_NAME."</option>";
            }
            echo "</select>";
        }
        echo "</td></tr>";
    }
    else echo "<input type='hidden' name='pays' value=''>";

    //=====================================================================
    // ligne email
    //=====================================================================
    $bad_email="";
    $bad_email_style="";
    if ( $P_EMAIL <> "") {
        $tmp=explode("@",$P_EMAIL);
        $domain=$tmp[1];
        if ( in_array($domain,$bad_mail_domains)) {
            $bad_email=" <i class='fa fa-exclamation-triangle' style='color:orange' 
                            title=\"Attention, les emails envoyés de ".$application_title." sur le domaine ".$domain.", peuvent même être bloqués.
                            Choisissez de préférence une autre adresse.\"></i>";
            $bad_email_style="color: Red;";
        }
    }
    echo "<TR class='pad0 trcolor'>
                <td align=left><b>email</b></td>
                <td align=left>";
    if ( $block_personnel or $disabled == 'disabled' )
        echo $P_EMAIL;
    else 
        echo "<input type='text' name='email' size='24' style='".$bad_email_style."'
                value='$P_EMAIL' onchange='mailCheck(form.email,\"".$P_EMAIL."\")'> ".$bad_email;
    echo "</td></TR>";
    //=====================================================================
    // ligne phone
    //=====================================================================
    if ( $P_STATUT == 'JSP' ) $c=' du JSP'; else $c='';
    echo "<TR class='pad0 trcolor'>
                <td align=left><b>Téléphone portable $c</b></td>
                <td align=left>";
    if ( $block_personnel or $disabled == 'disabled' )
        echo $P_PHONE." ".show_contry_code($P_PHONE);
    else 
        echo "<input type='text' name='phone' size='16' value='$P_PHONE' maxlength=16
                onchange='checkPhone(personnel.phone,\"".$P_PHONE."\",\"".$min_numbers_in_phone."\")'> ".show_contry_code($P_PHONE);
    echo "</td></TR>";
    
    //=====================================================================
    // ligne phone 2
    //=====================================================================

    echo "<TR class='pad0 trcolor'>
                <td align=left><b>Autre Téléphone</b></td>
                <td align=left>";
    if ( $block_personnel or $disabled == 'disabled' )
        echo $P_PHONE2." ".show_contry_code($P_PHONE2);
    else 
        echo "<input type='text' name='phone2' size='16' value='$P_PHONE2' $disabled maxlength=16
                onchange='checkPhone(form.phone2,\"".$P_PHONE2."\",\"".$min_numbers_in_phone."\")'> ".show_contry_code($P_PHONE2);
    echo "</td></TR>";
    
    if ( $syndicate == 0 ) {
        echo "<TR class='pad0 trcolor'>
                    <td align=left><b>Abrégé</b></td>
                    <td align=left>";
        if ( $disabled == 'disabled' )
            echo $P_ABBREGE;
        else
            echo "<input type='text' name='abbrege' size='4' maxlength='5' value='$P_ABBREGE' $disabled>";
        echo "</td></TR>";
    }
    else 
        echo "<input type='hidden' name='abbrege' value=''>";

    //=====================================================================
    // ligne address
    //=====================================================================

    // GEOLOC
    $map="";
    if ( $P_ADDRESS <> "" and $P_CITY <> "" and $geolocalize_enabled and $P_STATUT <> 'EXT' ) {
        $querym="select LAT, LNG from geolocalisation where TYPE='P' and CODE=".$P_ID;
        $resultm=mysqli_query($dbc,$querym);
        $NB=mysqli_num_rows($resultm);
        if ( $NB == 0 ) gelocalize("$P_ID", 'P');
        else if ( $NB > 0 ) {
            custom_fetch_array($resultm);
            $url = $waze_url."&ll=".$LAT.",".$LNG."&pin=1";
            $map = "<a href=".$url." target=_blank><i class='fab fa-waze fa-lg' title='Voir la carte Waze' class='noprint'></i></a>";
            if ( check_rights($id,76)) {
                $url = "map.php?type=P&code=".$P_ID;
                $map .= " <a href=".$url." target=_blank><i class='fa fa-map noprint' style='color:green' title='Voir la carte Google Maps' class='noprint'></i></a>";
            }
        }
    }

    if ( $NPAI == 1 ) {
         $npai=" <i class='fa fa-exclamation-triangle' style='color:orange' title=\"NPAI: n'habite pas à l'adresse indiquée ".$DATE_NPAI."\"></i>";
         $npai_style="color: Red;";
    }
    else {
        $npai="";
        $npai_style="";
    }

    echo "<TR class='pad0 trcolor'>
                <td align=left><b>Adresse</b> ".$map." ".$npai." </td>
                <td align=left >";
    if ( $block_personnel or $disabled == 'disabled' )
        echo "<div style='max-width: 220px;'>".$P_ADDRESS."</div>";
    else
        echo "<textarea name='address' cols='24' rows='3' 
                style='FONT-SIZE: 10pt; FONT-FAMILY: Arial; ".$npai_style."'
                value=\"$P_ADDRESS\" >".$P_ADDRESS."</textarea>";
    echo "</td></TR>";

    echo "<TR class='pad0 trcolor'>
                <td align=left><b>Code postal</b></td>
                <td align=left>";
    if ( $block_personnel or $disabled == 'disabled' )
        echo $P_ZIP_CODE;
    else {
        echo "<input type='text' name='zipcode' id='zipcode' size='5' maxlength='5' value='$P_ZIP_CODE'  style='".$npai_style."' autocomplete='off' ";
        if ( zipcodes_populated() ) echo " onkeyup='checkZipcode();' ";
        echo ">";
    }
    echo "</td></TR>";

    echo "<TR class='pad0 trcolor'>
                <td align=left><b>Ville</b></td>
                <td align=left>";
    if ( $block_personnel or $disabled == 'disabled' )
        echo $P_CITY;
    else {
        echo "<input type='text' name='city' id='city' size='24' maxlength='30' value=\"$P_CITY\"  style='".$npai_style."' autocomplete='off'>";
        echo  "<div id='divzipcode' 
                style='display: none;
                position: absolute; 
                border-style: solid;
                border-width: 2px;
                background-color: $mylightcolor; 
                border-color: $mydarkcolor;
                width: 480px;
                height: 140px;
                padding: 5px;
                z-index: 100;
                overflow-y: auto'>
                </div>";
    }
    echo "</td></TR>";


    echo "<TR class='pad0 trcolor' >";
    echo "<td align=left><b>NPAI</b></td><td>";
    if ($update_allowed) {
        if ( $NPAI == 1 ) $checked="checked";
        else $checked="";
        echo "<input type='checkbox' name='npai' value='1' $checked 
            title=\"Cocher si n'habite pas à l'adresse indiquée\" onchange=\"fillDate(form.npai,form.date_npai,'".date("d-m-Y")."');\">
            <input name=date_npai type=text size=13 placeholder='JJ-MM-AAAA'
            class='datepicker' data-provide='datepicker' autocomplete='off'
            value='".$DATE_NPAI."' onchange='checkDate2(personnel.date_npai,\"$DATE_NPAI\");'> <small>Date NPAI</small>";
    }
    else {
        if ( $NPAI == 1 ) echo " <i class='far fa-check-square' title=\"N'habite pas à l'adresse indiquée\"></i> ";
        echo $DATE_NPAI;
    }
    echo "</td></TR>";

    if ( $syndicate == 0 and $update_allowed) {
        $url="personnel_contact.php?person=".$pompier;
        echo "<TR class='pad0 trcolor' id=sRow3 $style>
                <td align=left><b>Identifiants</b></td>
                <td align=left >";
        print write_modal( $url, "contacts", "<span title=\"Informations de contact pour les réseaux sociaux, et outils de communication comme Skype\"><i>Outils de communication</i></span>");
        echo "</td>";
        echo "</TR>";
    }
    
    //=====================================================================
    // hide my contact infos?
    //=====================================================================
    if ( $P_HIDE == 1 ) $checked="checked";
    else $checked="";
    echo "<TR class='pad0 trcolor' $hide_contacturgence id=cRow2 $style>
                <td align=left><b>Infos masquées</b></td>
                <td align=left class=small>
                <input type='checkbox' name='hide'  value='1' $disabled $checked 
                title='Masquer aux personnes ayant la permission public, seules certaines personnes habilitées pourront voir les informations personnelles et compétences'>
                <i> choix de confidentialité</i></td>"; 
    echo "</TR>";
    echo "</table>";
    echo "</div>";
}

//=====================================================================
// block 3
//=====================================================================
if ( $tab == 1 and $infos_visible ) {
    echo "<div class='col-md-4'>";
    echo "<table cellspacing=0 border=0 bgcolor=$mylightcolor>";
    echo "<tr class=TabHeader >
            <td align=left colspan=2 width=".$blockwidth.">Autres Informations </td>
        </tr>";
        
    //=====================================================================
    // ancien membre
    //=====================================================================

    $query2="select TM_ID, TM_CODE NEWTM_CODE from type_membre where TM_SYNDICAT=".$syndicate;
    if ( $nbsections == 0 and $syndicate == 0) {
         // seuls les chefs de sections et adjoints (sauf niveau antenne locale) 
        // ou admin (9) ou habilités sécurité locale (25) (sauf niveau antenne locale)
        // peuvent modifier le statut des membres en "radié" = 4
        if (! check_rights($id, 9)  and ! check_rights($id, 25, "$P_SECTION"))
            $query2 .=" and ( TM_ID <> 4 or TM_ID=".$P_OLD_MEMBER.")";
    }
    $result2=mysqli_query($dbc,$query2);

    if ( $syndicate == 1 ) $c="Actif / Radié ";
    else $c="Actif / Ancien ";
    $curdate=date('d-m-Y');

    echo "<TR class='pad0 trcolor' id=pRow >
                <td><b>".$c."</b> $asterisk</td>
                <td align=left>";
    if ( $important_update_disabled == 'disabled' )
            echo $TM_CODE;
    else {
        echo "<select name='activite' id='activite'
                onchange=\"javascript:changedStatut('".$curdate."','".$mylightcolor."');\">";
        while (custom_fetch_array($result2)) {
            if ( $TM_ID == $P_OLD_MEMBER ) $selected='selected';
            else  $selected='';
            echo "<option value='$TM_ID' $selected>$NEWTM_CODE</option>";
        }
        echo "</select>";
    }
    echo "</TR>";


    //=====================================================================
    // ligne date engagement
    //=====================================================================
    if ( $syndicate == 1 ) $t='Date adhésion';
    else if ( $P_STATUT == 'EXT' )  $t='Date inscription';
    else $t='Date engagement';

    echo "<TR class='pad0 trcolor' >
                <td><b>".$t."</b></td>
                <td align=left>";
    if ( $important_update_disabled == 'disabled' )
            echo $P_DATE_ENGAGEMENT;
    else
        echo "<input type='text' name='debut' size='13' value='".$P_DATE_ENGAGEMENT."' onchange='checkDate2(personnel.debut)'
                    placeholder='JJ-MM-AAAA' autocomplete='off'
                    class='datepicker' data-provide='datepicker'>";
    echo "</td></TR>";

    if ( $syndicate == 1 ) $t='Date radiation';
    else $t='Date de fin';

    echo "<TR class='pad0 trcolor' id=aRow >
                <td><b>".$t."</b></td>
                <td align=left>";
    if ( $important_update_disabled == 'disabled' )
            echo $P_FIN;
    else
        echo "<input type='text' name='fin' id='fin' size='13' value='".$P_FIN."' onchange='checkDate2(personnel.fin)'
                    placeholder='JJ-MM-AAAA' autocomplete='off'
                    class='datepicker' data-provide='datepicker'>";
    echo "</td></TR>";

    if (  $syndicate  == 1 ) {
        echo "<tr class=trcolor>
            <td align=right><small><i>Détail radiation</i></small></td><td>";
        if ( $important_update_disabled == 'disabled' )
            echo $MOTIF_RADIATION;
        else
            echo "<input name=motif_radiation type=text size=20 value=\"$MOTIF_RADIATION\" >";
        echo "</td></TR>";
    }
    
    //=====================================================================
    // licence adhérent
    //=====================================================================
    if ( $licences ) {
        echo "<TR class='pad0 trcolor'>
                    <td colspan=2 align=left><b>Informations de Licence</b></td>";
        echo "</TR>";
        echo "<TR class='pad0 trcolor' >
                    <td align=right><small><i>Numéro Licence</i></small></td>
                    <td align=left>";
        if ( $block_personnel or $important_update_disabled == 'disabled' )
            echo $P_LICENCE;
        else 
            echo "<input type='text' name='licnum' size='20' value='".$P_LICENCE."' autocomplete='off' >";
        echo "</td></TR>";
        
        echo "<TR class='pad0 trcolor' >
                    <td align='right'><small><i>Date Licence</i></small></td>
                    <td align=left>";
        if ( $block_personnel or $important_update_disabled == 'disabled' )
            echo $P_LICENCE_DATE;
        else 
            echo "<input type='text' name='licence_date' size='13' value='".$P_LICENCE_DATE."' onchange='checkDate2(personnel.licence_date)'
                        placeholder='JJ-MM-AAAA' autocomplete='off'
                        class='datepicker' data-provide='datepicker'>";
        echo "</td></TR>";

        echo "<TR class='pad0 trcolor' >
                    <td align=right><small><i>Expiration Licence</i></small></td>
                    <td align=left>";
        if ( $block_personnel or $important_update_disabled == 'disabled' )
            echo $P_LICENCE_EXPIRY;
        else 
            echo "<input type='text' name='licence_end' size='13' value='".$P_LICENCE_EXPIRY."' onchange='checkDate2(personnel.licence_end)'
                        placeholder='JJ-MM-AAAA' autocomplete='off'
                        class='datepicker' data-provide='datepicker'>";
        echo "</td></TR>";

        if ( $import_api ) {
            echo "<TR class='pad0 trcolor' >
                        <td align=right><small><i>Id API</i></small></td>
                        <td align=left>";
            if ( $ID_API == 0 ) $ID_API="";
            if ( $block_personnel or $important_update_disabled == 'disabled' )
                echo $ID_API;
            else 
                echo "<input type='text' name='id_api' size='8' value='".$ID_API."' onchange=\"checkNumberNullAllowed(personnel.id_api,'');\"
                        title='Identifiant unique dans le système externe de gestion des licences'
                            autocomplete='off'>";
            echo "</td></TR>";
        }
    }

    //=====================================================================
    // ligne suspendu
    //=====================================================================
    if ( $syndicate == 1 ) {
        echo "<TR class='pad0 trcolor' >";

        echo "<td><b>Suspendu</b></td><td>";
        if ( $important_update_disabled == 'disabled' ) {
            if ( $SUSPENDU == 1 )  echo " Oui";
            else  echo " Non";
        }
        else {
            if ( $SUSPENDU == 1 ) $checked="checked";
            else $checked="";
            echo "<input type='checkbox' name='suspendu'  value='1' $checked 
            title=\"Cocher si adhérent suspendu\" onchange=\"fillDate(form.suspendu,form.date_suspendu,'".date("d-m-Y")."');\"></td>";
        }
        echo "</tr><TR class='pad0 trcolor' ><td align=right class=small>Date début suspension</td><td>";
        if ( $important_update_disabled == 'disabled' )
            echo $DATE_SUSPENDU;
        else {
            echo "<input name=date_suspendu type=text size=13 placeholder='JJ-MM-AAAA' value='".$DATE_SUSPENDU."'
            class='datepicker' data-provide='datepicker' autocomplete='off'
            onchange='checkDate2(personnel.date_suspendu,\"$DATE_SUSPENDU\");'> ";
        }
        echo "</td></TR>";
        
        echo "<TR class='pad0 trcolor' >";
        echo "<td align=right class=small> Date fin suspension</td>";
        echo "<td>";
        if ( $important_update_disabled == 'disabled' )
            echo $DATE_FIN_SUSPENDU;
        else {
            echo "<input name=date_fin_suspendu type=text size=13 placeholder='JJ-MM-AAAA' value='".$DATE_FIN_SUSPENDU."'
            class='datepicker' data-provide='datepicker' autocomplete='off'
            onchange='checkDate2(personnel.date_fin_suspendu,\"$DATE_SUSPENDU\");'>";
        }
        echo "</td></TR>";
    }

    //=====================================================================
    // ligne contact
    //=====================================================================
    if ( $P_STATUT == 'EXT' ) $style="style='display:none'";
    else  $style="";
    if ( $syndicate == 0 ) {
        $custom=count_entities("custom_field", "CF_TITLE='Tél Père'");
        if ( $custom == 0 or $P_STATUT <> 'JSP') {
            echo "<TR class='pad0 trcolor' id=uRow1 $style>
                    <td colspan=2 align=left><b>Personne à prévenir en cas d'urgence</b>".(($hide_contacturgence<>"")?" (confidentiel)":"")."</td>";
            echo "</TR>";

            echo "<TR class='pad0 trcolor' $hide_contacturgence id=uRow2 $style>
                    <td align=right><i><small>Nom</small></i></td>
                    <td align=left>";
            if ( $disabled == 'disabled' )
                echo $P_RELATION_NOM;
            else
                echo "<input type='text' name='relation_nom' size='20' value='$P_RELATION_NOM' $disabled>";
            echo "</td></TR>";
            echo "<TR class='pad0 trcolor' $hide_contacturgence id=uRow3 $style>
                    <td align=right><i><small>Prénom</small></i></td>
                    <td align=left>";
            if ( $disabled == 'disabled' )
                echo $P_RELATION_PRENOM;
            else
                echo "<input type='text' name='relation_prenom' size='20' value='$P_RELATION_PRENOM' $disabled>";
            echo "</td></TR>";
            echo "<TR class='pad0 trcolor' $hide_contacturgence id=uRow4 $style>
                    <td align=right><i><small>Téléphone</small></i></td>
                    <td align=left>";
            if ( $disabled == 'disabled' )
                echo $P_RELATION_PHONE;
            else
                echo "<input type='text' name='relation_phone' size='16'  maxlength=16
                    value='$P_RELATION_PHONE' $disabled onchange='checkPhone(form.relation_phone,\"".$P_RELATION_PHONE."\",\"".$min_numbers_in_phone."\")'> ".show_contry_code($P_RELATION_PHONE);  
            echo "</td></TR>";
            
            echo "<TR class='pad0 trcolor' $hide_contacturgence id=uRow5 $style>
                <td align=right><i><small>Email</small></i></td>
                <td align=left>";
            if ( $disabled == 'disabled' )
                echo $P_RELATION_MAIL;
            else
                echo "<input type='text' name='relation_email' size='24' $disabled
                value='$P_RELATION_MAIL' onchange='mailCheck(form.relation_email,\"".$P_RELATION_MAIL."\")'>";
            echo "</td></TR>";
        }
    }
    else 
        echo "<TR id=uRow1 style='display:none' ></tr>
              <TR id=uRow2 style='display:none' ></tr>
              <TR id=uRow3 style='display:none' ></tr>
              <TR id=uRow4 style='display:none' ></tr>
              <TR id=uRow5 style='display:none' ></tr>";

    //=====================================================================
    // custom fields
    //=====================================================================

    $query1="select CF_ID, CF_TITLE, CF_COMMENT, CF_USER_VISIBLE, CF_USER_MODIFIABLE, CF_TYPE, CF_MAXLENGTH, CF_NUMERIC from custom_field order by CF_ORDER";
    $result1=mysqli_query($dbc,$query1);
    while (custom_fetch_array($result1)) {
        $query2="select CFP_VALUE from custom_field_personnel where P_ID=".$pompier." and CF_ID=".$CF_ID;
        $result2=mysqli_query($dbc,$query2);
        $row2=mysqli_fetch_array($result2);
        $CFP_VALUE=$row2["CFP_VALUE"];
        
        if ( $CF_USER_VISIBLE == 1 or $update_allowed) {
            echo "<TR class='pad0 trcolor' id=sRow2 $style>
                <td align=left><b>".$CF_TITLE."</b></td>
                <td align=left class=small>";
        
            if ( $CF_USER_MODIFIABLE == 1 ) $custom_disabled=$disabled;
            else $custom_disabled=$disabled;
        
            if ( $CF_TYPE == 'checkbox' ) {
                if ( $custom_disabled == 'disabled' ) {
                    if ( $CFP_VALUE == 1 ) echo "<i class='far fa-check-square' title='Oui'></i>";
                }
                else {
                    if ( $CFP_VALUE == 1 ) $checked='checked';
                    else $checked='';
                    echo "<input type='checkbox' name='custom_".$CF_ID."'  value='1' $checked  title=\"".$CF_COMMENT."\">";
                }
                echo " <small>".$CF_COMMENT."</small>";
            }
            else if ( $CF_TYPE == 'text' ) {
                if( $CF_MAXLENGTH > 30 ) $textsize=30;
                else $textsize=$CF_MAXLENGTH;
                if ( $CF_MAXLENGTH > 0 ) $sz = "maxlength='".$CF_MAXLENGTH."' size='".$textsize."'";
                else $sz="size=10";
                if ( $CF_NUMERIC == 1 ) $chk = "onchange='checkNumberNullAllowed(form.custom_".$CF_ID.",\"".$CFP_VALUE."\");'";
                else $chk="";
                if ( $custom_disabled == 'disabled' ) echo $CFP_VALUE;
                else echo "<input type='text' ".$sz." name='custom_".$CF_ID."'  value=\"".$CFP_VALUE."\"  ".$chk." title=\"".$CF_COMMENT."\">";
            }
            else if ( $CF_TYPE == 'textarea' ) {
                if ( $CF_MAXLENGTH > 0 ) $sz = "maxlength='".$CF_MAXLENGTH."'";
                if ( $custom_disabled == 'disabled' ) echo "<div style='max-width:220px;font-size: 12px;'>".$CFP_VALUE."</div>";
                else
                    echo "<textarea name='custom_".$CF_ID."' cols='24' rows='8' style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;'
                    value=\"".$CFP_VALUE."\" $sz title=\"".$CF_COMMENT."\">".$CFP_VALUE."</textarea>";
            }
            echo "</td></TR>";
        }
    }

    //=====================================================================
    // no spam?
    //=====================================================================
    if ( $disabled  <> 'disabled' and $P_OLD_MEMBER == 0 ) {
        $url="notification_param.php?person=".$pompier;
        echo "<TR class='pad0 trcolor' id=sRow3 $style>
                <td align=left><b>Notifications</b></td>
                <td align=left >";
        print write_modal( $url, "notifications", "<span title=\"Paramétrage des notifications\"><i>paramétrage</i></span>");
        echo "</td>";
        echo "</TR>";
    }
    //=====================================================================
    // URL du calendrier perso
    //=====================================================================
    if ( $P_STATUT <> 'ADH' and $id == $P_ID and $evenements == 1)  {
        if ( check_rights($id, 41)) {
            echo "<TR class='pad0 trcolor' $style><td><b>Calendriers</b></td>";
            echo "<td><i><a href='myagenda.php' title=\"Ouvrir la page d'informations sur les calendriers\"  >téléchargement</a></i>";
            echo "</td><TR>";
        }
    }      
   
    //=====================================================================
    // connexions
    //=====================================================================
    if ( $update_allowed or $id == $P_ID ) {
        $query2="select DATE_FORMAT(min(P_LAST_CONNECT),'%d-%m-%Y') MIN_LAST_CONNECT from pompier";
        $result2=mysqli_query($dbc,$query2);
        $row2=mysqli_fetch_array($result2);
        $MIN_LAST_CONNECT=$row2["MIN_LAST_CONNECT"];

        if ( $P_LAST_CONNECT <> "" ) {
            echo "<TR class='pad0 trcolor'>
                <td><b>Dernière connexion</b></td>
                <td align=left ><small> ".$P_LAST_CONNECT." 
                 <a title='".$P_NB_CONNECT." connexions depuis le ".$MIN_LAST_CONNECT."'></small><span class='badge badge-pill badge-primary'>".$P_NB_CONNECT."</span></a></td>
                </TR>";
        }
        else  {
            echo "<TR class='pad0 trcolor'>
                <td align=right class=small2>Aucune connexion</td>
                <td></td>
                </TR>";
        }
    }
    if ( $charte_active and ( $update_allowed or $id == $P_ID)) {
        if ( $P_ACCEPT_DATE == "" ) $info="N'a pas encore accepté la<br><a href=charte.php title=\"Voir la charte d'utilisation\">charte d'utilisation</a>";
        else $info="<a href=charte.php title=\"Voir la charte d'utilisation\">charte d'utilisation</a> acceptée <br>".$P_ACCEPT_DATE;
    
        echo "<TR class='pad0 trcolor' $style id=sRow4>
            <td ><b>Conditions </b></td>
            <td> ".$info."</td>
            </TR>";
    }
    
    //=====================================================================
    // homonymes
    //=====================================================================
    if ($update_allowed and $externes ) {
        $query=" select count(1) as NB from pompier where P_NOM = \"".strtolower($P_NOM)."\" 
                 and P_PRENOM=\"".strtolower($P_PRENOM)."\" 
                 and P_ID <> ".$P_ID."
                 and P_OLD_MEMBER = 0";

        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
        $NB=intval($row["NB"]);
        
        if ( $NB > 0 ) {
            $url="homonymes_modal.php?pid=".$pompier;
            $modal = write_modal( $url, "homonymes", "<span class='badge' title='Voir les homonymes actifs'>".$NB."</span>");
            echo "<TR class='pad0 trcolor'>
                <td><b>Homonymes</b></td>
                <td align=left > ".$modal."</td>
                </TR>";
        }
    }
    
    echo "</table>";
    echo " </div>";
}
    

echo "</div></div><br>";

//=====================================================================
// boutons
//=====================================================================
if ( $tab == 1 ) {


    if ( $disabled == "") {
        if (( $P_STATUT == 'EXT'  and  check_rights($id, 37))
          or ( $P_STATUT <> 'EXT'  and check_rights($id, 2))
          or $pompier == $id )
            echo " <input type='submit' class='btn btn-default noprint' value='sauver' ></form>";

        if ( check_rights($id, 25,"$section") or check_rights($id, 9))
            
        echo " <a class='btn btn-default noprint' onclick=\"bouton_redirect('send_id.php?pid=".$P_ID."')\"
            title=\"Envoyer un mail à ".ucfirst($P_PRENOM)." ".strtoupper($P_NOM)." avec son identifiant de connexion et un nouveau mot de passe généré automatiquement.\">
            <i class='fa fa-key fa-lg' ></i></a>";
    }
    else echo "</form>";
    if ( $disabled_del == "" and  check_rights($id, 3) and $id <> $P_ID) {
        $csrf = generate_csrf ('delete_personnel');
        echo " <input type='button' class='btn btn-default noprint' value='supprimer' onclick=\"delete_personnel('".$P_ID."','".$csrf."');\">";
    }

    if ( $from == 'export' ) {
        echo " <input type='button' class='btn btn-default noprint' value='fermer cette page' onclick='fermerfenetre();' >";
    }
    elseif  ( $from == 'created' ) {
        if ( ! $iphone)
            echo " <input type='button' class='btn btn-default noprint' name='annuler' value='Retour' onclick=\"javascript:history.go(-3);\" />";
    }
    else  {
         if ( ! $iphone) {
             if ( $from == 'save' ) echo "<td > <input type='button' class='btn btn-default noprint' id='annuler' name='annuler' value='Retour' onclick=\"bouton_redirect('personnel.php');\" ></td>";
             else echo "<td > <input type='button' class='btn btn-default noprint' id='annuler' name='annuler' value='Retour' onclick=\"history.back(1);\" /></td>";
        }
    }


    if($nbsections == 0 && $P_EMAIL != '' && $P_OLD_MEMBER == 0 && check_rights($id, 43)){
        echo  "<form name=\"FrmEmail\" method=\"post\" action=\"mail_create.php\">
        <input type=\"hidden\" name=\"SelectionMail\" value=\"$P_ID\" />";
        echo " <input type='submit' class='btn btn-default noprint' value='message' title=\"envoi de message à partir de l'application web\"/></form></td>";
        
        if (( $P_STATUT == 'EXT'  and  check_rights($id, 37))
          or ( $P_STATUT <> 'EXT'  and check_rights($id, 2))) {
            $subject="Message de ".str_replace("'","",ucfirst(get_prenom($id))." ".strtoupper(get_nom($id)));
            echo " <input type='button' class='btn btn-default noprint' value='mailto'
            onclick=\"window.location.href='mailto:".$P_EMAIL."?subject=$subject';\" 
            title=\"envoi de message à partir de votre logiciel de messagerie\" />";
        }
    }
}

//=====================================================================
// Table compétences
//=====================================================================
if ( $tab == 2 and $infos_visible ) {
    echo "<div align=center>";
    $OLDEQ_NOM="NULL";
    $query2="select e.EQ_ID, e.EQ_NOM, p.PS_ID, TYPE, p.DESCRIPTION, q.Q_VAL,
             DATE_FORMAT(q.Q_EXPIRATION, '%d-%m-%Y') as Q_EXPIRATION, p.PS_DIPLOMA, p.PS_RECYCLE,
             DATEDIFF(q.Q_EXPIRATION,NOW()) as NB,
             q.Q_UPDATED_BY, DATE_FORMAT(q.Q_UPDATE_DATE, '%d-%m-%Y %k:%i') Q_UPDATE_DATE, p.PS_ORDER,
             p.PH_LEVEL, p.PH_CODE
             from equipe e, qualification q, poste p
             where q.PS_ID=p.PS_ID
             and e.EQ_ID=p.EQ_ID
             and q.P_ID=".$P_ID."
             union 
             select e.EQ_ID, e.EQ_NOM, p.PS_ID, TYPE, p.DESCRIPTION, -1 as Q_VAL,
             null as Q_EXPIRATION, p.PS_DIPLOMA, p.PS_RECYCLE, 0 as NB,
             null as Q_UPDATED_BY, null as Q_UPDATE_DATE, p.PS_ORDER,
             p.PH_LEVEL, p.PH_CODE
             from equipe e, personnel_formation pf, poste p
             where pf.PS_ID=p.PS_ID
             and e.EQ_ID=p.EQ_ID
             and pf.P_ID=".$P_ID."
             and not exists (select 1 from qualification q where q.PS_ID = p.PS_ID and q.P_ID = pf.P_ID)
             order by EQ_ID, PH_CODE desc, PH_LEVEL desc, PS_ORDER";

    $result2=mysqli_query($dbc,$query2);
    if (mysqli_num_rows($result2) == 0) {
        echo "Aucune compétence.";
    }
    else {

    echo "<table cellspacing=0 border=0 >";
    echo "<tr class=TabHeader>
                 <td colspan=4>Compétences</td>
          </tr>";
    $show2=true;
    while (custom_fetch_array($result2)) {
        $show=true;
        if ( $PH_CODE <> "" ) $hierarchie="<span class=small2>(".$PH_CODE." niveau ".$PH_LEVEL.")</span>";
        else $hierarchie="";
        $DESCRIPTION=strip_tags($DESCRIPTION);
        $D = $DESCRIPTION." ".$hierarchie;
        if ( $show2) {
            $label='Expiration';
            $show2=false;
        }
        else $label='';
        if ( $EQ_NOM <> $OLDEQ_NOM) {
             $OLDEQ_NOM =  $EQ_NOM;
             echo "<tr class=trcolor> 
             <td colspan=2 ><i><b>$EQ_NOM</b></i></td>
            <td class=small width=80>".$label."</td>
            <td align=right></td>
            </tr>";
        }
        if ( $Q_VAL == -1 ) {
            $mycolor='black';
            $D = $DESCRIPTION." ".$hierarchie." <font size=1>(formation en cours)</font>";
            // cas particulier: ne pas montrer PSE1 si PSE2 valide
            if ( $TYPE == 'PSE1') {
                  $query="select count(*) as NB from qualification q, poste p
                     where q.P_ID=".$P_ID." and p.PS_ID=q.PS_ID and p.TYPE='PSE2'";
                 $result=mysqli_query($dbc,$query);
                 $row=@mysqli_fetch_array($result);
                 $NB=$row["NB"];
                  if ( $NB == 1 ) $show=false;
            }
        }
        else if ( $Q_VAL == 1 ) $mycolor='green';
        else $mycolor='darkblue';
        if ( $Q_EXPIRATION <> '') {
            if ($NB < 61) $mycolor='orange';
             if ($NB <= 0) $mycolor='red';
        }
        if ( $PS_DIPLOMA == 1 or $PS_RECYCLE == 1) {
             $query="select count(1) as NB from personnel_formation 
                     where P_ID=".$P_ID." and PS_ID=".$PS_ID;
            $result=mysqli_query($dbc,$query);
             $row=@mysqli_fetch_array($result);
             $NB=$row["NB"];
              $cmt=$D." <a href=personnel_formation.php?P_ID=$pompier&PS_ID=$PS_ID><i class='fa fa-info' title=\"détails sur la formation $DESCRIPTION de ".ucfirst($P_PRENOM)." ".strtoupper($P_NOM)."\"></i></a>";
            if ( $NB > 0 ) $cmt .=" <font size=1>(x ".$NB.")</font>";
        }
        else $cmt = $DESCRIPTION;
         
        if ( $Q_UPDATED_BY <> '' ) {
             $audit="<a href='#'><i class='fa fa-sticky-note' title=\"Modifié par ".ucfirst(get_prenom($Q_UPDATED_BY))." ".strtoupper(get_nom($Q_UPDATED_BY))." le ".$Q_UPDATE_DATE."\"></a>";
        }
        else $audit='';
         
        if ( $show)
            echo "<tr class=trcolor>
                 <td width=15></td>
                   <td align=left>
                 <font color=$mycolor> ".$cmt."</font></td>
                 <td>    
                 <font color=$mycolor>$Q_EXPIRATION</font></td>
                 <td  align=center>".$audit."</td>
            </tr>";
    }

    echo "</table>"; 
    }

    echo "<p>";

    $queryn="select count(*) as NB from poste where PS_USER_MODIFIABLE = 1";
    $resultn=mysqli_query($dbc,$queryn);
    $rown=@mysqli_fetch_array($resultn);
    $n=$rown["NB"];

    if ($competence_allowed or ($n > 0 and $P_ID == $id)) {
        if ($P_ID == $id) $t='Modifier mes compétences';
        else $t='Modifier les compétences';
        echo " <input type=submit class='btn btn-default noprint' value=\"$t\" 
       onclick='bouton_redirect(\"qualifications.php?pompier=$P_ID&order=GRADE&from=personnel\");'> ";
    }
}

//=====================================================================
// liste des formations
//=====================================================================
if ( $tab == 3 ) {
    echo "<div align=center>";
    $query="select pf.PS_ID, p.TYPE, pf.PF_ID, pf.PF_COMMENT, pf.PF_ADMIS, DATE_FORMAT(pf.PF_DATE, '%d-%m-%Y') as PF_DATE, 
            pf.PF_RESPONSABLE, pf.PF_LIEU, pf.E_CODE, tf.TF_LIBELLE, pf.PF_DIPLOME,
            DATE_FORMAT(pf.PF_PRINT_DATE, '%d-%m-%Y %H:%i') as PF_PRINT_DATE,
            DATE_FORMAT(pf.PF_UPDATE_DATE, '%d-%m-%Y %H:%i') as PF_UPDATE_DATE, 
            pf.PF_PRINT_BY, pf.PF_UPDATE_BY, p.PS_PRINTABLE
            from personnel_formation pf, type_formation tf, poste p
            where tf.TF_CODE=pf.TF_CODE
            and p.PS_ID = pf.PS_ID
            and pf.P_ID=".$P_ID."
            order by pf.".$order;
    if ( $order == 'PF_DATE' ) $query .= ' desc';
    $result=mysqli_query($dbc,$query);
    $num=mysqli_num_rows($result);

    if ( $num > 0 ) {
        echo "<small>Télécharger le fichier excel</small> <a href=\"formations_xls.php?pompier=$P_ID&order=$order\" target=_blank>
            <i class='far fa-file-excel fa-lg' style='color:green; padding:5px;' title=\"Télécharger le fichier excel\" class=\"noprint\" ></i></a>";
        echo "<table cellspacing=0 border=0 bgcolor=$mylightcolor>";
        echo "<tr class=TabHeader>
          <td width=70><a href=upd_personnel.php?pompier=".$P_ID."&order=PS_ID class=TabHeader>Type</a></td>
          <td width=100><a href=upd_personnel.php?pompier=".$P_ID."&order=PF_DATE class=TabHeader>Fin Formation</a></td>
          <td width=200><a href=upd_personnel.php?pompier=".$P_ID."&order=TF_CODE class=TabHeader>Type de formation</a></td>
          <td width=100><a href=upd_personnel.php?pompier=".$P_ID."&order=PF_DIPLOME class=TabHeader>N° diplôme</a></td>
          <td width=70><a href=upd_personnel.php?pompier=".$P_ID."&order=PF_UPDATE_BY class=TabHeader>info</a></td>
          <td width=180 class='hide_mobile'><a href=upd_personnel.php?pompier=".$P_ID."&order=PF_LIEU class=TabHeader>Lieu</a></td>
          <td width=150 class='hide_mobile'><a href=upd_personnel.php?pompier=".$P_ID."&order=PF_RESPONSABLE class=TabHeader>Délivré par</a></td>
          <td width=160 class='hide_mobile'><a href=upd_personnel.php?pompier=".$P_ID."&order=PF_COMMENT class=TabHeader></a></td>";
        if ($change_formation_allowed)
               echo "<td width=20></td>
                     <td width=20></td>";
        echo "</tr>";
        $i=0;
        while (custom_fetch_array($result)) {
            $i=$i+1;
            if ( $i%2 == 0 ) {
                $mycolor=$mylightcolor;
            }
            else {
                $mycolor="#FFFFFF";
            }
           
            echo "<tr bgcolor=$mycolor class=trcolor>";
            echo "<td ><b>".$TYPE."</b></td>";
            
            if ( intval($E_CODE) <> 0) {
                $query2="select date_format(max(eh.EH_DATE_DEBUT),'%d-%m-%Y'), date_format(max(ep.EP_DATE_DEBUT),'%d-%m-%Y') 
                    from evenement_participation ep, evenement_horaire eh
                    where ep.E_CODE = eh.E_CODE
                    and ep.P_ID=".$P_ID."
                    and ep.E_CODE=".$E_CODE;
                $result2=mysqli_query($dbc,$query2);
                $row2=@mysqli_fetch_array($result2);
                $datedeb = $row2[0];
                $epdatedeb = $row2[1];
                if ( $epdatedeb <> "" ) $datedeb = $epdatedeb;
                if ( $datedeb == "" ) $datedeb = $PF_DATE;
                
                echo "<td >".$datedeb."</td>";
                echo "<td ><a href=evenement_display.php?evenement=".$E_CODE."&from=formation>".$TF_LIBELLE."</a></td>";
            }
            else {
                echo "<td >".$PF_DATE."</td>";
                echo "<td class=small>".$TF_LIBELLE."</td>";
            }
            echo "<td ><b>".$PF_DIPLOME."</b></td>";
           
            echo "<td >";
              if ( intval($E_CODE) <> 0 ) {
                $querye="select TF_CODE, E_CLOSED from evenement where E_CODE=".$E_CODE;
                $resulte=mysqli_query($dbc,$querye);
                custom_fetch_array($resulte);
                
                if ((check_rights($id,4,"$P_SECTION") or check_rights($id,48,"$P_SECTION") or $id == $P_ID) and $E_CLOSED == 1) {
                    echo " <a href=pdf_attestation_formation.php?section=".$P_SECTION."&evenement=".$E_CODE."&P_ID=".$P_ID." target=_blank>
                        <i class='far fa-file-pdf noprint' style='color:red;' title=\"imprimer l'attestation de formation\"></i></a>";
                }
                if ( $PS_PRINTABLE == 1 ) {
                    if ( $id == $P_ID or check_rights($id,48,"$P_SECTION")) {
                        if (! check_rights($id,54) and $TF_CODE == "I" and $PF_DIPLOME <> "")
                            echo " <a href=pdf_diplome.php?section=".$P_SECTION."&evenement=".$E_CODE."&mode=4&P_ID=".$P_ID." target=_blank>
                            <i class='far fa-file-pdf noprint' style='color:red;' title=\"imprimer le duplicata du diplôme\"></i></a>";
                    }
                }
            }
            $popup="";
            if ( $PF_UPDATE_BY <> "" )
                   $popup="Enregistré par:
".ucfirst(get_prenom($PF_UPDATE_BY))." ".strtoupper(get_nom($PF_UPDATE_BY))." le ".$PF_UPDATE_DATE."
";
            if ( $PF_PRINT_BY <> "" )        
                $popup .="Diplôme imprimé par:
".ucfirst(get_prenom($PF_PRINT_BY))." ".strtoupper(get_nom($PF_PRINT_BY))." le ".$PF_PRINT_DATE;
           
            if ( $popup <> "" ) 
                   $popup=" <i class='far fa-sticky-note noprint' title=\"".$popup."\"></i>";
            echo $popup."</td>";
           
            echo "<td class='small hide_mobile'>".$PF_LIEU."</td>
             <td class='small hide_mobile'>".$PF_RESPONSABLE."</td>
             <td class='small hide_mobile'>".$PF_COMMENT."</td>";
            if ($change_formation_allowed) {
                echo "<td align=center>
                   <a href=personnel_formation.php?P_ID=".$P_ID."&PS_ID=".$PS_ID."&PF_ID=".$PF_ID."&action=update>
                 <i class='fa fa-edit noprint' title='modifier cette formation'></i></a></td>";
                echo "<td align=center>
                   <a href=del_personnel_formation.php?P_ID=".$P_ID."&PS_ID=".$PS_ID."&PF_ID=".$PF_ID."&from=formations>
                 <i class='fa fa-trash-alt noprint' title='supprimer cette ligne'></i></a></td>";
            }
            echo "</tr>";
        }

        echo "</table>";
    }
    else {
        echo "<p>Aucune information disponible pour les formations suivies.<br>";
        $action = "nothingyet";
    }
}

if ( $tab == 4 ) {
    
    echo "<div align=center>";
    // required for pagination
    $_SESSION["from_inscriptions"]=1;

    //=====================================================================
    // affichage des engagements futurs
    //=====================================================================
    $out ="";
    include_once ("fonctions_infos.php");
    $query = write_query_participations($P_ID);
    // ====================================
    // pagination
    // ====================================
    $res=mysqli_query($dbc,$query);
    $number=mysqli_num_rows($res);

    $query2="select count(1) as NB from evenement_participation ep, evenement e, evenement_horaire eh
        where ep.P_ID=".$pompier."
        and eh.e_code = e.e_code
        and ep.eh_id=eh.eh_id
        and ep.E_CODE=e.E_CODE
        and e.E_CANCELED = 0
        and date_format(eh.eh_date_fin,'%Y-%m-%d') >= date_format(now(),'%Y-%m-%d')";
        
    if ( (! check_rights($id,9) and $id <> $P_ID ) or $gardes == 1 )
    $query2 .= " and e.e_visible_inside = 1";    
        
    $result2=mysqli_query($dbc,$query2);
    $row2=@mysqli_fetch_array($result2);
    $NB3=$row2["NB"];

    $query2="select count(1) as NB from astreinte a
         where a.P_ID=".$pompier."
        and date_format(a.AS_FIN,'%Y-%m-%d') >= date_format(now(),'%Y-%m-%d')";
    $result2=mysqli_query($dbc,$query2);
    $row2=@mysqli_fetch_array($result2);
    $NB3=$NB3+$row2["NB"];

    if ($number > 0 ) {
        echo "<a href=\"evenement_ical.php?pid=$P_ID&section=$section\" target=_blank><i class='far fa-calendar-alt fa-lg' title=\"Télécharger le fichier ical\" class=\"noprint\" ></i></a> ";
        echo "<span class='badge'>".$NB3."</span> futures participations <span class='badge'>".$number."</span> au total";
        echo " <a href='#'><i class='far fa-file-excel fa-lg' style='color:green;' id='StartExcel' height='20' border='0' 
            title='Extraire la liste des participations au format Excel' onclick=\"window.open('personnel_evenement_xls.php?pid=$P_ID')\" class='noprint'/></i></a>";
        if ( $syndicate == 1 )
            echo " <a href='#'><i class='far fa-file-excel fa-lg' style='color:green;' id='StartExcel' height='20' border='0' 
            title='Extraire la liste des réunions au format Excel' onclick=\"window.open('personnel_reunion_xls.php?pid=$P_ID')\" class='noprint'/></i></a>";
    }
    require_once('paginator.class.php');
    $pages = new Paginator; 
    $pages->page_name = $basedir."/upd_personnel.php";
    $pages->items_total = $number;
    $pages->mid_range = 9;
    if ( ! isset($_SESSION["ipp"])) $_SESSION["ipp"]=20;
    $pages->ipp = $_SESSION["ipp"];
    $pages->paginate();  
    if ( $number > 10 ) {
        echo $pages->display_pages();
        echo $pages->display_jump_menu(); 
        echo $pages->display_items_per_page(); 
        $query .= $pages->limit;
    }
    $res=mysqli_query($dbc,$query);

    if ($number > 0 ) {
        echo "    <table cellspacing=0 border=0>
            <tr class=TabHeader>
               <td width=30>Type</td>
                 <td width=180>Date</td>
                 <td width=100>Heures</td>
                 <td width=250 class='hide_mobile'>Lieu</td>
                 <td width=300>Description</td>
                 <td width=20></td>
            </tr>";
        while($row=mysqli_fetch_array($res)){
            $te_libelle=$row['te_libelle'];
            $future=$row['future'];
            $e_code=$row['e_code'];
            if ($future== 0) $class="class='mediumgrey trcolor'";
            else $class="class='trcolor'";
             
            if ( $e_code == 0 ) {
                //astreinte
                $datedeb=$row['datedeb'];
                $datefin=$row['datefin'];
                echo "<tr $class>";
                echo "<td align=left><i class='fa fa-star' style='color:yellow;' title=\"".$te_libelle."\"></i></td>";
                if ( $datedeb !=$datefin ) echo "<td>".$datedeb." au ".$datefin."</td>";
                else echo "<td>".$row['datedeb']." </td>";
                echo "<td></td>";
                echo "<td class='hide_mobile'>".$row['e_lieu']."</td>";
                $tmp=explode ( "-",$row['datedeb']); $year=$tmp[2]; $month=$tmp[1]; $day=$tmp[0];
                echo "<td><a href=\"astreinte_edit.php?from=personnel&astreinte=".$row['eh_id']."\" $class>";
                echo $row['e_libelle'];
                echo "</a></td>";
                echo "<td></td>";
                echo "</tr>";
            }
            else {
                // evenement ou garde
                if ( $row['epdatedeb'] == "" ) {
                    $datedeb=$row['datedeb'];
                    $datefin=$row['datefin'];
                    $debut=$row['eh_debut'];
                    $fin=$row['eh_fin'];
                    $duree=$row['eh_duree'];
                }
                else {
                    $datedeb=$row['epdatedeb'];
                    $datefin=$row['epdatefin'];
                    $debut=$row['ep_debut'];
                    $fin=$row['ep_fin'];
                    $duree=$row['ep_duree'];
                }
                $eh_description=$row['eh_description'];
                if ( $eh_description <> '') $eh_description = " - ".$eh_description; 
             
                // commentaire sur l'inscription
                $cmt="";
                if ( $row['tp_id'] > 0 ) {
                    $cmt=get_fonction($row['tp_id'])."\n";
                }
                $cmt .= $row['ep_comment'];
             
                if ( $row['ep_flag1'] == 1 ) {
                    $txtimg="sticky-note";
                    if ($nbsections > 0 ) $as = 'SPP';
                    else $as = 'salarié(e)';
                    $cmt="Participation en tant que ".$as." \n".$cmt;
                }
                else if ( $cmt  <> '' ) $txtimg="sticky-note";    

                if ( $cmt <> '' ) $txtimg="<i class='far fa-".$txtimg."' title=\"".$cmt."\" ></i>";
                else $txtimg="";
             
                $EP_ASA=$row['ep_asa'];
                $EP_DAS=$row['ep_das'];
                if ( $EP_ASA == 1 ) $asa="<font size=1><a href=\"javascript:alert('Autorisation spéciale absence');\" title=\"Autorisation spéciale d'absence\">ASA</a></font>";
                else $asa='';
                if ( $EP_DAS == 1 ) $das="<font size=1><a href=\"javascript:alert('Décharge activité de service');\" title=\"Décharge d'activité de service\">DAS</a></font>";
                else $das='';

                // affichage spécial pour les gardes
                if ( $row['te_code'] == 'GAR' and $row['EQ_ID'] > 0 ) {
                    $datefin=$datedeb;
                    $libelle=$row['EQ_NOM']." ".$duree."h";
                    if ( $row['eh_id'] == 1 ) {
                        if ( intval($duree) < 24 ) $libelle.=" jour";
                    }
                    else $libelle.=" nuit";
                }
                else  {
                    $n=get_nb_sessions($e_code);
                    if ( $n > 1 ) $part=" partie ".$row['eh_id']."/".$n;
                    else $part="";
                    $libelle=$row['e_libelle']." ".$part." ".$eh_description; 
                }
             
                echo "<tr $class >";
                if (  $row['e_visible_inside'] == 0 ) $libelle .= " <i class='fa fa-exclamation-triangle' style='color:orange;' title='ATTENTION événement caché, seules les personnes ayant la permission n°9 peuvent le voir'></i>";
                if ( $row['EQ_ICON'] == "" ) $img="images/evenements/".$row['te_icon'];
                else $img=$row['EQ_ICON'];
                echo "<td align=left><img border=0 src=".$img." class='img-max-20' title=\"".$te_libelle."\"></td>";
                if ( $datedeb !=$datefin ) echo "<td>".$datedeb." au ".$datefin."</td>";
                else echo "<td>".$datedeb."</td>";
                echo "<td >".$debut."-".$fin."</td>";
                echo "<td class='hide_mobile'>".$row['e_lieu']."</td>";
                echo "<td> <a href=\"evenement_display.php?evenement=".$e_code."&from=personnel&pid=".$P_ID."\" $class><small>".$libelle."</small></a></td>";
                echo "<td align=center><a href='#' data-toggle='popover' data-placement='left' title=\"Détail de la participation\" data-trigger='hover' data-content=\"".$cmt."\">".$txtimg."</a>".$asa." ".$das."</td>";
                echo "</tr>";
            }
        }
        $out .= "</table>";
    }
    else {
        $out= "<p>Aucune information disponible, concernant les participations.<br>";
}
echo $out;
}

if ( $tab == 5 ) {
    echo "<div align=center>";
//=====================================================================
// véhicules, matériel et animaux affectés
//=====================================================================

$query1="select p.P_ID, p.P_NOM, p.P_PRENOM, p.P_SEXE, p.P_CIVILITE, s.S_CODE 
        from pompier p, section s
        where p.P_OLD_MEMBER = 0
        and p.P_SECTION = s.S_ID
        and p.P_MAITRE=".$pompier;
$result1=mysqli_query($dbc,$query1);

$query2="select v.V_ID, s.S_CODE, v.V_MODELE, v.TV_CODE, v.V_IMMATRICULATION, tv.TV_LIBELLE
         from vehicule v, type_vehicule tv, section s, vehicule_position vp
         where v.TV_CODE=tv.TV_CODE
         and vp.VP_ID = v.VP_ID
         and s.S_ID=v.S_ID
         and vp.VP_OPERATIONNEL >=0
         and v.AFFECTED_TO=".$pompier;
$result2=mysqli_query($dbc,$query2);

$query3="select s.S_CODE, cm.PICTURE, tm.TM_DESCRIPTION, tm.TM_USAGE, tm.TM_CODE, m.MA_ID, m.MA_MODELE
        from materiel m, type_materiel tm, categorie_materiel cm, section s, vehicule_position vp
         where cm.TM_USAGE=tm.TM_USAGE
         and tm.TM_USAGE <> 'Habillement'
         and s.S_ID=m.S_ID
         and vp.VP_ID = m.VP_ID
         and tm.TM_ID=m.TM_ID
         and vp.VP_OPERATIONNEL >=0
         and m.AFFECTED_TO=".$pompier."
         order by tm.TM_USAGE, tm.TM_CODE asc";
$result3=mysqli_query($dbc,$query3);

if (mysqli_num_rows($result2) > 0 || mysqli_num_rows($result3) > 0 || mysqli_num_rows($result1) > 0) {
       echo "<table cellspacing=0 border=0 >";
       echo "<tr class=TabHeader>";
    echo "<td colspan=2>Véhicules, Matériel non réformé et animaux affectés</b></td>";
    
    while ($row1=@mysqli_fetch_array($result1)) {
            $P_ID=$row1["P_ID"];
            $_S_CODE=$row1["S_CODE"];
            $P_NOM=strtoupper($row1["P_NOM"]);
            $P_PRENOM=my_ucfirst($row1["P_PRENOM"]);
            echo "<tr class=trcolor><td width=50><img src=images/dog_small.png height=18>";
            $cmt="<i> (".$_S_CODE.")</i>";
            echo "<td width=350>
            <a href=upd_personnel.php?pompier=".$P_ID.">".$P_NOM." ".$P_PRENOM."</a>".$cmt."</td></tr>";
    }
    
    while ($row2=@mysqli_fetch_array($result2)) {
            $V_ID=$row2["V_ID"];
            $_S_CODE=$row2["S_CODE"];
            $V_MODELE=$row2["V_MODELE"];
            $TV_CODE=$row2["TV_CODE"];
            $TV_LIBELLE=$row2["TV_LIBELLE"];
            $V_IMMATRICULATION=$row2["V_IMMATRICULATION"];
                echo "<tr class=trcolor><td width=50><i class='fa fa-car fa-lg' title=\"".$TV_LIBELLE."\"></i></td>";
            $cmt="<i> (".$_S_CODE.")</i>";
            echo "<td width=350>
            <a href=upd_vehicule.php?from=personnel&vid=".$V_ID.">".$TV_CODE." ".$V_MODELE." ".$V_IMMATRICULATION."</a>".$cmt."</td></tr>";
    }
    while ($row3=@mysqli_fetch_array($result3)) {
            $PICTURE=$row3["PICTURE"];
            $TM_DESCRIPTION=$row3["TM_DESCRIPTION"];
            $TM_USAGE=$row3["TM_USAGE"];
            $MA_MODELE=$row3["MA_MODELE"];
            $TM_CODE=$row3["TM_CODE"];
            $MA_ID=$row3["MA_ID"];
            $_S_CODE=$row3["S_CODE"];
            $cmt="<i> (".$_S_CODE.")</i>";
            echo "<tr class=trcolor><td width=50><i class='fa fa-".$PICTURE." fa-lg' style='color:purple;' title=\"".$TM_DESCRIPTION."\"></i></td>";
            echo "<td width=350>
            <a href=upd_materiel.php?from=personnel&mid=".$MA_ID.">".$TM_CODE." ".$MA_MODELE."</a>".$cmt."</td></tr>";
    }
            
    echo "</table>";
}
else
    echo "<p>Aucun véhicule ou matériel affecté.<br>";

}

if ( $tab == 6 ){
echo "<div align=center>";
//=====================================================================
// documents
//=====================================================================
    include_once ("fonctions_documents.php");
    $nb=$NB5;
    if ( $P_STATUT == 'BEN' ) $nb++;  //recu adhesion
    if ( $P_STATUT == 'BEN' or ($P_STATUT == 'SAL' and $nbsections == 0 and $syndicate == 0  )) $nb++; // passeport benevole
    if ( $syndicate == 1 ) {
        $nb = $nb + $NB11 + $NB12;
    }
   
    if ( $nb == 0 ) {
        echo "Aucun document attaché.<br>";
    }
    else {
        echo "<table cellspacing=0 border=0>";
        if ( $document_security == 1 ) $s="Secu.";
        else $s="";
        echo "<tr>
            <td width=30 class=TabHeader></td>
            <td width=300 class=TabHeader>Documents attachés</td>
            <td width=50 class=TabHeader align=center>".$s."</td>
            <td width=120 class=TabHeader align=center>Auteur</td>
            <td width=100 class=TabHeader align=center>Date</td>
            <td width=20 class=TabHeader>Suppr.</td>
        </tr>";
      
        // RECU ADHESION
        if ( $P_STATUT == 'BEN' )
            echo "<tr class=trcolor>
                <td align=center><a href=pdf_document.php?section=".$P_SECTION."&P_ID=".$pompier."&mode=19 target=_blank><i class='far fa-file-pdf' style='color:red;'></i></a></td>
                <td ><a href=pdf_document.php?section=".$P_SECTION."&P_ID=".$pompier."&mode=19 target=_blank><font size=1>Reçu adhésion</font></a></td>
                <td colspan=4></td>
            </tr>";
        
        // Livret du bénévole
        if ( $P_STATUT == 'BEN' or ($P_STATUT == 'SAL' and $nbsections == 0 and $syndicate == 0  )) {
            echo "<tr class=trcolor>
                <td align=center><a href=pdf_livret.php?P_ID=".$pompier." target=_blank><i class='far fa-file-pdf' style='color:red;'></i></a></td>
                <td ><a href=pdf_livret.php?P_ID=".$pompier." target=_blank><font size=1>Passeport du bénévole</font></a></td>
                <td colspan=4></td>
            </tr>";    
        }
        // carte adhérent
        if ( $show_carte ) {
            $a="<a href=pdf_carte_adherent.php?P_ID=".$pompier." target=_blank title='Voir la carte adhérent'>";
            echo "<tr class=trcolor>
                <td align=center>".$a."<i class='fa fa fa-id-card fa-lg' style='color:green;'></i></a></td>
                <td >".$a."<font size=1>Carte adhérent</font></a></td>
                <td colspan=4></td>
                </tr>";
            
        }
        // courrier nouvel adhérent
        if ( $show_courrier_adherent ) {
            $a="<a href=pdf_courrier_nouvel_adherent.php?P_ID=".$pompier." target=_blank title='Voir le courrier nouvel adhérent'>";
            echo "<tr class=trcolor>
                <td align=center>".$a."<i class='far fa-file-pdf' style='color:red;'></i></a></td>
                <td >".$a."<font size=1>Courrier nouvel adhérent</font></a></td>
                <td colspan=4></td>
                </tr>";
            
        }

        // attestation fiscale
        if ( $syndicate == 1  and $NB11 > 0 ) {
            for ( $k = 1; $k <= $NB11; $k++ ) { 
                $fiscal_year = date('Y') - $k;
                $a="<a href=pdf_attestation_fiscale.php?P_ID=".$pompier."&year=".$fiscal_year." target=_blank title='Voir cette attestation fiscale'>";
                echo "<tr class=trcolor>
                <td align=center>".$a."<i class='far fa-file-pdf' style='color:red;'></i></a></td>
                <td >".$a."<font size=1>Attestation fiscale ".$fiscal_year."</font></a></td>
                <td colspan=4></td>
                </tr>";
            }
        }
        // ASA 
        if ( $NB12 > 0) {
            while ( custom_fetch_array($result_asa) ) {
                $query2 = "select e.E_LIBELLE, year(eh.EH_DATE_DEBUT) YEAR_DEBUT
                            from evenement e, evenement_horaire eh where eh.E_CODE = e.E_CODE and e.E_CODE=".$E_CODE_ASA;
                $result2=mysqli_query($dbc,$query2);
                custom_fetch_array($result2);
                $a="<a href=pdf_asa.php?P_ID=".$pompier."&evenement=".$E_CODE_ASA." target=_blank title=\"Voir cette Autorisation Spéciale d'Absence\">";
                echo "<tr class=trcolor>
                <td align=center>".$a."<i class='far fa-file-pdf' style='color:red;'></i></a></td>
                <td >".$a."<font size=1>ASA ".$E_LIBELLE." ".$YEAR_DEBUT."</font></a></td>
                <td colspan=4></td>
                </tr>";
                $a="<a href=pdf_asa.php?P_ID=".$pompier."&evenement=".$E_CODE_ASA."&type=OM target=_blank title=\"Voir cet Ordre de mission\">";
                echo "<tr class=trcolor>
                <td align=center>".$a."<i class='far fa-file-pdf' style='color:red;'></i></a></td>
                <td >".$a."<font size=1>OM ".$E_LIBELLE." ".$YEAR_DEBUT."</font></a></td>
                <td colspan=4></td>
                </tr>";
            }
        }

        // DOCUMENTS ATTACHES
        $mypath=$filesdir."/files_personnel/".$pompier;
        if (is_dir($mypath)) {
            $dir=opendir($mypath); 
            $querys="select DS_ID, DS_LIBELLE,F_ID from document_security";
       
            while ($file = readdir ($dir)) {
                $securityid = "1";
                $securitylabel ="Public";
                $fonctionnalite = "0";
                $author = "";
                $fileid = "0";
        
                if ($file != "." && $file != ".." and (file_extension($file) <> "db")) {
                    $query="select d.D_ID,d.S_ID,d.D_NAME,d.TD_CODE,d.DS_ID, td.TD_LIBELLE, p.P_NOM, p.P_PRENOM,
                      ds.DS_LIBELLE, ds.F_ID, d.D_CREATED_BY, date_format(d.D_CREATED_DATE,'%Y-%m-%d %H-%i') D_CREATED_DATE
                    from document d left join type_document td on td.TD_CODE=d.TD_CODE
                    left join pompier p on p.P_ID=d.D_CREATED_BY, document_security ds
                    where d.DS_ID=ds.DS_ID
                    and d.P_ID=".$pompier."
                    and d.D_NAME='".$file."'";
                    
                    $result=mysqli_query($dbc,$query);
                    $nb=mysqli_num_rows($result);
                    $row=@mysqli_fetch_array($result);
            
                    if ($row["F_ID"] == 0 
                        or check_rights($id, $row["F_ID"], "$P_SECTION")
                        or $update_allowed
                        or $row["D_CREATED_BY"] == $id
                        or $pompier == $id) {
                        $visible=true;
                    }
                    else $visible=false;
            
                    $myimg=get_smaller_icon(file_extension($file));
                   
                    $filedate = $row["D_CREATED_DATE"];
                    
                    if ( $nb > 0 ) {
                        $securityid = $row["DS_ID"];
                        $securitylabel =$row["DS_LIBELLE"];
                        $fonctionnalite = $row["F_ID"];
                        $author = $row["D_CREATED_BY"];
                        $fileid = $row["D_ID"];
                    }
                    if ( $visible ) 
                        echo "<tr class=trcolor><td align=center>
                        <a href=showfile.php?section=".$P_SECTION."&pompier=".$pompier."&file=".$file." target=_blank>".$myimg."</a></td>
                        <td ><a href=showfile.php?section=".$P_SECTION."&pompier=".$pompier."&file=".$file." target=_blank>
                          <font size=1>".$file."</font></a></td>";
                    else
                        echo "<tr class=trcolor><td align=center>".$myimg."</td>
                        <td ><font size=1 color=red> ".$file."</font></td>";
                
                    echo "<td align=center>";
            
                    if ( $document_security == 0 ) $img="";
                    else if ( $securityid > 1 ) $img="<i class='fa fa-lock ' style='color:orange;' title=\"".$securitylabel."\" ></i>";
                    else $img="<i class='fa fa-unlock' title=\"".$securitylabel."\" ></i>";
    
                    if (($update_allowed or $pompier == $id ) and $fileid > 0 ) {
                        $url="document_modal.php?docid=".$fileid."&pid=".$pompier;
                        print write_modal( $url, "doc_".$fileid, $img);
                    }
                    else echo $img;
    
                    echo "</td>";
            
                    if ( $author <> "" ) $author = "<a href=upd_personnel.php?pompier=".$author.">".
                        my_ucfirst($row["P_PRENOM"])." ".strtoupper($row["P_NOM"])."</a>";

                    echo "<td align=center><font size=1>".$author."</a></font></td>";
                
                    echo "<td align=center>
                        <font size=1>".$filedate."</td>";
                
                    if ($update_allowed)
                        echo "<td align=center><a href=\"javascript:deletefile('".$pompier."','".$fileid."','".$file."')\">
                        <i class ='fa fa-trash-alt' title='supprimer' ></i></a></td>";
                    else echo "<td></td>";
                    echo "</tr>";
                }
            }
        }
        echo "</table>";// end left table
    }
    if ( ( $update_allowed or $pompier == $id ) and ! $iphone) {
        echo "<p><input type='button' class='btn btn-default noprint' id='userfile' name='userfile' value='Ajouter fichier'
            onclick=\"openNewDocument('".$pompier."','".$P_SECTION."');\" >";
    }   
    echo "<p style='padding-top:150px'>";
}

if ( $tab == 8 and ($update_allowed or $P_ID == $pompier )) {
echo "<div align=center>";
//=====================================================================
// cotisations
//=====================================================================

// required for pagination
$_SESSION["from_cotisation"]=1;
$csrf = generate_csrf('cotisation');

if ( $cotisations_allowed ) {
    forceReloadJS('js/rib.js');
    echo "<form name='bic' method=POST action='save_info_adherent.php'>";
    print insert_csrf('update_personnel');
    $disabled='';
}
else $disabled='disabled';
echo "<input type=hidden name=P_ID value=".$P_ID.">";

$cotisation=get_montant_cotisation($pompier);
$COTISATION_MENSUELLE=round($cotisation / 12, 2 );
echo "<table cellspacing=0 border=0 class='noBorder'>";
echo "<tr style='background-color:white;'>";
echo "<td >Montant annuel </td>
    <td><b>".$cotisation." ".$default_money_symbol."</b>";
if ( $syndicate == 1 ) echo ", soit <b>".$COTISATION_MENSUELLE." ".$default_money_symbol."</b> / mois</td>";

$rembourse_style ="color: Black;";
if ( intval($MONTANT_REGUL) <> 0 ) {
    if ( $MONTANT_REGUL > 0 )  $rembourse_style ="color: Red;";
    else $rembourse_style ="color: Green;";
}
if ( $TP_ID == 1 ) $ti="Si des prélèvements ont été rejetés et doivent être représentés à la banque, ou si des sommes doivent être remboursées (montant négatif), indiquer les montants cumulés ici. Ils seront automatiquement ajoutés au prochain prélèvement.";
else  $ti="Montant complémentaire à payer par la personne (si la valeur est positive ) ou à lui rembourser (si la valeur est négative)";
if ( $MONTANT_REGUL < 0 ) $ta="A rembourser";
else if ( $TP_ID == 1 ) $ta="A représenter";
else $ta="A payer";
echo "<td width=100 align=right>".$ta."</td><td>";
if ( $disabled == '' ) {
    echo "<input type=text size=3 name='montant_regul' id ='montant_regul'  value='".$MONTANT_REGUL."'
            style='font-weight: bold;".$rembourse_style."'
            onchange='checkFloat(form.montant_regul,\"".$MONTANT_REGUL."\")'
            title=\"".$ti."\"><b> ".$default_money_symbol."</b></td>";
}
else
    echo "<b>".$MONTANT_REGUL." ".$default_money_symbol."</b>";
echo "</td>";

if ( $bank_accounts == 1 )  {
    echo "<tr align=left style='background-color:white;'>";
    echo "<td>Type paiement</td>";
    echo "<td align=left>";
    if ( $disabled == '' ) {
        $query2="select TP_ID, TP_DESCRIPTION from type_paiement";
        if ( $bank_accounts == 0 ) $query2 .=" where ( TP_ID <> 1 or TP_ID =".$TP_ID.")";
        $query2 .=" order by TP_DESCRIPTION" ;
        $result2=mysqli_query($dbc,$query2);
        echo "<select name='type_paiement'>";
            while ($row2=@mysqli_fetch_array($result2)) {
                    $_TP_ID=$row2["TP_ID"];
                    $_TP_DESCRIPTION=$row2["TP_DESCRIPTION"];
                    if ( $TP_ID == $_TP_ID ) $selected='selected';
                    else $selected='';
                    echo "<option value='$_TP_ID' $selected>$_TP_DESCRIPTION</option>";
        }
    }
    else {
        echo "<b>".$MODE_PAIEMENT."</b>";
    }
    echo "</select></td>";
}
else echo "<input type=hidden name='type_paiement' value='".$TP_ID."'>";

if ( $cotisations_allowed ) {
    $url="observations_modal.php?person=".$pompier;
    echo "<td width=100 align=right >Observations </td><td>";
    if ( $OBSERVATION == '' ) $fa='far';
    else $fa='fas';
    print write_modal( $url, "observation", "<i class='".$fa." fa-file-alt fa-lg' title=\"Cliquez pour modifier:\n".$OBSERVATION."\"></i>");
    echo "</td>";
}

echo "</tr></table>";

if ( $bank_accounts == 1 ) {
     // compte bancaire
        $query="select BIC,IBAN,date_format(UPDATE_DATE, '%d-%m-%Y %H:%i') as UPDATE_DATE from compte_bancaire where CB_TYPE='P' and CB_ID=".$pompier;
        $result=mysqli_query($dbc,$query);

        $row=@mysqli_fetch_array($result);
        $BIC=$row["BIC"];
        $IBAN=$row["IBAN"];
        $UPDATE_DATE=$row["UPDATE_DATE"];
        $IBAN1=substr($IBAN,0,4);
        $IBAN2=substr($IBAN,4,4);
        $IBAN3=substr($IBAN,8,4);
        $IBAN4=substr($IBAN,12,4);
        $IBAN5=substr($IBAN,16,4);
        $IBAN6=substr($IBAN,20,4);
        $IBAN7=substr($IBAN,24,4);
        $IBAN8=substr($IBAN,28,4); 

        if ( $UPDATE_DATE <> "" ) $UPDATE_DATE = "<span class=small>modifié le ".$UPDATE_DATE."</span>";
    
        echo "<table cellspacing=0 border=0 class='noBorder' >";
        if ( $cotisations_national_allowed ) {
            echo "<tr height=20 valign=bottom style='background-color:white;'>
            <td colspan=2><b>Compte bancaire </b>".$UPDATE_DATE."</td></tr>
            <tr style='background-color:white;'>
            <td width=50> BIC </td>
            <td><input type='text' name='bic' id='bic' size=11 maxlength=11 class='inputRIB-lg11' 
                title='11 caractères, chiffres et lettres' value='$BIC' onchange='isValid5(form.bic,\"$BIC\",\"11\");'></td>
            </tr>";
       
            // IBAN / BIC
            // http://fr.wikipedia.org/wiki/ISO_13616
        
            echo "<tr style='background-color:white;'><td>IBAN</td>
            <td><input type='text' name='iban1' id='iban1' size=4 maxlength=4 class='inputRIB-lg4' title='4 caractères, chiffres et lettres majuscules' value='$IBAN1' 
                onchange='isValid5(form.iban1,\"$IBAN1\",\"4\");' onKeyUp=\"suivant(this,'iban2',4)\">
                <input type='text' name='iban2' id='iban2' size=4 maxlength=4 class='inputRIB-lg4' title='4 caractères, chiffres et lettres majuscules' value='$IBAN2' 
                onchange='isValid5(form.iban2,\"$IBAN2\",\"4\");' onKeyUp=\"suivant(this,'iban3',4)\">
                <input type='text' name='iban3' id='iban3' size=4 maxlength=4 class='inputRIB-lg4' title='4 caractères, chiffres et lettres majuscules' value='$IBAN3' 
                onchange='isValid5(form.iban3,\"$IBAN3\",\"4\");' onKeyUp=\"suivant(this,'iban4',4)\">
                <input type='text' name='iban4' id='iban4' size=4 maxlength=4 class='inputRIB-lg4' title='4 caractères, chiffres et lettres majuscules' value='$IBAN4' 
                onchange='isValid5(form.iban4,\"$IBAN4\",\"4\");' onKeyUp=\"suivant(this,'iban5',4)\">
                <input type='text' name='iban5' id='iban5' size=4 maxlength=4 class='inputRIB-lg4' title='4 caractères maximum, chiffres et lettres majuscules' value='$IBAN5' 
                onchange='isValid4(form.iban5,\"$IBAN5\",\"4\");' onKeyUp=\"suivant(this,'iban6',4)\">
                <input type='text' name='iban6' id='iban6' size=4 maxlength=4 class='inputRIB-lg4' title='4 caractères maximum, chiffres et lettres majuscules' value='$IBAN6' 
                onchange='isValid4(form.iban6,\"$IBAN6\");' onKeyUp=\"suivant(this,'iban7',4)\">
                <input type='text' name='iban7' id='iban7' size=4 maxlength=4 class='inputRIB-lg4' title='4 caractères maximum, chiffres et lettres majuscules' value='$IBAN7' 
                onchange='isValid4(form.iban7,\"$IBAN7\");' onKeyUp=\"suivant(this,'iban8',4)\">
                <input type='text' name='iban8' id='iban8' size=4 maxlength=4 class='inputRIB-lg4' title='4 caractères maximum, chiffres et lettres majuscules' value='$IBAN8' 
                onchange='isValid4(form.iban8,\"$IBAN8\");' onKeyUp=\"suivant(this,'iban8',4)\">";
            $errstyle="style='display:none'";
            $successstyle="style='display:none'";
            $warnsstyle="style='display:none'";
            if ( $IBAN7 == "" or $IBAN2 == "") $warnsstyle="";
            else {
                if ( isValidIban($IBAN1.$IBAN2.$IBAN3.$IBAN4.$IBAN5.$IBAN6.$IBAN7.$IBAN8) ) $successstyle="";
                else $errstyle="";
            }
            echo " <span id='iban_warn' $warnsstyle><i class='fa fa-exclamation-triangle fa-lg' style='color:orange;' title='IBAN saisi incomplet, on ne peut pas vérifier si il est valide' ></i></span>
               <span id='iban_success' $successstyle><i class='fa fa-check-square fa-lg' style='color:green;' title='IBAN valide' ></i>
                    <a href='#'><i class='fa fa-copy fa-lg' title='Copier le numéro de compte IBAN' onclick='copy_to_clipboard(\"".$IBAN1.$IBAN2.$IBAN3.$IBAN4.$IBAN5.$IBAN6.$IBAN7.$IBAN8."\");'></i></a>
               </span>
               <span id='iban_error' $errstyle><i class='fa fa-ban fa-lg' style='color:red;' title='IBAN faux'></i></span>
              
              <a href='#'><i class='fa fa-eraser fa-lg' style='color:pink' title='Effacer données du BIC/IBAN' onclick='eraser_iban();'></i></a> </td>";
        }
        else {
            $helptext="Si vous souhaitez changer de coordonnées bancaires, veuillez faire parvenir un nouveau relevé d'identité bancaire et un mandat de prélevement sepa au secrétariat.";
            $helpicon="<a href='#' data-toggle='popover' title='Changer de compte' data-trigger='hover' data-content=\"".$helptext."\"><i class='fa fa-question-circle fa-lg' ></i></a>";
            echo "<tr style='background-color:white;'><td><b> BIC : $BIC </b> ".$helpicon."</td></tr>
                  <tr style='background-color:white;'><td><b> IBAN: $IBAN1 $IBAN2 $IBAN3 $IBAN4 $IBAN5 $IBAN6 $IBAN7 $IBAN8 </b></td></tr>";
        }
        echo "</table>";
}

if ( $cotisations_national_allowed )
    echo " <p><input type=submit class='btn btn-default noprint' value='sauver' id='save_rib'>";

echo "</form>";

if ( $cotisations_allowed ) $colspan=9;
else $colspan=8;


// afficher payements, pélèvements
         
$query="select pc.PC_ID ID, pc.ANNEE ANNEE, pc.PERIODE_CODE, pc.MONTANT MONTANT,
            date_format(pc.PC_DATE,'%d-%m-%Y') DATE, p.P_DESCRIPTION, pc.COMMENTAIRE, p.P_ORDER,
            pc.PC_DATE as RAW_DATE,
            
            tp.TP_DESCRIPTION, pc.NUM_CHEQUE, 
            pc.ETABLISSEMENT, pc.GUICHET, pc.COMPTE, pc.CODE_BANQUE, pc.REMBOURSEMENT,pc.BIC, pc.IBAN,
            0 as REJET,
            null as DATE_REGUL, null as MONTANT_REGUL, 0 as REPRESENTER, null as D_DESCRIPTION, 0 as REGULARISE, 0 as REGUL_ID, null as TR_DESCRIPTION
            
            from personnel_cotisation pc, type_paiement tp, periode p
            where pc.TP_ID=tp.TP_ID
            and pc.PERIODE_CODE=p.P_CODE
            and pc.P_ID=".$pompier;
$query .=" union 
            select r.R_ID ID, r.ANNEE ANNEE, r.PERIODE_CODE, r.MONTANT_REJET MONTANT,
            date_format(r.DATE_REJET,'%d-%m-%Y') DATE, p.P_DESCRIPTION, r.OBSERVATION as COMMENTAIRE, p.P_ORDER,
            r.DATE_REJET as RAW_DATE,
            
            null as TP_DESCRIPTION, null as NUM_CHEQUE, null as ETABLISSEMENT, null as GUICHET, null as COMPTE, null as CODE_BANQUE, 
            null as BIC, null as IBAN, 0 as REMBOURSEMENT,
            1 as REJET,
            date_format(r.DATE_REGUL,'%d-%m-%Y') DATE_REGUL, r.MONTANT_REGUL,
            r.REPRESENTER,d.D_DESCRIPTION,
            r.REGULARISE, r.REGUL_ID, tr.TR_DESCRIPTION
            
            from rejet r, periode p, defaut_bancaire d, type_regularisation tr
            where r.DEFAUT_ID=d.D_ID
            and r.REGUL_ID=tr.TR_ID
            and r.PERIODE_CODE=p.P_CODE
            and P_ID=".$pompier;
            
$query .= " order by RAW_DATE desc, ANNEE desc, P_ORDER desc, REJET desc";

$result=mysqli_query($dbc,$query);
$number=mysqli_num_rows($result); 

// ====================================
// pagination
// ====================================
require_once('paginator.class.php');
$pages = new Paginator;
$pages->page_name = $basedir."/upd_personnel.php"; 
$pages->items_total = $number;  
$pages->mid_range = 9;  
if ( ! isset($_SESSION["ipp"])) $_SESSION["ipp"]=20;
$pages->ipp = $_SESSION["ipp"];
$pages->paginate();  
if ( $number > 10 ) {
    echo $pages->display_pages();
    echo $pages->display_jump_menu(); 
    echo $pages->display_items_per_page(); 
    $query .= $pages->limit;
}
$result=mysqli_query($dbc,$query);

if ( $number > 0 ) {
    $c = $colspan -3;
    echo "<table cellspacing=0 border=0>
              <tr>
                   <td class=TabHeader colspan=".$c." >Paiements / Rejets (".$number.")</td>
                   <td class='hide_mobile TabHeader' colspan=3></td>
              </tr>";
    echo "<tr class='small trcolor'>
         <td width=25></td>
         <td width=200>Type</td>
         <td width=160>Période</td>
         <td width=70>Montant</td>
         <td class='hide_mobile' width=100>Date</td>
         <td class='hide_mobile'></td>
         <td class='hide_mobile'>Détail</td>";
    if ( $cotisations_allowed ) echo "<td width=60 align=left> Actions</td>";
    echo "</tr>";
    while ( custom_fetch_array($result)) {
            $OBS='';
            if ( $NUM_CHEQUE <> "" ) $OBS="Chèque n°".$NUM_CHEQUE;
            if ( $IBAN <> '' ) $OBS = "<small>".display_IBAN($IBAN)."</small>";
            else if ( $COMPTE <> '' ) $OBS = $CODE_BANQUE."-".$ETABLISSEMENT."-".$GUICHET."-".$COMPTE;
            if ( $REJET == 1 and $COMMENTAIRE == '' and $REGUL_ID > 0 )  $COMMENTAIRE = "par ".$TR_DESCRIPTION;
            if ( $COMMENTAIRE <> '' ) 
                $COMMENTAIRE="<a href='#' data-toggle='popover' title='Détails' data-trigger='hover' data-content=\"".$COMMENTAIRE."\"><i class='far fa-file-alt fa-lg'></i></a>";

            if ( $REJET == 1 ) { // rejet
                if ( $REGULARISE == 1 ) {
                    $img="<i class='far fa-check-square' style='color:green;' title='Rejet de paiement, mais a été régularisé'></i>";
                    $REGUL_CMT="Régul ".$MONTANT_REGUL.$default_money_symbol." le ".$DATE_REGUL;
                    $myclass='green12 trcolor';
                    $t="Rejet régularisé";
                }
                else if ( $REPRESENTER == 1 ) {
                    $img="<i class='fa fa-exclamation-triangle ' style='color:orange;' title='Rejet de paiement en cours de régularisation, sera représenté au prochain prélèvement'></i>";
                    $REGUL_CMT="Régularisation en cours de ".$MONTANT_REGUL.$default_money_symbol;
                    $myclass='orange12 trcolor';
                    $t="Rejet";
                }
                else {
                    $img="<i class='fa fa-exclamation-circle' style='color:red;' title='Rejet de paiement, Ce rejet est en attente de régularisation'></i>";
                    $REGUL_CMT="";
                    $myclass='red12 trcolor';
                    $t="Rejet";
                }
            
                echo "<tr class=".$myclass.">
                <td  align=center>".$img."</td>
                <td>".$t." ".$D_DESCRIPTION."</td>
                <td >".$P_DESCRIPTION." ".$ANNEE."</td>
                <td>".$MONTANT." ".$default_money_symbol."</td>
                <td align=center class='hide_mobile'>".$DATE."</td>
                <td align=center class='hide_mobile'>".$REGUL_CMT."</td>
                <td align=center class='hide_mobile'>".$COMMENTAIRE."</td>";
                if ( $cotisations_allowed )
                    echo "<td>
                    <a href=\"javascript:rejet('$ID','$P_ID','update','".$csrf ."');\"><i class='fa fa-pen-square fa-lg' title=\"Modifier les informations pour ce rejet\" ></i></a>
                    <a href=\"javascript:rejet('$ID','$P_ID','delete','".$csrf ."');\"><i class='far fa-trash-alt fa-lg'  title=\"Supprimer ce rejet\"></i></a>
                    </td>";
                echo "</tr>";
            }
            else if ( $REMBOURSEMENT == 0 ) {  //paiement
                echo "<tr class='trcolor'>
                <td align=center> <i class='fa fa-euro-sign' style='color:blue;' title='paiement (".$TP_DESCRIPTION.")'></i></td>
                <td>Paiement (".$TP_DESCRIPTION.")</td>
                <td >".$P_DESCRIPTION." ".$ANNEE."</td>
                <td>".$MONTANT." ".$default_money_symbol."</td>
                <td align=center class='hide_mobile'>".$DATE."</td>
                <td align=center class='hide_mobile'>".$OBS."</td>
                <td align=center class='hide_mobile'>".$COMMENTAIRE."</td>";
                if ( $cotisations_allowed ) 
                    echo "<td>
                    <a href=\"javascript:paiement('$ID','$P_ID','update','0','".$csrf ."');\"><i class='fa fa-pen-square fa-lg' title=\"Modifier les informations pour ce paiement\" ></i></a>
                    <a href=\"javascript:paiement('$ID','$P_ID','delete','0','".$csrf ."');\"><i class='far fa-trash-alt fa-lg' title=\"Supprimer ce paiement\" ></i></a>
                    <a href=pdf_document.php?section=$P_SECTION&P_ID=$P_ID&paiement_id=$ID&mode=20 target=_blank><i class='far fa-file-pdf fa-lg' style='color:red;' title='imprimer facture'></i></a>
                    </td>";
                echo "</tr>";
            }
            else { // remboursement
                echo "<tr class='trcolor' style='color: black;'>
                <td align=center> <i class='far fa-money-bill-alt' title=\"Remboursement (".$TP_DESCRIPTION.")\"></i></td>
                <td >Remboursement (".$TP_DESCRIPTION.")</td>
                <td></td>
                <td>".$MONTANT." ".$default_money_symbol."</td>
                <td align=center class='hide_mobile'>".$DATE."</td>
                <td align=center class='hide_mobile'>".$OBS."</td>
                <td align=center class='hide_mobile'>".$COMMENTAIRE."</td>";
                if ( $cotisations_allowed )
                    echo "<td>
                    <a href=\"javascript:paiement('$ID','$P_ID','update','1','".$csrf ."');\"><i class='fa fa-pen-square fa-lg'  title=\"Modifier les informations pour ce remboursement\" ></i></a>
                    <a href=\"javascript:paiement('$ID','$P_ID','delete','1','".$csrf ."');\"><i class='far fa-trash-alt fa-lg' title=\"Supprimer ce remboursement\"></i> </a>
                    </td>";
                echo "</tr>";
            }
        }
    }
    echo "</table><p align=center>"; // end cadre
    if ( $cotisations_allowed ) {
        echo "<a class='btn btn-default noprint' href='#' onclick=\"javascript:paiement('0','$P_ID','insert','0','".$csrf ."');\" title='Ajouter un paiement'><i class='fa fa-plus' style='color:blue;'></i> paiement</a>
              <a class='btn btn-default noprint' href='#' onclick=\"javascript:paiement('0','$P_ID','insert','1','".$csrf ."');\" title='Ajouter un remboursement'><i class='fa fa-plus' style='color:black;'></i> remboursement</a>
              <a class='btn btn-default noprint' href='#' onclick=\"javascript:rejet('0','$P_ID','insert','".$csrf ."');\" title='Ajouter un rejet'><i class='fa fa-plus' style='color:red;'></i> rejet</a>";
    }
    
    if ( $from == 'exportcotisation' ) {
        echo " <input type='button' class='btn btn-default noprint' value='fermer cette page' onclick='fermerfenetre();' >";
    }
    else if ( ! $iphone) {
        echo " <input type='button' class='btn btn-default noprint' id='annuler' name='annuler' value='Retour' onclick=\"history.back(1);\" />";
    }
}

if ( $tab == 9 ){
    echo "<div align=center>";
//=====================================================================
// notes de frais
//=====================================================================
    // required for pagination
    $_SESSION["from_notes_de_frais"]=1;

    $query="select n.NF_ID, date_format(n.NF_CREATE_DATE,'%d-%m-%Y') NF_CREATE_DATE, n.E_CODE, year(NF_CREATE_DATE) YEAR, month(NF_CREATE_DATE) MONTH,
                n.TOTAL_AMOUNT, n.FS_CODE, fs.FS_DESCRIPTION, fs.FS_CLASS, n.NF_VALIDATED_DATE, n.NF_VALIDATED_BY, n.NF_CREATE_BY,
                p.P_PRENOM, p.P_NOM,
                p2.P_PRENOM 'P_PRENOM2', p2.P_NOM 'P_NOM2',
                tm.TM_CODE, tm.TM_DESCRIPTION, n.NF_NATIONAL, n.NF_DEPARTEMENTAL, n.NF_DON, n.NF_CODE1, n.NF_CODE2, n.NF_CODE3
                from note_de_frais n left join pompier p on p.P_ID = n.NF_CREATE_BY
                left join pompier p2 on p2.P_ID = n.NF_VALIDATED_BY,
                note_de_frais_type_statut fs, note_de_frais_type_motif tm
                where fs.FS_CODE=n.FS_CODE
                and tm.TM_CODE = n.TM_CODE
                and n.P_ID=".$pompier."
                order by n.NF_CREATE_DATE desc";
    $result=mysqli_query($dbc,$query);
    $number=mysqli_num_rows($result); 
    
    if ($NB10 > 0 ) {
        echo "<span class='badge'>".$NB10."</span> Notes de frais<p>";
    }
    require_once('paginator.class.php');
    $pages = new Paginator;
    $pages->page_name = $basedir."/upd_personnel.php"; 
    $pages->items_total = $number;  
    $pages->mid_range = 9;  
    if ( ! isset($_SESSION["ipp"])) $_SESSION["ipp"]=20;
    $pages->ipp = $_SESSION["ipp"];
    $pages->paginate();  
    if ( $number > 10 ) {
        echo $pages->display_pages();
        echo $pages->display_jump_menu(); 
        echo $pages->display_items_per_page(); 
        $query .= $pages->limit;
    }
    $result=mysqli_query($dbc,$query);
    
    if ( mysqli_num_rows($result) > 0 ) {
    echo "<table cellspacing=0 border=0>";
    echo "<tr class=TabHeader>
          <td width=25></td>
          <td width=60>ID</td>";
    if ( $syndicate == 1 ) 
        echo "<td width=150>N° comptable</td>";
    echo "<td width=280>Type</td>";
    echo "<td width=70>Montant</td>
          <td width=150>Statut note</td>
          <td width=100>Date création</td>
          <td width=180>Par</td>
          <td width=120>Validation le</td>
          <td width=180>Par</td>
      </tr>";
    
    while ( custom_fetch_array($result)) {
        $E_CODE=intval($E_CODE);
        if ( $assoc == 0 )  $TM_DESCRIPTION="";
        if ( $NF_NATIONAL == 1 ) $TM_DESCRIPTION ="<b>National</b> ".$TM_DESCRIPTION;
        else if ( $NF_DEPARTEMENTAL == 1 ) $TM_DESCRIPTION ="<b>Départemental</b> ".$TM_DESCRIPTION;
        $COMPTABLE = $NF_CODE1." / ".str_pad($NF_CODE2, 2, '0', STR_PAD_LEFT)." / ".str_pad($NF_CODE3,3, '0' , STR_PAD_LEFT);
        if ($E_CODE > 0 ) $TM_DESCRIPTION .=" (<a href=evenement_display.php?evenement=".$E_CODE."&from=personnel_note&pid=".$pompier." title=\"Voir événement\">".$E_CODE."</a>)";
        $author = "<a href=upd_personnel.php?pompier=".$NF_CREATE_BY.">".my_ucfirst($P_PRENOM)." ".strtoupper($P_NOM)."</a>";
        if ( $NF_VALIDATED_BY <> '' ) $author2 = "<a href=upd_personnel.php?pompier=".$NF_VALIDATED_BY.">".my_ucfirst($P_PRENOM2)." ".strtoupper($P_NOM2)."</a>";
        else $author2 ="";
        
        if ( $NF_DON == 1 and $FS_CODE == 'REMB' and $assoc ) $FS_DESCRIPTION = "Don à l'association";
        
        if ( $syndicate == 1 ) {
            if ( $FS_CODE == 'VAL' ) $FS_DESCRIPTION = 'Validée trésorier';
            if ( $FS_CODE == 'VAL1' ) $FS_DESCRIPTION = 'Validée président';
        }
        echo "<tr class=trcolor>
          <td align=center><a href='pdf_document.php?P_ID=".$pompier."&evenement=".$E_CODE."&note=".$NF_ID."&mode=13' target='_blank' title='afficher la note de frais au format PDF'><i class = 'far fa-file-pdf' style='color:red;'></i></a></td>
          <td><a href='note_frais_edit.php?action=update&nfid=".$NF_ID."' title='Voir cette note de frais'>".$NF_ID."</a></td>";
        if ( $syndicate )  echo "<td>".$COMPTABLE."</td>";
        echo "<td>".$TM_DESCRIPTION."</td>
          <td align=left class='".$FS_CLASS."'>".my_number_format($TOTAL_AMOUNT)." ".$default_money_symbol."</td>
            <td class='".$FS_CLASS."'><a href='note_frais_edit.php?action=update&nfid=".$NF_ID."' title='Voir cette note de frais'>".$FS_DESCRIPTION."</a></td>
            <td>".$NF_CREATE_DATE."</td>
          <td>".$author."</td>
          <td class=small>".$NF_VALIDATED_DATE."</td>
          <td>".$author2."</td>
      </tr>";
    } 
    
  echo "</table>";
  }
  else echo "Aucune note de frais trouvée";
  
   if ($notes_allowed or $pompier == $id) {
        echo "<br><input type='button' class='btn btn-default noprint' id='userfile' name='userfile' value='Ajouter'
            onclick=\"javascript:self.location.href='note_frais_edit.php?person=".$pompier."';\" >";
    }  
}


if ( $tab == 10 ) {
    echo "<div align=center>";
//=====================================================================
// Tenues
//=====================================================================

$query3="select s.S_CODE, cm.PICTURE, tm.TM_DESCRIPTION, tm.TM_USAGE, tm.TM_CODE, 
        m.MA_ID, m.MA_NB, m.MA_MODELE, m.MA_ANNEE, tt.TT_CODE, tt.TT_NAME, tt.TT_DESCRIPTION, tv.TV_NAME
        from materiel m left join taille_vetement tv on m.TV_ID=tv.TV_ID,
        type_materiel tm left join type_taille tt on tt.TT_CODE=tm.TT_CODE,
        categorie_materiel cm, section s
        where cm.TM_USAGE=tm.TM_USAGE
        and tm.TM_USAGE='Habillement'
        and s.S_ID=m.S_ID
        and tm.TM_ID=m.TM_ID
        and m.AFFECTED_TO=".$pompier."
        order by tm.TM_CODE";
$result3=mysqli_query($dbc,$query3);

if (mysqli_num_rows($result3) > 0 ) {
    echo "<table cellspacing=0 border=0 >";
    echo "<tr class=TabHeader>";
    echo "<td colspan=5>Tenues</b></td></tr>";
    echo "<tr class='small trcolor' >
            <td></td>
            <td>Type</td>
            <td>Modèle année</td>
            <td>Taille</td>
            <td>Nombre</td></tr>";
    while (custom_fetch_array($result3)) {
            echo "<tr class=trcolor><td width=20><i class='fa fa-".$PICTURE." fa-lg' title=\"".$TM_DESCRIPTION."\"></i></td>
                  <td width=150><a href=upd_materiel.php?from=personnel&mid=".$MA_ID.">".$TM_CODE."</a></td>
                  <td width=200 align=left>".$MA_MODELE." ".$MA_ANNEE."</td>
                  <td width=50 align=left>".$TV_NAME."</td>
                  <td width=50 align=center>".$MA_NB."</td>
            </tr>";
    }
            
    echo "</table>";
}
else
    echo "<p>Aucune tenue affectée.<br>";

if ( $update_tenues or $id == $pompier )
    echo "<p><input type='button' class='btn btn-default noprint' value='modifier' onclick='javascript:bouton_redirect(\"personnel_tenues.php?pid=".$pompier."\") ;'>";
    
}

echo "<p><p>";

writefoot();
?>
