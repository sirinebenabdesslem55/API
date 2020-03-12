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
check_all(54);
$id=$_SESSION['id'];
writehead();
get_session_parameters();
if ( check_rights($id,54,"0")) $thislevel=0;
else $thislevel=get_highest_section_where_granted($id,54);
if (isset ($_GET["aml"])) {
    $filter=$thislevel;
}
test_permission_level(54);
if (! check_rights($id,54,$filter)) $filter=$thislevel;
if (isset ($_GET["psid"])) $psid=intval($_GET["psid"]);
else $psid=0;

?>
<STYLE type='text/css'>
.categorie{color:$mydarkcolor; background-color:$mylightcolor; font-size:10pt;}
.type{color:$mydarkcolor; background-color:white; font-size:9pt;}
</STYLE>
<script>
$(document).ready(function(){
    $('[data-toggle="popover"]').popover();
});

function redirect(psid,filter){
    url="diplome_edit.php?psid="+psid+"&filter="+filter;
    self.location.href=url;
    return true
}

function checkNumber(element,defaultvalue,max)
{   
    var e = document.getElementById(element);
    var s = element.value;
    var re = /^([0-9]+)$/;
    if (! re.test(s) || s > max ) {
          alert ("Saisissez un nombre inférieur à "+ max+ ": '"+ s + "' ne convient pas.");
         element.value = defaultvalue;
         return false;
    }
    // All characters are numbers.
    return true;
}

function hideRow(k) {
    var select = document.getElementById('affichage['+k+']').value;
    if ( select == 9 ) {
        document.getElementById('perso['+k+']').style.display = '';
    }
    else {
        document.getElementById('perso['+k+']').style.display = 'none';
    }
}

function changeStatus() { 
    what=document.getElementById('image');
    document.getElementById("imageform").submit();
}

</script>
</head>
<?php

$help="Le diplôme doit être imprimé sur une page au format A4 (210 mm x 297 mm), format paysage.
Le champ 'actif' indique si le champ doit être imprimé.
La taille de caractère, ainsi que la police et le style peuvent être définis.
Le champ x correspond à l'abscisse, distance horizontale en mm à partir de la gauche de la feuille. Valeurs de x entre 0 et 297).
Le champ y correspond à l'ordonnée, distance verticale en mm à partir du haut de la feuille. Valeurs de x entre 0 et 210.
Si affichage 'personnalisé' est choisi alors les données saisies dans 'Personnalisation' seront imprimées.";

echo "<body>";
echo "<div align=center class='table-responsive'>
    <table class='noBorder'><tr><td><i class='fa fa-certificate fa-3x' style='color:purple;'></i></td>
    <td><font size=4><b>Impression des diplômes</b> 
    <a href='#' data-toggle='popover' title='Comment bien imprimer les diplômes' data-trigger='hover' data-content=\"".$help."\"><i class='fa fa-question-circle fa-lg' title='aide'></i></a>
    </td></tr></table>";
 
$actif=array();
$affichage=array();
$style=array();
$police=array();
$pos_x=array();
$pos_y =array();
$annexe=array();
$style_org=array("Normal","Gras","Italique","Gras et Italique");
$taille_org=array(8,9,10,11,12,14,16,18);
$police_org=array("Courrier","Arial","Times");
extract($_POST); 

if (isset($_POST["action"])) $action=$_POST["action"];
else $action="show";

//============================================================
//   Reinitialiser
//============================================================
if (isset($_GET["reinit"])) {
    if ( $filter > 0 and $psid > 0 and check_rights($id,54,$filter)) {
        $query="delete from diplome_param where PS_ID=".$psid." and S_ID =".$filter;
        $result=mysqli_query($dbc,$query);
    }
}

//============================================================
//   Save info and upload file.
//============================================================
//Allowable file Mime Types. Add more mime types if you want
$FILE_MIMES = array('image/jpeg','image/jpg','image/x-png','image/pjpeg','image/gif','image/png');
//Allowable file ext. names. you may add more extension names.
$FILE_EXTS  = array('.jpg','.png','.gif');
$upload_dir=$filesdir."/diplomes";
$msgstring="";

//echo "<pre>";
//print_r($_POST);
//echo "</pre>";

// enregistrement dans la table
if ($action == "save") {
    $psid=intval($_POST["psid"]);
    $query="select TYPE from poste where PS_ID=".$psid;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $type=str_replace(" ", "",$row["TYPE"]);
    $file_name="";
    
    if ( isset ($_FILES['userfile']) and check_rights($id,54,"0") and $filter==0 ) {
        if ($_FILES['userfile']['size'] <> 0) {
            $error=0;
            $temp_name = $_FILES['userfile']['tmp_name'];
            $file_type = $_FILES['userfile']['type'];
            $file_ext = strtolower(substr($file_name,strrpos($file_name,".")));
            $file_path = $upload_dir."/".$type.".jpg";
           
            //File Size Check
            if ( $_FILES['userfile']['size'] > $MAX_SIZE) {
                 $msgstring = $MAX_SIZE_ERROR;
                 $error=1;
            }
            //File Type/Extension Check
            else if (!in_array($file_type, $FILE_MIMES) && !in_array($file_ext, $FILE_EXTS)) {
                   $msgstring = "Attention, les fichiers du type ($file_type) sont interdits.";
                   $error=1;
            }
            else {
                   // create upload subdir
                   if (!is_dir($upload_dir)) {
                        if (!mkdir($upload_dir))
                            die ("Le répertoire d'upload n'existe pas et sa création a échoué.");
                        if (!chmod($upload_dir,0755))
                            die ("Echec lors de la mise à jour des permissions.");
                   }
                   if (! $result  =  move_uploaded_file($temp_name, $file_path)) {
                      $msgstring ="Une erreur est apparue lors de l'upload du fichier.";
                      $error=1;
                   }
                   if (!chmod($file_path,0777)) {
                        $msgstring = "Echec lors de la mise à jour des permissions.";
                      $error=1;
                   }
            }
        }
    }
    echo "<font color=red>".$msgstring."</font>";
    
    for($i=1; $i <= $numfields_org ; $i++) { 
        
        $q1="select count(1) as NB from diplome_param where PS_ID=".$psid." and FIELD=".$i." and S_ID=".$filter;
        $r1=mysqli_query($dbc,$q1);
        $row=mysqli_fetch_array($r1);
        
        if ( isset ($actif[$i])) $is_actif = intval($actif[$i]);
        else $is_actif = 0;
        
        if ( $row["NB"] == 0 )
            $query="insert into diplome_param 
                (PS_ID,S_ID,FIELD,ACTIF,AFFICHAGE,TAILLE,STYLE,
                 POLICE,POS_X,POS_Y,
                 ANNEXE) values (".
                $psid.",".$filter.",".$i.",".intval($is_actif).",".intval($affichage[$i]).",".intval($aff_taille[$i]).",".intval($aff_style[$i]).","
                .intval($aff_police[$i]).",".intval($pos_x[$i]).",".intval($pos_y[$i]).", 
                \"".secure_input($dbc,str_replace("\"","'",$annexe[$i]))."\")";
        else     
            $query="UPDATE diplome_param SET ACTIF=".$is_actif.", 
                AFFICHAGE =".intval($affichage[$i]).",
                TAILLE =".intval($aff_taille[$i]).", 
                STYLE=".intval($aff_style[$i]).", 
                POLICE =".intval($aff_police[$i]).", 
                POS_X =".intval($pos_x[$i]).", 
                POS_Y=".intval($pos_y[$i]).", 
                ANNEXE=\"".secure_input($dbc,str_replace("\"","'",$annexe[$i]))."\"
                WHERE PS_ID=".$psid."
                and FIELD=".$i."
                and S_ID=".$filter;
        mysqli_query($dbc,$query);
    }
}


echo "<form enctype='multipart/form-data' name='imageform' id='imageform' action='diplome_edit.php' method='POST' >";    
echo "<table class='noBorder'><tr>";

//filtre section
if ( check_rights($id,24)) $disabled="";
else $disabled="disabled";

echo "<tr>";
echo "<td>".choice_section_order('diplome_edit.php')."</td>";
echo "<td><select id='filter' name='filter' $disabled
        onchange=\"redirect(document.getElementById('selectdiplome').value,document.getElementById('filter').value);\">";
display_children2(-1, 0, $filter, $nbmaxlevels -1, $sectionorder);
echo "</select></td> ";
echo "</tr>";

// filtre compétence
echo "<td>Diplôme pour la compétence</td>
    <td><select id='selectdiplome' name='selectdiplome' 
      onchange=\"redirect(document.getElementById('selectdiplome').value,document.getElementById('filter').value);\">";
$query="select PS_ID, TYPE, DESCRIPTION, PS_PRINT_IMAGE from poste 
                where PS_PRINTABLE=1
              and PS_DIPLOMA=1";
if ( $filter > 0 ) $query .= " and PS_NATIONAL=0";
$query .= " order by TYPE";

$result=mysqli_query($dbc,$query);
$curtype="";
$print_image=0;
while (custom_fetch_array($result)) {
    if ( $psid == 0 ) $psid = $PS_ID;
    if ( $psid == $PS_ID ) {
        $selected='selected';
        $curtype=$TYPE;
        $print_image = $PS_PRINT_IMAGE;
    }
    else $selected='';
     echo "<option value='".$PS_ID."' $selected>".$TYPE." - ".$DESCRIPTION."</option>";
}
echo "</select></td></tr>";

$default=$filesdir."/diplomes/diplome.jpg";
$file=$filesdir."/diplomes/".str_replace(" ", "",$curtype).".jpg";
$btn_class='btn-warning';

if ( file_exists($file)){
    $link="<a href=showfile.php?diplome=1&file=".str_replace(" ", "",$curtype).".jpg&section=0&evenement=0&message=0
            title='Télécharger image du diplôme'> <img src=".$file." class='img-thumbnail' width='120'></a>";
   
    $btn_class='btn-success';
    $t='Choisir une autre image';
}
else if ( file_exists($default)) {
    $link="<a href=showfile.php?diplome=1&file=diplome.jpg&section=0&evenement=0&message=0 
            title='Télécharger image du diplôme'><img src=".$default." class='img-thumbnail' width='120'></a>";
    $t='Choisir une image personnalisée pour ce diplôme';
}
else {
    $link=$curtype;
    $btn_class='btn-danger';
    $t='Choisir une image pour ce diplôme';
}

echo "<tr><td>Image diplôme <span class='badge'  style='background-color:purple;'>".str_replace(" ", "",$curtype)."</span></td><td>".$link."</td></tr>";
if ( $filter == 0 ) {
    echo "<tr><td>Modifier image</td><td><label class='btn $btn_class btn-file' title=\"".$t."\">
    <i class='fa fa-image fa-lg'></i><input type='file' id='userfile' name='userfile' style='display:none;' onchange='javascript:changeStatus();'>
    </label></td></tr>";
    if ( $print_image == 1 ) $i="<i class='fa fa-check-square fa-lg' style='color:green;' title='Image de diplôme imprimée, voir paramétrage des compétences'></i><span class='small'> Le diplôme sera obligatoirement imprimée sur du papier blanc, pas de papier pré-imprimé possible</span>";
    else $i="<i class='fa fa-ban fa-lg' style='color:red;'  title='Image de diplôme NON imprimée, voir paramétrage des compétences'></i><span class='small'> Le diplôme sera imprimée sur du papier pré-imprimé, ou sous forme d'aperçu avant impression sur papier blanc</span></i>";
    echo "<tr><td>Imprimer l'image</td><td>".$i."</td></tr>";
}
else 
    echo "<tr><td></td><td class=small>La modification de l'image liée à ce diplôme n'est possible que au niveau National</td></tr>";
echo "</table>";


for($i=1; $i <= $numfields_org; $i++) {
    
    $query="select PS_ID,S_ID, FIELD,ACTIF,AFFICHAGE,TAILLE,STYLE,
                 POLICE,POS_X,POS_Y, ANNEXE from diplome_param 
            where PS_ID=".$psid." and FIELD=".$i." and S_ID in (0,".$filter.")
            order by S_ID desc";
    $result=mysqli_query($dbc,$query);
    $data = @mysqli_fetch_array($result); 
 
    // bloquer certains champs au niveau local
    $modifiable=true;
    $local_disabled="";
    if ( $filter > 0 ) {
        $modifiable=false;
        $local_disabled="disabled";
    }
    
    echo "<p>
         <table cellspacing=0 border=0>
         <tr class=TabHeader >
         <td colspan=5>Impression champ N°:".$i."</td></tr>
         <tr bgcolor=".$mylightcolor.">";
         
    $local_disabled2=$local_disabled;
    if ($data["ACTIF"]=='1') $checked='checked'; else $checked='';
    if ( ! $modifiable and $data["ACTIF"] == 0 ) $local_disabled2=""; 
    if ( ! $modifiable )
        echo "<input type='hidden' name='actif[".$i."]' id='actif[".$i."]' value=".$data["ACTIF"].">";
    echo "<td width='120' >
        <input type='checkbox' name='actif[".$i."]' id='actif[".$i."]' value=1 $checked $local_disabled2/> Actif </td>";
        
        
    echo "<td width='120'>Taille : "; 
    echo "<select id='aff_taille[".$i."]' name='aff_taille[".$i."]'>";
    for($j=0; $j != 8; $j++) { 
        echo "<option value='".$j."'";
         if ($data["TAILLE"]==$j) echo " selected='selected'";
        echo'>'.$taille_org[$j].'</option>';
    };
    echo "</select></td>";
    
    echo "<td width='278' > Affichage : ";
    
    echo "<select name='affichage[".$i."]' id='affichage[".$i."]' onchange=\"hideRow(".$i.");\" $local_disabled>";
    
    $query1="select FIELD, FIELD_NAME, CATEGORY from diplome_param_field order by CATEGORY, DISPLAY_ORDER";
    $result1=mysqli_query($dbc,$query1);
    
    $cat="";
    while ( custom_fetch_array($result1)) {
        if ( $cat <> $CATEGORY ) {
            echo "<OPTGROUP class='categorie' LABEL='".$CATEGORY."'>";
            $cat = $CATEGORY;
        }
        echo "<option value=".$FIELD;
        if ($data["AFFICHAGE"]==$FIELD) echo " selected='selected'";
        echo ">".$FIELD_NAME."</option>";
    }
    echo "</select>";
    if ( ! $modifiable )
        echo "<input type='hidden' name='affichage[".$i."]' id='affichage[".$i."]' value=".$data["AFFICHAGE"].">";
    echo "</td>";
    
    echo "<td width='160'> Style :<select name='aff_style[".$i."]' id='aff_style[".$i."]'>";
    for($j=0; $j != 4; $j++) { 
        echo '<option value="'.$j.'"';
         if ($data["STYLE"]==$j) echo " selected='selected'";
        echo ">".$style_org[$j]."</option>";
    };
    echo "</select></td>";
    
    echo "<td width='160'>Police :  <select name='aff_police[".$i."]' id='aff_police[".$i."]'>";
    for($j=0; $j != 3; $j++) { 
        echo "<option value=".$j;
         if ($data["POLICE"]==$j) echo " selected='selected'";
        echo ">".$police_org[$j]."</option>";
    }
    echo"</select>   
    </td>
    </tr>
    <tr bgcolor=".$mylightcolor.">";
    
    echo "<td>Position X : <input name=pos_x[".$i."] id=pos_x_".$i." type='text' size='5' maxlength='5'
    title='Choisir une valeur comprise entre 0 et 297'
    onchange=\"checkNumber(pos_x_".$i.",'".$data["POS_X"]."',297);\"
    value='".$data["POS_X"]."'/></td>";
    
    echo "<td >Position Y : <input name=pos_y[".$i."] id=pos_y_".$i." type='text' size='5' maxlength='5'
    title='Choisir une valeur comprise entre 0 et 210'   
    onchange=\"checkNumber(pos_y_".$i.",'".$data["POS_Y"]."',210);\" 
    value='".$data["POS_Y"]."'/></td>";
    
    if ( $data["AFFICHAGE"] == 9 ) $style="";
    else $style="style='display:none'";
    echo "<td colspan='3' ><span name='perso[".$i."]' id='perso[".$i."]' $style>Personnalisation:
    <input name='annexe[".$i."]' id='annexe[".$i."]' type='text' size='50' maxlength='50'  
    value=\"".$data["ANNEXE"]."\"/></span></td>";

    echo "</tr></table></td></tr></table>";
}
 
echo "<p><input type='hidden' name='action' value='save'>
      <input type='hidden' name='psid' value='".$psid."'>
      <input type='submit'  class='btn btn-default' value='Sauver'>
      <input type='button'  class='btn btn-default' value='Retour' onclick='javascript:self.location.href=\"parametrage.php\";'>";
if ( $filter > 0 )
    echo " <input type='button'  class='btn btn-default' value='Réinitialiser' title='Remplacer ce paramétrage spécifique par le paramétrage national'
    onclick='javascript:self.location.href=\"diplome_edit.php?filter=".$filter."&psid=".$psid."&reinit=1\";'>";
echo "</form>
</div>";

writefoot();

?>
