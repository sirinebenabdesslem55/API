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
check_all(27);
get_session_parameters();

@set_time_limit($mytimelimit);

// parameters
$affichage=(isset($_GET['affichage'])?$_GET['affichage']:'ecran');
$dateJ = date("d-m-Y",mktime(0,0,0,date("m"),date("d"),date("Y")));
if ( $affichage == 'ecran' ) 
    writehead();


// functions
function NettoyerTexte($txt){
    return strip_tags(str_replace("\n"," ",str_replace("\r"," ",$txt)));
}

$section=$filter;
include_once ("export-sql.php");
$tab = array();

// process query
if(isset($table) && isset($select) && isset($_GET['show'])){
    $sql = "SELECT $select
    FROM $table";
    $sql .= (isset($where)?(($where!="")? "
    WHERE $where ":""):"");
    $sql .= (isset($groupby)?(($groupby!="")? "
    GROUP BY $groupby ":""):"");
    $sql .= (isset($orderby)?(($orderby!="")? "
    ORDER BY $orderby ":""):"");

    $result = mysqli_query($dbc,$sql);
    if ( ! $result ) {
        if( strpos( mysqli_error($dbc), "Incorrect key file for table '/tmp" ) !== false ) $errmsg ="Veuillez re-essayer avec une plage de dates plus petite.";
        else $errmsg = "L'erreur suivante est apparue: <p><span style='color:red;font-family:Courier;'>".strip_tags(mysqli_error($dbc))."</span><p> Veuillez transmettre ce message à votre administrateur.";
        write_msgbox("Erreur",$error_pic,"Impossible d'extraire les données du reporting:<p><b>$export_name</b><p>".$errmsg."
                    <p><input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\">",30,30); 
        write_debugbox($sql);
        die();       
    }
    $numlig = mysqli_num_rows($result);
    $numcol = mysqli_num_fields($result);
    // Titres
    while ($finfo = mysqli_fetch_field($result)) {
        // attention on doit commencer a zero mais currentfield commence a 1
        $currentfield = mysqli_field_tell($result) - 1;
        $tab[0][$currentfield] = $finfo->name;
    }
    // Données
    $nolig=1;
    while ( $row = mysqli_fetch_array($result)) {
        for($col = 0;$col<$numcol;$col++){
            //$tab[$nolig][$col] = mysqli_result($result, $lig, $col);
            $tab[$nolig][$col] = $row[$col];
        }
        $nolig++;
    }
}
// includes
if(substr($exp,0,4)=="tcd_" && in_array($affichage,array('xls')))
    include("export-tcd.php");
elseif ($affichage == "xls") 
    include("export-xls.php");
elseif ($affichage == "txt")
    include("export-txt.php");

// display
if ( $affichage == 'ecran' ) {
    test_permission_level(27);
    ?>
    <link rel="stylesheet" href="js/tablesorter/themes/blue/style.css" type="text/css" media="print, projection, screen" />
    <script type="text/javascript" src="js/checkForm.js"></script>
    <script type="text/javascript" src="js/tablesorter/jquery.tablesorter.js"></script>
    <script type="text/javascript" src="js/export.js"></script> 
    </head>

    <?php
    echo "<body>";

    // form
    echo "<div align=center class='table-responsive'><div class='noprint'><font size=4><b>Reporting et extractions</b></font></div><p>";
    echo "<form name='frmExport' action='' >";
    echo "<table class='noBorder'>";
    echo "<tr>";

    echo "<td width=100>".choice_section_order('export.php')."</td>
        <td width=500><select name='filter' id='filter'
        onchange=\"orderfilter(document.getElementById('filter').value,'".$subsections."','".$exp."','".$dtdb."','".$dtfn."','".$yearreport."','".$type_event."')\">";
    display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
    echo "</select>";
        "</td></tr>";
    if ($subsections==1) $checked='checked'; 
    else $checked='';
    echo "<tr><td><font size=1>inclure $sous_sections</font></td><td>
            <input type='checkbox' name='subsections' id='subsections' value=1 $checked
            onClick=\"orderfilter2(document.getElementById('filter').value, this,'".$exp."','".$dtdb."','".$dtfn."','".$yearreport."','".$type_event."')\"></td></tr>";
    echo "<tr><td>Report</td><td>
         <select name='exp' id='exp' 
         onchange=\"orderfilter('".$filter."','".$subsections."',document.getElementById('exp').value,'".$dtdb."','".$dtfn."','".$yearreport."');showdates(document.getElementById('exp').value,'info','".$yearreport."','".$type_event."');\";>";
    echo (isset($OptionsExport)?$OptionsExport:"<option value=''>--- Aucun état de synthèse disponible ---</option>");
    echo "</select>";
    echo "</td></tr>";

    //------------------------------
    // type evenement pour certains
    //------------------------------
    if ( $exp == '' ) $second_char='N';
    else $second_char=substr($exp,1,1);
    if ( $second_char == 'T' ) $style3= "style='display:'";
    else $style3= "style='display: none'";

    echo "<tr $style3><td width=100>Type évenement </td><td align=left width=400>";

    echo "<select id='type_event' name='type_event' 
    onchange=\"orderfilter(document.getElementById('filter').value,'".$subsections."','".$exp."','".$dtdb."','".$dtfn."','".$yearreport."',document.getElementById('type_event').value)\">";
    echo "<option value='ALL' selected>Toutes activités</option>";
    $query="select distinct te.CEV_CODE, ce.CEV_DESCRIPTION, te.TE_CODE, te.TE_LIBELLE
            from type_evenement te, categorie_evenement ce
            where te.CEV_CODE=ce.CEV_CODE
            and te.TE_CODE <> 'MC'";
    $query .= " order by te.CEV_CODE desc, te.TE_CODE asc";
    $result=mysqli_query($dbc,$query);
    $prevCat='';
    while (custom_fetch_array($result)) {
        if ( $prevCat <> $CEV_CODE ){
            echo "<optgroup class='categorie' label='".$CEV_DESCRIPTION."'";
            echo ">".$CEV_DESCRIPTION."</optgroup>\n";
        }
        $prevCat=$CEV_CODE;
        echo "<option class='type' value='".$TE_CODE."' title=\"".$TE_LIBELLE."\"";
        if ($TE_CODE == $type_event ) echo " selected ";
        echo ">".$TE_LIBELLE."</option>\n";
    }
    echo "</select></td></tr>";

    //-----------------
    //dates
    //-----------------

    echo "<tr><td colspan=2>";
    if ( $exp == "" ) {
        $first_char="N";
        $second_car="N";
    }
    else {
        $first_char=substr($exp,0,1);
        $second_char=substr($exp,1,1);
    }

    if ( $first_char == '1' ) {
        $style1= "style='display:'";
        $style2= "style='display:'";
        $t = "Début";
    }
    else if ( $first_char == '0' ) {
        $style1= "style='display:'";
        $style2= "style='display: none'"; 
        $t = "Date";
    }
    else {
        $style1= "style='display: none'";
        $style2= "style='display: none'"; 
        $t = "";
    }

    echo "<table class='noBorder'>";

    echo "<tr ".$style1."><td width=100>".$t."</td><td align=left width=400>
            <input type='text' size='10' name='dtdb' id='dtdb' value=\"".$dtdb."\" class='datepicker' data-provide='datepicker'
                placeholder='JJ-MM-AAAA'
                onchange=checkDate2(document.frmExport.dtdb)
                style='width:100px;'>";
    echo "</td></tr>";

    echo "<tr ".$style2."><td>Fin</td><td align=left>
            <input type='text' size='10' name='dtfn' id='dtfn' value=\"".$dtfn."\" class='datepicker' data-provide='datepicker'
                placeholder='JJ-MM-AAAA'
                onchange=checkDate2(document.frmExport.dtfn)
                style='width:100px;'>";
    echo "</td></tr></table>";

    //-----------------
    // année pour certains reports
    //-----------------
    if ( $first_char == '2')
        echo "<div id='info2' style='display:'>";
    else
        echo "<div id='info2' style='display: none'>";
    echo "<table class='noBorder'>";
    echo "<tr><td width=100>Année</td><td align=left width=400>";

    echo "<select id='yearreport' name='yearreport'>";
    for ( $k = date("Y") -4; $k <=  date("Y"); $k++ ) {
        if ( $k == $yearreport ) $selected = 'selected';
        else $selected='';
        echo "<option value='$k' $selected>$k</option>";
    }
    echo "</select>";
    echo "</td></tr></table>";
    echo "</div>";    


    if ( $exp <> '' ) {
        echo "<p><input type='hidden' name='affichage' value='ecran'>";
        echo "<tr><td colspan=2><div class='noprint'><input type='hidden' name='show' value=1><input type='submit'  class='btn btn-default' value='Afficher'
        onclick=\"document.frmExport.affichage.value='ecran';document.frmExport.submit();\"></div></td></tr></table>";
        if ( isset($sql) ) write_debugbox($sql);
    }
    echo "</form>";


    // output
    if(isset($_GET['exp']) and isset($_GET['show'])) {
        $nb=count($tab);
        if ( $nb > 0 ) $nb = $nb -1 ;
        if ( $nb == 1 ) $l = 'ligne';
        else $l = 'lignes';
        echo "<font size=4><b>$export_name</b></font> <span class='badge'>$nb $l</span>";
        echo " <a href='#'><i class='fa fa-print fa-lg noprint' id='StartPrint' title='Imprimante' onclick='impression();' class='noprint' align='right' /></i></a>";
        if (substr($exp,0,5) <> "1tcd_") {
            echo " <a href='#'><i class='far fa-file-excel fa-lg noprint' style='color:green;'   id='StartExcel' title='Excel' onclick=\"document.frmExport.affichage.value='xls';document.frmExport.submit();\" align='right' /></i></a>";
            echo " <a href='#'><i class='far fa-file-text fa-lg noprint' id='StartTxt' title='Fichier texte' onclick=\"document.frmExport.affichage.value='txt';document.frmExport.submit();\" class='noprint'  align='right' /></i></a>";
        }
        if(substr($exp,0,5)=="1tcd_") include("export-tcd.php");
        else include("export-html.php");
    }
    writefoot();
}
?>
