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
$id=$_SESSION['id'];
if ( isset($_GET["filter"])) $filter=intval($_GET["filter"]);

// laisser permissions sur sections 0 et 1
if ( $syndicate == 1 ) {
    if ( $filter > 1 and $filter <> $_SESSION['SES_SECTION'] and $filter <>  $_SESSION['SES_PARENT']) { 
        if (! check_rights($id, 44, "$filter") ) check_all(24);
    }
}

writehead();
echo "</head>
<body>";    

$query="SELECT g.GP_ID, g.GP_DESCRIPTION, g.TR_SUB_POSSIBLE, r.P_ID, r.P_NOM, r.P_PRENOM, r.P_SECTION, r.S_CODE, r.P_SEXE, r.P_GRADE, r.P_PHOTO, r.P_PHONE, r.P_HIDE
FROM groupe g
JOIN (
SELECT p.P_ID, p.P_NOM, p.P_PRENOM, p.P_SECTION, p.P_PHOTO, s.S_CODE, sr.GP_ID, p.P_SEXE, p.P_GRADE, p.P_PHONE, p.P_HIDE
FROM section_role sr, pompier p, section s
WHERE sr.P_ID = p.P_ID
AND s.S_ID = p.P_SECTION
AND sr.S_ID =".$filter."
) AS r 
ON g.GP_ID = r.GP_ID
WHERE g.GP_ID >100 AND g.TR_CONFIG=2
ORDER BY GP_ORDER, GP_ID ASC";

$logo=get_logo();

echo "<div align='center'>
        <table class='noBorder'>
        <tr>
        <td width=80><img src=".$logo." class='img-max-50'></td>
        <td><font size=4><b>Organigramme <br>".get_section_name("$filter")."</b></font><br><i> le ".date('d-m-Y')."</i>
        </td>
        </tr>
        </table>";

echo "<p><table class='noBorder'>";

$allowed=false;
if (  check_rights($id, 2,"$filter")
    or  check_rights($id, 12,"$filter")
    or  check_rights($id, 25)
)
$allowed=true;

$result=mysqli_query($dbc,$query);
$prevG=0;
while ($row=@mysqli_fetch_array($result)) {
    $GP_ID=$row["GP_ID"];
    if ( $GP_ID <> $prevG ) $GP_DESCRIPTION=$row["GP_DESCRIPTION"];
    else $GP_DESCRIPTION="";
    $P_ID=$row["P_ID"];
    $P_SEXE=$row["P_SEXE"];
    $P_PRENOM=$row["P_PRENOM"];
    $P_NOM=$row["P_NOM"];
    $P_GRADE=$row["P_GRADE"];
    $S_CODE=$row["S_CODE"];
    $P_PHOTO=$row["P_PHOTO"];
    $P_PHONE=$row["P_PHONE"];
    $P_HIDE=$row["P_HIDE"];
    
    $name=strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM);
    if ( $P_SEXE == 'M' )  $img = 'images/boy.png';
    else $img = 'images/girl.png';
    if ( $P_PHOTO <> "" and (file_exists($trombidir."/".$P_PHOTO)))
        $img = $trombidir."/".$P_PHOTO;

    if ( $P_PHONE <> "" and ( $P_HIDE == 0 or $allowed) )
        $phone ="<br><font size=1>".$P_PHONE."</font>";
    else $phone="";
    echo "<tr>
                <td align=left width=180><b>".$GP_DESCRIPTION."</b></td>
                <td align=center width=100><img src='".$img."' class='img-max-50'></td>
                  <td align=left width=300><a href=upd_personnel.php?pompier=".$P_ID.">".$name."</a> <i>(".$S_CODE.")</i>".$phone."</td>
            </tr>";
    $prevG=$GP_ID;
}
echo "</table>";

echo " <p><input type=submit class='btn btn-default' value='retour' onclick='javascript:history.back(1);'> ";
writefoot();
?>
