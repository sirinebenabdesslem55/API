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
$P_ID=intval($_GET["P_ID"]);
check_all(0);
$id=$_SESSION['id'];

if ( $P_ID <> $id) check_all(40);
$S_ID=get_section_of($P_ID);

$PS_ID=intval($_GET["PS_ID"]);
if (isset($_GET["order"])) $order=$_GET["order"];
else $order='PF_DATE';

if (isset($_GET["type"])) $type=$_GET["type"];
else $type='0';

if (isset($_GET["PF_ID"])) $PF_ID=$_GET["PF_ID"];
else $PF_ID='0';

if (isset($_GET["action"])) $action=$_GET["action"];
else $action='list';  // list,add,update,delete

if (isset($_GET["from"])) $from=$_GET["from"];
else $from='competences';  // list,add,update,delete

$mysection=$_SESSION['SES_SECTION'];
if ( check_rights($id, 4 , "$S_ID"))  $disabled="";
else $disabled="disabled";

writehead();
?>
<script language=JavaScript>
function redirect(pid,psid) {
     if ( psid == 0 ) {
         url="upd_personnel.php?tab=3&pompier="+pid+"&from=qualif";
     }
     else {
         url="personnel_formation.php?P_ID="+pid+"&PS_ID="+psid;
     }
     self.location.href=url;
}

function redirect2(pid) {
    url="upd_personnel.php?pompier="+pid+"&from=formations";
    self.location.href=url;
}

function changetype(pid,psid,type,pfid,action) {
     url="personnel_formation.php?P_ID="+pid+"&PS_ID="+psid+"&type="+type+"&action="+action+"&PF_ID="+pfid;
     self.location.href=url;
}

function add(pid,psid) {
     url="personnel_formation.php?P_ID="+pid+"&PS_ID="+psid+"&action=add";
     self.location.href=url;
}

function update(pid,psid,pfid) {
     url="personnel_formation.php?P_ID="+pid+"&PS_ID="+psid+"&PF_ID="+pfid+"&action=update";
     self.location.href=url;
}

</script>
<script type='text/javascript' src='js/checkForm.js'></script>
<?php




//=====================================================================
// debut tableau
//=====================================================================

$query="select TYPE, PS_DIPLOMA, PS_RECYCLE, DESCRIPTION 
        from poste where PS_ID=$PS_ID";
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$DESCRIPTION=$row["DESCRIPTION"];
$TYPE=$row["TYPE"];
$PS_RECYCLE=$row["PS_RECYCLE"];
$PS_DIPLOMA=$row["PS_DIPLOMA"];

echo "<div align=center>
<table class='noBorder'>
<tr><td width=50><i class='fa fa-book fa-3x' ></i></td><td>
<font size=4>
<b>Formation $DESCRIPTION<br>
de ".ucfirst(get_prenom($P_ID))." ".strtoupper(get_nom($P_ID))."</b>
</font></td></tr></table>";


if (( $PS_DIPLOMA == 1 ) or ( $PS_RECYCLE == 1 )) {
if ( $action == 'list' ) {
//=====================================================================
// statut de la compétence
//=====================================================================
$query="select Q_VAL,DATE_FORMAT(Q_EXPIRATION, '%d-%m-%Y' ) as Q_EXPIRATION, DATEDIFF(Q_EXPIRATION,NOW()) as NB
        from qualification
        where PS_ID=$PS_ID and P_ID=$P_ID";
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$Q_VAL=$row["Q_VAL"];
$Q_EXPIRATION=$row["Q_EXPIRATION"];
$NB=$row["NB"];

if ( $Q_VAL <> '' ) {
    if ( $Q_EXPIRATION <> '') {
        if ($NB <= 0) $cmt="<font color=red>Compétence $TYPE expirée depuis $Q_EXPIRATION</font>";
        else if ($NB < 61) $cmt="<font color=orange>Compétence $TYPE expire le $Q_EXPIRATION</font>";
        else if ( $Q_VAL == 2 ) $cmt="<font color=blue>Compétence secondaire $TYPE expire le $Q_EXPIRATION</font>";
         else if ( $Q_VAL == 1 ) $cmt="<font color=green>Compétence principale $TYPE expire le $Q_EXPIRATION</font>";
    }
    else if ( $Q_VAL == 2 ) $cmt="<font color=blue>Compétence secondaire $TYPE valide</font>";
     else if ( $Q_VAL == 1 ) $cmt="<font color=green>Compétence principale $TYPE valide</font>";
}
else $cmt="<font color=black>En formation pour obtenir la compétence $TYPE</font>";

echo "<i>".$cmt."</i>";;
//=====================================================================
// liste des formations
//=====================================================================
$query="select pf.PF_ID, pf.PF_COMMENT, pf.PF_ADMIS, DATE_FORMAT(pf.PF_DATE, '%d-%m-%Y') as PF_DATE, 
        pf.PF_RESPONSABLE, pf.PF_LIEU, pf.E_CODE, tf.TF_LIBELLE, pf.PF_DIPLOME,
        DATE_FORMAT(pf.PF_PRINT_DATE, '%d-%m-%Y') as PF_PRINT_DATE,
        DATE_FORMAT(pf.PF_UPDATE_DATE, '%d-%m-%Y') as PF_UPDATE_DATE, 
        pf.PF_PRINT_BY, pf.PF_UPDATE_BY,
        p.PS_PRINTABLE
        from personnel_formation pf, type_formation tf, poste p
        where tf.TF_CODE=pf.TF_CODE
        and pf.P_ID=".$P_ID."
        and pf.PS_ID=".$PS_ID."
        and p.PS_ID = pf.PS_ID
        order by pf.".$order;
$result=mysqli_query($dbc,$query);
$num=mysqli_num_rows($result);
if ( $num > 0 ) {
    echo "<p><table cellspacing=0 border=0 bgcolor=$mylightcolor>";
    echo "<tr class=TabHeader>
      <td width=80><a href=personnel_formation.php?P_ID=".$P_ID."&PS_ID=".$PS_ID."&order=PF_DATE class=TabHeader>Date</a></td>
      <td width=150><a href=personnel_formation.php?P_ID=".$P_ID."&PS_ID=".$PS_ID."&order=TF_CODE class=TabHeader>Type</a></td>
      <td width=130><a href=personnel_formation.php?P_ID=".$P_ID."&PS_ID=".$PS_ID."&order=PF_DIPLOME class=TabHeader>N° diplôme</a></td>
      <td width=70><a href=personnel_formation.php?P_ID=".$P_ID."&PS_ID=".$PS_ID."&order=PF_UPDATE_BY class=TabHeader>info</a></td>
      <td width=100><a href=personnel_formation.php?P_ID=".$P_ID."&PS_ID=".$PS_ID."&order=PF_LIEU class=TabHeader>Lieu</a></td>
      <td width=100><a href=personnel_formation.php?P_ID=".$P_ID."&PS_ID=".$PS_ID."&order=PF_RESPONSABLE class=TabHeader>Délivré par</a></td>
      <td width=130><a href=personnel_formation.php?P_ID=".$P_ID."&PS_ID=".$PS_ID."&order=PF_COMMENT class=TabHeader>Commentaire</a></td>";
    if ( $disabled == "" )
        echo "<td width=30>Suppr</td>";
    echo "</tr>";
    $i=0;
    while ($row=@mysqli_fetch_array($result)) {
        $PF_ID=$row["PF_ID"];
        $PF_COMMENT=$row["PF_COMMENT"];
        $PF_ADMIS=$row["PF_ADMIS"];
        $PF_DATE=$row["PF_DATE"];
        $PF_RESPONSABLE=$row["PF_RESPONSABLE"];
        $PF_LIEU=$row["PF_LIEU"];
        $PF_DIPLOME=$row["PF_DIPLOME"];
        $PS_PRINTABLE=$row["PS_PRINTABLE"];
        $E_CODE=$row["E_CODE"];
        $TF_LIBELLE=$row["TF_LIBELLE"];
        $PF_UPDATE_BY=$row["PF_UPDATE_BY"];
        $PF_UPDATE_DATE=$row["PF_UPDATE_DATE"];
        $PF_PRINT_BY=$row["PF_PRINT_BY"];
        $PF_PRINT_DATE=$row["PF_PRINT_DATE"];

        $popup="";
        if ( $PF_UPDATE_BY <> "" )
               $popup="Enregistré par:                
".ucfirst(get_prenom($PF_UPDATE_BY))." ".strtoupper(get_nom($PF_UPDATE_BY))." le ".$PF_UPDATE_DATE."
";
        if ( $PF_PRINT_BY <> "" )        
            $popup .="Diplôme imprimé par:
".ucfirst(get_prenom($PF_PRINT_BY))." ".strtoupper(get_nom($PF_PRINT_BY))." le ".$PF_PRINT_DATE;
       
        if ( $popup <> "" ) 
               $popup=" <i class='far fa-file-text' title=\"".$popup."\"></i>";
       
        $i=$i+1;
        if ( $i%2 == 0 ) {
              $mycolor=$mylightcolor;
        }
        else {
              $mycolor="#FFFFFF";
        }
       
        if ( $disabled == "" )
               echo "<tr bgcolor=$mycolor 
                  onMouseover=\"this.bgColor='yellow'\" 
                  onMouseout=\"this.bgColor='$mycolor'\"   
                  onclick=\"this.bgColor='#33FF00';update($P_ID,$PS_ID,$PF_ID)\">";
        else
                 echo "<tr bgcolor=$mycolor >";    
        echo "<td><font size=1>".$PF_DATE."</font></td>";
        if ( intval($E_CODE) <> 0)
             echo "<td ><font size=1>
             <a href=evenement_display.php?evenement=".$E_CODE."&from=formation>".$TF_LIBELLE."</a></font></td>";
        else 
             echo "<td><font size=1>".$TF_LIBELLE."</font></td>";
        echo "<td><font size=1><b>".$PF_DIPLOME."</b></font></td>";
        echo "<td>";
        if ( intval($E_CODE) <> 0 ) {
            $querye="select TF_CODE, E_CLOSED from evenement where E_CODE=".$E_CODE;
               $resulte=mysqli_query($dbc,$querye);
               $rowe=@mysqli_fetch_array($resulte);
            
                  if ( check_rights($id,4,"$S_ID") and $rowe["E_CLOSED"] == 1) {
                  echo " <a href=pdf_document.php?section=".$S_ID."&evenement=".$E_CODE."&mode=2&P_ID=".$P_ID.">
                <i class='far fa-file-pdf fa-lg' style='color:red;' title=\"imprimer l'attestation de formation\"></i></a>";
               }
               if ( $PS_PRINTABLE == 1 and $PF_DIPLOME <> '' ) {
                if ( check_rights($id,54) and $rowe["TF_CODE"] == "I" ) {
                          echo " <a href=pdf_diplome.php?section=".$S_ID."&evenement=".$E_CODE."&mode=4&P_ID=".$P_ID.">
                        <i class='far fa-file-pdf fa-lg' style='color:red;' title=\"imprimer le duplicata du diplôme\"></i></a>";
                }
               }
        }
        echo $popup."</td>";
        echo "<td><font size=1>".$PF_LIEU."</font></td>
         <td><font size=1>".$PF_RESPONSABLE."</font></td>
         <td><font size=1>".$PF_COMMENT."</font></td>";
        if ( $disabled == "")  
         echo "<td><a href=del_personnel_formation.php?P_ID=".$P_ID."&PS_ID=".$PS_ID."&PF_ID=".$PF_ID.">
             <i class='fa fa-trash fa-lg' title='supprimer cette information'></i></a></td>";
        echo "</tr>";
    }

   echo "</table>";
}
else {
    echo "<p>Aucune information disponible pour la formation ".$DESCRIPTION."<br>";
    $action = "nothingyet";
    }
}

//=====================================================================
// ajouter/modifier une formation
//=====================================================================
if ( ( $disabled == "" ) 
    and (($action == 'add') or ( $action == 'update') or ($action == 'nothingyet')) ) {


// proposer le type de formation le plus approprié
if (($action == 'add') and ( $type == '0' )) {
    $query="select TF_CODE, count(*) as NB from personnel_formation 
            where P_ID=".$P_ID." and PS_ID=".$PS_ID." group by TF_CODE order by TF_CODE desc";
    $result=mysqli_query($dbc,$query);
    while ($row=@mysqli_fetch_array($result)) {
         $TF_CODE=$row["TF_CODE"];
         $NB=$row["NB"];
         if (( $TF_CODE == 'P' ) and ( $NB > 0 )) $type='I';
         if (( $TF_CODE == 'I' ) and ( $NB > 0 ))
             if ( $PS_RECYCLE == 1 ) $type='R';
             else $type='C';
    }
}

echo "<table cellspacing=0 border=0 >";

echo "<form name=demoform action='save_personnel_formation.php' method='POST'>";

echo "<input type=hidden name=P_ID value='".$P_ID."'>";
echo "<input type=hidden name=PS_ID value='".$PS_ID."'>";
echo "<input type=hidden name=PF_ID value='".$PF_ID."'>";
echo "<input type=hidden name=from value='".$from."'>";

if ( $action == 'update' ) {
    $cmt="Modifier";
    $query = "select pf.TF_CODE, pf.PF_COMMENT, DATE_FORMAT(pf.PF_DATE, '%d-%m-%Y') as PF_DATE, 
                pf.PF_RESPONSABLE, pf.PF_LIEU, pf.PF_DIPLOME, pf.E_CODE
                from personnel_formation pf, type_formation tf
                where pf.TF_CODE=tf.TF_CODE
                and pf.PF_ID=".$PF_ID;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    if (isset($_GET["type"])) $type=$_GET["type"];
    else $type=$row["TF_CODE"];
    $PF_COMMENT=$row["PF_COMMENT"];        
    $PF_DATE=$row["PF_DATE"];        
    $PF_RESPONSABLE=$row["PF_RESPONSABLE"];
    $PF_LIEU=$row["PF_LIEU"];
    $PF_DIPLOME=$row["PF_DIPLOME"];
    $E_CODE=$row["E_CODE"];
}
else {
    $cmt="Ajouter";
    $PF_COMMENT="";        
    $PF_DATE="";        
    $PF_RESPONSABLE="";
    $PF_LIEU="";
    $PF_DIPLOME="";
    $E_CODE="";
}

echo "<input type=hidden name=evenement value='".$E_CODE."'>";

echo "<tr><td class=TabHeader colspan=2 ><b>".$cmt." une information</td></tr>";
echo "<tr bgcolor=$mylightcolor><td width=180 align=right><b>Type de formation $asterisk</td>
           <td width=250>";
echo "<select id='tf' name='tf' title='saisir ici le type de formation' 
    onchange=\"changetype('".$P_ID."','".$PS_ID."',this.form.tf.value,'".$PF_ID."','".$action."' );\">";          
$query2="select TF_CODE, TF_LIBELLE from type_formation";
if ( $PS_RECYCLE == 0 ) $query2 .= " where TF_CODE not in ('R','M')";
$result2=mysqli_query($dbc,$query2);
while ($row2=@mysqli_fetch_array($result2)) {
        $_TF_CODE=$row2["TF_CODE"];
        $_TF_LIBELLE=$row2["TF_LIBELLE"];
        if     ( $_TF_CODE == $type ) $selected ='selected';
        else $selected ='';     
        echo "<option value=".$_TF_CODE." $selected>".$_TF_LIBELLE."</option>\n";
}
echo "</select></td>";       
echo "</tr>";

if ( $type == 'I' ) $cmt = 'Diplôme délivré le';
else $cmt = 'Date de formation';
echo "<tr bgcolor=$mylightcolor>
            <td align=right><b>".$cmt."</b> $asterisk</td>
            <td align=left>";

echo "<input type='text' size='10' name='dc' value='".$PF_DATE."' class='datepicker' data-provide='datepicker'
        placeholder='JJ-MM-AAAA'
        style='width:100px;'
        onchange='checkDate2(document.demoform.dc)'>";
echo "</tr>";

echo "<tr bgcolor=$mylightcolor>
            <td align=right>Lieu </td>
            <td align=left>";
echo "<input type='text' name='lieu' size='30' value=\"".$PF_LIEU."\">";
echo " </tr>";
if ( $type == 'I' ) $cmt = 'Diplôme délivré par';
else $cmt = 'Responsable de la formation';
echo "<tr bgcolor=$mylightcolor>
            <td align=right>".$cmt."</td>
            <td align=left>";
echo "<input type='text' name='resp' size='30' value=\"".$PF_RESPONSABLE."\">";
echo " </tr>";

if ( $type == 'I' ) {
    echo "<tr bgcolor=$mylightcolor>
            <td align=right>Numéro de diplôme</td>
            <td align=left>";
    echo "<input type='text' name='numdiplome' size='30' value=\"".$PF_DIPLOME."\">";
    echo " </tr>";
}
else echo "<input type=hidden name='numdiplome' value=''>";

echo "<tr bgcolor=$mylightcolor>
            <td align=right>Commentaire </td>
            <td align=left>";
echo "<input type='text' name='comment' size='30' value=\"".$PF_COMMENT."\">";
echo " </tr>";
echo "</table>";
}
}
//=====================================================================
// boutons enregistrement
//=====================================================================
echo "<p>";
if ( $disabled == "" ) {
    if ($action == 'list')
        echo " <input type='button' class='btn btn-default' value='ajouter' onclick=\"add('".$P_ID."','".$PS_ID."');\">";
    else
        echo " <input type='submit' class='btn btn-default' value='enregistrer'>";
}
if (($action == 'add') or ($action == 'update')) {
    if ( $from == 'qualif' ) 
        echo " <input type='button' class='btn btn-default' value='retour' onclick=\"redirect('".$P_ID."','".$PS_ID."');\">";
    else
        echo " <input type='button' class='btn btn-default' value='retour' onclick=\"redirect2('".$P_ID."');\">";
}        
else
    echo " <input type='button' class='btn btn-default' value='retour' onclick=\"redirect('".$P_ID."','0');\">";
echo "</form></div>";

writefoot();
?>

