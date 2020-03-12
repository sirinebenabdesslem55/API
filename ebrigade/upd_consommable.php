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
check_all(42);
$id=$_SESSION['id'];
get_session_parameters();

if ( check_rights($id, 24)) $section='0';
else $section=$_SESSION['SES_SECTION'];


$suggestedsection=$section;
if ( check_rights($id, 71, $filter)) $suggestedsection=$filter;
if (isset ($_GET["section"])) $suggestedsection=$_GET["section"];

if ( isset($_GET["from"])) $from=$_GET["from"];
else $from='default';

if ( isset($_GET["action"])) $action=$_GET["action"];
else $action='update';

if ( isset($_GET["cid"])) $C_ID=intval($_GET["cid"]);
else $C_ID=0;

// test permission visible
if ( ! check_rights($id,40)) {
    $his_section=get_section_of_consommable($C_ID);
    if ( ! check_rights($id,42,$his_section )) {
        $mysectionparent=get_section_parent($section);
        if ( $his_section <> $mysectionparent and get_section_parent($his_section) <> $mysectionparent )
                check_all(40);
    }
}

writehead();
echo "<STYLE type='text/css'>
.categorie{color:$mydarkcolor; background-color:$mylightcolor; font-size:10pt;}
.type{color:$mydarkcolor; background-color:white; font-size:9pt;}
</STYLE>
<script type='text/javascript' src='js/checkForm.js'></script>
<script type='text/javascript' src='js/consommable.js'></script>
</head>
<body>";



//=====================================================================
// affiche la fiche consommable
//=====================================================================

if ( $action =='update' ) {
    $query="select c.C_ID, c.S_ID, c.TC_ID, c.C_DESCRIPTION, c.C_NOMBRE, c.C_MINIMUM, DATE_FORMAT(c.C_DATE_ACHAT, '%d-%m-%Y') as C_DATE_ACHAT, 
        DATE_FORMAT(c.C_DATE_PEREMPTION, '%d-%m-%Y') as C_DATE_PEREMPTION,
        tc.TC_DESCRIPTION, tc.TC_CONDITIONNEMENT, tc.TC_UNITE_MESURE, tc.TC_QUANTITE_PAR_UNITE, tc.TC_PEREMPTION, c.C_LIEU_STOCKAGE,
        tum.TUM_DESCRIPTION,
        tco.TCO_DESCRIPTION,
        cc.CC_NAME, cc.CC_CODE, cc.CC_IMAGE,
        s.S_CODE
        from consommable c, type_consommable tc,  categorie_consommable cc, type_conditionnement tco, type_unite_mesure tum, section s
        where c.TC_ID = tc.TC_ID
        and tc.CC_CODE = cc.CC_CODE
        and tc.TC_CONDITIONNEMENT = tco.TCO_CODE
        and tc.TC_UNITE_MESURE = tum.TUM_CODE
        and s.S_ID=c.S_ID
        and c.C_ID = ".$C_ID;

    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
    $C_NOMBRE = intval($C_NOMBRE);
    $C_MINIMUM = intval($C_MINIMUM);

}
else {
    $S_ID = $_SESSION['SES_SECTION'];
    $C_ID = 0;
    $TC_ID = intval($type_conso);
    if (  $type_conso == 'ALL' ) 
        $query="select TC_ID, TC_DESCRIPTION, CC_CODE from type_consommable
         where TC_ID=(select min(TC_ID) from type_consommable )";
    else if ( $TC_ID == 0 ) 
        $query="select TC_ID, TC_DESCRIPTION, CC_CODE from type_consommable
         where TC_ID=(select min(TC_ID) from type_consommable where CC_CODE='".$type_conso."')";
    else 
        $query="select TC_ID, TC_DESCRIPTION, CC_CODE from type_consommable
        where TC_ID=".$TC_ID;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $TC_ID = $row["TC_ID"];
    $TC_DESCRIPTION = $row["TC_DESCRIPTION"];
    $CC_CODE = $row["CC_CODE"];
    $C_MINIMUM = "";
    $C_NOMBRE = 1;
    $C_DATE_ACHAT=date('d-m-Y');
    $C_DATE_PEREMPTION='';
    $C_DESCRIPTION='';
    $C_LIEU_STOCKAGE='';
}

// permettre les modifications si je suis habilit� sur la fonctionnalit� 71 au bon niveau
// ou je suis habilit� sur la fonctionnalit� 24 )
if (check_rights($id, 71,"$S_ID")) $responsable_consommable=true;
else $responsable_consommable=false;

if ( $responsable_consommable ) $disabled=""; 
else $disabled="disabled";

//=====================================================================
// cas changement de type
//=====================================================================

if ( isset($_GET["TC_ID"])) $TC_ID=intval($_GET["TC_ID"]);
if ( isset($_GET["C_NOMBRE"])) $C_NOMBRE=intval($_GET["C_NOMBRE"]);
if ( isset($_GET["C_MINIMUM"])) $C_MINIMUM=intval($_GET["C_MINIMUM"]);
if ( isset($_GET["C_DESCRIPTION"])) $C_DESCRIPTION=($_GET["C_DESCRIPTION"]);
if ( isset($_GET["S_ID"])) $S_ID=intval($_GET["S_ID"]);
if ( isset($_GET["C_DATE_ACHAT"])) $C_DATE_ACHAT=$_GET["C_DATE_ACHAT"];
if ( isset($_GET["C_DATE_PEREMPTION"])) $C_DATE_PEREMPTION=$_GET["C_DATE_PEREMPTION"];
if ( isset($_GET["C_LIEU_STOCKAGE"])) $C_LIEU_STOCKAGE=$_GET["C_LIEU_STOCKAGE"];
if ( isset($_GET["operation"])) $action=$_GET["operation"];

$query="select tc.TC_DESCRIPTION, tc.CC_CODE, cc.CC_IMAGE, tc.TC_PEREMPTION from categorie_consommable cc, type_consommable tc
        where cc.CC_CODE = tc.CC_CODE
        and tc.TC_ID=".$TC_ID;
$result=mysqli_query($dbc,$query);
$row=@mysqli_fetch_array($result);
$CC_IMAGE = $row["CC_IMAGE"];
$TC_DESCRIPTION = $row["TC_DESCRIPTION"];
$CC_CODE = $row["CC_CODE"];
$TC_PEREMPTION = $row["TC_PEREMPTION"];
if ( $CC_IMAGE == '' ) $CC_IMAGE="inventory.png";

//=====================================================================
// afficher fiche
//=====================================================================

echo "<div align=center><table class='noBorder'>
      <tr><td width = 60 ><i class='fa fa-".$CC_IMAGE." fa-3x' style='color:saddlebrown;'></i></td><td>
      <font size=4><b>".$CC_CODE." - ".$TC_DESCRIPTION."</b></font></td></tr></table>";

echo "<form name='consommable' action='save_consommable.php'>";
echo "<input type='hidden' name='C_ID' value='$C_ID'>";
echo "<input type='hidden' name='operation' value='".$action."'>";

//=====================================================================
// ligne 1
//=====================================================================

echo "<p><table cellspacing=0 border=0>";
echo "<tr class='TabHeader'>
            <td class=TabHeader colspan=2>informations produit</td>
      </tr>";

//=====================================================================
// type de consommable
//=====================================================================

echo "<tr bgcolor=$mylightcolor >
         <td width=150><b>Type</b> $asterisk</td>
         <td ><select id='TC_ID' name='TC_ID' $disabled class=smallcontrol2
         onchange=\"changedType('".$C_ID."',document.getElementById('TC_ID').value,document.getElementById('quantity').value,document.getElementById('C_DESCRIPTION').value,document.getElementById('S_ID').value,document.getElementById('C_DATE_ACHAT').value,document.getElementById('C_DATE_PEREMPTION').value,'".$action."');\">";

$query2="select tc.TC_ID, tc.CC_CODE, cc.CC_NAME,tc.TC_DESCRIPTION,tc.TC_CONDITIONNEMENT,tc.TC_UNITE_MESURE,
            tc.TC_QUANTITE_PAR_UNITE , tum.TUM_CODE, tum.TUM_DESCRIPTION, tco.TCO_DESCRIPTION, tco.TCO_CODE
            from type_consommable tc, categorie_consommable cc, type_conditionnement tco, type_unite_mesure tum
            where cc.CC_CODE = tc.CC_CODE
            and tco.TCO_CODE = tc.TC_CONDITIONNEMENT
            and tum.TUM_CODE = tc.TC_UNITE_MESURE
            order by tc.CC_CODE,tc.TC_DESCRIPTION asc";
$result2=mysqli_query($dbc,$query2);
if ( $catconso == 'ALL' ) $selected="selected ";
else $selected ="";
$prevCat='';
while ($row=@mysqli_fetch_array($result2)) {
    $NEWTC_ID=$row["TC_ID"];
    $NEWCC_CODE=$row["CC_CODE"];
    $NEWCC_NAME=$row["CC_NAME"];
    $NEWTC_DESCRIPTION=ucfirst($row["TC_DESCRIPTION"]);
    $NEWTCO_DESCRIPTION=$row["TCO_DESCRIPTION"];
    $NEWTCO_CODE=$row["TCO_CODE"];
    $NEWTUM_DESCRIPTION=$row["TUM_DESCRIPTION"];
    $NEWTUM_CODE=$row["TUM_CODE"];
    $NEWTC_QUANTITE_PAR_UNITE=$row["TC_QUANTITE_PAR_UNITE"];
    if ( $NEWTC_QUANTITE_PAR_UNITE > 1 ) $NEWTUM_DESCRIPTION .="s";
    if ( $prevCat <> $NEWCC_CODE ){
           echo "<optgroup class='categorie' label=\"".$NEWCC_NAME."\" />\n";
        $prevCat=$NEWCC_CODE;
    }
    if ($NEWTC_ID == $TC_ID ) $selected="selected ";
    else $selected ="";
    if ( $NEWTCO_CODE == 'PE' ) $label =  $NEWTC_DESCRIPTION." (".$NEWTUM_DESCRIPTION."s)";
    else if ( $NEWTUM_CODE <> 'un' or  $NEWTC_QUANTITE_PAR_UNITE <> 1 ) $label = $NEWTC_DESCRIPTION." (".$NEWTCO_DESCRIPTION." ".$NEWTC_QUANTITE_PAR_UNITE." ".$NEWTUM_DESCRIPTION.")";
    else $label = $NEWTC_DESCRIPTION;
    echo "<option class='conso' value='".$NEWTC_ID."' $selected>".$label."</option>\n";
}
echo "</select></td>";

//=====================================================================
// nombre
//=====================================================================

if ( $C_NOMBRE < $C_MINIMUM ) $class="class=red12";
else $class="class=green12";
    
echo "<tr bgcolor=$mylightcolor >
            <td $class><b>Quantit� en stock</b> $asterisk</td>
            <td align=left>
            <input type='text' name='quantity' id='quantity' maxlength='7' size='6' value='$C_NOMBRE' onchange='checkNumber(form.quantity,\"$C_NOMBRE\")' $disabled></td>";        
echo "</tr>";

//=====================================================================
// stock minimum
//=====================================================================
 
echo "<tr bgcolor=$mylightcolor>
            <td ><b>Stock minimum</b> $asterisk</td>
            <td align=left class=small>
            <input type='text' name='minimum' id='minimum'
            maxlength='7' size='6' value='$C_MINIMUM' onchange='checkNumber(form.minimum,\"$C_MINIMUM\")' $disabled>
            Commander si le stock est inf�rieur � ce stock minimum</td>";        
echo "</tr>";

//=====================================================================
// mod�le
//=====================================================================

echo "<tr bgcolor=$mylightcolor >
            <td><b>Description</b> $asterisk</td>
            <td align=left><input type='text' name='C_DESCRIPTION' id='C_DESCRIPTION' maxlength='25' size='25' value=\"$C_DESCRIPTION\" $disabled>";        
echo "</td>
      </tr>";

//=====================================================================
// section
//=====================================================================

echo "<tr bgcolor=$mylightcolor >
            <td><b>Section</b> $asterisk</td>
            <td align=left>";
echo "<select id='S_ID' name='S_ID' $disabled class=smallcontrol2>"; 

if ( $responsable_consommable ) {
       $mysection=get_highest_section_where_granted($id,71);
       if ( $mysection == '' ) $mysection=$S_ID;
       if ( ! is_children($section,$mysection)) $mysection=$section;
}
else $mysection=$S_ID;
   
$level=get_level($mysection);
if ( $level == 0 ) $mycolor=$myothercolor;
elseif ( $level == 1 ) $mycolor=$my2darkcolor;
elseif ( $level == 2 ) $mycolor=$my2lightcolor;
elseif ( $level == 3 ) $mycolor=$mylightcolor;
else $mycolor='white';
$class="style='background: $mycolor;'";
   
if ( isset($_SESSION['sectionorder']) ) $sectionorder=$_SESSION['sectionorder'];
else $sectionorder=$defaultsectionorder;

if ( $action == 'update' ) {
    if ( check_rights($id, 24))
        display_children2(-1, 0, $S_ID, $nbmaxlevels, $sectionorder);
    else {
        echo "<option value='$mysection' class=smallcontrol $class >".
                  get_section_code($mysection)." - ".get_section_name($mysection)."</option>";
        if ( "$S_ID" <> "$mysection" ) {
            if (! in_array("$S_ID",explode(',' ,get_family("$mysection"))))
                echo "<option value='$S_ID' selected>".
                  get_section_code("$S_ID")." - ".get_section_name("$S_ID")."</option>";
        }
        if ( $disabled == '') display_children2($mysection, $level +1, $S_ID, $nbmaxlevels);
    }
}
else {
    if ( check_rights($id, 24))
        display_children2(-1, 0, $suggestedsection, $nbmaxlevels, $sectionorder);
    else { 
        echo "<option value='$suggestedsection' $class >".get_section_code($suggestedsection)." - ".get_section_name($suggestedsection)."</option>";
        display_children2($section, $level +1, $suggestedsection, $nbmaxlevels);
    }
}

echo "</select></td> ";
echo "</tr>";


//=====================================================================
// dates achat
//=====================================================================

echo "<input type='hidden' name='dc0' value='".getnow()."'>";

echo "<tr bgcolor=$mylightcolor >
            <td ><b>Date achat</b></font></td>
            <td align=left>
            <input type='text' name='C_DATE_ACHAT' id='C_DATE_ACHAT' maxlength='10' size='10' placeholder='JJ-MM-AAAA' value='".$C_DATE_ACHAT."' $disabled 
            class='datepicker' data-provide='datepicker' onchange='checkDate2(form.C_DATE_ACHAT)'>
        </td>
    </tr>";    
        

//=====================================================================
// dates de p�remption
//=====================================================================
if ( $TC_PEREMPTION == 1 ) {
    $class='blue12';
    if ( $C_DATE_PEREMPTION <> '' ) {
        if ( my_date_diff(getnow(),$C_DATE_PEREMPTION) < 0 ) $class='red12';
        else if ( my_date_diff(getnow(),$C_DATE_PEREMPTION) < 30 ) $class='orange12';
        else $class='green12';
    }

    echo "<tr bgcolor=$mylightcolor >
            <td class=$class>Date de p�remption</td>
            <td align=left>
            <input type='text' name='C_DATE_PEREMPTION' id='C_DATE_PEREMPTION' maxlength='10' size='10' placeholder='JJ-MM-AAAA' value='".$C_DATE_PEREMPTION."' $disabled 
            class='datepicker' data-provide='datepicker' onchange='checkDate2(form.C_DATE_PEREMPTION)'>
            </td>
    </tr>";    
}
else echo "<input type='hidden' name='C_DATE_PEREMPTION' id='C_DATE_PEREMPTION' value=''>";

//=====================================================================
// lieu de stockage
//=====================================================================

echo "<tr bgcolor=$mylightcolor >
            <td><b>Lieu Stockage</b></td>
            <td align=left><input type='text' name='C_LIEU_STOCKAGE' id='C_LIEU_STOCKAGE' maxlength='25' size='25' value=\"$C_LIEU_STOCKAGE\" $disabled>";        
echo "</td>
      </tr>";


echo "</table></tr></table>";
 
echo "<p><table class='noBorder'><tr>"; 
if ( $disabled == "") {
    echo "<td><input type='submit' class='btn btn-default' value='sauver'> ";
    echo "</form></td>";
}

if ( check_rights($id, 71, "$S_ID")) {
    echo "<form name='consommable2' action='save_consommable.php'>";
    echo "<input type='hidden' name='C_ID' value='$C_ID'>";
    echo "<input type='hidden' name='S_ID' value='$S_ID'>";
    echo "<input type='hidden' name='operation' value='delete'>";
    echo "<td><input type='submit' class='btn btn-default' value='supprimer'> ";
    echo "</form></td>";
}

if ( $C_ID > 0 ) {
    $query="select count(1) as NB from evenement_consommable where C_ID=".$C_ID;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $NB = $row["NB"];

    if ( $NB > 0 ) {
        if ( $NB == 1 ) $t="1 utilisation";
        else $t=$NB." utilisations";
        echo "<td>
        <input type='button' class='btn btn-default' value='$NB utilisations' title='Voir toutes les utilisations qui ont �t� enregistr�es pour ce produit'
        onclick=\"redirect('evenement_consommable.php?cid=".$C_ID."');\" >
        </td>";
    }
}

echo "<td>";
if ( $from == 'export' ) {
    echo "<input type=submit class='btn btn-default' value='fermer cette page' onclick='fermerfenetre();'> ";
}
else if ( $from == 'evenement' ) {
    echo "<input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\">";
}
else {
    echo "<input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"javascript:bouton_redirect('consommable.php');\">";
}

echo "</td></tr></table></div>";

writefoot();
?>
