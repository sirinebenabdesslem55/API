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
check_all(14);
writehead();

?>
<link  rel='stylesheet' href='css/bootstrap-toggle.css'>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/configuration.js?version=<?php echo $version; ?>'></script>
<script type='text/javascript' src='js/theme.js'></script>
<script type='text/javascript' src='js/bootstrap-toggle.js'></script>

<?php

if ( isset($_GET['tab']) ) $tab=$_GET['tab']; 
else $tab='conf1';


$html = "</head>";
$html .= "<body>";
$html .= "<div align=center > <font size=4><b>Configuration de l'application</b></font><p>";

if ( isset($_GET['saved']) ) {
    $errcode=$_GET['saved'];
    $html .= "<div id='fadediv' align=center>";
    if ( $errcode == 'nothing' ) $html .= "<div class='alert alert-info' role='alert'> Aucun changement à sauver.</div></div><p>";
    else if ( $errcode == 0 ) $html .= "<div class='alert alert-success' role='alert'> Paramètres de configuration sauvés.</div></div><p>";
    else $html .= "<div class='alert alert-danger' role='alert'> Erreur lors de la sauvegarde des paramètres de configuration.</div></div><p>";
}

$html .=  "<ul class='nav nav-tabs noprint' id='myTab' role='tablist'>";
if ( $tab == 'conf1' ) $class='active';
else $class='';

if ( $tab == 'conf1' ) $class='active';
else $class='';
$html .= "<li class='nav-item'>
    <a class='nav-link $class' href='configuration.php?tab=conf1' title='Configuration de base' role='tab' aria-controls='conf1' href='#conf1' >
    <span>Base</span></a></li>";

    
if ( $tab == 'conf2' ) $class='active';
else $class='';
$html .= "<li class='nav-item'>
    <a class='nav-link $class' href='configuration.php?tab=conf2' title='Configuration avancée' role='tab' aria-controls='conf2' href='#conf2' >
    <span>Avancée</span></a></li>";


if ( $tab == 'conf3' ) $class='active';
else $class='';
$html .= "<li class='nav-item'>
    <a class='nav-link $class' href='configuration.php?tab=conf3' title='Configuration de la sécurité' role='tab' aria-controls='conf3' href='#conf3' >
    <span>Sécurité</span></a></li>";

$html .= "</ul>";// fin tabs

// ===============================================
// function display group
// ===============================================

function display_configuration_group($group_id) {
    global $dbc, $mydarkcolor, $mylightcolor, $sms_provider, $tab, $allow_sdis_mode, $wikiurl, $version, $patch_version;
    $H = "<div id='".$group_id."' style='position: relative; top:10px;' align=center>";
     
    $H .= "<form name='config' method=POST action=save_configuration.php>";
    $H .= insert_csrf('configuration');
    $H .= "<input type='hidden' name='tab' value='".$group_id."'>";
    $H .= "<table cellspacing=0 border=0>";
    $H .= "<tr class=TabHeader>
          <td width=120>Paramètre</td>
          <td width=250>Valeur</td>
          <td width=550 class='hide_mobile'>Description</td>
          </tr>";

    $query="select ID, NAME, VALUE, DESCRIPTION, HIDDEN, TAB, YESNO from configuration ";
    if ($group_id == 'conf1')  $query .=" where TAB=1";
    if ($group_id == 'conf2')  $query .=" where TAB=2";
    if ($group_id == 'conf3')  $query .=" where TAB=3";
    
    $query .= " order by ORDERING, NAME";
    $result=mysqli_query($dbc,$query);
    $i=0;
    $current_nbsections=0;
    $current_dispos=0;
    $current_sdis = 0;
    $current_syndicate = 0;
    $current_sms = 0;
    $current_geolocalize_enabled = 0;
    $current_import_api = 0;
    $current_gardes=0;
    $current_army = 0;
    while ($row=@mysqli_fetch_array($result)) {
        $style = '';
        $ID=$row["ID"];
        $NAME=$row["NAME"];
        $VALUE=$row["VALUE"];
        $HIDDEN=$row["HIDDEN"];
        $TAB=$row["TAB"];
        $YESNO=$row["YESNO"];
        if ( $NAME == 'disponibilites' and $VALUE == '1' ) $current_dispos = 1;
        if ( $NAME == 'sdis' and $VALUE == '1' ) $current_sdis = 1;
        if ( $NAME == 'syndicate' and $VALUE == '1' ) $current_syndicate = 1;
        if ( $NAME == 'army' and $VALUE == '1' ) $current_army = 1;
        if ( $NAME == 'sms_provider' and intval($VALUE) > 0 ) $current_sms = intval($VALUE);
        if ( $NAME == 'geolocalize_enabled' and intval($VALUE) > 0 ) $current_geolocalize_enabled = intval($VALUE);
        if ( $NAME == 'import_api' and intval($VALUE) > 0 ) $current_import_api = intval($VALUE);
        if ( $NAME == 'nbsections') {
            $NAME='type_organisation';
            $current_nbsections=$VALUE;
        }
        $DESCRIPTION=$row["DESCRIPTION"];
        if ( $i%2 == 0 ) $mycolor="$mylightcolor";
        else $mycolor="#FFFFFF";
        
        if ( ($current_nbsections == 3 or $current_sdis == 1) and $ID == 30 ) {$style ="style='display:none'";$i++; }
        if ( $current_dispos == 0 and $ID == 47 ) {$style ="style='display:none'";$i++; }
        if ( $current_geolocalize_enabled == 0 and ($ID == 57 or $ID == 60 )) {$style ="style='display:none'";}
        if ( $current_import_api == 0 and ($ID == 65 or $ID == 66 )) {$style ="style='display:none'";}
        if ( $current_sms == 0 and ($ID == 10 or $ID == 11 or $ID == 12 )) {$style ="style='display:none'";$i++; }
        if ( ($current_sms == 1 or $current_sms == 2) and $ID == 12 ) {$style ="style='display:none'";$i++; }
        if ( ($current_sms == 3 or $current_sms == 4) and $ID == 4 ) {$style ="style='display:none'";$i++; }
          
        if ( $HIDDEN == 0 ) {
            $H .= "\n<tr bgcolor=$mycolor id='row".$ID."' $style>
              <td title='paramètre n°".$ID."' >$NAME </td>
              <td align=left valign=middle>";
            if ( $ID == 1 ) {
                if ( $patch_version <> '' ) $H .= $patch_version;
                else $H .= "$VALUE";
            }
            elseif ( $ID == 2 ) {
                $H .= "<select id='f2' name='f$ID' onchange='modify(config.f2,\"".$ID."\", this.value, \"".$VALUE."\")'>";
                if ( $VALUE == '0' ) {
                    // association ou syndicat? hack
                    if ( $current_syndicate == 1 ) $VALUE='4'; // syndicat
                    else if ( $current_army == 1 ) $VALUE='5'; // militaires
                    else if ( $current_sdis == 1 ) $VALUE='2'; // sdis
                }
                if ( $VALUE == '0' ) $selected="selected"; 
                else $selected="";
                $H .= "<option value='0' $selected>Association de secourisme</option>";
                if ( $VALUE == '4' ) $selected="selected"; 
                else $selected="";
                $H .= "<option value='4' $selected>Gestion des adhérents</option>";
                if ( $VALUE == '3' ) $selected="selected";
                else $selected="";
                $H .= "<option value='3' $selected>Caserne pompiers</option>";
                if ( $allow_sdis_mode ) {
                    if ( $VALUE == '2' ) $selected="selected";
                    else $selected="";
                    $H .= "<option value='2' $selected>Service d'incendie et Secours</option>";
                }
                if ( $VALUE == '5' ) $selected="selected";
                else $selected="";
                $H .= "<option value='5' $selected>Organisation militaire</option>";
                $H .= "</select>";
            }
            else if ($YESNO == 1){
                if ( $VALUE == '1' ) $checked='checked';
                else $checked='';
                $H .= "<input type='hidden' id='f".$ID."hidden' name='f$ID' value='0'>";
                $H .= "<input type='checkbox' id='f$ID' name='f$ID' data-toggle='toggle' data-size='small' data-onstyle='success' 
                        data-offstyle='danger' data-on='Oui' data-off='Non' value='1' style='height:22px' $checked
                        onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")' >";
            }
            else if ( $ID == 54){
                $s0='background-color:#CEF6F5;color:black;';
                $s1='background-color:#FF9999;color:black;';
                $s2='background-color:#FACC2E;color:black;';
                $s3='background-color:black;color:white;';
                $current_style=$s0;
                if ( $VALUE == '1' ) $current_style=$s1;
                if ( $VALUE == '2' ) $current_style=$s2;
                if ( $VALUE == '3' ) $current_style=$s3;
                $H .= "<select id='f$ID' name='f$ID' onchange='modify(config.f".$ID.", \"".$ID."\", this.value, \"".$VALUE."\")' class='theme'  style='".$current_style."'>";
                if ( $VALUE == '0' ) $selected='selected'; else $selected='';
                $H .= "<option value='0' $selected style='".$s0."'>Aucune</option>";
                if ( $VALUE == '1' ) $selected='selected'; else $selected='';
                $H .= "<option value='1' $selected style='".$s1."'>Erreurs</option>";
                if ( $VALUE == '2' ) $selected='selected'; else $selected='';
                $H .= "<option value='2' $selected style='".$s2."'>Erreurs + Warnings</option>";
                if ( $VALUE == '3' ) $selected='selected'; else $selected='';
                $H .= "<option value='3' $selected style='".$s3."'>Erreurs + Warnings + Debug</option>";
                $H .= "</select>";
            }
            elseif ( $ID == 8 ) {
                $H .= "<input id='f8' name='f$ID' type=text value='$VALUE' size=30 
                onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
            }
            elseif ( $ID == 9 ) {
                $H .= "<select id='f9' name='f$ID' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
                if ( $VALUE == '0' ) $selected="selected"; 
                else $selected="";
                $H .= "<option value='0' $selected>SMS désactivés</option>";
                if ( $VALUE == '1' ) $selected="selected";
                else $selected="";
                $H .= "<option value='1' $selected>envoyersmspro.com</option>";
                if ( $VALUE == '2' ) $selected="selected";
                else $selected="";
                $H .= "<option value='2' $selected>envoyersms.org</option>";
                if ( $VALUE == '3' ) $selected="selected";
                else $selected="";
                $H .= "<option value='3' $selected>clickatell.com - ancien compte</option>";
                if ( $VALUE == '6' ) $selected="selected";
                else $selected="";
                $H .= "<option value='6' $selected>clickatell.com</option>";
                if ( $VALUE == '5' ) $selected="selected";
                else $selected="";
                $H .= "<option value='5' $selected>smsmode.com</option>";
                if ( $VALUE == '4' ) $selected="selected";
                else $selected="";
                $H .= "<option value='4' $selected>SMS Gateway Android</option>";
                if ( $VALUE == '7' ) $selected="selected";
                else $selected="";
                $H .= "<option value='7' $selected>smsgateway.me</option>";
                if ( $VALUE == '8' ) $selected="selected";
                else $selected="";
                $H .= "<option value='8' $selected>SMSEagle</option>";
                $H .= "</select>";
                $H .= " <a href='".$wikiurl."/SMS' target=_blank><i class='fa fa-question-circle fa-lg' title='Information sur la configuration des comptes SMS'></a>";
            }
            elseif ( $ID == 11 ) {
                $H .= "<input id='f$ID' name='f$ID' type='text' value='$VALUE' size=30 onBlur='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>"; 
            }
            elseif ( $ID == 12 ) {
                $H .= "<input id='f$ID' name='f$ID' type=text value='$VALUE'  size=30 
                onBlur='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>"; 
            }
            elseif ( $ID == 10 ) {
                    $H .= "<input id='f$ID' name='f$ID' type='text' value='$VALUE' size=30 onBlur='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>"; 
            }
            elseif ( $ID == 15 ) {
                $H .= "<select id='f15' name='f$ID' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
                if ( $VALUE == '0' ) $selected="selected"; 
                else $selected="";
                $H .= "<option value='0' $selected>pas de contrainte</option>";
                if ( $VALUE == '1' ) $selected="selected";
                else $selected="";
                $H .= "<option value='1' $selected>chiffres et lettres</option>";
                    if ( $VALUE == '2' ) $selected="selected";
                else $selected="";
                $H .= "<option value='2' $selected>chiffres,lettres et caractères spéciaux</option>";
                $H .= "</select>";
            }
            elseif ( $ID == 47 ) {
                $H .= "<select id='f47' name='f$ID' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
                if ( $VALUE == '1' ) $selected="selected"; 
                else $selected="";
                $H .= "<option value='1' $selected>1 période de 24h</option>";
                if ( $VALUE == '2' ) $selected="selected";
                else $selected="";
                $H .= "<option value='2' $selected>2 périodes de 12h (Jour/Nuit)</option>";
                if ( $VALUE == '3' ) $selected="selected";
                else $selected="";
                $H .= "<option value='3' $selected>3 périodes de 8h (Matin/A-M/Nuit)</option>";
                if ( $VALUE == '4' ) $selected="selected";
                else $selected="";
                $H .= "<option value='4' $selected>4 périodes de 6h (Matin/A-M/Soir/Nuit)</option>";
                $H .= "</select>";
            }
            elseif ( $ID == 16 ) {
                $H .= "<select id='f16' name='f$ID' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
                if ( $VALUE == '0' ) $selected="selected"; 
                else $selected="";
                $H .= "<option value='0' $selected>pas de longueur minimum</option>";
                for ( $k=1 ; $k<=20 ; $k++) {
                    if ( $VALUE == $k ) $selected="selected";
                    else $selected='';
                    $H .= "<option value='$k' $selected>$k</option>";
                }
                $H .= "</select>";
            }
            elseif ( $ID == 17 ) {
                $H .= "<select id='f17' name='f$ID' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
                if ( $VALUE == '0' ) $selected="selected"; 
                else $selected="";
                $H .= "<option value='0' $selected>jamais de bloquage</option>";
                for ( $k=3 ; $k<=10 ; $k++) {
                    if ( $VALUE == $k ) $selected="selected";
                    else $selected='';
                    $H .= "<option value='$k' $selected>$k échecs</option>";
                }
                $H .= "</select>";
            }
            elseif ( $ID == 27 ) {
                $queryt="select COLOR from theme where NAME='".$VALUE."'";
                $resultt=mysqli_query($dbc,$queryt);
                $rowt=@mysqli_fetch_array($resultt);
                $current_color=$rowt['COLOR'];
                $H .= "<select class='theme' id='f$ID' name='f$ID' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'  style='background-color:#".$current_color.";' >";
                $queryt="select NAME, COLOR from theme order by name asc";
                $resultt=mysqli_query($dbc,$queryt);
                while ($rowt=@mysqli_fetch_array($resultt)) {
                    if ( $VALUE == $rowt['NAME'] ) $selected='selected';
                    else $selected ='';
                    $H .= "<option value='".$rowt['NAME']."' $selected style='background-color:#".$rowt['COLOR'].";'>".$rowt['NAME']."</option>";
                }
                $H .= "</select>";
            }
            elseif ( $ID == 34 ) {
                $H .= "<select id='f34' name='f$ID' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
                if ( $VALUE == '0' ) $selected="selected"; 
                else $selected="";
                for ( $k=1 ; $k<=100 ; $k++) {
                    if ( $VALUE == $k ) $selected="selected";
                    else $selected='';
                    if ( $k ==1 ) $jour='jour';
                    else $jour='jours';
                    $H .= "<option value='$k' $selected>$k $jour</option>";
                }
                $H .= "</select>";
            }
            elseif ( $ID == 36 ) {
                $H .= "<select id='f36' name='f$ID' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
                if ( $VALUE == '0' ) $selected="selected"; 
                else $selected="";
                for ( $k=0 ; $k <=1000 ; $k = $k + 10) {
                    if ( $VALUE == $k ) $selected="selected";
                    else $selected='';
                    if ( $k ==0 ) $jour='illimité';
                    else $jour=$k.' jours';
                    $H .= "<option value='$k' $selected>$jour</option>";
                }
                $H .= "</select>";
            }
            elseif ( $ID == 43 ) {
                $H .= "<select id='f43' name='f$ID' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
                if ( $VALUE == 'hierarchique' ) $selected="selected"; 
                else $selected="";
                $H .= "<option value='hierarchique' $selected>Ordre hiérarchique</option>";
                if ( $VALUE == 'alphabetique' ) $selected="selected"; 
                else $selected="";
                $H .= "<option value='alphabetique' $selected>Ordre alphabétique</option>";
                $H .= "</select>";
            }
            elseif ( $ID == 44 ) {
                $H .= "<select id='f44' name='f$ID' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
                if ( $VALUE == 'md5' ) $selected="selected"; 
                else $selected="";
                $H .= "<option value='md5' $selected>MD5 (défaut)</option>";
                if ( $VALUE == 'bcrypt' ) $selected="selected"; 
                else $selected="";
                $H .= "<option value='bcrypt' $selected>BCRYPT (recommandée)</option>";
                if ( $VALUE == 'pbkdf2' ) $selected="selected"; 
                else $selected="";
                $H .= "<option value='pbkdf2' $selected>PBKDF2 (obsolete)</option>";
                $H .= "</select>";
            }
            elseif ( $ID == 49 ) {
                $H .= "<select id='f49' name='f$ID' onchange='modify(config.f".$ID.",\"".$ID."\", this.value, \"".$VALUE."\")'>";
                if ( $VALUE == '0' ) $selected="selected";  else $selected="";
                $H .= "<option value='0' $selected>Pas d'expiration</option>";
                if ( $VALUE == '1' ) $selected="selected"; else $selected="";
                $H .= "<option value='1' $selected>1 minute d'inactivité</option>";
                if ( $VALUE == '5' ) $selected="selected"; else $selected="";
                $H .= "<option value='5' $selected>5 minutes d'inactivité</option>";
                if ( $VALUE == '10' ) $selected="selected"; else $selected="";
                $H .= "<option value='10' $selected>10 minutes d'inactivité</option>";
                if ( $VALUE == '15' ) $selected="selected"; else $selected="";
                $H .= "<option value='15' $selected>15 minutes d'inactivité</option>";
                if ( $VALUE == '30' ) $selected="selected"; else $selected="";
                $H .= "<option value='30' $selected>30 minutes d'inactivité</option>";
                if ( $VALUE == '60' ) $selected="selected"; else $selected="";
                $H .= "<option value='60' $selected>60 minutes d'inactivité</option>";
                $H .= "</select>";
            }
            elseif ( $ID == 60 ) {
                $H .= "<select id='f60' name='f$ID''>";
                if ( $VALUE == 'google' ) $selected="selected";  else $selected="";
                $H .= "<option value='google' $selected>Google API (service payant)</option>";
                if ( $VALUE == 'osm' ) $selected="selected";  else $selected="";
                $H .= "<option value='osm' $selected>OSM (data.gouv.fr gratuit)</option>";
                $H .= "</select>";
            }
            elseif ( $ID >= 6 ) {
                $H .= "<input id='f$ID' name='f$ID' type=text value=\"$VALUE\" size=30 onBlur=\"modify(config.f".$ID.",'".$ID."', this.value, '".addslashes($VALUE)."')\">"; 
            }
            $H .= "</td>
                <td class='small hide_mobile' >$DESCRIPTION</td>
                </tr>";
                $i++;
        }
    }
    $H .= "</table>";
    $H .= "<p><input type='button' class='btn btn-default' value='retour accueil' onclick='redirect();'> <input type='submit' class='btn btn-default' value='sauver'>";
    $H .= "</form>";
    
    if ( $group_id == 'conf2' ) {
        $H .= " <input type='button' class='btn btn-default' value='phpinfo' onclick='javascript:self.location.href=\"phpinfo.php\";'>";
        $H .= " <input type='button' class='btn btn-default' value='functions' title='Mettre à jour les fonctions SQL' onclick='javascript:self.location.href=\"buildsql.php\";'>";
        $H .= " <input type='button' class='btn btn-default' value='zipcodes' title='Mettre à jour les codes postaux' onclick='javascript:self.location.href=\"buildzipcode.php\";'>";
        $H .= " <input type='button' class='btn btn-default' value='organigramme' title='Regénérer organigramme' onclick='javascript:self.location.href=\"rebuild_section_flat.php\";'>";
        $H .= "<p>";
    }
    
    if ( $group_id == 'conf1' ) {
        $logo=get_logo();
        $banner=get_banner();
        $icon=get_favicon();
        $apple=get_iphone_logo();
        $splash=get_splash();
            
        $H .= "<div align=left>
        <ul>
        <li>Le logo peut être personnalisé: placer un fichier <b>logo.png</b> dans le répertoire images/user-specific. <img src=".$logo." class='img-max-22' title='logo actuel'>
        <li>De même la bannière de la page d'accueil peut être personnalisée. Placer un fichier <b>banniere.jpg</b> dans le répertoire images/user-specific. <img src=".$banner." class='img-max-22'  title='bannière actuelle'>
        <li>De même que l'icone de l'onglet web. Placer un fichier <b>favicon.png</b> dans le répertoire images/user-specific. <img src='".$icon."' class='img-max-22'  title='icone actuelle'>
        <li>L'icone pour écran d'accueil iOS. Placer un fichier <b>apple-touch-icon.png</b> dans le répertoire images/user-specific. <img src='".$apple."' class='img-max-22'  title='icone actuelle'>
        <li>Une grande image de fond peut être utilisée sur l'écran d'accueil. Placer un fichier  <b>splash.jpg</b> dans le répertoire images/user-specific. <img src='".$splash."' class='img-max-40' title='image de fond'>

        </font>
        </ul>
        </div>";
    }  
    
    $H .= "</div>";
    return $H;
    
}

$html .= display_configuration_group($tab);
$html .= "</div>";
print $html;
writefoot();

// consider configuration is done now
$query2="update configuration set VALUE=1 where ID=-1";
$result2=mysqli_query($dbc,$query2);

?>
