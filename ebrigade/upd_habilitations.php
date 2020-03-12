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
check_all(9);
writehead();

if ( isset($_GET["order"])) $order=$_GET["order"];
else $order='TF_ID';

if ( isset($_GET["from"])) $from=$_GET["from"];
else $from='default';

$GP_ID=intval($_GET["gpid"]);

// check input parameters
if ( $order <> secure_input($dbc,$order)){
    param_error_msg();
    exit;
}

?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/habilitations.js?version=<?php echo $version; ?>'></script>
<script type='text/javascript'>
$(document).ready(function(){
    $('[data-toggle="popover"]').popover();
});
</script>
</head>
<body>
<?php

//=====================================================================
// affiche la fiche groupe
//=====================================================================

$query="select GP_DESCRIPTION, TR_SUB_POSSIBLE, TR_ALL_POSSIBLE, GP_USAGE, GP_ASTREINTE, GP_ORDER, TR_CONFIG
         from groupe where GP_ID=".$GP_ID;
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);

if ( $TR_CONFIG == 1 ) $title="Droit d'accès";
else if ( $TR_CONFIG == 2 ) $title="Rôle de l'organigramme";
else $title="Permission de l'organigramme";

echo "<div align=center><font size=4><b>$title n° $GP_ID<br>$GP_DESCRIPTION</b></font>";

echo "<p><table cellspacing=0 border=0>";
echo "<form name='habilitations' action='save_habilitations.php'>";
echo "<input type='hidden' name='GP_ID' value='$GP_ID'>";
echo "<input type='hidden' name='GP_DESCRIPTION' value=\"$GP_DESCRIPTION\">";
echo "<input type='hidden' name='sub_possible' value='0'>";
echo "<input type='hidden' name='all_possible' value='0'>";
echo "<input type='hidden' name='gp_usage' value=\"$GP_USAGE\">";
echo "<input type='hidden' name='gp_astreinte' value='0'>";
echo "<input type='hidden' name='gp_order' value='50'>";
echo "<input type='hidden' name='category' value='$TR_CONFIG'>";

//=====================================================================
// ligne 1
//=====================================================================

echo "<tr class=TabHeader>
          <td colspan=2></td>
        <td colspan=2>Informations</td>
      </tr>";

//=====================================================================
// ligne description
//=====================================================================
$disabled="";
if ($GP_ID == 4) $disabled="disabled";
 
if ( $GP_ID < 100 )  $tt='groupe';
else $tt='rôle';

$help = write_help_habilitations();

echo "<tr bgcolor=$mylightcolor >
          <td width=320 colspan=2><b>Nom du ".$tt."</b></td>
            <td width=150 colspan=2 align=left>";
    echo"<input type='text' name='GP_DESCRIPTION' size='25' value=\"$GP_DESCRIPTION\" $disabled> ";
echo "</tr>";

if ( $GP_ID >= 100 ) {
    if ( $TR_SUB_POSSIBLE == 1 ) $checked="checked";
    else $checked="";
    echo "<tr bgcolor=$mylightcolor >
            <td colspan=2 ><b>Membre d'une sous-section possible</b></td>
            <td align=left colspan=2>
            <input type='checkbox' name='sub_possible'  value='1' $checked title=\"Si cette case est cochée, alors un membre d'une sous-section peut avoir le rôle\">
          </td>";
    echo "</tr>";
    if ( $TR_ALL_POSSIBLE == 1 ) $checked="checked";
    else $checked="";
    echo "<tr bgcolor=$mylightcolor >
            <td colspan=2 ><b>Membre de n'importe quelle section</b></td>
            <td align=left colspan=2>
            <input type='checkbox' name='all_possible'  value='1' $checked title=\"Si cette case est cochée, alors un membre de n'importe quelle section peut avoir le rôle\">
          </td>";
    echo "</tr>";
    if ( $cron_allowed == 1 ) {
        if ( $GP_ASTREINTE == 1 ) $checked="checked";
        else $checked="";
        echo "<tr>
            <td bgcolor=$mylightcolor colspan=2 ><b>Peut être attribué pour des astreintes</b></td>
            <td bgcolor=$mylightcolor align=left colspan=2>
            <input type='checkbox' name='gp_astreinte'  value='1' $checked 
            title=\"Si cette case est cochée, alors ce rôle peut être attribué \nde façon temporaire pour des astreintes.\nATTENTION: Si décoché, les astreintes correspondantes seront supprimées.\">
          </td>";
        echo "</tr>";
    }
    else {
         echo "<input type =hidden name='gp_astreinte'  value='".$GP_ASTREINTE."'>";
    }
    
    // type rôle ou permission
    echo "<tr bgcolor=$mylightcolor >
            <td colspan=2><b>Catégorie (rôle ou permission)</b></td>
            <td align=left colspan=2>
            <select name='category'>";
    if ( $TR_CONFIG == 2) $selected ='selected'; else $selected='';
    echo     "<option value='2' $selected>Rôle dans l'organigramme</option>";
    if ( $TR_CONFIG == 3) $selected ='selected'; else $selected='';
    echo    "<option value='3' $selected>Permission dans l'organigramme</option>";
    echo " </select> ".$help."
          </td>";
    echo "</tr>";
    
}
else {
    // attribuable à certaines catégories de personnel seulement
    echo "<tr bgcolor=$mylightcolor >
            <td colspan=2><b>Utilisable pour le personnel</b></td>
            <td align=left colspan=2>
            <select name='gp_usage'>";
    if ( $GP_USAGE == 'internes') $selected ='selected'; else $selected='';
    echo     "<option value='internes' style='background:white;' $selected>interne seulement</option>";
    if ( $GP_USAGE == 'externes') $selected ='selected'; else $selected='';
    echo    "<option value='externes' style='background:".$mygreencolor.";' $selected>externe seulement</option>";
    if ( $GP_USAGE == 'all') $selected ='selected'; else $selected='';
    echo    "<option value='all' style='background:yellow;' $selected>interne et externe</option>";
    echo "        </select>
          </td>";
    echo "</tr>";
}

echo "<tr bgcolor=$mylightcolor >
          <td colspan=2><b>Ordre d'affichage</b></td>
            <td align=left colspan=2>";
          
if ( $GP_ID >= 100 ) $tt="Si l'ordre choisi est 100, alors le rôle n'apparaît pas dans l'organigramme imprimable avec photos";
else $tt="Choisir l'ordre d'affichage dans le tableau";
echo "<select id='gp_order' name='gp_order' title=\"".$tt."\">";
for ( $i=1; $i <= 100; $i++ ) {
    if ( $i == $GP_ORDER ) $selected="selected";
    else $selected="";
    echo "<option value='".$i."' $selected>".$i."</option>\n";
}
echo "</select></td>";
echo "</tr>";

//=====================================================================
// nombre de membres
//=====================================================================

if ( $GP_ID >= 100 )
$query="select count(*) as NB
        from pompier p , section s, section_role sr
        where sr.S_ID= s.S_ID
        and sr.GP_ID=".$GP_ID."
        and sr.P_ID = p.P_ID
        and p.P_OLD_MEMBER=0";
else 
$query="select count(*) as NB from pompier where P_OLD_MEMBER=0 and (GP_ID=$GP_ID or GP_ID2=$GP_ID )";
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$NB=$row["NB"];
    
echo "<tr bgcolor=$mylightcolor >
            <td colspan=2><b>Nombre de membres</b></td>
            <td align=left colspan=2>";
print write_modal("membres.php?groupe=".$GP_ID, 'litse', "<span class='badge' style='background-color:purple;' title='cliquer pour voir la liste du personnel'>$NB</span>");      
echo "</tr>";
      
//=====================================================================
// ligne fonctionnalités
//=====================================================================
$query="select distinct f.F_ID , f.F_TYPE, f.F_LIBELLE, tf.TF_ID, tf.TF_DESCRIPTION, f.F_FLAG,f.F_DESCRIPTION
         from fonctionnalite f, type_fonctionnalite tf
         where f.TF_ID = tf.TF_ID
     order by ".$order.",F_ID";    
$result=mysqli_query($dbc,$query);

echo "<tr class=TabHeader>
          <td width=20 align=left><a href=upd_habilitations.php?gpid=".$GP_ID."&order=F_ID class=TabHeader>N°</a></td>
          <td width=250 align=left><a href=upd_habilitations.php?gpid=".$GP_ID."&order=F_LIBELLE class=TabHeader>Fonctionnalité</a></td>
          <td width=100 align=left><a href=upd_habilitations.php?gpid=".$GP_ID."&order=TF_ID class=TabHeader>Catégorie</a></td>
          <td width=150 align=left class=TabHeader>Permission</td>
      </tr>";

$i=0;$prevtype=0;
while (custom_fetch_array($result)) {

    if (( $gardes == 1 ) or ( $F_TYPE <> 1 )) {
            $query2="select count(1) as NB from habilitation where F_ID=$F_ID and GP_ID=$GP_ID";
            $result2=mysqli_query($dbc,$query2);
            custom_fetch_array($result2);
            if ( $NB > 0 ) $checked="checked";
            else $checked="";
    
            if (( $prevtype <> $TF_ID) and ( $TF_ID <> 0 ) and ( $order=='TF_ID')) {
                echo "<tr class=TabHeader ><td colspan=4></td></tr>";
              }
              $prevtype=$TF_ID;
      
              if (( $F_FLAG == 1 ) and ( $nbsections == 0 ))  $cmt=" $asterisk";
              else $cmt="";
              
            $disabled="";
            if ($GP_ID == 4){
                 if (($F_ID == 9) and ( $NB > 0)) $disabled="disabled";
            }
            if  (($F_ID == 0) and ( $NB > 0)) $disabled="disabled";
            if ($GP_ID == -1)  $disabled="disabled";
            
            $help_link=" <a href='#' data-toggle='popover' title=\"".$F_ID." - ".$F_LIBELLE."\" data-trigger='hover' data-content=\"".strip_tags($F_DESCRIPTION)."\">".$F_LIBELLE."</a>";
            echo "<tr bgcolor=$mylightcolor >
                <td width=20 align=right>$F_ID</td>
                <td width=250>- ".$help_link;
            echo "</td><td width=100><font size=1><i>$TF_DESCRIPTION</i></font></td>
                    <td width=150 align=left><input type='checkbox' name='$F_ID'  value='1' $checked $disabled>";
            echo "</tr>";
    }
}

//=====================================================================
// bas de tableau
//=====================================================================
echo "</table>";
if ( check_rights($_SESSION['id'], 9)) {
   echo "<p><input type='submit' class='btn btn-default' value='sauver' > ";
   echo " <input type='button' class='btn btn-default' value='dupliquer' onclick=\"duplicate_groupe('".$GP_ID."');\"> ";
}
echo "</form>";
if ( check_rights($_SESSION['id'], 9)) {
   // on ne peut pas supprimer les groupes admin, public et acces interdit
   if ( $GP_ID <> 4  and  $GP_ID > 0 ) 
      echo " <input type='button' class='btn btn-default' value='supprimer'
          onclick=\"suppr_groupe('".$GP_ID."');\">";
}

if ( $from='astreintes' ) 
echo " <input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"javascript:history.back();\">";

if ( $nbsections == 0 ) 
    echo "<p><small>$asterisk<i> ces fonctionnalités ne sont pas accessibles aux personnes habilitées seulement au niveau antenne</i></small>";
      
echo "</div>";
writefoot();
?>
