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
$id=$_SESSION['id'];
$mysection=$_SESSION['SES_SECTION'];
get_session_parameters();
writehead();

if (isset($_GET["KID"])) $KID=intval($_GET["KID"]);
else $KID=0;
if (isset($_GET["S_ID"])) $S_ID=intval($_GET["S_ID"]);
else $S_ID=0;
if (isset($_GET["what"])) $what=$_GET["what"];
else $what='vehicule';

check_all(17);
if (! check_rights($id, 17,"$S_ID")) {
    check_all(24);
}

?>
<STYLE type="text/css">
.categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.materiel{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
</STYLE>

<?php
echo "<script type='text/javascript' src='js/upd_materiel.js?version=".$version."'></script>";
echo "</head>";
echo "<body>";

echo "<p><div align=center><table class='noBorder'>
  <tr><td>
  <font size=4><b> Ajout matériel</b></font></td></tr>
  </table>";

echo "<div align=center>";
echo "<table cellspacing=0 border=0>";
echo "<tr class=TabHeader>
        <td width=250 class=TabHeader colspan=2>Matériel à embarquer</td>
  </tr>";

//filtre type de materiel
echo "<tr bgcolor=$mylightcolor ><td align=right><b> Type</b></td>
<td><select id='type' name='type' 
onchange=\"filtermateriel(this.value,'".$KID."');\">";
if ( $type == 'ALL' ) $selected='selected';
else $selected='';
echo "<option value='ALL' $selected>tous types de matériel</option>";
$query2="select TM_ID, TM_CODE,TM_USAGE,TM_DESCRIPTION 
        from type_materiel 
        where TM_USAGE <> 'Habillement'
        order by TM_USAGE, TM_CODE";
$result2=mysqli_query($dbc,$query2);
$prevUsage='';
while (custom_fetch_array($result2)) {
    if ( $prevUsage <> $TM_USAGE ){
        echo "<option class='categorie' value='".$TM_USAGE."'";
        if ($TM_USAGE == $type ) echo " selected ";
        echo ">".$TM_USAGE."</option>\n";
    }
    $prevUsage=$TM_USAGE;
    echo "<option class='materiel' value='".$TM_ID."' title=\"".$TM_DESCRIPTION."\"";
    if ($TM_ID == $type ) echo " selected ";
    echo ">".$TM_CODE."</option>\n";
}
echo "</select></td></tr>";

$query="select m.MA_ID, m.MA_MODELE, tm.TM_CODE, m.MA_NUMERO_SERIE, s.S_CODE, s.S_DESCRIPTION, tm.TM_USAGE, tm.TM_LOT,
            m.MA_LIEU_STOCKAGE, m.MA_NB
            from materiel m, type_materiel tm, section s
            where s.S_ID= m.S_ID
            and m.TM_ID=tm.TM_ID";
if ( $what =='vehicule' )
    $query .= " and ( m.V_ID <> $KID or m.V_ID is null )";
else 
    $query .= " and ( m.MA_PARENT <> $KID or m.MA_PARENT is null )";
$query .= " and s.S_ID = ".$S_ID."
            and tm.TM_USAGE <> 'Habillement'
            and m.VP_ID in (select VP_ID from vehicule_position where VP_OPERATIONNEL>= 0 )";
if ( $type <> 'ALL' ) $query .= " and (tm.TM_ID='".$type."' or tm.TM_USAGE='".$type."')";
if ( $nbsections == 0 ) $query .= " order by s.S_CODE, tm.TM_USAGE, tm.TM_CODE, m.MA_MODELE";
else $query .= " order by tm.TM_USAGE, tm.TM_CODE, m.MA_MODELE";
$result=mysqli_query($dbc,$query);

echo "<tr bgcolor=$mylightcolor><td><b>ajouter </b></td>";
echo "<td><select id='addmateriel' name='addmateriel' style='width: 480px'
        onchange=\"javascript:addmateriel('".$what."','".$KID."',this.value);\" >
    <option value='0' selected>choix du matériel</option>\n";

$prevTM_USAGE="";
while (custom_fetch_array($result)) {
    if ( $TM_LOT == 1 ) {
          $query2="select count(1) from materiel where MA_PARENT=".$MA_ID;
          $result2=mysqli_query($dbc,$query2);
          $row2=@mysqli_fetch_array($result2);
          $elements=$row2[0];
    }
    else $elements=-1;
    if ( $prevTM_USAGE <> $TM_USAGE ) echo "<OPTGROUP LABEL='".$TM_USAGE."' class='categorie'>";
    $prevTM_USAGE=$TM_USAGE;
    if ( $MA_NB > 1 ) $add=" (".$MA_NB.")";
    else $add="";
    if ( $elements >= 0 ) $add2=" (".$elements." éléments dans ce lot)";
    else $add2="";
    if ( $MA_NUMERO_SERIE <> "" ) $add.=" ".$MA_NUMERO_SERIE;
    echo "<option value='".$MA_ID."' class='materiel'>".$TM_CODE." - ".$MA_MODELE.$add.$add2.". ".$MA_LIEU_STOCKAGE."</option>\n";
  
}
echo "</select>
    </td></tr></table>";
echo "</div>";

if ( $what == 'vehicule' ) $url = "upd_vehicule.php?vid=".$KID."&tab=3";
else $url = "upd_materiel.php?mid=".$KID."&tab=3";

echo "<div align=center><p><input type=button class='btn btn-default' value='retour' onclick=\"self.location.href='".$url."'\"></div>";
writefoot();
?>
