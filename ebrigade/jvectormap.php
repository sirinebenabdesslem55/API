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
include_once ("fonctions_map.php");
$urlExec = $_SERVER['PHP_SELF'];
check_all(27);
writehead();
if ( isset($_GET["param"])) $param=$_GET["param"];
else $param=0;
if ( isset($_GET["map_mode"])) $map_mode=$_GET["map_mode"];
else $map_mode=6;

$_SESSION["map_mode"]=$map_mode;
$_SESSION["param"]=$param;

$maps = array(
    0 => "<optgroup class='categorie' label='Affichage du Personnel'>",
    1 => "Opérations de secours - participants",
    2 => "Autres Opérations - participants",
    3 => "Formations - participants",
    4 => "Veille opérationnelle",
    5 => "Personnel disponible",
    6 => "Membres",
    7 => "<optgroup class='categorie' label='Affichage des Véhicules et du Matériel'>",
    8 => "Matériel ".$cisname,
    9 => "Véhicules",
    10 => "Matériel de pompage",
    11 => "Matériel hébergement urgence",
    12 => "<optgroup class='categorie' label='Affichage des Compétences du personnel'>",
    13 => "Compétences",
    14 => "<optgroup class='categorie' label='Affichage des événements en cours'>",
    15 => "Evénements"
);

?>
<link rel="stylesheet" href="css/jquery-jvectormap-2.0.3.css" type="text/css" media="screen"/>
<script src="js/jquery-jvectormap-2.0.3.min.js"></script>
<script src="js/jquery-jvectormap-fr-merc.js"></script>
<script language="JavaScript">
function orderfilter(report, param){
     self.location.href="jvectormap.php?map_mode="+report+"&param="+param;
     return true;
}
$('#map').vectorMap({map: 'fr_merc'});
</script>
</head>
<?php

echo "<body>
    <div align=center>
    <table class='noBorder'><tr>
    <td>Choisir une carte</td>
    <td><select id='report' name='report' 
        onchange=\"orderfilter(document.getElementById('report').value, '0')\">";
        
foreach ($maps as $i => $value) {
    if ( strpos($value,"optgroup")) 
        echo $value;
    else {
        if ($map_mode  == $i ) $selected='selected';
        else $selected ='';
        echo "<option value='$i' $selected>".$value."</option>";
    }
}
echo "</select></td></tr>";

if ( $map_mode == 13 ) {
    echo "<tr><td>Compétence</td>
    <td><select id='param' name='param' 
        onchange=\"orderfilter('".$map_mode."', document.getElementById('param').value)\">";
    $query2="select p.PS_ID, p.DESCRIPTION, e.EQ_NOM, e.EQ_ID from poste p, equipe e 
           where p.EQ_ID=e.EQ_ID
           order by p.EQ_ID, p.PS_ID";
    $result2=mysqli_query($dbc,$query2);
    $prevEQ_ID=0;
    echo "<option value=0";
    if ($param == 0 ) echo " selected ";
    echo ">Choisir une compétence.....</option>";
    while (custom_fetch_array($result2)) {
        if ( $prevEQ_ID <> $EQ_ID ) echo "<OPTGROUP LABEL='".$EQ_NOM."'>";
        $prevEQ_ID=$EQ_ID;
        echo "<option value='".$PS_ID."'";
        if ($PS_ID == $param ) echo " selected ";
        echo ">".$DESCRIPTION."</option>\n";
    }
    echo "</select></td></tr>";
}

if ( $map_mode == 15 ) {
    echo "<tr><td>Evénements</td>
    <td><select id='param' name='param' 
        onchange=\"orderfilter('".$map_mode."', document.getElementById('param').value)\">";
    $query2="select c.CEV_DESCRIPTION, c.CEV_CODE, t.TE_CODE, t.TE_LIBELLE
            from type_evenement t , categorie_evenement c
            where t.CEV_CODE = c.CEV_CODE
            order by c.CEV_DESCRIPTION, t.TE_LIBELLE asc";
    $result2=mysqli_query($dbc,$query2);
    $prevCAT="";
    echo "<option value=0";
    if ($param == 0 ) echo " selected ";
    echo ">Choisir un type d'événement .....</option>";
    
    echo "<option value=ALL";
    if ($param == 'ALL' ) echo " selected ";
    echo ">Tous les événements</option>";
    while (custom_fetch_array($result2)) {
        if ( $prevCAT <> $CEV_CODE ) echo "<OPTGROUP LABEL='".$CEV_DESCRIPTION."'>";
        $prevCAT=$CEV_CODE;
        echo "<option value='".$TE_CODE."'";
        if ($TE_CODE == $param ) echo " selected ";
        echo ">".$TE_LIBELLE."</option>\n";
    }
    echo "</select></td></tr>";
}

echo "</table><br>";

if ( $param > 0 or $map_mode <> 14 ){
    if (is_iphone() ) {
        $w=400;
        $h=520;
    }
    else {
        $w=490;
        $h=600;
    }
    echo "<div id='vector-map' style='width: ".$w."px; height: ".$h."px' style='background-color:$mydarkcolor;'></div>";
?>
<script>
<?php print get_map_data($map_mode, $param);?>
$(function(){
    $('#vector-map').vectorMap({
        map: 'fr_merc',
        backgroundColor: <?php echo "'".$mydarkcolor."'"; ?>,
        series: {
            regions: [{
                values: Data,
                scale: ['#FFF9C4', '#FFEB3B', '#FDD835', '#FBC02D', '#F9A825', '#F57F17'],
                normalizeFunction: 'linear',
                legend: {
                    vertical: false,
                    title: <?php echo "'".$maps[$map_mode]."'"; ?>
                }
            }]
        },
        onRegionTipShow: function(e, el, code){
            var nb = 0;
            var suffix='';
            if (code in Data) {
                nb = Data[code];
                if ( nb > 1 ) suffix = 's';
            }
            el.html(el.html()+': '+ nb + ' ' + <?php echo "'".$name."'"; ?> + suffix );
        }
    });
});
</script>
<?php

}
writefoot();
?>
