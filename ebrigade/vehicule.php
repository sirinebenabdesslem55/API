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
get_session_parameters();
writehead();
test_permission_level(42);

$possibleorders= array('TV_CODE','V_IMMATRICULATION','V_INDICATIF','V_MODELE','V_COMMENT','VP_OPERATIONNEL',
'DT_ASS','DT_CT','DT_REV','V_KM','V_KM_REVISION','V_FLAG1','V_FLAG2','V_FLAG3','V_FLAG4','AFFECTED_TO','AFFECTED_TO','S_CODE','V_ANNEE');
if ( ! in_array($order, $possibleorders) or $order == '' ) $order='TV_CODE';


?>

<STYLE type="text/css">
.categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.materiel{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
</STYLE>
<script type='text/javascript' src='js/vehicule.js'></script>
<?php

$querycnt="select count(*) as NB";

$query1="select distinct v.V_ID ,v.VP_ID, v.TV_CODE, v.V_MODELE, v.EQ_ID, vp.VP_LIBELLE, 
        tv.TV_LIBELLE, vp.VP_OPERATIONNEL, v.V_IMMATRICULATION, v.V_COMMENT, v.V_KM, v.V_KM_REVISION,
        v.V_ANNEE, tv.TV_USAGE, tv.TV_ICON, s.S_ID, s.S_CODE, v.V_INDICATIF,
        case when v.V_ASS_DATE is null then '2100-01-01' else v.V_ASS_DATE end as DT_ASS,
        case when v.V_CT_DATE is null then '2100-01-01' else v.V_CT_DATE end as DT_CT,
        case when v.V_REV_DATE is null then '2100-01-01' else v.V_REV_DATE end as DT_REV,
        DATE_FORMAT(v.V_ASS_DATE, '%d-%m-%Y') as V_ASS_DATE1,
        DATE_FORMAT(v.V_CT_DATE, '%d-%m-%Y') as V_CT_DATE1,
        DATE_FORMAT(v.V_REV_DATE, '%d-%m-%Y') as V_REV_DATE1,
        v.V_FLAG1, v.V_FLAG2, v.V_FLAG3, v.V_FLAG4, v.AFFECTED_TO, v.V_EXTERNE";
        
$queryadd=" from vehicule v, type_vehicule tv, vehicule_position vp, section s
        where v.TV_CODE=tv.TV_CODE
        and s.S_ID=v.S_ID
        and vp.VP_ID=v.VP_ID";

if ( $filter2 <> 'ALL' ) $queryadd .= "\nand (tv.TV_USAGE='".$filter2."' or tv.TV_CODE='".$filter2."')";

$title="Véhicules et engins";
if ( $old == 1 ) {
      $queryadd .="\nand vp.VP_OPERATIONNEL <0";
      $mylightcolor=$mygreycolor;
      $title .= " réformés";
}
else {
     $queryadd .="\nand vp.VP_OPERATIONNEL >=0";
}

// choix section
if ( $subsections == 1 ) {
    if ( $filter > 0 ) 
        $queryadd .= "\nand v.S_ID in (".get_family("$filter").")";
}
else {
      $queryadd .= "\nand v.S_ID =".$filter;
}

$querycnt .= $queryadd;

$query1 .= $queryadd." \norder by ". $order;
if ( $order == 'VP_OPERATIONNEL' ) $query1 .=",VP_LIBELLE";
if ( $order == 'TV_USAGE' || $order == 'V_FLAG1' || $order == 'V_FLAG2' || $order == 'V_FLAG3' || $order == 'V_FLAG4'
|| $order == 'AFFECTED_TO' || $order == 'V_EXTERNE' || $order == 'V_ANNEE' || $order == 'V_KM') $query1 .=" desc";

$resultcnt=mysqli_query($dbc,$querycnt);
$rowcnt=@mysqli_fetch_array($resultcnt);
$number = $rowcnt[0];

echo "<div align=center class='table-responsive'><table class='noBorder'>
      <tr><td width = 40 ><i class='fa fa-truck fa-3x'></i></td><td>
      <font size=4><b> $title</b></font> <span class='badge'>$number</span></td>
      <td><a href='#'><i class='far fa-file-excel fa-2x' style='color:green;' id='StartExcel' title='Excel' 
onclick=\"window.open('vehicule_xls.php?filter=$filter&filter2=$filter2&subsections=$subsections&order=$order&old=$old')\" align='right' /></td></i></a></tr></table>";

echo "<table class='noBorder'>";
echo "<tr height=40>";

//filtre section
echo "<td width=100 align=right>".choice_section_order('vehicule.php')."</td>";
echo "<td > <select id='filter' name='filter' 
        onchange=\"orderfilter('".$order."',document.getElementById('filter').value,'".$filter2."','".$subsections."','".$old."')\">";
      display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select></td>";
if ( get_children("$filter") <> '' ) {
    if ($subsections == 1 ) $checked='checked';
    else $checked='';
    echo "
        <td align=left ><input type='checkbox' name='sub' id='sub' $checked
       onClick=\"orderfilter2('".$order."',document.getElementById('filter').value,'".$filter2."', this, '".$old."')\"/>
       <label for='sub'>inclure $sous_sections</td>";
}
echo "</tr><tr>";

if ( check_rights($_SESSION['id'], 17)) {
   echo "<td> <input type='button' class='btn btn-default' value='Ajouter' name='ajouter' onclick=\"bouton_redirect('ins_vehicule.php');\"></td>";
}
else echo "<td></td>";

//filtre type
echo "<td> <select id='filter2' name='filter2' class='smallcontrol3'
    onchange=\"orderfilter('".$order."','".$filter."',document.getElementById('filter2').value,'".$subsections."','".$old."')\">
      <option value='ALL'>tous types</option>";

$query2="select distinct TV_CODE, TV_USAGE, TV_LIBELLE from type_vehicule 
         order by TV_USAGE, TV_CODE";
$prevUsage='';
$result2=mysqli_query($dbc,$query2);
while ($row=@mysqli_fetch_array($result2)) {
      $TV_USAGE=$row["TV_USAGE"];
      $TV_CODE=$row["TV_CODE"];
      $TV_LIBELLE=$row["TV_LIBELLE"];
      if ( $prevUsage <> $TV_USAGE ){
           echo "<option class='categorie' value='".$TV_USAGE."'";
           if ($TV_USAGE == $filter2 ) echo " selected ";
        echo ">".$TV_USAGE."</option>\n";
      }
      $prevUsage=$TV_USAGE;
      echo "<option class='materiel' value='".$TV_CODE."' title=\"".$TV_LIBELLE."\"";
      if ($TV_CODE == $filter2 ) echo " selected ";
      echo ">".$TV_CODE." - ".$TV_LIBELLE."</option>\n";
}
echo "</select></td> ";

//filtre anciens vehicules

if ($old == 1 ) $checked='checked';
else $checked='';
echo "<td align=left ><input type='checkbox' name='old' id='old' $checked
       onClick=\"orderfilter3('".$order."','".$filter."','".$filter2."', '".$subsections."',this)\"/>
       <label for='old'>véhicules réformés</label></td>";

echo "</tr><tr><td></td><td colspan=3>";
// ====================================
// pagination
// ====================================
require_once('paginator.class.php');
$pages = new Paginator;  
$pages->items_total = $number;  
$pages->mid_range = 9;  
$pages->paginate();  
if ( $number > 10 ) {
    echo $pages->display_pages();
    echo $pages->display_jump_menu(); 
    echo $pages->display_items_per_page(); 
    $query1 .= $pages->limit;
}

$result1=mysqli_query($dbc,$query1);
$numberrows=mysqli_num_rows($result1);

echo "</td></tr></table>";


if ( $number > 0 ) {
    echo "<table cellspacing=0 border=0>";

    // ===============================================
    // premiere ligne du tableau
    // ===============================================

    echo "<tr class=TabHeader>
        <td width=90 align=center colspan=2><a href=vehicule.php?order=TV_CODE class=TabHeader>Type</a></td>
        <td width=80 align=center><a href=vehicule.php?order=V_IMMATRICULATION class=TabHeader>Immat.</a></td>
        <td width=150 align=center><a href=vehicule.php?order=V_INDICATIF class=TabHeader>Indicatif</a></td>";
                          
    echo "<td width=100 align=center>
            <a href=vehicule.php?order=S_CODE class=TabHeader>Section</a></td>";          
    echo "<td width=140 align=center><a href=vehicule.php?order=V_MODELE class=TabHeader>Modèle</a></td>
        <td width=100 align=center><a href=vehicule.php?order=VP_OPERATIONNEL class=TabHeader>Statut</a></td>
        <td width=30 align=center><a href=vehicule.php?order=V_ANNEE class=TabHeader>Année</a></td>
        <td width=60 align=center><a href=vehicule.php?order=DT_ASS class=TabHeader>Fin assurance</a></td>
        <td width=60 align=center><a href=vehicule.php?order=DT_CT class=TabHeader>Prochain CT</a></td>
        <td width=60 align=center><a href=vehicule.php?order=DT_REV class=TabHeader>Révision</a></td>
        <td width=20 align=center><a href=vehicule.php?order=V_KM class=TabHeader data-toggle=\"tooltip\" data-placement=\"bottom\" title='kilométrage actuel du véhicule' >km</a></td>
        <td width=20 align=center><a href=vehicule.php?order=V_KM class=TabHeader data-toggle=\"tooltip\" data-placement=\"bottom\" title='kilométrage de la prochaine révision' >révis.</a></td>
        <td width=20 align=center><a href=vehicule.php?order=V_FLAG1 class=TabHeader
            data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Véhicule équipé pour rouler sur la neige\">Neige</a></td>
        <td width=20 align=center><a href=vehicule.php?order=V_FLAG2 class=TabHeader
            data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Véhicule équipé de climatisation\">Clim</a></td>
        <td width=20 align=center><a href=vehicule.php?order=V_FLAG3 class=TabHeader
            data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Véhicule équipé public address (diffusion sonore de message au micro)\">PA</a></td>
        <td width=20 align=center><a href=vehicule.php?order=V_FLAG4 class=TabHeader
            data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Véhicule équipé équipé d'un crochet d'attelage (indiquant la possibilité d'utiliser une remorque)\">Att</a></td>
        <td width=100 align=center><a href=vehicule.php?order=AFFECTED_TO class=TabHeader>Affecté à</a></td>";
    if ( $materiel == 1 )
        echo "<td width=30 align=center class=TabHeader>Mat.</a></td>";

    if ( $nbsections == 0 ) 
    echo "<td width=20 align=center><a href=vehicule.php?order=V_EXTERNE class=TabHeader
            data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"Mis à disposition par $cisname\">MàD</a></td>";
    echo "</tr>";

    // ===============================================
    // le corps du tableau
    // ===============================================
    $i=0;
    while (custom_fetch_array($result1)) {
        if ( $TV_ICON == "" ) $vimg="";
        else $vimg="<img src='".$TV_ICON."' class='img-max-22'>";
          
        $i=$i+1;
        if ( $i%2 == 0 ) $mycolor=$mylightcolor;
        else $mycolor="#FFFFFF";

        if ( $V_FLAG1 == 1 ) $img1="<i class='fa fa-check' title='ce véhicule est équipé pour rouler sur la neige'></i>";
        else $img1='';
        if ( $V_FLAG2 == 1 ) $img2="<i class='fa fa-check' title='ce véhicule est climatisé'></i>";
        else $img2='';
        if ( $V_FLAG3 == 1 ) $img3="<i class='fa fa-check' title='ce véhicule est équipé public alert'></i>";
        else $img3='';
        if ( $V_FLAG4 == 1 ) $img4="<i class='fa fa-check' title=\"ce véhicule est équipé d'un attelage pour tracter une remorque\"></i>";
        else $img4='';

        if ( $V_EXTERNE == 1 ) $img5="<i class='fa fa-check' title=\"véhicule mis à disposition par $cisname\"></i>";
        else $img5='';
        if ( $AFFECTED_TO <> '' ) {
            $queryp="select P_NOM, P_PRENOM, P_OLD_MEMBER from pompier where P_ID=".$AFFECTED_TO;
            $resultp=mysqli_query($dbc,$queryp);
            $rowp=@mysqli_fetch_array($resultp);
            $P_NOM=$rowp["P_NOM"];
            $P_PRENOM=$rowp["P_PRENOM"];
            $P_OLD_MEMBER=$rowp["P_OLD_MEMBER"];
            $owner=strtoupper(substr($P_PRENOM,0,1).".".$P_NOM);
            if ( $P_OLD_MEMBER == 1 ) $owner="<font color=black title='ancien membre'><b>".$owner."</b><font>";
        }
        else $owner='';
          
        if ( $VP_OPERATIONNEL == -1 ) $opcolor="black";
        else if ( $VP_OPERATIONNEL == 1) $opcolor=$red;
        else if ( my_date_diff(getnow(),$V_ASS_DATE1) < 0 ) {
            $opcolor=$red;
            $VP_LIBELLE = "assurance périmée";
        }
        else if ( my_date_diff(getnow(),$V_CT_DATE1) < 0 ) {
            $opcolor=$red;
            $VP_LIBELLE = "CT périmé";
        }
        else if ( $VP_OPERATIONNEL == 2) {
            $opcolor=$orange;
        }
        else if (( my_date_diff(getnow(),$V_REV_DATE1) < 0 ) and ( $VP_OPERATIONNEL <> 1)) {
            $opcolor=$orange;
            $VP_LIBELLE = "révision à faire";
        }  
        else $opcolor=$green;
          
        // matériel embarqué
        $query2="select count(*) as NB from materiel where V_ID=".$V_ID;
        $result2=mysqli_query($dbc,$query2);
        $row2=@mysqli_fetch_array($result2);
        if ( $row2["NB"] > 0 ) $mat="<i class='fa fa-cog'  style='color:purple;' title='matériel embarqué: ".$row2["NB"]." éléments'></i> <font size=1>".$row2["NB"]."</font>";
        else $mat="";

        if ( my_date_diff(getnow(),$V_ASS_DATE1) < 0 ) $assurance=$red;
        else if ( my_date_diff(getnow(),$V_ASS_DATE1) < 60 ) $assurance="#FF8C00";
        else $assurance=$mydarkcolor;
        if ( my_date_diff(getnow(),$V_CT_DATE1) < 0 ) $controle=$red;
        else if ( my_date_diff(getnow(),$V_CT_DATE1) < 60 ) $controle="#FF8C00";
        else $controle=$mydarkcolor;
        if ( my_date_diff(getnow(),$V_REV_DATE1) < 0 ) $rev=$red;
        else if ( my_date_diff(getnow(),$V_REV_DATE1) < 60 ) $rev="#FF8C00";
        else $rev=$mydarkcolor;
              
        echo "<tr bgcolor=$mycolor onMouseover=\"this.bgColor='yellow'\" onMouseout=\"this.bgColor='$mycolor'\" onclick=\"this.bgColor='#33FF00'; displaymanager($V_ID)\" >
              <td align=center>".$vimg."</td>
              <td><font color=$opcolor><B>$TV_CODE</B></font></td>
              <td align=center>$V_IMMATRICULATION</td>
              <td align=center>$V_INDICATIF</td>";  
        echo "<td align=center><font size=1>$S_CODE</font></td>";
        echo "<td align=center><font size=1>$V_MODELE</font></td>
              <td align=center><font color=$opcolor size=1><b>$VP_LIBELLE</b></font></td>
              <td align=center><font size=1>$V_ANNEE</font></td>
              <td align=center><font color=$assurance size=1><b>$V_ASS_DATE1</b></font></td>
              <td align=center><font color=$controle size=1><b>$V_CT_DATE1</b></font></td>
              <td align=center><font color=$rev size=1><b>$V_REV_DATE1</b></font></td>
              <td align=center><font size=1>$V_KM</font></td>
              <td align=center><font size=1>$V_KM_REVISION</font></td>
              <td align=center><font size=1>$img1</font></td>
              <td align=center><font size=1>$img2</font></td>
              <td align=center><font size=1>$img3</font></td>
              <td align=center><font size=1>$img4</font></td>
              <td align=center><font size=1><a href=upd_personnel.php?from=vehicules&pompier=".$AFFECTED_TO.">".$owner."</a></font></td>";
        if ( $materiel == 1 )    
            echo "<td align=left>".$mat."</a></td>";
                  
        if ( $nbsections == 0 )
            echo "<td align=center><font size=1>".$img5."</font></td>";
            echo "</tr>";
              
        }
    echo "</table>";
} 
else {
    echo "<span class=small>Aucun véhicule</span>";
}
echo "<div>";
writefoot();

?>
