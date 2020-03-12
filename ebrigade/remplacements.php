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

if ( $assoc or $army ) $perm=41;
else $perm=61;
check_all($perm);
$id=$_SESSION['id'];
get_session_parameters();
test_permission_level($perm);

if (isset($_GET["replaced"])) $replaced = intval($_GET["replaced"]);
else $replaced = 0;

if (isset($_GET["substitute"])) $substitute = intval($_GET["substitute"]);
else $substitute = 0;

$html = writehead();
?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/remplacements.js'></script>
<?php

echo  "</head><body><div align=center><table class='noBorder'>
      <tr><td><i class='fa fa-user-times fa-3x' ></i></td>
      <td><font size=4><b> Remplacements</b></font></td></tr>
      </table><p>";

//=====================================================================
// formulaire filtre
//=====================================================================      
echo "<form><table class='noBorder' >";
if ( $nbsections == 0 ) {
    //filtre section
    echo "<tr><td align=right>Section</td><td>";
    echo " <select id='filter' name='filter' onchange=\"changeParam(document.getElementById('filter').value);\">";
    echo display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
    echo "</select></td></tr>";
}

// choix statut 
$html .= "<tr><td align=right> Statut </td>";
$html .= "<td align=left>
<select id='status' name='status' onchange=\"changeParam('".$filter."');\">";
if ( $status == 'ALL' ) $selected='selected'; else $selected='';
$html .= "<option value='ALL' $selected>Tous </option>\n";
if ( $status == 'DEM' ) $selected='selected'; else $selected='';
$html .=  "<option value='DEM' $selected>Demandé</option>\n";
if ( $status == 'ACC' ) $selected='selected'; else $selected='';
$html .=  "<option value='ACC' $selected>Accepté par le remplaçant</option>\n";
if ( $status == 'VAL' ) $selected='selected'; else $selected='';
$html .=  "<option value='VAL' $selected>Approuvé</option>\n";
if ( $status == 'REJ' ) $selected='selected'; else $selected='';
$html .=  "<option value='REJ' $selected>Rejeté</option>\n";
if ( $status == 'ATT' ) $selected='selected'; else $selected='';
$html .=  "<option value='ATT' $selected>A approuver</option>\n";
$html .= "</select></td>
<td></td></tr>";

// choix remplaçé
$html .= "<tr><td align=right> Remplacé </td>";
$html .= "<td align=left><select name='replaced' id='replaced' onchange=\"changeParam('".$filter."');\">
        <option value='0'>Tous</option>";
$query="select distinct p.P_ID, p.P_NOM, p.P_PRENOM, p.P_GRADE , p.P_STATUT from pompier p
        where p.P_OLD_MEMBER = 0 and P_STATUT <> 'EXT'";
if ( $filter > 0 ) 
    $query.= " and p.P_SECTION in (".get_family("$filter").")";
$query.= " order by P_NOM, P_PRENOM";
$result=mysqli_query($dbc,$query);
while ($row=mysqli_fetch_array($result)) {
    $R = strtoupper($row["P_NOM"])." ".my_ucfirst($row["P_PRENOM"]);
    if ( $grades ) $R .= " (".$row["P_GRADE"].")";
    if ( $row["P_ID"] == $replaced ) $selected='selected';
    else $selected='';
    $html .= "<option value='".$row["P_ID"]."' class='".$row["P_STATUT"]."' $selected>".$R."</option>";
}
$html .="</select></td>
<td></td></tr>";

// choix remplaçant
$html .= "<tr><td align=right> Remplaçant </td>";
$html .= "<td align=left><select name='substitute' id='substitute' onchange=\"changeParam('".$filter."');\">
        <option value='0'>Tous</option>";
$query="select distinct p.P_ID, p.P_NOM, p.P_PRENOM, p.P_GRADE , p.P_STATUT from pompier p
        where p.P_OLD_MEMBER = 0 and P_STATUT <> 'EXT'";
if ( $filter > 0 ) 
    $query.= " and p.P_SECTION in (".get_family("$filter").")";
$query.= " order by P_NOM, P_PRENOM";

$result=mysqli_query($dbc,$query);
while ($row=mysqli_fetch_array($result)) {
    $R = strtoupper($row["P_NOM"])." ".my_ucfirst($row["P_PRENOM"]);
    if ( $grades ) $R .= " (".$row["P_GRADE"].")";
    if ( $row["P_ID"] == $substitute ) $selected='selected';
    else $selected='';
    $html .= "<option value='".$row["P_ID"]."' class='".$row["P_STATUT"]."' $selected>".$R."</option>";
}
$html .="</select></td>
<td></td></tr>";

// Choix Dates
$html .= "<tr><td align=right >Début:</td><td align=left>
        <input type='text' size='10' name='dtdb' id='dtdb' value=\"".$dtdb."\" class='datepicker' data-provide='datepicker'
            placeholder='JJ-MM-AAAA'
            onchange=checkDate2(this.form.dtdb)
            style='width:100px;'>
    </td>
    <td rowspan=2><input type='button' class='btn btn-default' value='go'  onclick=\"changeParam('".$filter."');\"></td>
    </tr>
    <tr><td align=right >Fin :</td><td align=left>
        <input type='text' size='10' name='dtfn' id='dtfn' value=\"".$dtfn."\" class='datepicker' data-provide='datepicker'
            placeholder='JJ-MM-AAAA'
            onchange=checkDate2(this.form.dtfn)
            style='width:100px;'>
    </td></tr></table></form>";
$html .= "<p>";
      
$html .= table_remplacements($evenement=0, $status, $dtdb, $dtfn, $replaced, $substitute, $filter );
$html .= " <input type=submit class='btn btn-default' value='retour' onclick='javascript:history.back(1);'>";
$html .= "<p></div>";
$html .= writefoot();
print $html;
?>