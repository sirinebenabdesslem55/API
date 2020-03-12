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
check_all(44);

$shownewbuttons = false;

$id = $_SESSION['id'];

get_session_parameters();

if ( isset($_GET['status']) ) {
    $status=$_GET['status'];
    $_SESSION['status']=$status;
} 
else if ( isset($_SESSION['status']) ) $status=$_SESSION['status'];
else $status='infos';

if ( isset($_GET["from"]))$from=$_GET["from"];
else $from="default";

if ( isset($_GET["S_ID"])) {
    $S_ID=intval($_GET["S_ID"]);
    $_SESSION['filter'] = $S_ID;
}
else $S_ID=$filter;
writehead();

if ( check_rights($id, 26, "$S_ID")) $perm26=true;
else $perm26=false;

// laisser permissions sur sections 0 et 1
if ( $syndicate == 1 ) {
    if ( $S_ID > 1 and $S_ID <> $_SESSION['SES_SECTION'] and $S_ID <>  $_SESSION['SES_PARENT']) { 
        if (! check_rights($id, 44, "$S_ID") ) check_all(24);
    }
}

?>
<style type="text/css">
textarea{
FONT-SIZE: 10pt; 
FONT-FAMILY: Arial;
width:90%;
}
</style>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/section.js?version=<?php echo $version; ?>'></script>
<script>
$(document).ready(function(){
    $('[data-toggle="popover"]').popover();
});
</script>
<?php
if ( zipcodes_populated() ) {
    forceReloadJS('js/zipcode.js');
}
echo "</head>";

//=====================================================================
// get infos
//=====================================================================
if (check_rights($id, 22, "$S_ID"))
$granted22=true;
else $granted22=false;

if ( $granted22 ) $disabled="";
else $disabled="disabled";

if (check_rights($id, 29, "$S_ID") || $granted22 )
$showfact=true;
else $showfact=false;

if (check_rights($id, 30, "$S_ID"))
$showbadge=true;
else $showbadge=false;

if ( $granted22 )
$granted_cotisations=true;
else $granted_cotisations=false;

if (check_rights($id, 36, "$S_ID")) {
   $granted_agrement=true;
   $disabled_agrement='';
}
else {
   $granted_agrement=false;
   $disabled_agrement='disabled';
}

if ( check_rights($id, 22) or check_rights($id, 44)) $showresponsable=true;
else $showresponsable=false;

if ( $granted22 ) $unlock_save=true;
else $unlock_save=false;

$query1="select S_ID, S_CODE, S_DESCRIPTION, S_PARENT, S_URL,
        S_PHONE,S_PHONE2,S_PHONE3, S_FAX,
        S_ADDRESS, S_ADDRESS_COMPLEMENT, S_ZIP_CODE, S_CITY, S_EMAIL, S_EMAIL2, S_EMAIL3, S_HIDE, S_INACTIVE,
        S_PDF_PAGE, S_PDF_SIGNATURE, S_PDF_MARGE_TOP, S_PDF_MARGE_LEFT, S_PDF_TEXTE_TOP, S_PDF_TEXTE_BOTTOM,
        S_PDF_BADGE, S_IMAGE_SIGNATURE, S_DEVIS_DEBUT, S_DEVIS_FIN, S_FACTURE_DEBUT, S_FACTURE_FIN, DPS_MAX_TYPE, NB_DAYS_BEFORE_BLOCK,
        SMS_LOCAL_PROVIDER, SMS_LOCAL_USER, SMS_LOCAL_PASSWORD, SMS_LOCAL_API_ID, WEBSERVICE_KEY as LOCAL_KEY, S_ORDER, S_ID_RADIO,
        SHOW_PHONE3, SHOW_EMAIL3, SHOW_URL, S_SIRET, S_AFFILIATION
        from section
        where S_ID=".$S_ID;
$result1=mysqli_query($dbc,$query1);

// check input parameters
if ( mysqli_num_rows($result1) <> 1 ) {
    param_error_msg();
    exit;
}

custom_fetch_array($result1);
$S_PHONE=phone_display_format($S_PHONE);
$S_PHONE2=phone_display_format($S_PHONE2);
$S_PHONE3=phone_display_format($S_PHONE3);
$S_FAX=phone_display_format($S_FAX);
$S_INACTIVE=intval($S_INACTIVE);
if ($S_PDF_MARGE_TOP == "" ) $S_PDF_MARGE_TOP=15;
if ($S_PDF_MARGE_LEFT == "" ) $S_PDF_MARGE_LEFT=15;
if ($S_PDF_TEXTE_TOP == "" ) $S_PDF_TEXTE_TOP=40;
if ($S_PDF_TEXTE_BOTTOM == "" ) $S_PDF_TEXTE_BOTTOM=25;
$devis_debut=stripslashes($S_DEVIS_DEBUT);
$devis_fin=stripslashes($S_DEVIS_FIN);
$facture_debut=stripslashes($S_FACTURE_DEBUT);
$facture_fin=stripslashes($S_FACTURE_FIN);

$query1="select NIV from section_flat where S_ID=".$S_ID;
$result1=mysqli_query($dbc,$query1);
$row1=@mysqli_fetch_array($result1);
$NIV=$row1["NIV"];

//=====================================================================
// entete
//=====================================================================
if ( check_rights($id, 52)) $withlinks=true;
else  $withlinks=false;

if ( check_rights($id, 40)) {
    if ( $syndicate == 1 ) $t="adh�rents";
    else $t="personnes";
    $complement=" <a href=personnel.php?category=interne&order=P_NOM&filter=".$S_ID."&subsections=1&position=actif title=\"voir $t\"><span class='badge' style='background-color:purple;'>".get_section_tree_nb_person("$S_ID")."</span></a> ".$t;
    if ( $syndicate == 0 ) $complement .= " et <a href=vehicule.php?order=TV_USAGE&filter=".$S_ID."&filter2=ALL&subsections=1 title=\"voir v�hicules\"><span class='badge' style='background-color:purple;'>".get_section_tree_nb_vehicule("$S_ID")."</span></a> v�hicules ";
}
else $complement="";

$complement .= " <a href='section.php'><i class='fa fa-sitemap fa-2x' title='retour graphique organigramme'></i></a>";
$complement .= " <a href='departement.php'><i class='fa fa-list-ol fa-2x' title='retour liste'></i></a>";
if ( check_rights($id, 49) and $log_actions == 1 )
    $complement .= " <a href='history.php?lccode=S&lcid=$S_ID&order=LH_STAMP&ltcode=ALL'><i class='fa fa-search fa-2x noprint' title=\"Historique des modifications\" style='PADDING-LEFT:3px' class='noprint'></i></a>";

if ( check_rights($id, 55))
    $complement .= " <a href='ins_section.php'><i class='fa fa-plus-square fa-2x' title='Ajouter une sous-section'></i></a>";

if (! $withlinks ) $complement = '';

echo "<body><div align=center class='table-responsive'><table class='noBorder'>
      <tr><td width = 60 ><i class='fa fa-globe-europe fa-3x' ></i></td><td>
      <font size=4><b>".$S_CODE." - ".$S_DESCRIPTION."</b></font> ".$complement."</td></tr></table>";


echo write_full_path_to_section($S_ID,$withlinks);

if ( $nbsections == 0 ) {
    $highestniv=get_highest_niv();
    if ( $highestniv >= 3 and  $NIV < $highestniv) {
        $below=$NIV+1;
        if ( $below < $nbmaxlevels ) {
            $l=rtrim($levels[$below],'s');
            $nbsubsections=get_subsections_nb($S_ID);
            if ( $nbsubsections > 0 and $withlinks and check_rights($id,40)) 
            echo " <i>Voir les <a href=departement.php?filter=".$S_ID."&niv=".$below.">
                <span class='badge' style='background-color:purple;'>".$nbsubsections."</span></a> ".$l."s</i>";
        }
    }
}
else if ( $S_ID == 0 ) {
    $nbsubsections=get_subsections_nb($S_ID);
    echo " ( <i>Voir les <a href=departement.php?filter=".$S_ID."><span class='badge' style='background-color:purple;'>".$nbsubsections."</span></a> sections</i> )";
}

if ( isset($_GET["tab"])) $tab=intval($_GET["tab"]);
else if ( $status == 'infos') $tab = 1;
else if ( $status == 'responsables' ) $tab = 2;
else if ( $status == 'permissions' ) $tab = 7;
else if ( $status == 'parametrage' ) $tab = 3;
else if ( $status == 'agrements' ) $tab = 4;
else if ( $status == 'cotisations' ) $tab = 5;
else $tab = 1;
if ( $tab == 0 ) $tab = 1;

echo  "<p><ul class='nav nav-tabs noprint'>";
if ( $tab == 1 ) $class='active';
else $class='';
echo "<li class='nav-item'>
<a class='nav-link $class' href='upd_section.php?S_ID=".$S_ID."&tab=1' title='Informations' role='tab' aria-controls='tab1' href='#tab1' >
<span>Informations</span></a></li>";

if ( $showresponsable) {
    if ( $tab == 2 ) $class='active';
    else $class='';
    echo "<li class='nav-item'>
    <a class='nav-link $class' href='upd_section.php?S_ID=".$S_ID."&tab=2' title='Organigramme' role='tab' aria-controls='tab2' href='#tab2' >
    <span>Organigramme</span></a></li>";
    if ( $tab == 7 ) $class='active';
    else $class='';
    echo "<li class='nav-item'>
    <a class='nav-link $class' href='upd_section.php?S_ID=".$S_ID."&tab=7' title='Permissions' role='tab' aria-controls='tab7' href='#tab7' >
    <span>Permissions</span></a></li>";
}
if ( $assoc ) {
    if ( $showfact or $showbadge or $granted22) {
        if ( $tab == 3 ) $class='active';
        else $class='';
        echo "<li class='nav-item'>
        <a class='nav-link $class' href='upd_section.php?S_ID=".$S_ID."&tab=3' title='Param�trage' role='tab' aria-controls='tab3' href='#tab3' >
        <span>Param�trage</span></a></li>";
    }
}
if ( $assoc ) {
    if ( $NIV < $nbmaxlevels -1 ) {
        if ( $tab == 4 ) $class='active';
        else $class='';
        echo "<li class='nav-item'>
        <a class='nav-link $class' href='upd_section.php?S_ID=".$S_ID."&tab=4' title='Agr�ments et M�dailles' role='tab' aria-controls='tab4' href='#tab4' >
        <span>Agr�ments et M�dailles</span></a></li>";
    }
}
if ( $NIV < $nbmaxlevels -1 and $cotisations == 1 and check_rights($id, 22)) {
    if ( $tab == 5 ) $class='active';
    else $class='';
    echo "<li class='nav-item'>
    <a class='nav-link $class' href='upd_section.php?S_ID=".$S_ID."&tab=5' title='d�finir le montant des cotisations' role='tab' aria-controls='tab5' href='#tab5' >
    <span>Cotisations</span></a></li>";

}
if ( $assoc ) {
    if ( $NIV >= $nbmaxlevels -2 ) {
        if ( $tab == 6 ) $class='active';
        else $class='';
        echo "<li class='nav-item'>
            <a class='nav-link $class'  href='upd_section.php?S_ID=".$S_ID."&tab=6' title=\"blocage de certains types d'�v�nements\">
            <span>Ev�nements</span></a></li>";
    }
}
echo "</ul>";
// fin tabs

echo "<div id='export' style='position: relative; ' align=center >";

//=====================================================================
// tab infos
//=====================================================================
if ( $tab == 1 ) {
    
    $help="Les identifiants Radio sont compos�s de 5 chiffres au niveau des sections.
    Un premier bloc de 3 chiffres correspond au d�partement, compl�t� � gauche par un z�ro (006 pour Alpes Maritimes, 083 pour le Var).
    Le deuxi�me bloc de 2 chiffres correspond � l'antenne (01, 02, 03 ..., 99). On utilise 00 si on n'est pas dans une antenne mais directement au niveau d�partemental.
    Ces identifiants de 5 chiffres doivent �tre uniques. Seul l'administrateur peut les modifier.
    Remarque: Il y a encore 2 autres chiffres suppl�mentaires, pas propos�s ici, qui permettent d'identifier une radio appartenant � une section.";
    
    
    $help2="Certaines donn�es relatives aux sections sont affich�es sur un site public, en plus de $application_title. 
    En particulier les donn�es relatives au contact pour les inscriptions aux formations.
    Vous pouvez choisir d'afficher toutes ces informations ou seulement certaines parmi: le t�l�phone de la formation, 
    l'adresse email de la formation, et l'adresse URL du site web d�taillant ces formations. 
    Pour cela il suffit de cocher les cases � c�t� des champs devant �tre montr�s au public.";
    
    $help3="La radiation d'une section a les effets suivants: 1 - L'ensemble du personnel devient automatiquement 'ancien' et ne pourra plus se connecter.
    La coche 'section inactive' est coch�e sur la section, cette section n'est plus visible sur le site public (si il y en a un).
    La suppression d'une section ne devrait normalement pas �tre utilis�e sauf si elle a �t� cr��e par erreur. Elle n'est possible que par quelques personnes habilit�es. La supression a les effets suivants:
    1 - L'ensemble du personnel, des v�hicules et du mat�riel de la section ne sont pas perdus mais sont d�plac�s au niveau sup�rieur.
    2 - Les �v�nements qui ont �t� cr��s au niveau de cette section sont aussi d�plac�s au niveau sup�rieur.
    3 - La fiche section est d�finitivement supprim�e avec son �ventuel param�trage.";
    
    echo "<div id='infos' >";

    echo "<form name='sectionform1' action='save_section.php' method='POST' enctype='multipart/form-data'>";
    print insert_csrf('section');
    echo "<input type='hidden' name='operation' value='update'>";
    echo "<input type='hidden' name='S_ID' value='$S_ID'>";
    echo "<input type='hidden' name='status' value='infos'>";

    echo "<p>";

    echo "<table cellspacing=0 border=0>";
    echo "<tr>
               <td colspan=2 class=TabHeader>Informations obligatoires</td>
          </tr>";
          
    echo "<tr bgcolor=$mylightcolor >
              <td colspan=2></td>";
    echo "</tr>";

    //=====================================================================
    // code
    //=====================================================================
    if ( $syndicate == 0 ) {
        echo "<tr bgcolor=$mylightcolor >
              <td bgcolor=$mylightcolor width=150 ><b>Identifiant</b></td>
              <td bgcolor=$mylightcolor width=150 align=left><b>$S_ID</b></td>";        
        echo "</tr>";
    }
    if ( $syndicate == 1 and $NIV == 3) $t="D�partement";
    else $t="Nom";

    if (check_rights($id, 55, $S_PARENT )) $disabled3='';
    else $disabled3='disabled';

    echo "<tr bgcolor=$mylightcolor >
              <td ><b>".$t."</b></td>
              <td align=left><input type='text' name='code' size='25' maxlength='25' value=\"$S_CODE\" $disabled  $disabled3>";        
    echo "</tr>";

    echo "<tr bgcolor=$mylightcolor >
              <td >Description</td>
              <td align=left ><input type='text' name='nom' size='40' maxlength='50' value=\"$S_DESCRIPTION\" $disabled  $disabled3>";        
    echo "</tr>";

    if ( $gardes == 1 ) {
        echo "<tr bgcolor=$mylightcolor >
              <td >Ordre garde</td>
              <td align=left ><select name='ordre'  $disabled  $disabled3>";
        if ( $S_ORDER == 0 ) $selected='selected';
        else $selected='';
        echo "<option value='0' $selected>Non d�fini</option>";
        for ( $i=1; $i < 10; $i++ ) {
            if ( $S_ORDER == $i ) $selected='selected';
            else $selected='';
            echo "<option value='".$i."' $selected>".$i."</option>";
        }      
        echo "</select></tr>";
    }

    //=====================================================================
    // parent section 
    //=====================================================================

    if (  $nbsections <> 0 ) echo "<input type='hidden' name='parent' value='".$S_PARENT."'>";
    else {
        $disabledparent=$disabled3;
        if ( isset($_SESSION['sectionorder']) ) $sectionorder=$_SESSION['sectionorder'];
        else $sectionorder=$defaultsectionorder;

        if ( $S_ID == 0 ) {
            echo "<input type='hidden' name='parent' value='-1'>";
        }    
        else {
            if ( check_rights($id, 24)) $mysection='0';
            else {
                $mysection=get_highest_section_where_granted($id,55);
                if ( $mysection == '' ) $mysection=$_SESSION['SES_SECTION'];
            }
     
            if ( $disabled == "" and $mysection <> $S_ID and check_rights($id, 55, $S_PARENT )) {
                echo "<tr bgcolor=$mylightcolor >
              <td ><b>D�pend de ";
                echo choice_section_order('upd_section.php')."</b></td>";
                echo "<td align=left>";
                echo "<select id='parent' name='parent' $disabledparent>"; 
                if ( $mysection <> 0 ){ 
                    $level=get_level($mysection);
                    display_children2($mysection, $level +1, $S_PARENT, $nbmaxlevels - 1,$sectionorder);
                }
                else {
                    $mycolor=$myothercolor;
                    $class="style='background: $mycolor;'";
                    echo "<option value='0' $class >".get_section_code('0')." - ".get_section_name('0')."</option>";
                    display_children2(0, 1, $S_PARENT , $nbmaxlevels - 1,$sectionorder);
                }
                echo "</select></td> ";       
            }
            else {
                echo "<tr bgcolor=$mylightcolor >
                <td bgcolor=$mylightcolor ><b>D�pend de </b></td>";
                echo "<td bgcolor=$mylightcolor align=left>";
                if ( $withlinks ) echo "<a href=upd_section.php?S_ID=$S_PARENT>".get_section_code($S_PARENT)." - ".get_section_name($S_PARENT)."</a>";
                else echo get_section_code($S_PARENT)." - ".get_section_name($S_PARENT);
                echo "<input type='hidden' name='parent' value='$S_PARENT'>";
            }
            echo "</tr>";
        }
    }

    //=====================================================================
    // intercalaire
    //=====================================================================
    if ( $nbsections == 0 ) {
        echo "<tr >
                   <td width=300 colspan=2 class=TabHeader>
                            <i>Informations facultatives</i>
                    </td>
              </tr>";

        //=====================================================================
        // ID Radio
        //=====================================================================
        if ( $assoc ) {
            if ( intval($S_ID_RADIO) > 0 ) {
                $rad1=substr($S_ID_RADIO,0,3);
                $rad2=substr($S_ID_RADIO,3,2);
            }
            else {
                $rad1='';
                $rad2='';
            }
            
            echo "<tr  bgcolor=$mylightcolor>
                       <td >ID Radio</td>
                        <td>";
            if ( check_rights($id,14))
                echo "<input type='text' name='rad1' size=3 maxlength=3 title='code d�partement sur 3 chiffres' value='$rad1' style='width: 40px; padding: 2px'
                        onchange='checkNumber(form.rad1,\"$rad1\");'>
                      <input type='text' name='rad2' size=2 maxlength=2 title='code antenne sur 2 chiffres' value='$rad2' style='width: 28px; padding: 2px'
                        onchange='checkNumber(form.rad2,\"$rad2\");'>";
            else
                echo "<b>".$rad1." ".$rad2."</b>";
            echo " <a href='#' data-toggle='popover' title='Information sur les ID Radio' data-trigger='hover' data-content=\"".$help."\"><i class='fa fa-question-circle fa-lg' ></i></a>
                        </td>
                  </tr>";
        }

        //=====================================================================
        // ligne address
        //=====================================================================
        $map="";
        if ( $S_ADDRESS <> "" and $geolocalize_enabled ) {
            $querym="select LAT, LNG from geolocalisation where TYPE='S' and CODE=".$S_ID;
            $resultm=mysqli_query($dbc,$querym);
            $NB=mysqli_num_rows($resultm);
            if ( $NB > 0 ) {
                custom_fetch_array($resultm);
                $url = $waze_url."&ll=".$LAT.",".$LNG."&";
                $map = " <a href=".$url." target=_blank><i class='fab fa-waze fa-lg' title='Voir la carte Waze' class='noprint'></i></a>";
                if ( check_rights($id,76) )
                    $map .= " <a href=map.php?type=S&code=".$S_ID." target=_blank><i class='fa fa-map' style='color:green;' title='Voir la carte Google Maps' ></i></a>";
            }
        }
        echo "<tr bgcolor=$mylightcolor  >
                  <td align=left>Adresse ".$map."</td>
                  <td align=left><textarea name='address' cols='25' rows='2' value=\"$S_ADDRESS\" $disabled>$S_ADDRESS</textarea></td>";
        echo "</tr>";

        echo "<tr bgcolor=$mylightcolor >
                  <td align=left><i>Compl�ment d'adresse</i></td>
                  <td align=left><input type='text' name='address_complement' size='33' value=\"$S_ADDRESS_COMPLEMENT\" $disabled></td>";
        echo "</tr>";

        echo "<tr bgcolor=$mylightcolor >
                  <td align=left>Code postal</td>
                  <td align=left><input type='text' name='zipcode' id='zipcode' maxlength='5' size='5' value='$S_ZIP_CODE' $disabled></td>";
        echo "</tr>";

        echo "<tr bgcolor=$mylightcolor >
                  <td align=left>Ville</td>
                  <td align=left><input type='text' name='city' id='city' size='33' maxlength='30' value=\"$S_CITY\" $disabled>";

        echo  "<div id='divzipcode' 
                    style='display: none;
                    position: absolute; 
                    border-style: solid;
                    border-width: 2px;
                    background-color: $mylightcolor; 
                    border-color: $mydarkcolor;
                    width: 450px;
                    height: 130px;
                    padding: 5px;
                    overflow-y: auto'>
                    </div>";
                 
                 
        echo "</td>
         </tr>";

        //=====================================================================
        // ligne phone
        //=====================================================================

        echo "<tr bgcolor=$mylightcolor >
                  <td >T�l�phone</td>
                  <td align=left><input type='text' name='phone' size='16' maxlength=16
                    value='$S_PHONE' $disabled onchange='checkPhone(form.phone,\"".$S_PHONE."\",\"".$min_numbers_in_phone."\")'> ".show_contry_code($S_PHONE)."</td>";        
        echo "</tr>";

        if ($assoc ) {
            echo "<tr bgcolor=$mylightcolor >
                  <td >TPH veille op�rationnelle</td>
                  <td align=left><input type='text' name='phone2' size='16' maxlength=16
                    value='$S_PHONE2' $disabled onchange='checkPhone(form.phone2,\"".$S_PHONE2."\",\"".$min_numbers_in_phone."\")'> ".show_contry_code($S_PHONE2)."</td>";        
            echo "</tr>";
            echo "<tr bgcolor=$mylightcolor >
                  <td >T�l�phone Formations</td>
                  <td align=left><input type='text' name='phone3' size='16' maxlength=16
                    value='$S_PHONE3' $disabled 
                    onchange='checkPhone(form.phone3,\"".$S_PHONE3."\",\"".$min_numbers_in_phone."\");changeInfoFormation( form.phone3, form.SHOW_PHONE3 );'> ".show_contry_code($S_PHONE3);
            if ( $webservice_key <> '' and $shownewbuttons) {
                if ( $SHOW_PHONE3 == 1 ) $checked ='checked';
                else $checked ='';
                echo " <input type = checkbox name='SHOW_PHONE3' value='1' title='cocher pour afficher cette information sur le site des formations' $checked $disabled>";
                                        
                echo " <a href='#' data-toggle='popover' title='Affichage sur le site de formation' data-trigger='hover' data-content=\"".$help2."\">
                    <i class='fa fa-question-circle fa-lg' ></i></a>";
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "<tr bgcolor=$mylightcolor >
                  <td >Fax</td>
                  <td align=left><input type='text' name='fax' size='16' maxlength=16
                    value='$S_FAX' $disabled onchange='checkPhone(form.fax,\"".$S_FAX."\",\"".$min_numbers_in_phone."\")'> ".show_contry_code($S_FAX)."</td>";        
        echo "</tr>";

        //=====================================================================
        // ligne email
        //=====================================================================
        
        if ( $syndicate == 1 ) $e="Email pr�sident";
        else $e="Email op�rationnel";
        echo "<tr bgcolor=$mylightcolor >
                  <td >".$e."</td>
                  <td align=left><input type='text' name='email' size='33' title='Cette adresse est utilis�e pour les besoins de la veille op�rationnelle.'
                    value='$S_EMAIL' $disabled onchange='mailCheck(form.email,\"".$S_EMAIL."\")'></td>";
        echo "</tr>";

        echo "<tr bgcolor=$mylightcolor >
                  <td >Email secr�tariat</td>
                  <td align=left><input type='text' name='email2' size='33' title='Cette adresse email utilis�e dans les documents PDF g�n�r�s, et re�oit toutes les notifications relatives aux �v�nements et au personnel.'
                    value='$S_EMAIL2' $disabled onchange='mailCheck(form.email2,\"".$S_EMAIL2."\")'></td>";
        echo "</tr>";

        if ( $assoc ) {
            echo "<tr bgcolor=$mylightcolor >
                  <td >Email formation</td>
                  <td align=left><input type='text' name='email3' size='33' title='Adresse email utilis�e pour les contacts li�s aux formations.'
                    value='$S_EMAIL3' $disabled onchange='mailCheck(form.email3,\"".$S_EMAIL3."\");changeInfoFormation( form.email3, form.SHOW_EMAIL3 );'>";
            if ( $webservice_key <> '' and $shownewbuttons) {
                if ( $SHOW_EMAIL3 == 1 ) $checked ='checked';
                else $checked ='';
                echo " <input type = checkbox name='SHOW_EMAIL3' value='1' onchange=\"javascript:\" title='cocher pour afficher cette information sur le site des formations' $checked $disabled>";
                            
                echo " <a href='#' data-toggle='popover' title='Affichage sur le site de formation' data-trigger='hover' data-content=\"".$help2."\">
                    <i class='fa fa-question-circle fa-lg' ></i></a>";
            }
            echo "</td>
                </tr>";
        }

        echo "<tr bgcolor=$mylightcolor >
                  <td>Site web</td>
                  <td align=left>
                    <input type='text' name='url' size='33' value='$S_URL' $disabled
                    onchange='changeInfoFormation( form.url, form.SHOW_URL );'>";
        if ( $assoc and $webservice_key <> '' and $shownewbuttons) {
            if ( $SHOW_URL == 1 ) $checked ='checked';
            else $checked ='';
            echo " <input type = checkbox name='SHOW_URL' value='1' title='cocher pour afficher cette information sur le site des formations' $checked $disabled>";
            
            echo " <a href='#' data-toggle='popover' title='Affichage sur le site de formation' data-trigger='hover' data-content=\"".$help2."\">
                    <i class='fa fa-question-circle fa-lg' ></i></a>";

        }
        echo "</td>
                </tr>";

        //=====================================================================
        // used by webservices
        //=====================================================================

        if ( $nbsections == 0 and $LOCAL_KEY <> "") {
            if ( $S_INACTIVE == 0 ) $checked='';
            else $checked='checked';
            echo "<tr bgcolor=$mylightcolor >
                  <td> Section inactive</td>
                  <td align=left>
                  <input type = checkbox name='inactive' value='1' title='cocher pour ne pas afficher cette section sur le site public' $checked $disabled $disabled3>";
            
            if (check_rights($id, 14)) {
                echo "<tr bgcolor=$mylightcolor >
                  <td > Webservice key section</td>
                  <td align=left>".$LOCAL_KEY."</td>";
                echo "</tr>";
            }
        }
        else 
            echo "<input type='hidden' name='inactive' value='".$S_INACTIVE."'>";


        //=====================================================================
        // autorisation DPS des antennes
        //=====================================================================
        if ( $NIV == $nbmaxlevels -1 and $assoc ) {

            // agr�ment DPS du d�partement
            $queryag="select TAV_ID from agrement where  TA_CODE='D' and S_ID=".$S_PARENT;
            $resultag=mysqli_query($dbc,$queryag);
            $rowag=mysqli_fetch_array($resultag);
            $tagid = $rowag["TAV_ID"];

            if ( $tagid <> '') {
                $querydps="select TAV_ID,TA_VALEUR,TA_FLAG from type_agrement_valeur where TA_CODE='D' and TAV_ID <=".$tagid;
                $resultdps=mysqli_query($dbc,$querydps);

                echo "<tr >
                <td bgcolor=$mylightcolor >Permission DPS</td>
                <td bgcolor=$mylightcolor align=left>
                <select id='dps' name='dps' $disabled>";
                if ($DPS_MAX_TYPE == '' ) 
                    echo "<option value='' selected>� d�finir</option>";
                while ( $rowdps=mysqli_fetch_array($resultdps)) {
                    $TAV_ID = $rowdps["TAV_ID"];
                    $TA_VALEUR = $rowdps["TA_VALEUR"];
                    $TA_FLAG = $rowdps["TA_FLAG"];
                    if ($DPS_MAX_TYPE == $TAV_ID ) $selected='selected';
                    else $selected='';
                    echo "<option value='".$TAV_ID."' $selected>".$TA_VALEUR."</option>";
                }
                echo "</select></td>";
                echo "</tr>";
            }
        }
        
        //=====================================================================
        // SIRET et NUM affiliation
        //=====================================================================
        if ( $assoc ) {
            echo "<tr bgcolor=$mylightcolor >
                      <td >SIRET</td>
                      <td align=left><input type='text' name='siret' size='20' title=\"Code SIRET de l'organisation\" autocomplete='off'
                        value='$S_SIRET' $disabled></td>";
            echo "</tr>";
            echo "<tr bgcolor=$mylightcolor >
                      <td >N� Affiliation</td>
                      <td align=left><input type='text' name='affiliation' size='20' title=\"Num�ro d'affiliation l'organisation\" autocomplete='off'
                        value='$S_AFFILIATION' $disabled></td>";
            echo "</tr>";
        }
    }
    echo "</table>";

    if ($unlock_save) {
        echo "<p><input type='submit' class='btn btn-default' value='sauver infos'>";
        if ($granted22 and  $S_ID <> 0 ) {
            if (check_rights($id, 19) and check_rights($id, 55)) {
                echo " <input type='button' class='btn btn-default' value='supprimer' onclick=\"suppr_section('".$S_ID."')\">";
                if ( $nbsections == 0 and $assoc and check_rights($id, 2, $S_ID)) {
                    echo " <input type='button' class='btn btn-default' value='radier' onclick=\"radier_section('".$S_ID."')\" title='mettre la section inactive radier les �ventuelles sous-sections et tout le personnel'>";
                    echo " <a href='#' data-toggle='popover' title=\"Suppression et radiation d'une section\" data-trigger='hover' data-content=\"".$help3."\">
                    <i class='fa fa-question-circle fa-lg' ></i></a>";
                }
            }
        }
    }
    echo "</form>";
    echo "</div>"; // fin tab infos
}


//=====================================================================
// tab 2 responsables
//=====================================================================
if ( ($tab == 2 or $tab == 7 )and $showresponsable) {
    echo "<div id='responsables'>";
    echo "<p>";
    
    if ( $tab == 7 ) $link=3;
    else $link=2;
    if ($withlinks) $T="<a href=habilitations.php?tab=$link title='voir les habilitations de chaque r�le' 
               class=TabHeader>
                 <i class='fa fa-question-circle'></i> <font size=1>voir les habilitations $application_title</font></a>";
    else $T="";
    echo "<table cellspacing=0 border=0>";
    echo "<tr class=TabHeader><td width=200 class=TabHeader>R�le ou permission</td>
               <td colspan=2>".$T."</td>
          </tr>";
    $query="SELECT g.GP_ID c, g.GP_DESCRIPTION, g.TR_SUB_POSSIBLE, r.P_ID CURPID, r.P_NOM CURPNOM, r.P_PRENOM CURPPRENOM,
    r.P_SECTION CURPSECTION, r.S_CODE CURSECTIONCODE, g.GP_ORDER
    FROM groupe g
    LEFT JOIN (
    SELECT p.P_ID, p.P_NOM, p.P_PRENOM, p.P_SECTION, s.S_CODE, sr.GP_ID
    FROM section_role sr, pompier p, section s
    WHERE sr.P_ID = p.P_ID
    AND s.S_ID = p.P_SECTION
    AND sr.S_ID =".$S_ID."
    ) AS r 
    ON g.GP_ID = r.GP_ID
    WHERE g.GP_ID >100";
    if ( $tab == 2 ) $query .= " and g.TR_CONFIG=2";
    else $query .= " and g.TR_CONFIG=3";
    $query .= " order BY GP_ORDER, c ASC,CURPNOM, CURPPRENOM";
    $result=mysqli_query($dbc,$query);
         
    $i=0; $prev=0;
    while (custom_fetch_array($result)) {
        if ( $prev == $c ) $GP_DESCRIPTION="";
        $i=$i+1;
        if ( $i%2 == 0 ) $mycolor=$mylightcolor;
        else $mycolor="#FFFFFF";
        // cas specifique association, pas de pr�sident sur les antennes
        if (( get_level("$S_ID") + 1 == $nbmaxlevels ) and ( $nbsections == 0 )) {
            if ( $GP_DESCRIPTION == "Pr�sident (e)" ) $GP_DESCRIPTION="Responsable d'antenne";
            if ( $GP_DESCRIPTION == "Vice pr�sident (e)" ) $GP_DESCRIPTION="Responsable adjoint";
        }
        echo "<tr>
                <td bgcolor=$mycolor width=200 >".$GP_DESCRIPTION."</td>
                <td bgcolor=$mycolor width=250 align=left>";
        if ( check_rights($id, 40 )) echo "<a href=upd_personnel.php?pompier=".$CURPID.">".strtoupper($CURPNOM)." ".my_ucfirst($CURPPRENOM)."</a>";
        else echo strtoupper($CURPNOM)." ".my_ucfirst($CURPPRENOM);
        if ( $CURSECTIONCODE <> "" ) echo " <small>(".$CURSECTIONCODE.")</small>";
        echo "</td>
        <td bgcolor=$mycolor width=20>";
        
        $cadre=false;
        if ( $perm26 and $c == 107 ) $cadre=true;
        
        // le cadre de permanence peut se changer
        if ( ($granted22 or $cadre) and $prev <> $c){
            print write_modal( "upd_responsable.php?S_ID=".$S_ID."&GP_ID=".$c, $c, "<i class='fa fa-user' title='choisir une ou des personnes pour ce r�le'></i>");
        }
        echo "</td></tr>";
        $prev=$c;
    }
    echo "</table>";

    if ( $tab == 2 )
        echo " <button class='btn btn-default' title=\"Voir l'organigramme des responsables avec les photos\"
                onclick=\"redirect('organigramme.php?filter=".$S_ID."');\" >
                <i class='fas fa-camera'></i> Afficher
            </button>";

    echo "</div>";
}
//=====================================================================
// tab 3 parametrage
//=====================================================================
if (( $showfact or $showbadge or $granted22) and $assoc and $tab == 3){
    echo "<div id='parametrage'>";

    echo "<form name='sectionform3' action='save_section.php' method='POST' enctype='multipart/form-data'>";
    print insert_csrf('section');
    echo "<input type='hidden' name='operation' value='update'>";
    echo "<input type='hidden' name='S_ID' value='$S_ID'>";
    echo "<input type='hidden' name='status' value='parametrage'>";

    echo "<p><table cellspacing=0 border=0>";
    if ($showfact and $assoc) {
        echo "<tr >
                     <td colspan=2 class=TabHeader>
                             <i>Papier � ent�te</i>
                    </td>
              </tr>";
        echo "<tr >
                    <td bgcolor=$mylightcolor width=150>Mod�le (.PDF)</td>
                    <td bgcolor=$mylightcolor width=150 align=left>"
                  .(($S_PDF_PAGE!="")?(file_exists($basedir."/images/user-specific/".$S_PDF_PAGE)?
                  "<a href=\"".$basedir."/images/user-specific/".$S_PDF_PAGE."\" target=\"_blank\">Voir</a>"
                  :"<font size=1 color=red>Fichier non trouv� sur le serveur</font>")
                  ."  <input type=\"checkbox\" name=\"delpage\"> Supprimer"
                  :"<input type='file' name='pdf_page' size='20' value=\"$S_PDF_PAGE\">")
                  ."</td>";
        echo "</tr>";
        echo "<tr >
                    <td bgcolor=$mylightcolor >Marge Haut</td>
                    <td bgcolor=$mylightcolor align=left>
                    <input type='text' name='pdf_marge_top' size='5' value=\"$S_PDF_MARGE_TOP\" onchange='checkNumber(form.pdf_marge_top,\"$S_PDF_MARGE_TOP\");'>
                    <font size=1><i> mm</td>";
        echo "</tr>";
        echo "<tr >
                    <td bgcolor=$mylightcolor >Marge Gauche / Droite</td>
                    <td bgcolor=$mylightcolor align=left>
                    <input type='text' name='pdf_marge_left' size='5' value=\"$S_PDF_MARGE_LEFT\" onchange='checkNumber(form.pdf_marge_left,\"$S_PDF_MARGE_LEFT\");'>
                    <font size=1><i> mm</td>";
        echo "</tr>";
        echo "<tr >
                    <td bgcolor=$mylightcolor >D�but de la zone de texte</td>
                    <td bgcolor=$mylightcolor align=left>
                    <input type='text' name='pdf_texte_top' size='5' value=\"$S_PDF_TEXTE_TOP\" onchange='checkNumber(form.pdf_texte_top,\"$S_PDF_TEXTE_TOP\");'>
                    <font size=1><i>mm du haut de la feuille</i></td>";
        echo "</tr>";
        echo "<tr >
                    <td bgcolor=$mylightcolor >Fin de la zone de texte</td>
                    <td bgcolor=$mylightcolor align=left>
                    <input type='text' name='pdf_texte_bottom' size='5' value=\"$S_PDF_TEXTE_BOTTOM\"  onchange='checkNumber(form.pdf_texte_bottom,\"$S_PDF_TEXTE_BOTTOM\");'>
                    <font size=1><i>mm du bas de la feuille</i></td>";
        echo "</tr>";

        echo "<tr>
                   <td width=300 colspan=2 class=TabHeader>
                    <i>Textes par d�faut pour devis et factures</i>
                </td>
              </tr>";
              
        echo "<tr >
                    <td bgcolor=$mylightcolor align=left>Signature des documents</td>
                    <td bgcolor=$mylightcolor align=left>
                    <textarea name='pdf_signature' cols='30' rows='2'>$S_PDF_SIGNATURE</textarea></td>";
        echo "</tr>";
        echo "<tr >
                    <td bgcolor=$mylightcolor align=left>D�but du devis</td>
                    <td bgcolor=$mylightcolor align=left>
                    <textarea name='devis_debut' cols='30' rows='2'>$devis_debut</textarea></td>";
        echo "</tr>";            
        echo "<tr >
                    <td bgcolor=$mylightcolor align=left>Fin de devis</td>
                    <td bgcolor=$mylightcolor align=left>
                    <textarea name='devis_fin' cols='30' rows='2'>$devis_fin</textarea></td>";
        echo "</tr>";      
        echo "<tr >
                    <td bgcolor=$mylightcolor align=left>D�but de facture</td>
                    <td bgcolor=$mylightcolor align=left>
                    <textarea name='facture_debut' cols='30' rows='2'>$facture_debut</textarea></td>";
        echo "</tr>";     
        echo "<tr >
                    <td bgcolor=$mylightcolor align=left>Fin de facture</td>
                    <td bgcolor=$mylightcolor align=left>
                    <textarea name='facture_fin' cols='30' rows='2'>$facture_fin</textarea></td>";
        echo "</tr>";
        if ( $NIV < $nbmaxlevels -1 ) {
            echo "<tr>
                   <td colspan=2 class=TabHeader>
                    <i>Image de la signature du pr�sident</i>
                </td>
              </tr>";

            echo "<tr >
                    <td bgcolor=$mylightcolor >Signature scann�e</td>
                    <td bgcolor=$mylightcolor align=left>
                  ".(($S_IMAGE_SIGNATURE!="")?(file_exists($basedir."/images/user-specific/".$S_IMAGE_SIGNATURE)?
                  "<a href=\"".$basedir."/images/user-specific/".$S_IMAGE_SIGNATURE."\" target=\"_blank\">Voir</a>"
                  :"<font size=1 color=red>Fichier non trouv� sur le serveur</font>")
                  ." <input type=\"checkbox\" name=\"delsignature\"> Supprimer"
                  :"<input type='file' name='image_signature' size='20' value=\"$S_IMAGE_SIGNATURE\">
                  <br><font size=1>Image .gif, .jpg ou .png, taille recommand�e 5cm x 3cm<i>")."
                  </td>";    
        }
    }
    else $showfact=false;

    //------------------------------
    // ligne badge
    //------------------------------
    if ($showbadge) { 
       echo "<tr>
               <td colspan=2 class=TabHeader>
                <i>Badge</i>
            </td>
          </tr>";
       echo "<tr >
                <td bgcolor=$mylightcolor >Image de fond du badge</td>
                <td bgcolor=$mylightcolor align=left>
              ".(($S_PDF_BADGE!="")?(file_exists($basedir."/images/user-specific/".$S_PDF_BADGE)?
              "<a href=\"".$basedir."/images/user-specific/".$S_PDF_BADGE."\" target=\"_blank\">Voir</a>"
              :"<font size=1 color=red>Fichier non trouv� sur le serveur</font>")
              ." <input type=\"checkbox\" name=\"delbadge\"> Supprimer"
              :"<input type='file' name='pdf_badge' size='20' value=\"$S_PDF_BADGE\">
              <br><font size=1><i>Image .gif, .jpg ou .png, Taille 86mm x 54mm")."
              </td>
              </tr>";          
    }

    //------------------------------
    // bloquer �v�nements termin�s
    //------------------------------
      echo "<tr>
               <td colspan=2 class=TabHeader>
                <i>Interdire les modifications sur les �v�nements termin�s</i>
            </td>
          </tr>";
       echo "<tr >
                <td bgcolor=$mylightcolor >Modifications interdites</td>
                <td bgcolor=$mylightcolor align=left>";
    if ( $granted22 and $NIV < $nbmaxlevels -1 ) {
       echo "<select id='NB_DAYS_BEFORE_BLOCK' name='NB_DAYS_BEFORE_BLOCK'>";
       $values=array(0,3,7,15,30,60,90);
       for ($i=0; $i < sizeof($values); $i++) {
           if ( $NB_DAYS_BEFORE_BLOCK == $values[$i] ) $selected='selected';
           else $selected='';
           if ( $values[$i] == 0 ) echo "<option value='0' $selected>Jamais</option>";
           else echo "<option value='".$values[$i]."' $selected>".$values[$i]." jours apr�s la fin</option>";
       }
       echo "</select>";          
    }
    else {
        if ( $NB_DAYS_BEFORE_BLOCK == 0 ) echo "Jamais";
        else echo $NB_DAYS_BEFORE_BLOCK." jours apr�s la fin";
    }
    if ( $NB_DAYS_BEFORE_BLOCK > 0 ) echo "<br><font size=1><i>Sauf pour les personnes ayant la permission n�19</i></font></td>";
    echo "</tr>";

    //------------------------------
    // cacher evenements
    //------------------------------
    if ( $granted22 and $NIV == $nbmaxlevels -2 ) {
        if ( $S_HIDE == 0 ) $checked='';
        else $checked='checked';
        echo "<tr>
               <td colspan=2 class=TabHeader>
                <i>Cacher activit� aux autres d�partements</i>
            </td>
          </tr>";
        echo "<tr >
                <td bgcolor=$mylightcolor >Cacher �v�nements</td>
                <td bgcolor=$mylightcolor align=left>
              <input type = checkbox name='hide' value='1' title='cocher pour rendre les �v�nements du d�partement invisibles pour les personnes non habilit�es des autres d�partements' $checked>";
    }
    else 
        echo "<input type='hidden' name='hide' value='".$S_HIDE."'>";

    //------------------------------
    // Compte SMS local
    //------------------------------
    if ( $granted22 and $S_ID > 0 and $nbsections == 0 ) {
        echo "<tr>
               <td colspan=2 class=TabHeader>
                <i>Compte SMS</i>
            </td>
          </tr>";
          
        $style="disabled";
        $style_user="disabled";
        $style_api="disabled";
        if ( $SMS_LOCAL_PROVIDER > 0 ) $style="";
        if ( $SMS_LOCAL_PROVIDER == 3  or $SMS_LOCAL_PROVIDER == 4 or $SMS_LOCAL_PROVIDER == 6 or $SMS_LOCAL_PROVIDER == 7 or $SMS_LOCAL_PROVIDER == 8) $style_api="";
        if ( $SMS_LOCAL_PROVIDER == 1 or $SMS_LOCAL_PROVIDER == 2 or $SMS_LOCAL_PROVIDER == 3 or $SMS_LOCAL_PROVIDER == 5 or $SMS_LOCAL_PROVIDER == 6 or $SMS_LOCAL_PROVIDER == 7 or $SMS_LOCAL_PROVIDER == 8) $style_user="";
        
        echo "<tr >
                <td bgcolor=$mylightcolor >Fournisseur SMS <a href=".$wikiurl."/SMS target=_blank><i class='fa fa-question-circle fa-lg' title='Information sur la configuration des comptes SMS'></a></td>
                <td bgcolor=$mylightcolor align=left>";
        echo "<select id='SMS_LOCAL_PROVIDER' name='SMS_LOCAL_PROVIDER'>";
        if ( $SMS_LOCAL_PROVIDER == '0' ) $selected="selected"; 
        else $selected="";
        echo "<option value='0' $selected>Pas de compte SMS local</option>";
        if ( $SMS_LOCAL_PROVIDER == '1' ) $selected="selected";
        else $selected="";
        echo "<option value='1' $selected>envoyersmspro.com</option>";
        if ( $SMS_LOCAL_PROVIDER == '2' ) $selected="selected";
        else $selected="";
        echo "<option value='2' $selected>envoyersms.org</option>";
        if ( $SMS_LOCAL_PROVIDER == '3' ) $selected="selected";
        else $selected="";
        echo "<option value='3' $selected>clickatell.com - developer central</option>";
        if ( $SMS_LOCAL_PROVIDER == '6' ) $selected="selected";
        else $selected="";
        echo "<option value='6' $selected>clickatell.com - SMS platform</option>";
        if ( $SMS_LOCAL_PROVIDER == '5' ) $selected="selected";
        else $selected="";
        echo "<option value='5' $selected>smsmode.com</option>";
        if ( $SMS_LOCAL_PROVIDER == '4' ) $selected="selected";
        else $selected="";
        echo "<option value='4' $selected>SMS Gateway Android</option>";
        if ( $SMS_LOCAL_PROVIDER == '7' ) $selected="selected";
        else $selected="";
        echo "<option value='7' $selected>smsgateway.me</option>";
        if ( $SMS_LOCAL_PROVIDER == '8' ) $selected="selected";
        else $selected="";
        echo "<option value='8' $selected>SMSEagle</option>";
        echo "</select>
            </td>
            </tr>";
        echo "<tr bgcolor=$mylightcolor >
                <td> SMS user </td>
              <td align=left>
                <input name='SMS_LOCAL_USER' id='SMS_LOCAL_USER' autocomplete='off'  type='text' maxlength='30' size='30'  value='".$SMS_LOCAL_USER."' 
                    onchange='isValidSMSUser(form.SMS_LOCAL_USER,\"$SMS_LOCAL_USER\");' 
                    title=\"Utilisateur du compte SMS. Ce champ est inutile dans le cas de SMS Gateway\"
                    $style_user>
              </td>
            </tr>";
        echo "<tr bgcolor=$mylightcolor   >
                <td> SMS password </td>
              <td align=left>
                <input name='SMS_LOCAL_PASSWORD' id='SMS_LOCAL_PASSWORD' autocomplete='off'  type='text'  size='30'  value='****************' $style>
             </td>
            </tr>";
        echo "<tr bgcolor=$mylightcolor  >
                <td title='API ID clickatell, ou address:port pour SMS Gateway Android, ou Device ID pour smsgateway.me'> SMS API ID </td>
              <td align=left>
                <input name='SMS_LOCAL_API_ID' id='SMS_LOCAL_API_ID' type='text' maxlength='30' size='30' value='".$SMS_LOCAL_API_ID."' 
                title=\"Num�ro d'API dans le cas de clickatell, ou adresseIP:port dans le cas de SMS Gateway exemple 88.65.125.65:9000, ou adresse pour SMSEagle exemple demounit.smseagle.eu\"
                $style_api>
              </td>
            </tr>";
    }

    echo "</table>";

    if ($showbadge or $showfact or $granted22) {
        echo "<p><input type='submit' class='btn btn-default' value='sauver param�trage'>";
    }
    echo "</form>";

    echo "</div>"; // fin tab 3
} // if $showfact or $showbadge


//=====================================================================
// tab 4 agr�ments - sauf niveau antenne locale
//=====================================================================

if (( $NIV < $nbmaxlevels -1 ) and  $assoc and $tab == 4 ) {
     
    echo "<div id='agrements'>";

    echo "<form name='sectionform5' action='save_section.php' method='POST' enctype='multipart/form-data'>";
    print insert_csrf('section');
    echo "<input type='hidden' name='operation' value='update'>";
    echo "<input type='hidden' name='S_ID' value='$S_ID'>";
    echo "<input type='hidden' name='status' value='agrements'>";

    echo "<p><table cellspacing=0 border=0>";

    $query2="select ca.CA_CODE, ca.CA_DESCRIPTION, ta.TA_CODE, ta.TA_DESCRIPTION, ta.TA_FLAG
             from categorie_agrement ca, type_agrement ta
             where ca.CA_CODE =ta.CA_CODE
             order by ca.CA_DESCRIPTION, ta.TA_CODE ";
    $result2=mysqli_query($dbc,$query2);

    $old_CA_CODE="";
    while ($row2=@mysqli_fetch_array($result2)) {
        $CA_CODE=$row2["CA_CODE"];
        $CA_DESCRIPTION=$row2["CA_DESCRIPTION"];
        $TA_CODE=$row2["TA_CODE"];
        $TA_FLAG=$row2["TA_FLAG"];
        $TA_DESCRIPTION=$row2["TA_DESCRIPTION"];

        if ( $old_CA_CODE <> $CA_CODE ) {
            echo "<tr class=TabHeader>
                <td colspan=3 class=TabHeader >".$CA_DESCRIPTION."</td>";
            if ( $CA_CODE == '_MED' ) echo "<td >D�livr�e le</td><td >Agrafe</td>";
            else echo "<td >D�but</td><td>Fin</td>";
            echo "</tr>";
             $old_CA_CODE = $CA_CODE;
        }
        $mycolor=$mylightcolor;
        
        $query="select date_format(a.A_DEBUT,'%d-%m-%Y') A_DEBUT, date_format(a.A_FIN,'%d-%m-%Y') A_FIN, 
                a.TAV_ID , tav.TA_VALEUR, tav.TA_FLAG, a .A_COMMENT
                from agrement a
                left outer join type_agrement_valeur tav
                on a.TAV_ID= tav.TAV_ID
                where a.S_ID=".$S_ID." 
                and a.TA_CODE='".$TA_CODE."'";
        $result=mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        $CURA_COMMENT=$row["A_COMMENT"];
        $CURA_DEBUT=$row["A_DEBUT"];
        $CURA_FIN=$row["A_FIN"];
        $CURTAV_ID=$row["TAV_ID"];
        $CURTA_VALEUR=$row["TA_VALEUR"];
        $CURTA_FLAG=$row["TA_FLAG"];
        
        $agr=0;
        if (( $CURA_DEBUT == '' ) and ( $CURA_FIN == '' )) $agr=0;
        else if (( $CURA_FIN <> '' ) and ( $CURA_DEBUT == '' )){
            if (my_date_diff(getnow(),$CURA_FIN) > 0) $agr=1;
            else $agr=-1;
        }
        else if (( $CURA_DEBUT <> '' ) and ( $CURA_FIN == '' )) {
             if (my_date_diff($CURA_DEBUT,getnow()) > 0) $agr=1;
        }
        else { // 2 dates renseign�es
             if (my_date_diff(getnow(),$CURA_FIN) < 0)$agr=-1;
             else if ((my_date_diff($CURA_DEBUT,getnow()) > 0) and (my_date_diff(getnow(),$CURA_FIN) > 0)) $agr=1;
        }
        $img='';
        if ( $agr == 1 and $CA_CODE == '_MED' ) $img="<i class='fa fa-certificate fa-lg' style='color:yellow;' title='m�daille d�cern�e'></i>";
        else if ( $agr == 1 ) $img="<i class='fa fa-check' style='color:green;' title='agr�ment actif'></i>";
        else if ( $agr == -1 ) $img="<i class='fa fa-exclamation-triangle'  style='color:orange;' title='agr�ment p�rim�' ></i>";
        
        echo "<tr bgcolor=$mycolor >
                <td width=70 ><b>".$TA_CODE."</b></td>
                <td width=350 align=left>".$TA_DESCRIPTION."</td>
              <td width=10 align=left>".$img."</td>";
        if ( $granted_agrement or ($granted22 and $TA_FLAG == 1)) {
            echo "<td width=100> <input type=text maxlength=10  style='width:84px;' name='deb_".$TA_CODE."'
                    value='$CURA_DEBUT' title='JJ-MM-AAAA' 
                    onchange='checkDate2(this)'></td>";
            if ( $CA_CODE == '_MED' ) {
                echo "<td> <input type=text maxlength=40 style='width:125px;' name='comment_".$TA_CODE."' value=\"".$CURA_COMMENT."\"></td>";
            }
            else 
                echo "<td width=100> <input type=text maxlength=10 style='width:84px;' name='fin_".$TA_CODE."'
                    value='$CURA_FIN' title='JJ-MM-AAAA' 
                    onchange='checkDate2(this)'></td>";
        }
        else {
           echo "<td width=100>$CURA_DEBUT</td>";
           echo "<td width=100>$CURA_FIN</td>";
        }
        echo "</tr>";
        
        $query="select TAV_ID, TA_CODE, TA_VALEUR from type_agrement_valeur where TA_CODE='".$TA_CODE."'";
        $result=mysqli_query($dbc,$query);
        if ( mysqli_num_rows($result) > 0 ) {
             echo "<tr><td bgcolor=$mycolor align=right><font size=1>agr�ment</font></td>";
             echo "<td bgcolor=$mycolor colspan=4 align=left>";
             if ( $granted_agrement ) {
                echo " <select name='val_".$TA_CODE."'>";
                while ($row=@mysqli_fetch_array($result)) {
                     $TAV_ID=$row["TAV_ID"];
                     $TA_VALEUR=$row["TA_VALEUR"];
                     if ( $CURTAV_ID == $TAV_ID ) $selected='selected';
                     else $selected='';
                     echo "<option value=".$TAV_ID." $selected>".$TA_VALEUR."</option>";
                }
                echo "</select>";
            }
            else {
                 echo "<i>".$CURTA_VALEUR."</i>";
            }
            echo "</td></tr>";
        }
    }
    echo "</table>";

    if ($granted_agrement or $granted22) {
        echo "<p><input type='submit' class='btn btn-default' value='sauver'>";
    }
    echo "</form>";
    echo "</div>";
}

//=====================================================================
// tab 5 cotisations / profession - sauf niveau antenne locale
//=====================================================================

if ( $NIV < $nbmaxlevels -1  and $cotisations == 1 and $granted22 and $tab == 5 ) {
     
    echo "<div id='cotisations'>";

    if ( $granted_cotisations )
        forceReloadJS('js/rib.js');

    echo "<form name='bic' action='save_section.php' method='POST'>";
    print insert_csrf('section');    
    echo "<input type='hidden' name='operation' value='update'>";
    echo "<input type='hidden' name='S_ID' value='$S_ID'>";
    echo "<input type='hidden' name='status' value='cotisations'>";

    if ( $bank_accounts == 1 ) {
        echo "<input type=hidden name=S_ID value=".$S_ID.">";
        echo "<p><table cellspacing=0 border=0 width=840>
              <tr>
                 <td class=TabHeader colspan=7>Compte bancaire - utilis� pour pr�l�vements et virements</td>
             </tr>";

        // compte bancaire   
        $query="select BIC,IBAN,UPDATE_DATE from compte_bancaire where CB_TYPE='S' and CB_ID=".$S_ID;
        $result=mysqli_query($dbc,$query);
        
        $row=@mysqli_fetch_array($result);
        $BIC=$row["BIC"];
        $IBAN=$row["IBAN"];
        $UPDATE_DATE=$row["UPDATE_DATE"];
        if ( $UPDATE_DATE <> "" ) $UPDATE_DATE = "modifi� le ".$UPDATE_DATE;
        $IBAN1=substr($IBAN,0,4);
        $IBAN2=substr($IBAN,4,4);
        $IBAN3=substr($IBAN,8,4);
        $IBAN4=substr($IBAN,12,4);
        $IBAN5=substr($IBAN,16,4);
        $IBAN6=substr($IBAN,20,4);
        $IBAN7=substr($IBAN,24,4);
        $IBAN8=substr($IBAN,28,4);

        if ( $granted_cotisations ) {
                   
            echo "<tr bgcolor=$mylightcolor align=center>
                 <td> 
                    BIC <input type='text' name='bic' id='bic' size=12 maxlength=11 class='inputRIB-lg11'  style='width:120px;'
                    title='11 caract�res, chiffres et lettres' value='$BIC' onchange='isValid5(this,\"$BIC\",\"11\");'></td>
                 <td colspan=4 align=left> IBAN 
                    <input type='text' name='iban1' id='iban1' style='width:43px;' maxlength=4 class='inputRIB-lg4' title='4 caract�res, chiffres et lettres majuscules' value='$IBAN1' 
                    onchange='isValid5(form.iban1,\"$IBAN1\",\"4\");' onKeyUp=\"suivant(this,'iban2',4)\">
                    <input type='text' name='iban2' id='iban2' style='width:43px;' maxlength=4 class='inputRIB-lg4' title='4 caract�res, chiffres et lettres majuscules' value='$IBAN2' 
                    onchange='isValid5(form.iban2,\"$IBAN2\",\"4\");' onKeyUp=\"suivant(this,'iban3',4)\">
                    <input type='text' name='iban3' id='iban3' style='width:43px;' maxlength=4 class='inputRIB-lg4' title='4 caract�res, chiffres et lettres majuscules' value='$IBAN3' 
                    onchange='isValid5(form.iban3,\"$IBAN3\",\"4\");' onKeyUp=\"suivant(this,'iban4',4)\">
                    <input type='text' name='iban4' id='iban4' style='width:43px;' maxlength=4 class='inputRIB-lg4' title='4 caract�res, chiffres et lettres majuscules' value='$IBAN4' 
                    onchange='isValid5(form.iban4,\"$IBAN4\",\"4\");' onKeyUp=\"suivant(this,'iban5',4)\">
                    <input type='text' name='iban5' id='iban5' style='width:43px;' maxlength=4 class='inputRIB-lg4' title='4 caract�res maximum, chiffres et lettres majuscules' value='$IBAN5' 
                    onchange='isValid4(form.iban5,\"$IBAN5\",\"4\");' onKeyUp=\"suivant(this,'iban6',4)\">
                    <input type='text' name='iban6' id='iban6' style='width:43px;' maxlength=4 class='inputRIB-lg4' title='4 caract�res maximum, chiffres et lettres majuscules' value='$IBAN6' 
                    onchange='isValid4(form.iban6,\"$IBAN6\");' onKeyUp=\"suivant(this,'iban7',4)\">
                    <input type='text' name='iban7' id='iban7' style='width:43px;' maxlength=4 class='inputRIB-lg4' title='4 caract�res maximum, chiffres et lettres majuscules' value='$IBAN7' 
                    onchange='isValid4(form.iban7,\"$IBAN7\");' onKeyUp=\"suivant(this,'iban8',4)\">
                    <input type='text' name='iban8' id='iban8' style='width:43px;' maxlength=4 class='inputRIB-lg4' title='4 caract�res maximum, chiffres et lettres majuscules' value='$IBAN8' 
                    onchange='isValid4(form.iban8,\"$IBAN8\");' onKeyUp=\"verificationIBAN()\">";
            $errstyle="style='display:none'";
            $successstyle="style='display:none'";
            $warnsstyle="style='display:none'";
            if ( $IBAN7 == "" or $IBAN2 == "") $warnsstyle="";
            else {
                if ( isValidIban($IBAN1.$IBAN2.$IBAN3.$IBAN4.$IBAN5.$IBAN6.$IBAN7.$IBAN8) ) $successstyle="";
                else $errstyle="";
            }
            echo " <span id='iban_warn' $warnsstyle><i class='fa fa-exclamation-triangle fa_lg' style='color:orange;' title='IBAN saisi incomplet, on ne peut pas v�rifier si il est valide' ></i></span>
                   <span id='iban_success' $successstyle><i class='fa fa-check-square fa-lg' style='color:green;' title='IBAN valide' ></i></span>
                   <span id='iban_error' $errstyle><i class='fa fa-ban fa-lg' style='color:red;'  title='IBAN faux'></i></span>
                  </td>
                  <td><i class='fa fa-eraser fa-lg' style='color:pink' title='Effacer donn�es du BIC/IBAN' onclick='eraser_iban();'></i></td>
                  <td class=small>".$UPDATE_DATE."</tr>";
        }
        else {
            echo "<tr bgcolor=$mylightcolor align=center>
                  <td> BIC : $BIC </td>
                  <td colspan=6> IBAN: $IBAN1 $IBAN2 $IBAN3 $IBAN4 $IBAN5 $IBAN6 $IBAN7 $IBAN8 </td>
                  <td class=small>".$UPDATE_DATE."</td>";
        }
        echo "</table>";
    }

    echo "<p><table cellspacing=0 border=0 width=840>";

    echo "<tr class=TabHeader align=center>";
    if ( $syndicate == 1 ) 
        echo "<td width=50 align=left>Code</td>
        <td width=200 align=center>Profession</td>";
    echo "<td width=120 >Montant annuel</td>";
    echo "<td width=80 > mensuel</td>";
    // cas particulier syndicat FA SPP PATS, la reference est le niveau 1 et pas le niveau 0
    if ( $syndicate == 1 ) $n=1;
    else $n=0;
    $query3="select S_ID,S_CODE from section_flat where S_ID=(select min(S_ID) from section_flat where NIV=".$n.")";
    $result3=mysqli_query($dbc,$query3);
    $row3=@mysqli_fetch_array($result3);
    $S_ID3=$row3[0];
    $S_CODE3=$row3[1];

    if ( $NIV > $n ) echo "<td width=130 class=small2>idem ".$S_CODE3."?</td>";
    echo "<td width=230>Commentaire</td>";
    echo "</tr>";

    // afficher les montants par profession ( si syndicat) , le m�me pour toutes professions sinon
    $query2="select TP_CODE, TP_DESCRIPTION from type_profession tp";
    if ( $syndicate == 0 ) $query2 .=" where TP_CODE='SPP'";

    $result2=mysqli_query($dbc,$query2);
    while ($row2=@mysqli_fetch_array($result2)) {
        $TP_CODE=$row2["TP_CODE"];
        $TP_DESCRIPTION=$row2["TP_DESCRIPTION"];
        
        $cotisation=get_param_cotisation($S_ID,$TP_CODE);
        $MONTANT=$cotisation[0];
        $IDEM=$cotisation[1];
        $COMMENTAIRE=$cotisation[2];
        
        echo "<tr bgcolor=$mylightcolor>";
        if ( $syndicate == 1 ) echo "<td align=left>".$TP_CODE."</td><td align=left class=small2>".$TP_DESCRIPTION."</td>";
        if ( $IDEM == 1 and $S_ID > 0 ) $disabled_montant='disabled';
        else $disabled_montant='';
        echo "<td align=center>
            <input type=text size=5 name='montant_".$TP_CODE."' id='montant_".$TP_CODE."' value='".$MONTANT."'  $disabled_montant $disabled
            onchange=\"checkFloat(this,'".$MONTANT."');\"
            onKeyUp=\"calculate_monthly('".$TP_CODE."');\"> ".$default_money_symbol."</td>";
        
        $mensuel = round($MONTANT / 12, 2);
        echo "<td align=center ><input type=text readonly  name='monthly_".$TP_CODE."' id='monthly_".$TP_CODE."'
              style='border:0px;background-color:$mylightcolor;width:40px;color:$mydarkcolor;'
              value=".$mensuel.">".$default_money_symbol."</td>";
        
        if ( $NIV > $n )  {
            if ( $IDEM == 1 ) $checked='checked';
            else $checked='';
            
            $cotisation_defaut=get_param_cotisation("$S_ID3",$TP_CODE);
            $montant_defaut=$cotisation_defaut[0];
            
            echo "<td align=center class=small><input type=checkbox name='idem_".$TP_CODE."' id='idem_".$TP_CODE."' value='1' $checked $disabled
                onchange=\"isdefault('".$TP_CODE."','".$montant_defaut."');\">
                  <a href=upd_section.php?S_ID=".$S_ID3."&status=cotisations 
                  title=\"Voir configuration des cotisations ".$S_CODE3.", montant: $montant_defaut $default_money_symbol par an\">".$S_CODE3."</a></td>";
        }
        echo "<td><input type=text maxlength=50 style='width:240px;' name='commentaire_".$TP_CODE."' value=\"".$COMMENTAIRE."\" $disabled></td>";
        echo "</tr>";
    }

    echo "</table>";

    if ($granted_cotisations) {
        echo "<p><input type='submit' class='btn btn-default' value='sauver param�trage'>";
    }
    echo "</form>";
    echo "</div>";
}


//=====================================================================
// interdire evenements
//=====================================================================

if ( $NIV >= $nbmaxlevels -2  and $assoc == 1 and $granted22 and $tab == 6 ) {
     
    echo "<div id='evenements'><p>";
    $help="La cr�ation de certains types d'�v�nements peut �tre bloqu�e temporairement. 
    Le responsable d�partemental peut enregistrer une p�riode d'interdiction pour un type d'�v�nement, pour une section ou pour le d�partement .
    Ceci permet d'�viter l'engagement du personnel sur des activit�s secondaires non critiques au d�triment de certaines activit�s importantes d�j� pr�vues.";
    
    echo " <h4>Interdictions de cr�er certains �v�nements 
        <a href='#' data-toggle='popover' title='Interdiction de certaines activit�s' data-trigger='hover' data-content=\"".$help."\"><i class='fa fa-question-circle' ></i></a> </h4>";
    
    $query="select s.SSE_ID, s.TE_CODE, te.TE_LIBELLE, te.TE_ICON, 
            date_format(s.START_DATE, '%d-%m-%Y') START_DATE,
            date_format(s.END_DATE, '%d-%m-%Y') END_DATE,
            s.SSE_COMMENT, se.S_ID S_ID2, se.S_CODE S_CODE2,
            s.SSE_ACTIVE, s.SSE_BY, date_format(s.SSE_WHEN, '%d-%m-%Y %H:%i') SSE_WHEN,
            datediff(s.END_DATE, NOW()) as DAYS,
            p.P_NOM, p.P_PRENOM
            from section_stop_evenement s
            left join pompier p on p.P_ID = s.SSE_BY
            left join type_evenement te on te.TE_CODE = s.TE_CODE
            left join section se on se.S_ID = s.S_ID
            where s.S_ID in (".get_family_up($S_ID).")
            order by s.START_DATE asc";
    $result=mysqli_query($dbc,$query);
    write_debugbox($query);
    $i=0;
    if ( mysqli_num_rows ($result) > 0 ) {
        echo "<table cellspacing=0 border=0>";
        echo "<tr class=TabHeader><td colspan=2 class=TabHeader style='min-width:220px;'>Type �v�nement</td>
                   <td style='min-width:60px;'>Niveau</td>
                   <td width=80 >D�but</td>
                   <td width=80 >Fin</td>
                   <td width=30 >Actif</td>
                   <td colspan=3></td>
              </tr>";
        while (custom_fetch_array($result)) {
            $i=$i+1;
            if ( $i%2 == 0 ) $mycolor=$mylightcolor;
            else $mycolor="#FFFFFF";
            if ( $TE_ICON == '' ) $TE_ICON ="BALL1.png";
            if ( $TE_CODE == 'ALL' ) $TE_LIBELLE = "<b>Tous les types d'�v�nements</b>";
            $img="<img src=images/evenements/".$TE_ICON." class='img-max-20'>";
            if ( $SSE_ACTIVE == 1 ) $active="<i class='fas fa-check' style='color:green;' title=\"L'interdiction est active\"></i>";
            else $active="<i class='far fa-stop-circle' style='color:red;' title=\"L'interdiction est suspendue\"></i>";
            
            if ( $DAYS < 0 ) $style="style='color:grey;'";
            else $style='';
            
            $cmt = $SSE_COMMENT;
            if ( intval($SSE_BY) > 0 ) $cmt .= " - Interdiction ajout�e par ".my_ucfirst($P_PRENOM)." ".strtoupper($P_NOM)." le ".$SSE_WHEN; 
            if ( $cmt <> '' )  $cmt = "<a href='#' data-toggle='popover' data-placement='bottom' title=\"Commentaire\" data-trigger='hover' 
                data-content=\"".$cmt."\"><i class='far fa-file-alt fa-lg' $style></i></a>";
                
            echo "<tr bgcolor=$mycolor>
                <td width=30 align=center>".$img."</td>
                <td align=left $style>".$TE_LIBELLE."</td>
                <td align=left><a href='upd_section.php?S_ID=".$S_ID2."&tab=6' title='Voir les interdictions pour ce niveau' $style>".$S_CODE2."</a></td>
                <td align=left $style>".$START_DATE."</td>
                <td align=left $style>".$END_DATE."</td>
                <td align=left>".$active."</td>
                <td width=16 align=left $style>".$cmt."</td>";
            if ( $S_ID == $S_ID2 ) {
                echo "<td width=16 align=left>
                    <a href='#' onclick=\"javascript:delete_stop('".$S_ID."','".$SSE_ID."');\" title='supprimer cette interdiction' >
                    <i class='far fa-trash-alt fa-lg' $style></i>
                    </a></td>";
                $url="section_stop.php?section=".$S_ID."&sseid=".$SSE_ID."&action=update";
                $cmt= write_modal( $url, $SSE_ID, "<i class='fa fa-pen-square fa-lg' title='Modifier' $style></i>");
                echo "<td width=16 align=left>".$cmt."</td>";
            }
            else 
                echo "<td colspan=2></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    $url="section_stop.php?section=".$S_ID."&action=add";
    echo "<p>";
    print write_modal( $url, $S_ID, "<input type=submit class='btn btn-default' value='Ajouter'> ");
    
}
else echo "<p>";
//=====================================================================
// save buttons
//=====================================================================

if ( $from == 'export' ) {
    echo " <input type=submit class='btn btn-default' value='fermer cette page' onclick='fermerfenetre();'> ";
}
elseif ( $from == 'save' ) {
     echo " <input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"javascript:self.location.href='index_d.php';\">";
     $_SESSION['status'] = "infos";
}
else {
    echo " <input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\">";
    $_SESSION['status'] = "infos";
}

echo "</form>";
echo "</div><p style='margin-top:180px'>";
writefoot();
?>
