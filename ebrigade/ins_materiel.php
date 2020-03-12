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
check_all(70);

$id=$_SESSION['id'];
$section=$_SESSION['SES_SECTION'];
get_session_parameters();

$suggestedsection=$section;
if ( check_rights($id, 70, $filter)) $suggestedsection=$filter;
if (isset ($_GET["section"])) $suggestedsection=$_GET["section"];

if ( isset($_GET["type"])) $type=secure_input($dbc,$_GET["type"]);
else $type='ALL';
if ( isset($_GET["usage"])) $usage=secure_input($dbc,$_GET["usage"]);
else $usage='ALL';
if ( isset($_GET["like"])) $like=intval($_GET["like"]);
else $like=0;
if ( isset($_GET["from"])) $from=$_GET["from"];
else $from='default';

$mysection=get_highest_section_where_granted($id,70);
if ( check_rights($id, 24) ) $section='0';
else if ( $mysection <> '' ) {
    if ( is_children($section,$mysection)) 
        $section=$mysection;
}

//=====================================================================
// affiche la fiche materiel
//=====================================================================

writehead();
?>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/upd_materiel.js'></script>
</head>
<?php


$query="select CM_DESCRIPTION,PICTURE from categorie_materiel
        where TM_USAGE='".$usage."'";
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
if ( $usage == 'ALL' ) $cmt=" Ajout d'un nouvel �l�ment";
else $cmt="Ajout d'un nouveau ".$row["CM_DESCRIPTION"];
$picture=$row["PICTURE"];

echo "<div align=center><table class='noBorder'>
      <tr><td width = 40 ><i class='fa fa-".$picture." fa-2x' style='color:purple;'></i></td><td>
      <font size=4><b>".$cmt."</b></font></td></tr></table>";

echo "<p><table cellspacing=0 border=0>";
echo "<form name='vehicule' action='save_materiel.php'>";


if ( $like > 0 ) {
    $query="select distinct m.TM_ID,tm.TM_CODE,tm.TM_DESCRIPTION,
        tm.TM_USAGE,m.VP_ID,vp.VP_LIBELLE, vp.VP_OPERATIONNEL,vp.VP_LIBELLE,m.MA_EXTERNE, m.MA_INVENTAIRE,
         m.MA_ID, m.MA_NUMERO_SERIE, m.MA_COMMENT,m.MA_LIEU_STOCKAGE, m.MA_MODELE,  m.VP_ID,
         m.MA_ANNEE, m.MA_NB, m.S_ID, s.S_CODE, DATE_FORMAT(m.MA_UPDATE_DATE,'%d-%m-%Y') as MA_UPDATE_DATE,
         DATE_FORMAT(m.MA_REV_DATE, '%d-%m-%Y') as MA_REV_DATE,
         m.MA_UPDATE_BY, m.AFFECTED_TO, m.V_ID, m.MA_PARENT, tm.TM_LOT
        from materiel m, type_materiel tm, section s, vehicule_position vp
        where m.TM_ID=tm.TM_ID
        and m.VP_ID=vp.VP_ID
        and s.S_ID=m.S_ID
        and m.MA_ID=".$like;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $_TM_ID=$row["TM_ID"];
    $type=$_TM_ID;
    $_TM_CODE=$row["TM_CODE"];
    $_TM_LOT=$row["TM_LOT"];
    $_TM_DESCRIPTION=$row["TM_DESCRIPTION"];
    $_TM_USAGE=$row["TM_USAGE"];
    $usage=$_TM_USAGE;
    $_MA_ID=$row["MA_ID"];
    $_MA_EXTERNE=$row["MA_EXTERNE"];
    $_MA_NUMERO_SERIE=$row["MA_NUMERO_SERIE"];
    $_MA_COMMENT=$row["MA_COMMENT"];
    $_MA_PARENT=$row["MA_PARENT"];
    $_MA_LIEU_STOCKAGE=$row["MA_LIEU_STOCKAGE"];
    $_MA_MODELE=$row["MA_MODELE"];
    $_MA_REV_DATE=$row["MA_REV_DATE"];
    $_MA_INVENTAIRE=$row["MA_INVENTAIRE"];
    $_MA_ANNEE=$row["MA_ANNEE"]; if ( $_MA_ANNEE == '0000' ) $_MA_ANNEE ='';
    $_MA_NB=$row["MA_NB"]; 
    if ( $_MA_NB == '' ) $_MA_NB = 1;
    $_S_ID=$row["S_ID"];
    $_VP_LIBELLE=$row["VP_LIBELLE"];
    $_VP_OPERATIONNEL=$row["VP_OPERATIONNEL"];
    $_VP_ID=$row["VP_ID"];
    $_MA_UPDATE_DATE=$row["MA_UPDATE_DATE"];
    $_MA_UPDATE_BY=$row["MA_UPDATE_BY"];
    $_AFFECTED_TO=intval($row["AFFECTED_TO"]);
    $_V_ID=$row["V_ID"];
}
else {
     $_MA_NB=1;
     $_MA_EXTERNE=0;
    $_MA_NUMERO_SERIE="";
    $_MA_REV_DATE="";
    $_MA_MODELE="";
    $_MA_COMMENT="";
    $_MA_INVENTAIRE="";
    $_MA_LIEU_STOCKAGE="";
    $_VP_ID="";
    $_AFFECTED_TO=0;
    echo "<input type='hidden' name='TM_ID' value=''>";
    echo "<input type='hidden' name='MA_NUMERO_SERIE' value=''>";
    echo "<input type='hidden' name='MA_COMMENT' value=''>";
    echo "<input type='hidden' name='VP_ID' value=''>";
    echo "<input type='hidden' name='MA_ANNEE' value=''>";
    echo "<input type='hidden' name='MA_INVENTAIRE' value=''>";
    echo "<input type='hidden' name='MA_REV_DATE' value=''>";
}

echo "<input type='hidden' name='groupe' value=''>";
echo "<input type='hidden' name='MA_ID' value=''>";
echo "<input type='hidden' name='operation' value='insert'>";
echo "<input type='hidden' name='from' value=''>";

//=====================================================================
// ligne 1
//=====================================================================

echo "<tr class=TabHeader>
             <td bgcolor=$mydarkcolor width=200 align=right></td>
            <td bgcolor=$mydarkcolor width=250 align=right><b>informations mat�riel</b></td>
      </tr>";

//=====================================================================
// ligne cat�gorie
//=====================================================================
echo "<tr>
            <td bgcolor=$mylightcolor ><b>Cat�gorie </b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left>
          <select id ='TM_USAGE'name='TM_USAGE' 
          onchange=\"displaymanager(document.getElementById('TM_USAGE').value)\">";
if ( $usage == 'ALL') echo "<option value='ALL'>Choisissez une cat�gorie</option>";
$query2="select cm.TM_USAGE, cm.CM_DESCRIPTION from categorie_materiel cm
        where exists (select 1 from type_materiel tm where cm.TM_USAGE =tm.TM_USAGE)
        order by cm.CM_DESCRIPTION";
$result2=mysqli_query($dbc,$query2);
while ($row=@mysqli_fetch_array($result2)) {
      $TM_USAGE=$row["TM_USAGE"];
      $CM_DESCRIPTION=$row["CM_DESCRIPTION"];
      echo "<option value='".$TM_USAGE."'";
      if ($TM_USAGE == $usage ) echo " selected ";
      echo ">".$CM_DESCRIPTION."</option>\n";
}
echo "</select>";
echo "</td>
      </tr>";
//=====================================================================
// ligne type
//=====================================================================

if ( $usage <> 'ALL' ) {
$query2="select distinct TM_ID, TM_CODE, TM_DESCRIPTION, TM_LOT from type_materiel where TM_USAGE='".$usage."'
         order by TM_CODE";
$result2=mysqli_query($dbc,$query2);

echo "<tr>
            <td bgcolor=$mylightcolor ><b>Type</b> $asterisk</td>
            <td bgcolor=$mylightcolor  align=left>
         <select name='TM_ID' class='smalldropdown'>";
            while ($row2=@mysqli_fetch_array($result2)) {
                $TM_ID=$row2["TM_ID"];
                $TM_LOT=$row2["TM_LOT"];
                if ( $TM_LOT == 1 ) $lot=" (lot)";
                else $lot="";
                $TM_CODE=$row2["TM_CODE"];
                $TM_DESCRIPTION=$row2["TM_DESCRIPTION"];
                if ( $TM_DESCRIPTION <> "" ) $addcmt= " - ".$TM_DESCRIPTION;
                else $addcmt="";
                if ($TM_ID == $type ) $selected = 'selected';
                else $selected =''; 
                echo "<option value='".$usage."_".$TM_ID."' $selected>".substr($TM_CODE.$addcmt,0,45).$lot."</option>";
            }
            echo "</select>";
 echo "</td>
      </tr>";
     
//=====================================================================
// ligne mod�le
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor><b>Marque/Mod�le</b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left><input type='text' name='MA_MODELE' size='35' maxlength='40' value=\"$_MA_MODELE\">";        
echo "</tr>";

//=====================================================================
// ligne section
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor><b>Section</b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left>";
echo "<select id='groupe' name='groupe' class='smalldropdown'>";
     
$level=get_level($section);
if ( $level == 0 ) $mycolor=$myothercolor;
elseif ( $level == 1 ) $mycolor=$my2darkcolor;
elseif ( $level == 2 ) $mycolor=$my2lightcolor;
elseif ( $level == 3 ) $mycolor=$mylightcolor;
else $mycolor='white';
$class="style='background: $mycolor;'";
if ( $like > 0 ) {
    $suggestedsection=$_S_ID;
}

if ( isset($_SESSION['sectionorder']) ) $sectionorder=$_SESSION['sectionorder'];
else $sectionorder=$defaultsectionorder;

if ( check_rights($id, 24))
    display_children2(-1, 0, $suggestedsection, $nbmaxlevels, $sectionorder);
else {
    echo "<option value='$suggestedsection' $class >".get_section_code($suggestedsection)." - ".get_section_name($suggestedsection)."</option>";
    display_children2($section, $level +1, $suggestedsection, $nbmaxlevels);
}

echo "</select></td> ";
echo "</tr>";

//=====================================================================
// ligne nombre
//=====================================================================
echo "<tr>
            <td bgcolor=$mylightcolor ><b>Nombre de pi�ces</b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left height=25>
            <input type='text' name='quantity' size='6' value='".$_MA_NB."' onchange='checkNumber(form.quantity,\"1\")'></td>";        
echo "</tr>";

//=====================================================================
// ligne statut
//=====================================================================

$query2="select VP_LIBELLE, VP_ID, VP_OPERATIONNEL
         from vehicule_position
         where VP_OPERATIONNEL <> 0
         order by  VP_OPERATIONNEL desc";
$result2=mysqli_query($dbc,$query2);

echo "<tr>
            <td bgcolor=$mylightcolor ><b>Position du mat�riel</b> $asterisk</td>
            <td bgcolor=$mylightcolor align=left>
        <select name='VP_ID' >";
             while ($row2=@mysqli_fetch_array($result2)) {
                  $VP_ID=$row2["VP_ID"];
                  $VP_LIBELLE=$row2["VP_LIBELLE"];
                  $VP_OPERATIONNEL=$row2["VP_OPERATIONNEL"];
                  if ($VP_ID == $_VP_ID) $selected='selected';
                  else if ($VP_ID == 'OP') $selected='selected';
                  else $selected='';
                  echo "<option value='$VP_ID' $selected>$VP_LIBELLE</option>";
                  }
             echo "</select>";
echo " </td>
      </tr>";

//=====================================================================
// ligne num�ro de s�rie
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor ><b>Num�ro de s�rie</b></td>
            <td bgcolor=$mylightcolor align=left height=25><input type='text' name='MA_NUMERO_SERIE' size='20' value=\"".$_MA_NUMERO_SERIE."\">";        
echo "</tr>";

//=====================================================================
// ligne ann�e
//=====================================================================

$curyear=date("Y");
$year=$curyear - 30; 
echo "<tr>
            <td bgcolor=$mylightcolor ><b>Ann�e</b></td>
            <td bgcolor=$mylightcolor align=left>
            <select name='MA_ANNEE'>";
echo "<option value='' selected>inconnue</option>";
while ( $year <= $curyear + 1 ) {
             if ( $like > 0 ) {
                 if ( $year == $_MA_ANNEE ) $selected = 'selected';
                 else $selected = '';
            }
            else $selected = '';
            echo "<option value='$year' $selected>$year</option>";
            $year++;
        }        
echo "</select></tr>";

//=====================================================================
// ligne commentaire
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor ><b>Commentaire</b></td>
            <td bgcolor=$mylightcolor align=left><input type='text' name='MA_COMMENT' size='35' value=\"$_MA_COMMENT\">";        
echo " </td>
      </tr>";

//=====================================================================
// affect� � 
//=====================================================================
if ( $_AFFECTED_TO > 0 ) {
    $query2="select p.P_ID, p.P_PRENOM, p.P_NOM , s.S_CODE
        from pompier p, section s
            where S_ID= P_SECTION
         and ( p.P_SECTION in (".get_family($_S_ID).") or p.P_ID = '".$_AFFECTED_TO."' )
         and p.P_CODE <> '1234'
         and p.P_STATUT <> 'EXT'
         and (p.P_OLD_MEMBER = 0 or p.P_ID = '".$_AFFECTED_TO."' )
         order by p.P_NOM";
    $result2=mysqli_query($dbc,$query2);

    echo "<tr >
            <td bgcolor=$mylightcolor ><b>Affect� � </b></td>
            <td bgcolor=$mylightcolor align=left>";
   echo "<select id='affected_to' name='affected_to' class='smalldropdown'>
           <option value='0' selected >--personne--</option>\n";
   while ($row2=@mysqli_fetch_array($result2)) {
      $P_NOM=$row2["P_NOM"];
      $P_PRENOM=$row2["P_PRENOM"];
      $P_ID=$row2["P_ID"];
      $S_CODE=$row2["S_CODE"];
      if ( $P_ID == $_AFFECTED_TO ) $selected='selected';
      else $selected="";
      $cmt=" (".$S_CODE.")";
      echo "<option value='".$P_ID."' $selected>".strtoupper($P_NOM)." ".ucfirst($P_PRENOM).$cmt."</option>\n";
   }
    echo "</select>";
    echo "</td></tr>";
}  
//=====================================================================
// ligne inventaire
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor ><b>N�d'inventaire</b></td>
            <td bgcolor=$mylightcolor align=left><input type='text' name='MA_INVENTAIRE' size='35' value=\"$_MA_INVENTAIRE\">";        
echo " </td>
      </tr>";

//=====================================================================
// ligne lieu stockage
//=====================================================================

echo "<tr>
            <td bgcolor=$mylightcolor ><b>Lieu de stockage</b></td>
            <td bgcolor=$mylightcolor align=left><input type='text' name='MA_LIEU_STOCKAGE' size='35' value=\"$_MA_LIEU_STOCKAGE\">";        
echo " </td>
      </tr>";

//=====================================================================
// dates de prochaine r�vision ou p�remption
//=====================================================================

echo "<input type='hidden' name='dc0' value='".getnow()."'>";


// assurance
echo "<tr>
            <td bgcolor=$mylightcolor ><b>Prochaine r�vision ou p�remption</b></td>
            <td bgcolor=$mylightcolor align=left>
            <input type='text' size='10' name='dc1' value='' class='datepicker' data-provide='datepicker'
            placeholder='JJ-MM-AAAA' autocomplete='off'
            onchange=checkDate2(this.form.dc1)
            style='width:100px;'>";
      
//=====================================================================
// materiel externe
//=====================================================================

if (check_rights($_SESSION['id'], 24) and ($nbsections ==  0 )) {

if ( $_MA_EXTERNE == 1 )$checked='checked';
else $checked='';

echo "<tr>
            <td bgcolor=$mylightcolor><label for='MA_EXTERNE'>$cisname</label></td>
            <td bgcolor=$mylightcolor align=left>
            <input type='checkbox' name='MA_EXTERNE' id='MA_EXTERNE' value='1' $checked>
            <small>mis � disposition (utilisable, non modifiable)<small>";
echo " </td>
      </tr>";
}            

echo "</table>";
echo "<p><input type='submit' class='btn btn-default' value='sauver'></form>";
if ( $from == 'personnel' and $_AFFECTED_TO > 0 ) echo " <input type='button' class='btn btn-default' value='Annuler' name='annuler' onclick=\"redirect3('upd_personnel.php?from=tenues&pid=".$_AFFECTED_TO."');\"></div>";
else echo " <input type='button'class='btn btn-default'  value='Annuler' name='annuler' onclick='redirect2();'></div>";
}
writefoot();
?>
