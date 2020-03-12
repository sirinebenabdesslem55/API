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
check_all(76);
$id=$_SESSION['id'];
writehead();
get_session_parameters();

$fixed_company = false;
if ( $category == 'EXT' ) {
    if (! check_rights($id, 37)) {
        check_all(45);
        $company=$_SESSION['SES_COMPANY'];
        $_SESSION['company'] = $company;
        $fixed_company = true;
    }
} 
else {
    test_permission_level(56);
}


if ( isset($_GET["position"])) $position=$_GET["position"];
else $position='actif';

if ( isset($_GET["category"])) $category=$_GET["category"];
else $category='interne';

if ( isset($_GET["competence"])) $competence=intval($_GET["competence"]);
else $competence=0;

$disabled="disabled";
$envoisEmail=false;


$query1="select distinct pompier.P_ID, P_CODE , P_NOM , P_PRENOM, P_HIDE, P_SEXE, pompier.C_ID, company.C_NAME, 
        P_GRADE, P_STATUT, P_SECTION, P_PHONE, P_PHONE2, S_CODE, section.S_ID, P_EMAIL, P_PHOTO, g.lat, g.lng";
        
$queryadd =" from pompier , grade, section, company , geolocalisation g";
if ( $competences and $competence > 0 ) $queryadd .=", qualification q";
$queryadd .=" where P_GRADE=G_GRADE
        and company.C_ID = pompier.C_ID
        and g.code= pompier.P_ID and g.type='P'
        and P_SECTION=section.S_ID
        and P_NOM <> 'admin' ";    
if ( $competences and $competence > 0 ) 
    $queryadd .=" and q.P_ID = pompier.P_ID and q.PS_ID=".$competence." and q.Q_VAL > 0";
if ( $company >=0 ) $queryadd .= " and company.C_ID = $company";

if ( $category == 'EXT' ) {
    $queryadd .= " and P_STATUT = 'EXT'";
    $mylightcolor=$mygreencolor;
    $title='Localisation externes';
}
else if ( $position == 'actif' ) {
    $queryadd .= " and P_OLD_MEMBER = 0 and P_STATUT <> 'EXT'";
    $title='Localisation actifs';
}
else {
    $queryadd .= " and P_OLD_MEMBER > 0";
    $mylightcolor=$mygreycolor;
    $title='Localisation anciens';
}

// recherche position des sections
$sqls = "select g.code, g.lat, g.lng , s.s_description
        from geolocalisation g, section s
        where g.type='S'
        and g.code= s.s_id
";

if ( $subsections == 1 ) {
      $queryadd .= "\n and P_SECTION in (".get_family("$filter").")";
    $sqls .= "\n and g.code in (".get_family("$filter").")";
}
else {
      $queryadd .= "\n and P_SECTION =".$filter;
    $sqls .= "\n and g.code =".$filter;
}

if ( ! check_rights($id,2,"$filter"))     {
    $queryadd .= "\n and P_HIDE=0";
    $sqls .= "\n and P_HIDE=0";
}

$query1 .= $queryadd;
$resultsection=mysqli_query($dbc,$sqls);
$result1=mysqli_query($dbc,$query1);
$number=mysqli_num_rows($result1);
$map_data="";
$center_lat="";
$center_lng="";


// personnes
while ($row=@mysqli_fetch_array($result1)) {
    $P_ID=$row["P_ID"];
    $P_PRENOM=$row["P_PRENOM"];
    $P_NOM=$row["P_NOM"];
    $L_LAT=$row["lat"];
    $L_LNG=$row["lng"]; 
    $name=strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM);
    if( strlen($L_LAT) > 1 ){    
        $map_data .= "
        var pers".$P_ID." = new google.maps.Marker({
        position: new google.maps.LatLng(".$L_LAT.",".$L_LNG."),
        title:\"".$name."\",
        url: 'upd_personnel.php?pompier=".$P_ID."',
        map: map
        });
        
        google.maps.event.addListener(pers".$P_ID.", 'click', function() {
           window.location.href = pers".$P_ID.".url;
        });
        ";
    }
    // point de centrage par défaut sur la dernière personne trouvée
    $center_lat=$L_LAT;
    $center_lng=$L_LNG;
}

// sections
if ( $filter > 0 ) {
    while ($rows=@mysqli_fetch_array($resultsection)) {
        if( strlen($rows['lat']) > 1 ){
            $map_data .= "
            var section".$rows['code']." = new google.maps.Marker({
            position: new google.maps.LatLng(".$rows['lat'].",".$rows['lng']."),
            title:\"".$rows['s_description']."\",
            url: 'upd_section.php?S_ID=".$rows['code']."',
            icon:icoHouse,
            map: map
            });
        
            google.maps.event.addListener(section".$rows['code'].", 'click', function() {
            window.location.href = section".$rows['code'].".url;
            });
            ";
            // corriger point de centrage sur adresse section
            $center_lat=$rows['lat'];
            $center_lng=$rows['lng'];
        }
    }
}

if ( $filter == 0 and $nbsections == 0 ) $zoom=6;
else $zoom=10;
?>
<script type='text/javascript' src='<?php echo $google_maps_url; ?>'></script>
<script language="JavaScript">
function orderfilter(p1,p2,p3,p4,p5,p6){
     self.location.href="gmaps_personnel.php?filter="+p1+"&subsections="+p2+"&position="+p3+"&category="+p4+"&company="+p5+"&competence="+p6;
     return true
}

function orderfilter2(p1,p2,p3,p4,p5,p6){
      if (p2.checked) s = 1;
      else s = 0;
     self.location.href="gmaps_personnel.php?filter="+p1+"&subsections="+s+"&position="+p3+"&category="+p4+"&company="+p5+"&competence="+p6;
     return true
}

var map;
var icoHouse = "images/house.png";
var icoCenter = "images/center.png";    
 
<?php if ( $center_lat <> 0 ) { ?>

window.onresize = function(event) {
     resizeMap();
}

function resizeMap() {
    var height = $(window).height() - 220; 
    var width = $(window).width() - 50;
    $('#map_canvas').height(height);
    $('#map_canvas').width(width);
}

$(document).ready(function() {
    resizeMap();
}
);

function initialise(){
    /* Centre sur la moyenne des latitudes et longitudes trouvées*/
    var pointc = new google.maps.LatLng(<?php echo $center_lat; ?>, <?php echo $center_lng; ?>);  
    var myOptions = {
        zoom: <?php echo $zoom; ?>,
        center:pointc,
        icon:icoCenter,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    var map = new google.maps.Map(document.getElementById("map_canvas"),
    myOptions);
    <?php echo $map_data; ?>
};

<?php } ?>
</script>
 
<?php
echo "</head>";
include_once ("config.php");

if ( $center_lat <> 0 ) 
    echo "<body onload=\"initialise();\" >";
else echo "<body >";
echo "<div align=center><font size=4><b>$title</b></font> <span class='badge'>$number </span>";
echo " <input type='button'  class='btn btn-default' value='retour'  title='Retour ¦ la carte' onclick=\"javascript:history.back(1);\">";
echo "<p><table class='noBorder'>";

echo "<tr>";
if ( $_SESSION['SES_STATUT'] == 'EXT' ) {
    echo "<td>Section</td>";
}
else {
    echo "<td>".choice_section_order('gmaps_personnel.php')."</td>";
}
echo "<td><select id='filter' name='filter' 
        onchange=\"orderfilter(document.getElementById('filter').value,'".$subsections."','".$position."','".$category."','-1','".$competence."')\">";
    display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select>";
    
if ( get_children("$filter") <> '' ) {
    if ($subsections == 1 ) $checked='checked';
    else $checked='';
    echo "<br><input type='checkbox' name='sub' $checked
       onClick=\"orderfilter2(document.getElementById('filter').value, this,'".$position."','".$category."','".$company."','".$competence."')\"/>
       <span class='small'>inclure les $sous_sections</span>";
}
echo "</td></tr>";

if ($nbsections == 0  ) {
    if ( $fixed_company ) $disabled='disabled';
    else $disabled='';
    echo "<tr><td></td><td><select id='company' name='company' title='filtre par entreprise' $disabled style='max-width:380px;font-size:10pt;'
        onchange=\"orderfilter('".$filter."','".$subsections."','".$position."','".$category."',document.getElementById('company').value,'".$competence."')\">";    
    echo "<option value='-1' 'selected'>... Pas de filtre par entreprise ...</option>";    
    $treenode=get_highest_section_where_granted($_SESSION['id'],37);
    if ( $treenode == '' ) $treenode=$mysection;
    if ( check_rights($_SESSION['id'], 24) ) $treenode=$filter;
    echo companychoice("$treenode","$company");
    echo "</select></td></tr>";
}

if ( $competences ) {
    echo "<tr><td></td>
    <td><select id='competence' name='competence' style='width:360px;' 
        onchange=\"orderfilter('".$filter."','".$subsections."','".$position."','".$category."','".$company."', document.getElementById('competence').value)\">";
    $query2="select p.PS_ID, p.DESCRIPTION, e.EQ_NOM, e.EQ_ID from poste p, equipe e 
           where p.EQ_ID=e.EQ_ID
           order by p.EQ_ID, p.PS_ORDER";
    $result2=mysqli_query($dbc,$query2);
    $prevEQ_ID=0;
    echo "<option value=0";
    if ($competence == 0 ) echo " selected ";
    echo ">Pas de filtre sur les compétences</option>";
    while ($row=@mysqli_fetch_array($result2)) {
        $PS_ID=$row["PS_ID"];
        $EQ_ID=$row["EQ_ID"];
        $EQ_NOM=$row["EQ_NOM"];
        if ( $prevEQ_ID <> $EQ_ID ) echo "<OPTGROUP LABEL='".$EQ_NOM."'>";
        $prevEQ_ID=$EQ_ID;
        $DESCRIPTION=$row["DESCRIPTION"];
        echo "<option value='".$PS_ID."'";
        if ($PS_ID == $competence ) echo " selected ";
        echo ">".$DESCRIPTION."</option>\n";
    }
    echo "</select></td></tr>";
}
echo "</tr><tr><td colspan=4>";
echo "</td></tr></table>";

if ( $center_lat <> 0 ) {
    echo "<div id='map_canvas'></div>";
}
else {
    echo "Pas de données de personnel à afficher";
}

writefoot();
