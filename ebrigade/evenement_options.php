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
check_all(41);
$id=$_SESSION['id'];
$mysection=$_SESSION['SES_SECTION'];

if (isset ($_POST["evenement"]))  $evenement=intval($_POST["evenement"]);
else $evenement=intval($_GET["evenement"]);
if (isset ($_POST["option"]))  $option=intval($_POST["option"]);
else if (isset ($_GET["option"])) $option=intval($_GET["option"]);
else $option=0;
if (isset ($_POST["groupe"]))  $groupe=intval($_POST["groupe"]);
else if (isset ($_GET["groupe"])) $groupe=intval($_GET["groupe"]);
else $groupe=0;
if (isset ($_POST["action"])) $action=secure_input($dbc,$_POST["action"]);
else if (isset ($_GET["action"])) $action=secure_input($dbc,$_GET["action"]);
else $action='display';
if (isset ($_POST["EO_TITLE"])) $EO_TITLE=secure_input($dbc,$_POST["EO_TITLE"]);
if (isset ($_POST["EOG_TITLE"])) $EOG_TITLE=secure_input($dbc,$_POST["EOG_TITLE"]);
if (isset ($_POST["EO_TYPE"])) $EO_TYPE=secure_input($dbc,$_POST["EO_TYPE"]);
if (isset ($_POST["EO_COMMENT"])) $EO_COMMENT=secure_input($dbc,$_POST["EO_COMMENT"]);
if (isset ($_POST["EO_ORDER"])) $EO_ORDER=intval($_POST["EO_ORDER"]);
else $EO_ORDER=1;
if (isset ($_POST["EOG_ORDER"])) $EOG_ORDER=intval($_POST["EOG_ORDER"]);
else $EOG_ORDER=1;
if (isset ($_GET["renfort"]))  $renfort=intval($_GET["renfort"]);
else $renfort=$evenement;
if (isset ($_GET["what"])) $what=$_GET["what"];
else $what='option';

writehead();

?>
<script type='text/javascript'>
$(document).ready(function(){
    $('[data-toggle="popover"]').popover();
});
function redirect_evenement(evenement){
    url="evenement_display.php?tab=1&evenement="+evenement;
    self.location.href=url;
}
function redirect_options(evenement){
    url="evenement_options.php?evenement="+evenement;
    self.location.href=url;
}
function add_choice(evenement,option){
    txt = document.getElementById('newtexte').value;
    val = document.getElementById('newvalue').value;
    url="evenement_options.php?evenement="+evenement+"&option="+option+"&action=add_choice&val="+val+"&txt="+txt;
    self.location.href=url;
}
function del_choice(evenement,option,choix){
    if (confirm ("Voulez vous vraiment supprimer cette option? les choix saisis seront perdus")) {
        url="evenement_options.php?evenement="+evenement+"&option="+option+"&choix="+choix+"&action=del_choice";
        self.location.href=url;
    }
}

function del_group(evenement,group){
    if (confirm ("Voulez vous vraiment supprimer ce groupe d'options")) {
        url="evenement_options.php?evenement="+evenement+"&groupe="+group+"&action=delete&what=groupe";
        self.location.href=url;
    }
}
function del_option(evenement,option){
    if (confirm ("Voulez vous vraiment supprimer cette option, les choix saisis pour cette option seront perdus")) {
        url="evenement_options.php?evenement="+evenement+"&option="+option+"&action=delete&what=option";
        self.location.href=url;
    }
}

</script>
</head>
<body>
<?php


//=====================================================================
// recup�rer infos evenement
//=====================================================================
$query="select e.TE_CODE, e.E_LIBELLE, e.E_CLOSED, e.E_CANCELED, e.E_OPEN_TO_EXT, e.S_ID, te.TE_ICON
        from evenement e, type_evenement te
        where te.TE_CODE = e.TE_CODE
        and e.E_CODE=".$renfort;
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);

if ( $evenement == $renfort ) $evts=get_event_and_renforts($evenement);
else $evts=$renfort;

$query1="select count(1) as NB from evenement_participation
       where EP_ABSENT = 0 
       and EH_ID = 1
       and E_CODE in (".$evts.")";
$result1=mysqli_query($dbc,$query1);
$row1=@mysqli_fetch_array($result1);
$n1=$row1["NB"];

echo "<div align=center><table class='noBorder'>
      <tr><td>
      <font size=4><b><img src=images/evenements/".$TE_ICON." height=30> ".$E_LIBELLE."</b></font> <span class='badge'>".$n1." pr�sents</span></td></tr>
      </table><p>";
      
//=====================================================================
// contr�le des permissions
//=====================================================================
// bloquer les changements dans le pass�
$ended=get_number_days_after_block($evenement);
$changeallowed=true;
if ( $ended > 0 ) {
    if ( ! check_rights($id, 19, "$S_ID") ) $changeallowed=false;
}

if ( ( is_chef_evenement($id, $evenement) or check_rights($id, 15, "$S_ID")) and $changeallowed ) $update_allowed=true;
else $update_allowed=false;

if ( $renfort <> $evenement ) $update_allowed=false;

if ( $update_allowed ) $disabled="";
else $disabled="disabled";

$action_requiring_permissions = array('save_update', 'save_insert','delete','add_choice','del_choice','save_groupe_insert');
if ( in_array($action, $action_requiring_permissions ) and ! $update_allowed ) {
    check_all(15);
    if ( ! check_rights($id, 15, "$S_ID")) check_all(24);
    if ( ! $changeallowed ) 
        write_msgbox("WARNING", $warning_pic, "Attention Vous ne pouvez plus modifier les options de cet �v�nement.<p><a href=evenement_options.php?evenement=".$evenement." ><input type='submit' class='btn btn-default' value='Retour'></a></p>",30,0);
}

write_debugbox("<pre>".print_r($_POST,true)."</pre>");

//=====================================================================
// sauver informations
//=====================================================================
if ( $update_allowed ) {
    if ( $action == 'save_update' and $EO_TITLE <> '' ) {
        $query="update evenement_option
            set EO_TITLE=\"".$EO_TITLE."\",
            EO_COMMENT=\"".$EO_COMMENT."\",
            EO_TYPE=\"".$EO_TYPE."\",
            EO_ORDER=".$EO_ORDER.",
            EOG_ID=".$groupe."
            where EO_ID=".$option;
        $result=mysqli_query($dbc,$query);
        
        if ( $EO_TYPE == 'dropdown' ) {
            $query="select EOD_ID, EO_ID, EOD_ORDER, EOD_TEXTE from evenement_option_dropdown where EO_ID=".$option;
            $result=mysqli_query($dbc,$query);
            while ( custom_fetch_array($result)) {
                if ( isset($_POST["choix_value_".$EOD_ID])) {
                    $new_value=intval($_POST["choix_value_".$EOD_ID]);
                    $new_texte=secure_input($dbc,$_POST["choix_texte_".$EOD_ID]);
                    $new_texte=STR_replace("\"","",$new_texte);
                    $query2="update evenement_option_dropdown set EOD_ORDER=".$new_value.", EOD_TEXTE=\"".$new_texte."\"
                            where EOD_ID=".$EOD_ID;
                    mysqli_query($dbc,$query2);
                }
            }
        }
        $action='display';
    }
    else if ( $action == 'save_groupe_update' and $EOG_TITLE <> '' ) {
         $query="update evenement_option_group
            set EOG_TITLE=\"".$EOG_TITLE."\",
            EOG_ORDER=".$EOG_ORDER."
            where EOG_ID=".$groupe;
        $result=mysqli_query($dbc,$query);
        $action='display';
    }
    else if ($action == 'save_groupe_insert' and $EOG_TITLE <> '' ) {
        $query="insert into evenement_option_group (E_CODE, EOG_TITLE, EOG_ORDER)
            values (".$evenement.",\"".$EOG_TITLE."\",".$EOG_ORDER.")";
        $result=mysqli_query($dbc,$query);
        $action='display';
    }
    else if ($action == 'save_insert' and $EO_TITLE <> '' ) {
        $query="insert into evenement_option(E_CODE, EO_TITLE, EO_COMMENT, EO_TYPE, EOG_ID, EO_ORDER)
            values (".$evenement.",\"".$EO_TITLE."\",\"".$EO_COMMENT."\",\"".$EO_TYPE."\",".$groupe.",".$EO_ORDER.")";
        $result=mysqli_query($dbc,$query);
        $query="select max(EO_ID) MAXOPTION from evenement_option where E_CODE=".$evenement;
        $result=mysqli_query($dbc,$query);
        custom_fetch_array($result);
        $query="insert into evenement_option_dropdown(EO_ID, EOD_ORDER, EOD_TEXTE)
                values (".intval($MAXOPTION).",0,\"Choisir une option\")";
        $result=mysqli_query($dbc,$query);
        $action='display';
    }
    else if ( $action == 'delete' ) {
        if ( $option > 0 and $what == 'option') {
            $query="delete from evenement_option where E_CODE=".$evenement." and EO_ID=".$option;
            $result=mysqli_query($dbc,$query);
            $query="delete from evenement_option_choix where E_CODE=".$evenement." and EO_ID=".$option;
            $result=mysqli_query($dbc,$query);
        }
        if ( $groupe > 0 and $what == 'groupe' ) {
            $query="delete from evenement_option_group where E_CODE=".$evenement." and EOG_ID=".$groupe;
            $result=mysqli_query($dbc,$query);
            $query="update evenement_option set EOG_ID=null where E_CODE=".$evenement." and EOG_ID=".$groupe;
            $result=mysqli_query($dbc,$query);
        }
        $action='display';
    }
    else if ( $action == 'add_choice' and $option > 0 ) {
        if (isset ($_GET["val"])) {
            $val=intval($_GET["val"]);
            $txt=secure_input($dbc,$_GET["txt"]);
            $txt=STR_replace("\"","",$txt);
            $query="insert into evenement_option_dropdown(EO_ID, EOD_ORDER, EOD_TEXTE)
                values (".$option.",".$val.",\"".$txt."\")";
            $result=mysqli_query($dbc,$query);
            $action='update';
        }
    }
    else if ( $action == 'del_choice' and $option > 0 ) {
        if (isset ($_GET["choix"])) {
            $choix=intval($_GET["choix"]);
            $query="delete from evenement_option_dropdown where EO_ID=".$option." and EOD_ID=".$choix;
            $result=mysqli_query($dbc,$query);
            $query="delete from evenement_option_choix where EO_ID=".$option." and EOC_VALUE=".$choix;
            $result=mysqli_query($dbc,$query);
            $action='update';
        }
    }
}
//=====================================================================
// afficher une ou toutes les options
//=====================================================================

if ( $update_allowed ) echo "<form action='evenement_options.php' method='POST'>";
$num=0;

//modifier
if ( $action == 'update') {
    if ( $what =='groupe') { // groupe d'options
        echo "<table cellspacing=0 border=0>";
        $querym="select EOG_TITLE, EOG_ORDER from evenement_option_group
            where E_CODE=".$evenement." and EOG_ID=".$groupe;
        $resultm=mysqli_query($dbc,$querym);
        custom_fetch_array($resultm);
        echo "<input type=hidden name='evenement' value='".$evenement."'>
               <input type=hidden name='groupe' value='".$groupe."'>
               <input type=hidden name='action' value='save_groupe_update'>
              <tr class=TabHeader><td colspan=2>Modification</td></tr>
               <tr bgcolor=$mylightcolor><td width=120>Nom groupe</td>
               <td ><input name=EOG_TITLE type=text size=20 value=\"".$EOG_TITLE."\" $disabled width=250></td>
              <tr bgcolor=$mylightcolor><td >Ordre affichage</td>
                <td>
              <select name=EOG_ORDER $disabled>";
        for ( $i=1; $i <= 25; $i++ ) {
            if ( $i == $EOG_ORDER ) $selected="selected";
            else $selected="";
            echo "<option value='".$i."' $selected>$i</option>";
        }
        echo "</select></td></tr>";
        echo "</table><p>";
    }
    else  { // option
        echo "<table cellspacing=0 border=0>";
        $querym="select EO_TITLE, EO_COMMENT, EO_TYPE, EO_ORDER, EOG_ID groupe from evenement_option
            where E_CODE=".$evenement."
            and EO_ID=".$option;
        $resultm=mysqli_query($dbc,$querym);
        custom_fetch_array($resultm);
        echo "<input type=hidden name='evenement' value='".$evenement."'>
               <input type=hidden name='option' value='".$option."'>
               <input type=hidden name='action' value='save_update'>
              <tr class=TabHeader><td colspan=2>Modification</td></tr>
               <tr bgcolor=$mylightcolor><td width=100>Nom option</td>
                <td ><input name=EO_TITLE type=text size=20 value=\"".$EO_TITLE."\" $disabled></td>";
                
        // groupe d'option
        $queryg="select EOG_ID, EOG_TITLE from evenement_option_group where E_CODE=".$evenement." order by EOG_ORDER";
        $resultg=mysqli_query($dbc,$queryg);
        $nb=mysqli_num_rows($resultg);
        if ( $nb > 0 ) {
            echo "<tr bgcolor=$mylightcolor><td >Groupe</td>
                <td>
              <select name=groupe $disabled>";
            if ( intval($groupe) == 0  ) $selected="selected";
            else $selected="";
            echo "<option value='0' $selected>Ne fait pas partie d'un groupe d'options</option>";
            while ( custom_fetch_array($resultg)) {
                if ( $EOG_ID == $groupe ) $selected="selected";
                else $selected="";
                echo "<option value='".$EOG_ID."' $selected>".$EOG_TITLE."</option>";
            }
            echo "</select></td></tr>";
        }
        // order
        echo "<tr bgcolor=$mylightcolor><td >Ordre affichage</td>
                <td>
              <select name=EO_ORDER $disabled>";
        for ( $i=1; $i <= 25; $i++ ) {
            if ( $i == $EO_ORDER ) $selected="selected";
            else $selected="";
            echo "<option value='".$i."' $selected>$i</option>";
        }
        echo "</select><small> dans le groupe</small></td></tr>";
        
        echo " <tr bgcolor=$mylightcolor><td>Type d'option</td>
                    <td><select name=EO_TYPE $disabled title=\"choisir le type d'option\">";
        if ( $EO_TYPE == 'checkbox' ) $selected='selected'; else $selected='';
        echo " <option value='checkbox' $selected>Case � cocher</option>";
        if ( $EO_TYPE == 'text' ) $selected='selected'; else $selected='';
        echo " <option value='text' $selected>Texte libre</option>";
        if ( $EO_TYPE == 'textnum' ) $selected='selected'; else $selected='';
        echo " <option value='textnum' $selected>Valeur Num�rique</option>";
        if ( $EO_TYPE == 'dropdown' ) $selected='selected'; else $selected='';
        echo " <option value='dropdown' $selected>Liste d�roulante</option>";
        if ( $EO_TYPE == 'date' ) $selected='selected'; else $selected='';
        echo " <option value='date' $selected>Date JJ-MM-AAAA</option>";
        if ( $EO_TYPE == 'hour' ) $selected='selected'; else $selected='';
        echo " <option value='hour' $selected>Heure HH:mm</option>";
        echo " </select></td></tr>";
        
        // choix de la liste d�roulante
        $nbmaxchoix=10;
        if ( $EO_TYPE == 'dropdown' ) $style='';
        else $style='display:none;';
        echo " <tr bgcolor=$mylightcolor style='$style'><td align=center><i>Choix</i></td>";
        echo "<td><table class='noBorder'>";
        // liste des choix existants
        $query="select EOD_ID, EOD_ORDER, EOD_TEXTE from evenement_option_dropdown 
            where EO_ID=".$option." order by EOD_ORDER";
        $result=mysqli_query($dbc,$query);
        
        while ( custom_fetch_array($result)) {
            $query2="select count(1) as nb from evenement_option_choix where EO_ID=".$option." and EOC_VALUE=".$EOD_ID;
            $result2=mysqli_query($dbc,$query2);
            custom_fetch_array($result2);
            echo " <tr bgcolor=$mylightcolor>";
            echo "<td><input type='texte' name='choix_texte_".$EOD_ID."' id='choix_texte_".$EOD_ID."' value=\"".$EOD_TEXTE."\"></td>";
            echo "<td><select id='choix_value_".$EOD_ID."' name='choix_value_".$EOD_ID."' title=\"Ordre d'affichage de l'option dans la liste\">";
            for ( $i=0; $i <= $nbmaxchoix; $i++ ) {
                if ( $EOD_ORDER == $i ) $selected = 'selected';
                else $selected ='';
                echo "<option value='".$i."' $selected>$i</option>";
            } 
            echo "</select></td>";
            echo  "<td><a href='#' onclick=\"javascript:del_choice('".$evenement."','".$option."','".$EOD_ID."');\"><i class='fa fa-trash fa-lg' title='supprimer ce choix de la liste'></i></a>
                    <span class='badge' style='background-color:grey' title='$nb inscrits ont s�lectionn� cette option'>".$nb."</span></td>";
            echo "</tr>";
        }
        // nouveau choix
        echo "<tr bgcolor=$mylightcolor>";
        echo "<td><input type='texte' value='' id='newtexte' name='newtexte' title='saisir le texte de ce choix dans la liste d�roulante'></td>";
        echo "<td><select id='newvalue' name='newvalue' title=\"Ordre d'affichage de l'option dans la liste\">";
        for ( $i=1; $i <= $nbmaxchoix; $i++ ) {
            echo "<option value='".$i."' >$i</option>";
        } 
        echo "</select></td>";
        echo "<td><a href='#' onclick=\"javascript:add_choice('".$evenement."','".$option."');\" title='Ajouter ce nouveau choix dans la liste d�roulante'><i class='fa fa-plus-circle fa-lg' style='color:green'></i></a></td>";
        echo "</tr></table>";
        
        
        echo " <tr bgcolor=$mylightcolor><td>Description</td><td>
                 <textarea cols=35 rows=3 name=EO_COMMENT style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;' $disabled>".$EO_COMMENT."</textarea></td></tr>";
        
        echo "</table><p>";
    }
    // boutons
    if ( $update_allowed ) echo " <input type=submit class='btn btn-default' name='OK' value='sauver' $disabled>";
    echo " <input type=button class='btn btn-default' value='annuler' onclick=\"redirect_options('".$evenement."');\">
            <input type=button class='btn btn-default' value='retour �v�nement' onclick=\"redirect_evenement('".$evenement."');\">";
    if ( $update_allowed ) echo " </form>";
}
// ajouter
else if ( $action == 'insert') {
    if ( $what =='groupe' ) {
         echo "<input type=hidden name='evenement' value='".$evenement."'>
              <input type=hidden name='action' value='save_groupe_insert'>
              <table cellspacing=0 border=0>
              <tr class=TabHeader><td colspan=2>Ajouter</td></tr>
              <tr bgcolor=$mylightcolor><td width=100>Nom groupe</td><td width=200><input name=EOG_TITLE type=text size=20 value='' $disabled></td>
              <tr bgcolor=$mylightcolor><td >Ordre affichage</td><td>
              <select name=EOG_ORDER>";
        for ( $i=1; $i <= 25; $i++ ) {
            echo "<option value='".$i."'>$i</option>";
        } 
        echo "</select></td>";
        echo "</table><p>";
    }
    else {
        echo "<input type=hidden name='evenement' value='".$evenement."'>
              <input type=hidden name='action' value='save_insert'>
              <table cellspacing=0 border=0>
              <tr class=TabHeader><td colspan=2>Ajouter</td></tr>
              <tr bgcolor=$mylightcolor><td width=100>Nom option</td><td width=200><input name=EO_TITLE type=text size=20 value='' $disabled></td>";
        
        // groupe
        $queryg="select EOG_ID, EOG_TITLE from evenement_option_group where E_CODE=".$evenement." order by EOG_ORDER";
        $resultg=mysqli_query($dbc,$queryg);
        $nb=mysqli_num_rows($resultg);
        if ( $nb > 0 ) {
            echo "<tr bgcolor=$mylightcolor><td >Groupe</td>
                <td>
              <select name=groupe $disabled>";
            echo "<option value='0' >Ne fait pas partie d'un groupe d'options</option>";
            while ( custom_fetch_array($resultg)) {
                echo "<option value='".$EOG_ID."' >".$EOG_TITLE."</option>";
            }
            echo "</select></td></tr>";
        }
        // ordre
        echo "<tr bgcolor=$mylightcolor><td >Ordre affichage</td><td>
              <select name=EO_ORDER>";
        for ( $i=1; $i <= 25; $i++ ) {
            echo "<option value='".$i."'>$i</option>";
        } 
        echo "</select></td>";
        echo " <tr bgcolor=$mylightcolor><td>Type d'option</td>
                    <td><select name=EO_TYPE $disabled title=\"choisir le type d'option\">";
        echo " <option value='checkbox'>Case � cocher</option>";
        echo " <option value='text'>Texte libre</option>";
        echo " <option value='textnum'>Valeur Num�rique</option>";
        echo " <option value='dropdown'>Liste d�roulante</option>";
        echo " <option value='date'>Date JJ-MM-AAAA</option>";
        echo " <option value='hour'>Heure HH:mm</option>";
        echo " </select></td></tr>";
        echo " <tr bgcolor=$mylightcolor><td>Description</td><td>
                <textarea cols=35 rows=3 name=EO_COMMENT style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;' ></textarea></td>
              </tr>";
        echo "</table><p>";
    }
    
    if ( $update_allowed ) echo " <input type=submit class='btn btn-default' name='OK' value='sauver'>";
    echo " <input type=button class='btn btn-default' value='annuler' onclick=\"redirect_options('".$evenement."');\">
            <input type=button class='btn btn-default' value='retour �v�nement' onclick=\"redirect_evenement('".$evenement."');\">";
    if ( $update_allowed ) echo " </form>";
}

//lister
else if ( $action == 'display') {
    $querym="select EOG_ID, 0 EO_ID, EOG_TITLE EO_TITLE, '' EO_COMMENT, 'group' EO_TYPE, 0 EO_ORDER, EOG_ORDER 
        from evenement_option_group
        where E_CODE=".$evenement."
        union select eo.EOG_ID, eo.EO_ID, eo.EO_TITLE, eo.EO_COMMENT, eo.EO_TYPE, eo.EO_ORDER, eog.EOG_ORDER
        from evenement_option eo left join evenement_option_group eog on eo.EOG_ID = eog.EOG_ID
        where eo.E_CODE=".$evenement."
        order by EOG_ORDER asc, EO_ORDER, EO_TITLE";
    $resultm=mysqli_query($dbc,$querym);
    $num=mysqli_num_rows($resultm);
    
    if ( $num == 0 ) echo "<span class=small>Aucune option n'a �t� cr��e</span><p>";
    else {
        echo  "<table cellspacing=0 border=0>
            <tr class=TabHeader>
            <td width=40></td>
            <td width=150>Nom option</td>
            <td width=100> Choix</td>
            <td width=250 >Description</td>
            <td width=130> Type</td>
            <td width=30 align=center title=\"Ordre d'affichage du groupe - de l'option dans son groupe\"> Ordre </td>";
        if (  $update_allowed ) 
            echo "<td colspan=2 ></td>";
        echo "</tr>";

        while (custom_fetch_array($resultm) ) {
            if ( $evenement == $renfort ) $complement="";
            else $complement= " and P_ID in (select P_ID from evenement_participation where E_CODE=".$renfort.")";

            if ( $EO_TYPE == 'checkbox' ) {
                $type='Case � cocher';
                $nb=count_entities("evenement_option_choix", "EO_ID=".$EO_ID." and EOC_VALUE=1 ".$complement);
                $title= $nb." inscrits ont coch� cette option";
            }
            else if  ( $EO_TYPE == 'text' or $EO_TYPE == 'date' or $EO_TYPE == 'hour' ) {
                if (  $EO_TYPE == 'text' ) $type='Texte libre';
                else if (  $EO_TYPE == 'date' ) $type='Date';
                else if (  $EO_TYPE == 'hour' ) $type='Heure';
                $query2="select count(1) as nb from evenement_option_choix where EO_ID=".$EO_ID." and EOC_VALUE <> ''";
                $result2=mysqli_query($dbc,$query2);
                custom_fetch_array($result2);
                $title= $nb." inscrits ont renseign� cette option";
            }
            else if  ( $EO_TYPE == 'textnum' ) {
                $type='Valeur Num�rique';
                $query2="select sum(EOC_VALUE) as nb from evenement_option_choix where EO_ID=".$EO_ID.$complement;
                $result2=mysqli_query($dbc,$query2);
                custom_fetch_array($result2);
                $title="Total des nombres saisis pour cette option = ".intval($nb);
                $nb='total '.intval($nb);
            }
            else if  ( $EO_TYPE == 'dropdown' ) {
                $type='Liste d�roulante';
                $query2="select count(1) as nb from evenement_option_choix where EO_ID=".$EO_ID." and EOC_VALUE > 0 ";
                $result2=mysqli_query($dbc,$query2);
                custom_fetch_array($result2);
                $title= $nb." inscrits ont renseign� cette option. Les choix possibles sont les suivants:";
                
                $query2="select EOD_TEXTE from evenement_option_dropdown where EO_ID=".$EO_ID." order by EOD_ORDER";
                $result2=mysqli_query($dbc,$query2);
                while ( custom_fetch_array($result2) ) {
                    $title .=" ".$EOD_TEXTE.",";
                }
                $title =rtrim($title,',');
            }
            else  if  ( $EO_TYPE == 'group' ) {
                $type="groupe d'options";
                $nb=0;
            }
            else {
                $type='inconnu';
                $nb=0;
            }
            
            $nb="<a href='#' data-toggle='popover' title=\"Option ".$EO_TITLE."\" data-trigger='hover' data-content=\"".$title."\"><span class='badge' >".$nb."</span>";
           
            echo "<tr bgcolor=$mylightcolor>";
            if ( $EO_TYPE == 'group' )
                echo "<td colspan=3 align=left style='font-size:15px;padding-left:5px;'><u><b>".$EO_TITLE."</b></u></td>";
            else
                echo "<td></td><td><b>".$EO_TITLE."</b></td><td>".$nb."</td>";
            echo "<td class=small>".$EO_COMMENT."</td>
                <td>".$type."</td>
                <td align=center title=\"Ordre d'affichage du groupe - de l'option dans son groupe\">".intval($EOG_ORDER)." - ".$EO_ORDER."</td>";
            
            if (  $update_allowed ) {
                if ( $EO_TYPE =='group' ) 
                    echo "<td width=20><a href=evenement_options.php?evenement=".$evenement."&groupe=".$EOG_ID."&action=update&what=groupe>
                        <i class='fa fa-edit fa-lg' title=\"modifier ce groupe d'options\"></i></a></td>
                        <td width=20>
                        <a href='#' onclick=\"javascript:del_group('".$evenement."','".$EOG_ID."');\"><i class='fa fa-trash fa-lg' title=\"supprimer ce groupe d'options\"></i></a>
                        </td>";
                else
                    echo "<td width=20><a href=evenement_options.php?evenement=".$evenement."&option=".$EO_ID."&action=update>
                        <i class='fa fa-edit fa-lg' title=\"modifier cette option\"></i></a></td>
                        <td width=20>
                        <a href='#' onclick=\"javascript:del_option('".$evenement."','".$EO_ID."');\"><i class='fa fa-trash fa-lg' title=\"supprimer cette option\"></i></a>
                        </td>";
            }
            echo "</tr>";
        }
        echo "</table><p>";
    }
    if ( $update_allowed ) {
       
        echo "<div class='dropdown show' style='display: inline-block; white-space: nowrap; align:top'  >
                  <a class='btn btn-default dropdown-toggle' href='#' role='button' id='dropdownMenuLink' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' 
                      title='Ajouter'>
                      <i class='fa fa-plus'></i> Ajouter
                  </a>
                  <div class='dropdown-menu' aria-labelledby='dropdownMenuLink'>
                    <a class='dropdown-item' href=evenement_options.php?evenement=".$evenement."&option=0&action=insert&what=option>Une option</a>
                    <a class='dropdown-item' href=evenement_options.php?evenement=".$evenement."&option=0&action=insert&what=groupe>Un groupe d'option</a>
                  </div>
                </div>";
    }
    else if ( $renfort <> $evenement ) 
        echo " <p style='color:orange;'><i class='fa fa-exclamation-triangle fa-lg' ></i> <b>Modifications possibles seulement sur l'�v�nement principal</b><p>";
}

$_SESSION['from']='infos';
if ( $action == 'display') {
    if ($option > 0 or isset($_POST["EO_TITLE"]))
       echo " <input type=button class='btn btn-default' value='termin�' onclick=\"redirect_evenement('".$renfort."');\"> ";
    else
       echo " <input type=button class='btn btn-default' value='annuler' onclick=\"redirect_evenement('".$renfort."');\"> ";
   
    if ( $num > 0 ) {
        $url="evenement_option_choix.php?evenement=".$evenement."&inscription=1&apercu=1";
        print write_modal( $url, "formulaire_".$evenement, "<input type='submit' class='btn btn-default' id='apercu' value='aper�u' title=\"voir un aper�u du formulaire propos� � celui qui s'inscrit\">");
    }
    echo "</div>";
}
writefoot();
?>
