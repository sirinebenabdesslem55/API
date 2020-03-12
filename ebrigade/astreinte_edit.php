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
check_all(26);
$id=$_SESSION['id'];
get_session_parameters();
$section = $filter;
if (isset ($_GET["section"])) $section=intval($_GET["section"]);
if (isset ($_GET["from"])) $from=$_GET["from"];
else $from='default';
writehead();
?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/dateFunctions.js'></script>
<script type='text/javascript'>
function redirect(section) {
     url = "astreinte_edit.php?section="+section;
     self.location.href = url;
}
function delete_astreinte(astreinte,section) {
     if ( confirm("Vous allez supprimer cette astreinte\nContinuer?")) {
          url = "astreinte_save.php?action=delete&astreinte="+astreinte+"&section="+section;
          self.location.href = url;
     }
}
</script> 
<STYLE type="text/css">
.categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.type{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
</STYLE>

</head>
<?php
if (isset ($_GET["astreinte"])) $astreinte=intval($_GET["astreinte"]);
else  $astreinte=0;

if ( $astreinte > 0 ) {
    // cas modification
    $query="select a.AS_ID, a.S_ID, a.GP_ID, a.P_ID, g.GP_DESCRIPTION,
    DATE_FORMAT(a.AS_DEBUT, '%d-%m-%Y') as AS_DEBUT,
    DATE_FORMAT(a.AS_FIN, '%d-%m-%Y') as AS_FIN, 
    a.AS_UPDATED_BY, a.AS_UPDATE_DATE,
    p.P_NOM, p.P_PRENOM, p.P_EMAIL, p.P_PHONE,
    s.S_CODE, s.S_DESCRIPTION
    from section s, astreinte a left join pompier p on a.P_ID=p.P_ID, groupe g
    where a.S_ID = s.S_ID
    and a.GP_ID=g.GP_ID
    and a.AS_ID=".$astreinte;
    $result=mysqli_query($dbc,$query);
    
    // check input parameters
    if ( mysqli_num_rows($result) <> 1 ) {
        param_error_msg();
        exit;
    }
   
    $row=mysqli_fetch_array($result);
    $MYS_ID=$row["S_ID"];
    $MYS_CODE=$row["S_CODE"];
    $MYS_DESCRIPTION=$row["S_DESCRIPTION"];
    $MYP_ID=$row["P_ID"];
    $MYP_NOM=$row["P_NOM"];
    $MYP_PRENOM=$row["P_PRENOM"];
    $MYAS_UPDATED_BY=$row["AS_UPDATED_BY"];
    $MYAS_UPDATE_DATE=$row["AS_UPDATE_DATE"];
    $MYGP_ID=$row["GP_ID"];
    $MYGP_DESCRIPTION=$row["GP_DESCRIPTION"];
    $MYAS_DEBUT=$row["AS_DEBUT"];
    $MYAS_FIN=$row["AS_FIN"];
}
else {
    // cas nouvelle astreint
    $MYAS_DEBUT="";
    $MYAS_FIN="";
    $MYGP_ID="";
    $MYP_ID=0;
    $MYS_ID=$section;
}

//=====================================================================
// debut tableau
//=====================================================================

echo "<body>
<div align=center><font size=4><b>Saisie astreinte</b></font><br>";

$query="select distinct GP_ID, GP_DESCRIPTION
        from groupe
        where GP_ASTREINTE=1";
$result=mysqli_query($dbc,$query);
$nb=mysqli_num_rows($result);

if ($nb == 0 ) {
    write_msgbox("paramétrage incomplet", $warning_pic, "Aucun <a href=habilitations.php?category=R>rôle</a> de l'organigramme ne supporte les astreintes",10,0);
    exit;
}
echo "<form name=demoform action='astreinte_save.php' method='POST'>
<input type='hidden' name='astreinte' value=".$astreinte.">
<input type='hidden' name='section' value=".$MYS_ID.">
<input type='hidden' name='type' value=".$MYGP_ID.">
<p><table cellspacing=0 border=0>";
echo "<tr><td CLASS='MenuRub' colspan=2>informations</td></tr>";

//=====================================================================
// type
//=====================================================================
echo "<tr>
            <td bgcolor=$mylightcolor width=150><b>Astreinte pour</b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left>";

if ( $astreinte > 0 ) $disabled='disabled';
else $disabled='';

echo "<select name='type' $disabled>";
while ($row=@mysqli_fetch_array($result)) {
    $GP_ID=$row["GP_ID"];
    $GP_DESCRIPTION=$row["GP_DESCRIPTION"];

    echo "<option class='type' value='".$GP_ID."' title=\"".$GP_DESCRIPTION."\"";
    if ($GP_ID == $MYGP_ID ) echo " selected ";
    echo ">".$GP_DESCRIPTION."</option>\n";
}
echo "</select>";
echo " </tr>";

//=====================================================================
// section
//=====================================================================
   

echo "<tr>
            <td bgcolor=$mylightcolor><b>Au niveau de</b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left colspan=2>";
$mysection=get_highest_section_where_granted($id,26);
if ( $mysection == '' ) $mysection=$section;
if ( ! is_children($section,$mysection)) $mysection=$_SESSION['SES_SECTION'];      
echo "<select id='section' name='section' onChange=\"redirect(document.getElementById('section').value);\" $disabled>"; 

$level=get_level($mysection);
if ( $level == 0 ) $mycolor=$myothercolor;
elseif ( $level == 1 ) $mycolor=$my2darkcolor;
elseif ( $level == 2 ) $mycolor=$my2lightcolor;
elseif ( $level == 3 ) $mycolor=$mylightcolor;
else $mycolor='white';
$class="style='background: $mycolor;'";

if (check_rights($_SESSION['id'], 24))
        display_children2(-1, 0, $MYS_ID, $nbmaxlevels , $sectionorder);
else {
    $list = preg_split('/,/' , get_family("$mysection"));
    if (in_array($section, $list) or ($mysection == $MYS_ID )) {
            echo "<option value='$mysection' $class >".
                get_section_code($mysection)." - ".get_section_name($mysection)."</option>";
            display_children2($mysection, $level +1, $MYS_ID, $nbmaxlevels);
    }
    else
        echo "<option value='$MYS_ID' $class selected>".
                    get_section_code($MYS_ID)." - ".get_section_name($MYS_ID)."</option>";
}
echo "</select></td>";
echo "</tr>";      


//=====================================================================
// dates
//=====================================================================

echo " <tr bgcolor=$mylightcolor><td align=left><b>Date début </b>$asterisk";
echo "</td><td><input type='text' size='10' name='dc1' id='dc1' value=\"".$MYAS_DEBUT."\" class='datepicker' data-provide='datepicker'
            placeholder='JJ-MM-AAAA'
            onchange=checkDate2(document.demoform.dc1)
            style='width:100px;'
            autocomplete='off'>";

echo "</td></tr><tr bgcolor=$mylightcolor >";
echo "<td align=left><b>Date fin </b>$asterisk";
echo "</td><td><input type='text' size='10' name='dc2' id='dc2' value=\"".$MYAS_FIN."\" class='datepicker' data-provide='datepicker'
            placeholder='JJ-MM-AAAA'
            onchange=checkDate2(document.demoform.dc2)
            style='width:100px;'
            autocomplete='off'>";
echo "</td></tr>";

//=====================================================================
// choix personne
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor><b>Personne</b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left><select id='person' name='person'>
          <option value='0'>Choisir une personne ...</option>";
$query="select P_ID, P_PRENOM, P_NOM , S_CODE
              from pompier, section
           where P_SECTION = S_ID
           and P_OLD_MEMBER = 0
           and P_STATUT <> 'EXT'";      
$query .= " and (P_SECTION in  (".get_family("$MYS_ID").") or P_ID = ".$MYP_ID.")";          
$query .= " order by P_NOM";
   
$result=mysqli_query($dbc,$query);
while ($row=@mysqli_fetch_array($result)) {
    $P_NOM=$row["P_NOM"];
    $P_PRENOM=$row["P_PRENOM"];
    $P_ID=$row["P_ID"];
    $S_CODE=$row["S_CODE"];
    echo "<option value='".$P_ID."'";
    if ($P_ID == $MYP_ID ) echo " selected ";
    echo ">".strtoupper($P_NOM)." ".ucfirst($P_PRENOM)." (".$S_CODE.")</option>\n";
}
echo "</select>";
echo "</table><p>";

//=====================================================================
// boutons enregistrement
//=====================================================================
if ( $from == 'calendar' ) $s='calendar.php?pompier='.$MYP_ID;
else if ( $from == 'personnel' ) $s='upd_personnel.php?pompier='.$MYP_ID;
else $s='astreintes.php';
echo " <input type=button  class='btn btn-default' value='retour' onclick=\"javascript:self.location.href='".$s."';\"> ";
echo " <input name='astreinte' type='hidden' value='$astreinte'>";
echo " <input type='submit' class='btn btn-default' id='sauver' value='enregistrer'> ";
echo " <input type='button' class='btn btn-default'  id='supprimer' value='supprimer' onclick=\"javascript:delete_astreinte('".$astreinte."','".$section."');\"> ";
echo "</form></div>";
writefoot();
?>

