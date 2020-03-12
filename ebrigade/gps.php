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
include_once ("fonctions_sms.php");
check_all(0);
$id=$_SESSION['id'];
$mysection = $_SESSION['SES_SECTION'];
$SMS_CONFIG=get_sms_config($mysection);
get_session_parameters();
writehead();
check_all(76);
test_permission_level(76);
$time = intval($gps_persistence);
$autorefresh_period = 2 * $autorefresh_period;

echo "<meta http-equiv='Refresh' content='".$autorefresh_period."'>";

// personnel
$query1="select distinct p.P_ID, p.P_NOM , p.P_PRENOM, p.P_SEXE, p.P_HIDE,
         p.P_SECTION, s.S_CODE, s.S_ID, p.P_PHOTO, g.LAT, g.LNG, ADDRESS,
         date_format(g.DATE_LOC, '%d-%m � %H:%i') DATE_LOC";
        
$query1 .=" from pompier p, section s, gps g";
if ( $competences and $competence > 0 ) 
    $query1 .=", qualification q";
$query1 .=" where g.P_ID= p.P_ID
        and TIMESTAMPDIFF(MINUTE,g.DATE_LOC,NOW()) < ".$time."
        and p.P_SECTION=s.S_ID";

if ( $competences and $competence > 0 ) 
    $query1 .=" and q.P_ID = p.P_ID and q.PS_ID=".$competence." and q.Q_VAL > 0";

if ( $subsections == 1 ) {
    if ( $filter > 0 ) 
        $query1 .= " and p.P_SECTION in (".get_family("$filter").")";
}
else {
      $query1 .= " and p.P_SECTION =".$filter;
}
// ajout num�ros de t�l�phones
$query1 .=" union select distinct P_ID, null as P_NOM, null as P_PRENOM, 'Z' as P_SEXE, 0 as P_HIDE,
            null as P_SECTION, null as S_CODE, null as S_ID, null as P_PHOTO, LAT, LNG, ADDRESS,
            date_format(DATE_LOC, '%d-%m � %H:%i') DATE_LOC
            from gps 
            where P_ID > 1000000
            and TIMESTAMPDIFF(MINUTE,DATE_LOC,NOW()) < ".$time;

$result1=mysqli_query($dbc,$query1);
$number=mysqli_num_rows($result1);
$map_data="";

// Map parameters
if ( isset($_SESSION['zoomlevel'])) $zoom = $_SESSION['zoomlevel'];
else {
    if ( $filter == 0 and $nbsections == 0 ) $zoom=6;
    else $zoom=12;
}


if ( isset($_SESSION['maptypeid'])) $maptypeid = $_SESSION['maptypeid'];
else $maptypeid='roadmap';
if ( isset($_SESSION['centerlat'])) $centerlat = $_SESSION['centerlat'];
else $centerlat=0;
if ( isset($_SESSION['centerlng'])) $centerlng = $_SESSION['centerlng'];
else $centerlng=0;

// personnes
$localized=array();
while ($row=@mysqli_fetch_array($result1)) {
    $P_ID=$row["P_ID"];
    // avoid duplicates
    if (in_array($P_ID, $localized)) continue;
    array_push($localized, $P_ID);
    $P_PRENOM=$row["P_PRENOM"];
    $P_SEXE=$row["P_SEXE"];
    $P_HIDE=$row["P_HIDE"];
    $P_NOM=$row["P_NOM"];
    $L_LAT=$row["LAT"];
    $L_LNG=$row["LNG"];
    $P_PHOTO=$row["P_PHOTO"];
    $P_SECTION=$row["P_SECTION"];
    if ( $P_HIDE == 0 ) $granted_address=true;
    else if ( check_rights($id,2) and check_rights($id,24)) $granted_address=true;
    else if ( $P_SECTION == "" ) $granted_address=true;
    else if ( check_rights($id,2,$P_SECTION))  $granted_address=true;
    else  $granted_address=false;
    if ( $granted_address ) $ADDRESS=str_replace("'","\'",fixcharset($row["ADDRESS"]));
    else $ADDRESS="";
  
    $DATE_LOC=$row["DATE_LOC"];
    if ( $P_NOM <> '' ) {
        $name=strtoupper($P_NOM)." ".my_ucfirst($P_PRENOM);
        $name=str_replace("'","",fixcharset($name));
        $url="upd_personnel.php?pompier=".$P_ID;
        $link="<i class=\"fa fa-user fa-lg\"></i><a href=".$url."> <span class=blue12>".$name."</span></a>";
    }
    else {
        $name=$phone_prefix.$P_ID;
        $url="";
        $link="<i class=\"fa fa-phone fa-lg\"></i> <span class=blue12>".$name."</span>";
    }
    $POSITION="Lat ".$row["LAT"].", Lng ".$row["LNG"];
    $POSITION_GPS="<b>Position GPS: </b>".$POSITION; 
    if ( $ADDRESS <> "" ) $ADDRESS_HTML = "<b>Adresse GPS: </b>".$ADDRESS;
    else $ADDRESS_HTML = "";

    if( strlen($L_LAT) > 1 ){
        if ( $P_PHOTO <> '' and  is_file($trombidir.'/'.$P_PHOTO))
            $ICON = $trombidir.'/'.$P_PHOTO;
        else if ( $P_SEXE == 'Z' )
            $ICON ='images/phone.png';
        else if ( $P_SEXE == 'M' )
            $ICON ='images/sitac/boy.png';
        else
            $ICON = 'images/sitac/girl.png';

    $map_data .= "
var icon".$P_ID." = new google.maps.MarkerImage(
    '".$ICON."',
    null, /* size is determined at runtime */
    null, /* origin is 0,0 */
    null, /* anchor is bottom center of the scaled image */
    new google.maps.Size(40, 45)
);";
            
    $map_data .= "
var pers".$P_ID." = new google.maps.Marker({
    position: new google.maps.LatLng(".$L_LAT.",".$L_LNG."),
    title:\"".$name." ".$POSITION." ".$ADDRESS.", localisation GPS: ".$DATE_LOC."\",
    url: '".$url."',
    icon:icon".$P_ID.",
    map: map
});

var contentString".$P_ID."= '<div id=content>".$link."<p>".$ADDRESS_HTML."<p>".$POSITION_GPS."<p><b>Localisation GPS: </b>".$DATE_LOC."</div><p>';
    
var infowindow".$P_ID." = new google.maps.InfoWindow({
    content: contentString".$P_ID."
});

google.maps.event.addListener(pers".$P_ID.", 'click', function() {
    infowindow".$P_ID.".open(map,pers".$P_ID.");
  });

";
    }
    // point de centrage par d�faut sur la derni�re personne trouv�e
    if ( $centerlat == 0 ) {
        $centerlat=$L_LAT;
        $centerlng=$L_LNG;
    }
}
?>
<script type='text/javascript' src='<?php echo $google_maps_url; ?>'></script>
<script type='text/javascript' src='js/popupBoxes.js'></script>
<script language="JavaScript">
function orderfilter(p1,p2,p3){
    self.location.href="gps.php?filter="+p1+"&subsections="+p2+"&competence="+p3;
    return true
}

function orderfilter2(p1,p2,p3){
     if (p2.checked) s = 1;
     else s = 0;
    self.location.href="gps.php?filter="+p1+"&subsections="+s+"&competence="+p3;
    return true
}

function add_person(){
    self.location.href="localize.php";
    return true
}

var myMapTypeId = google.maps.MapTypeId.<?php echo strtoupper($maptypeid); ?>;
 
<?php if ( $centerlat <> 0 ) { ?>

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
    var pointc = new google.maps.LatLng(<?php echo $centerlat; ?>, <?php echo $centerlng; ?>);  
    var myOptions = {
        zoom: <?php echo $zoom; ?>,
        center: pointc,
        mapTypeId: myMapTypeId
    };
    var map = new google.maps.Map(document.getElementById("map_canvas"),
    myOptions);

<?php echo $map_data; ?>

google.maps.event.addListener(map, 'zoom_changed', function() {
    var newZoomLevel = map.getZoom();
    $.post(
        'gps_save2.php',
        {zoomlevel: newZoomLevel, },
            function(responseText){
                $('#result').html(responseText);
        },
        'html'
    );
 });
google.maps.event.addListener(map, 'maptypeid_changed', function() {
    var newMapTypeId = map.getMapTypeId();
    $.post(
        'gps_save2.php',
        {maptypeid: newMapTypeId},
            function(responseText){
                $('#result').html(responseText);
        },
        'html'
    );
 });
google.maps.event.addListener(map, 'center_changed', function() {
    var newCenter = map.getCenter();
    var newLat = newCenter.lat();
    var newLng = newCenter.lng();
    $.post(
        'gps_save2.php',
        {centerlat: newLat, centerlng: newLng },
        function(responseText){
            $('#result').html(responseText);
        },
        'html'
    );
  });
}

<?php } ?>
</script>
</head>
 
<?php
if ( $centerlat <> 0 ) 
    echo "<body onload=\"initialise();\">";
else echo "<body>";

//print getaddress('43.6201','7.08015');

echo "<div align=center><font size=4><b>$title</b></font> <span class='badge'>$number</span> localisations depuis $time minutes ";
$SMS=false;
if ( check_rights($id, 23) and $SMS_CONFIG[1] <> 0) {
    $credits = get_sms_credits($mysection);
    if ( intval($credits) > 0  or $credits == "Solde illimit�" or $credits == "OK" ) $SMS=true;
}
if ( $SMS )
    echo "<input type='button' class='btn btn-primary' value='Ajouter' onclick='javascript:add_person();' title=\"forcer la localisation d'une personne en lui envoyant un SMS\">";
echo " <input type='button' class='btn btn-default' value='Retour' name='annuler' onclick=\"javascript:history.back(1);\">";
echo "<p><table class='noBorder'>";

echo "<tr>";
echo "<td>Section</td><td> <select id='filter' name='filter' 
        onchange=\"orderfilter(document.getElementById('filter').value,'".$subsections."','".$competence."')\">";
display_children2(-1, 0, $filter, $nbmaxlevels, $sectionorder);
echo "</select>";
    
echo "</td></tr>";
if ( get_children("$filter") <> '' ) {
    if ($subsections == 1 ) $checked='checked';
    else $checked='';
    echo "<tr><td></td><td align=left><input type='checkbox' name='sub' $checked
       onClick=\"orderfilter2('".$filter."', this,'".$competence."')\"/>
       <font size=1>inclure les $sous_sections</td>";
}
echo "</tr>";



if ( $competences ) {
    echo "<tr><td></td>
    <td><select id='competence' name='competence' 
        onchange=\"orderfilter('".$filter."','".$subsections."',document.getElementById('competence').value)\">";
    $query2="select p.PS_ID, p.DESCRIPTION, e.EQ_NOM, e.EQ_ID from poste p, equipe e 
           where p.EQ_ID=e.EQ_ID
           order by p.EQ_ID, p.PS_ORDER";
    $result2=mysqli_query($dbc,$query2);
    $prevEQ_ID=0;
    echo "<option value=0";
    if ($competence == 0 ) echo " selected ";
    echo ">Pas de filtre sur les comp�tences</option>";
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

echo "<tr><td colspan=4>";
echo "</td></tr></table>";

// les personnes et num�ros de t�l�phone que je recherches sont-ils localis�s?
$query="select d.P_ID, P_NOM, P_PRENOM 
        from demande d left join pompier p on d.P_ID = p.P_ID
        where d.D_BY=".$id." 
        and d.D_TYPE='gps'";
$result=mysqli_query($dbc,$query);
$msg="";
while ($row=mysqli_fetch_array($result)) {
    $P_ID=$row["P_ID"];
    if ( in_array($row["P_ID"], $localized)) {
        if ( $row["P_NOM"] <> '' ) $msg .=" ".my_ucfirst($row["P_PRENOM"])." ".strtoupper($row["P_NOM"]).",";
        else $msg .= $phone_prefix.$row["P_ID"];
        $query2="delete from demande where P_ID=".$P_ID." and D_TYPE='gps' and D_BY=".$id;
        $result2=mysqli_query($dbc,$query2);
    }
}
if ( $msg <> "" ) {
   echo "<div class='alert alert-success' role='alert'> Votre demande de g�olocalisation a abouti pour ".rtrim($msg,",").".</div>";
}

if ( $centerlat <> 0 ) {
echo "<div id='map_canvas' ></div>";
}
else {
    echo "<div class='alert alert-info' role='alert'> Pas de donn�es GPS de personnel localis� depuis $time minutes </div>";
}

writefoot();

?>
