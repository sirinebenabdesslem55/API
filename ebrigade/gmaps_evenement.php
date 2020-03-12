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
check_all(0);
$id=$_SESSION['id'];
get_session_parameters();

if (isset ($_GET['display'])) $display=$_GET['display'];
else $display='E';

writehead();
check_all(76);
check_all(41);

if ( $display == 'E' ) {
    $query1="select distinct E.E_CODE, E.TE_CODE, TE.TE_LIBELLE, E.E_LIEU, TE.TE_ICON,
                    E.E_LIBELLE, E.E_CODE, E.E_CLOSED, E.E_OPEN_TO_EXT, E.E_CANCELED, S.S_CODE, E.S_ID,
                    E.E_PARENT, E.TAV_ID, S.S_CODE, g.lat, g.lng";


    $queryadd =" from evenement E, evenement_horaire EH, type_evenement TE, section S, geolocalisation g
    where E.TE_CODE=TE.TE_CODE
    and E.E_CODE=EH.E_CODE
    and E.E_CANCELED=0
    and g.code= E.E_CODE and g.type='E'
    and E.S_ID = S.S_ID
    and E.TE_CODE <> 'MC'";
    
    if ( $type_evenement <> 'ALL' ) 
        $queryadd .= "\n and (TE.TE_CODE = '".$type_evenement."' or TE.CEV_CODE = '".$type_evenement."')";

    $curdate=date("Y-m-d");
    $queryadd .="\n and EH.EH_DATE_DEBUT <= '".$curdate."' 
             and EH.EH_DATE_FIN   >= '".$curdate."'";
    
}

else {
// interventions
    $query1 ="select E.E_CODE, E.TE_CODE, TE.TE_LIBELLE, E.E_LIEU, TE.TE_ICON,
                    E.E_LIBELLE, E.E_CODE, E.E_CLOSED, E.E_OPEN_TO_EXT, E.E_CANCELED, E.S_ID,
                    el.EL_ID, el.EL_TITLE, date_format(el.EL_DEBUT, '%d-%m-%Y %h:%i') EL_DEBUT, g.lat, g.lng, el.EL_ADDRESS";
    $curdatetime=date("Y-m-d H:i");
    $today=date("Y-m-d");
    $queryadd =" from evenement_log el , evenement E, type_evenement TE, geolocalisation g 
        where g.TYPE ='I' 
        and E.TE_CODE=TE.TE_CODE
        and g.CODE=el.E_CODE 
        and el.EL_ID = g.CODE2
        and el.E_CODE=E.E_CODE
        and el.EL_DEBUT < '".$curdatetime."'
        and ( el.EL_FIN is null or el.EL_FIN >= '".$today."')
        and el.TEL_CODE='I'
        and datediff('".date("Y-m-d")."', el.EL_DEBUT) <= 30";    
    
    if ( $type_evenement <> 'ALL' ) 
        $queryadd .= "\n and (TE.TE_CODE = '".$type_evenement."' or TE.CEV_CODE = '".$type_evenement."')";
}


if ( $subsections == 1 ) {
      $queryadd .= "\n and E.S_ID in (".get_family("$filter").")";
}
else {
      $queryadd .= "\n and E.S_ID =".$filter;
}             
             
$query1 .= $queryadd;

$result1=mysqli_query($dbc,$query1);
$number=mysqli_num_rows($result1);
$map_data="";
$center_lat="";
$center_lng="";

// événements ou interventions
while ($row=@mysqli_fetch_array($result1)) {
    $E_CODE=$row["E_CODE"];
    $TE_CODE=$row["TE_CODE"];
    $TE_ICON=$row["TE_ICON"];
    $TE_LIBELLE=$row["TE_LIBELLE"];
    $E_LIEU=$row["E_LIEU"];
    $E_LIBELLE=$row["E_LIBELLE"];
    $L_LAT=$row["lat"];
    $L_LNG=$row["lng"];
    $desc=$TE_LIBELLE.": ".$E_LIBELLE." ".$E_LIEU;
    if ( $display == 'E' ) {
        $icon = "'images/evenements/".$TE_ICON."'";
        $url = "evenement_display.php?evenement=".$E_CODE;
    }
    else {
        $EL_ID=$row["EL_ID"];
        $EL_TITLE=$row["EL_TITLE"];
        $EL_DEBUT=$row["EL_DEBUT"];
        $icon = "'images/gyro.png'";
        $url = "intervention_edit.php?evenement=".$E_CODE."&numinter=".$EL_ID."&action=update&type=I&from=map";
        $desc .= " ".$EL_TITLE." ".$EL_DEBUT;
    }
    
    if( strlen($L_LAT) > 1 ){    
        $map_data .= "
        var ev".$E_CODE." = new google.maps.Marker({
        position: new google.maps.LatLng(".$L_LAT.",".$L_LNG."),
        title:\"".$desc."\",
        icon: new google.maps.MarkerImage(
            ".$icon.",
            new google.maps.Size(28, 28),
            new google.maps.Point(0, 0),
            new google.maps.Point(11, 11),
            new google.maps.Size(28, 28)
        ),
        url: '".$url."',
        map: map
        });
        
        google.maps.event.addListener(ev".$E_CODE.", 'click', function() {
           window.location.href = ev".$E_CODE.".url;
        });
        ";
    }
    // point de centrage par défaut sur la dernière personne trouvée
    $center_lat=$L_LAT;
    $center_lng=$L_LNG;
}

if ( $filter == 0 and $nbsections == 0 ) $zoom=6;
else $zoom=12;
?>
<script type='text/javascript' src='<?php echo $google_maps_url; ?>'></script>
<script type='text/javascript' src='js/markerwithlabel.js'></script>
<script type='text/javascript' src='js/popupBoxes.js'></script>
<script language="JavaScript">
function orderfilter(p1,p2,p3,p4){
     self.location.href="gmaps_evenement.php?filter="+p1+"&subsections="+p2+"&type_evenement="+p3+"&display="+p4;
     return true
}

function orderfilter2(p1,p2,p3,p4){
      if (p2.checked) s = 1;
      else s = 0;
     self.location.href="gmaps_evenement.php?filter="+p1+"&subsections="+s+"&type_evenement="+p3+"&display="+p4;
     return true
}

var map;
var icoCenter = new google.maps.MarkerImage('images/house.gif');
var iconInter = new google.maps.MarkerImage('images/gyro.png');
 
<?php if ( $center_lat <> 0 ) { ?>

window.onresize = function(event) {
     resizeMap();
}

function resizeMap() {
    var height = $(window).height() - 200; 
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
        animation:google.maps.Animation.BOUNCE,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    var map = new google.maps.Map(document.getElementById("map_canvas"),
    myOptions);
    <?php echo $map_data; ?>
};

<?php } ?>
</script>

<STYLE type="text/css">
.categorie{color:<?php echo $mydarkcolor; ?>;background-color:<?php echo $mylightcolor; ?>;font-size:10pt;}
.type{color:<?php echo $mydarkcolor; ?>; background-color:white; font-size:9pt;}
</STYLE>

 
<?php
echo "</head>";
include_once ("config.php");

if ( $center_lat <> 0 ) 
    echo "<body onload=\"initialise();\">";
else echo "<body>";
if ( $display == 'E' ) 
    echo "<div align=center><font size=4><b>Evénements en cours</b></font> <span class='badge' title='$number localisations'>$number</span>";
else
    echo "<div align=center><font size=4><b>Interventions en cours</b></font> <span class='badge' title='$number localisations'>$number</span>";
echo "<p><table class='noBorder'>";
echo "<tr>";

// choix section
echo "<td> Organisés par</td>";
echo "<td><select id='filter' name='filter' 
        onchange=\"orderfilter(document.getElementById('filter').value,'".$subsections."','".$type_evenement."', '".$display."')\">";
display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select>";
    
echo "</td>";
if ( get_children("$filter") <> '' ) {
    if ($subsections == 1 ) $checked='checked';
    else $checked='';
    echo "<td align=center width=120><input type='checkbox' name='sub' $checked
       onClick=\"orderfilter2(document.getElementById('filter').value, this,'".$type_evenement."', '".$display."')\"/>
       <font size=1>inclure les<br>$sous_sections</td>";
}
echo "</td></tr>";

// choix type événement
echo "<tr ><td align=right> Type d'activité </td>";
echo "<td align=left><select id='type_evenement' name='type_evenement' 
   onchange=\"orderfilter(document.getElementById('filter').value,'$subsections', document.getElementById('type_evenement').value, '".$display."')\">";
echo "<option value='ALL' selected>Toutes activités </option>\n";
$query2="select distinct te.CEV_CODE, ce.CEV_DESCRIPTION, te.TE_CODE, te.TE_LIBELLE
        from type_evenement te, categorie_evenement ce
        where te.CEV_CODE=ce.CEV_CODE
        order by te.CEV_CODE desc, te.TE_LIBELLE asc";
$result2=mysqli_query($dbc,$query2);
$prevCat='';
while ($row2=@mysqli_fetch_array($result2)) {
    $TE_CODE=$row2["TE_CODE"];
    $TE_LIBELLE=$row2["TE_LIBELLE"];
    $CEV_DESCRIPTION=$row2["CEV_DESCRIPTION"];
    $CEV_CODE=$row2["CEV_CODE"];
    if ( $prevCat <> $CEV_CODE ){
       echo "<option class='categorie' value='".$CEV_CODE."' label='".$CEV_DESCRIPTION."'";
       if ($CEV_CODE == $type_evenement ) echo " selected ";
    echo ">".$CEV_DESCRIPTION."</option>\n";
    }
    $prevCat=$CEV_CODE;
    echo "<option class='type' value='".$TE_CODE."' title=\"".$TE_LIBELLE."\"";
    if ($TE_CODE == $type_evenement ) echo " selected ";
    echo ">".$TE_LIBELLE."</option>\n";
}
echo "</select></td>
<td> <input type='button'  class='btn btn-default' value='retour' onclick='javascript:history.back(1);'></td>
</tr>";

// choix evenements ou interventions en cours
echo "<tr ><td align=right> Afficher </td>";
if ( $display == 'E' ) $checked1='checked'; else $checked1='';
if ( $display == 'I' ) $checked2='checked'; else $checked2='';

echo "<td align=left>
        <input type='radio' name='display' value='E' $checked1
        onclick=\"orderfilter(".$filter.",'".$subsections."','".$type_evenement."', 'E')\" >Evenements 
        <input type='radio' name='display' value='I' $checked2 
        onclick=\"orderfilter(".$filter.",'".$subsections."','".$type_evenement."', 'I')\"> Interventions";
echo "</td></tr>";

echo "</table>";

if ( $center_lat <> 0 ) {
    echo "<div id='map_canvas' ></div>";
}
else {
    echo "Pas de données d'événements à afficher";
}
writefoot();
?>
