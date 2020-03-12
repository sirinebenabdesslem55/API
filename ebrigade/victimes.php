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
$mysection=$_SESSION['SES_SECTION'];
writehead();

if (isset ($_GET["action"])) $action=$_GET["action"];
elseif (isset ($_POST["action"])) $action=$_POST["action"];
else $action='update';

if (isset ($_GET["numinter"])) $numinter=intval($_GET["numinter"]);
elseif (isset ($_POST["numinter"])) $numinter=intval($_POST["numinter"]);
else $numinter="0";

if (isset ($_GET["numcav"])) $numcav=intval($_GET["numcav"]);
elseif (isset ($_POST["numcav"])) $numcav=intval($_POST["numcav"]);
else $numcav="0";

if (isset ($_GET["victime"])) $victime=intval($_GET["victime"]);
elseif (isset ($_POST["victime"])) $victime=intval($_POST["victime"]);
else $victime="0";

if (isset ($_GET["from"])) $from=$_GET["from"];
else if (isset ($_POST["from"])) $from=$_POST["from"];
else $from='default';

get_session_parameters();


function write_cnil_warning() {
    $cmt="Attention, l'identit� compl�te des victimes ne doit pas �tre enregistr�e. Il n'y a pas d'agr�ment CNIL pour cela. Seules les initiales du nom peuvent �tre enregistr�es sur cette fiche. Les lettres saisies en plus seront supprim�es. Le pr�nom par contre peut �tre renseign�.";
    return " <a href='#' data-toggle='popover' title='Attention CNIL' data-trigger='hover' data-content=\"".$cmt."\"><i class='fa fa-exclamation-triangle fa-lg' style='color:orange;' ></i></a>";
}

//=====================================================================
// check_security
//=====================================================================
$granted_update=false;
$responsable=0;
if ( $victime > 0 ) {
    $query="select E_CODE from evenement_log where EL_ID=(select EL_ID from victime where VI_ID=".$victime.")";
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $evenement=intval($row[0]);
    $query="select E_CODE, CAV_RESPONSABLE from centre_accueil_victime where CAV_ID=(select CAV_ID from victime where VI_ID=".$victime.")";
    $result=mysqli_query($dbc,$query);
    $row1=@mysqli_fetch_array($result);
    $responsable=intval($row1[1]);
    $evenement1=intval($row1[0]);
    if ( $evenement1 > 0 ) $evenement=$evenement1;
    if (  $evenement_victime == 0 ) $evenement_victime = $evenement;
}
else if ( $numinter > 0 ) {
    $query="select E_CODE from evenement_log where EL_ID=".$numinter;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $evenement=$row[0];
}
else if ( $numcav > 0 ) {
    $query="select E_CODE, CAV_RESPONSABLE from centre_accueil_victime where CAV_ID=".$numcav;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $evenement=$row[0];
    $responsable=intval($row[1]);
}
else if ( isset($_POST["type_victime"])) {
    $type_victime=intval($_POST["type_victime"]);
    $query="select E_CODE, CAV_RESPONSABLE from centre_accueil_victime where CAV_ID=".$type_victime;
    $result=mysqli_query($dbc,$query);
    $row=@mysqli_fetch_array($result);
    $evenement=$row[0];
    $responsable=intval($row[1]);
}
else if ( $evenement_victime > 0 ) $evenement = $evenement_victime;
if ( $responsable == $id ) $granted_update=true;
else if ( check_rights($id, 15, (get_section_organisatrice ( $evenement )))) $granted_update=true;
else if ( is_chef_evenement($id, $evenement) ) $granted_update=true;
else if ( is_operateur_pc($id,$evenement)) $granted_update=true;

if ($granted_update)
    $disabled='';
else  {
    $disabled='disabled';
    check_all(15);
    check_all(24);
}

?>
<STYLE type="text/css">
.RedClass{color:red;font-size:10pt;}
.BlueClass{color:blue;font-size:10pt;}
.PurpleClass{color:purple;font-size:10pt;}
.PinkClass {color:<?php echo $mydarkcolor; ?>; background-color:#FFC0C0; font-weight:bold;};
.TITLE {color:black;  font-weight: bold;font-size:10pt;}
.yellow { background-color: yellow; color:red; font-weight:bold;};
.normal { background-color: <?php echo $mylightcolor; ?>; }
.highlighted { background-color: yellow;}
</STYLE>
<script type='text/javascript' src='js/popupBoxes.js'></script>
<script type="text/javascript" src="js/checkForm.js"></script>
<script type='text/javascript' src='js/victimes.js'></script>
<script language="JavaScript">
function checkFilled(field) {
    var t = field.type;
    var color0 = "white";
    var color1 = "yellow";
    var color2 = "<?php echo $mylightcolor; ?>"
    if ( t == 'select-one' || t == 'text' || t == 'textarea') {
        if ( field.value == '' ) 
            field.style.backgroundColor = color0;
        else 
            field.style.backgroundColor = color1;
    }
    if ( t == 'checkbox' || t == 'radio') {
        var id = field.getAttribute("id");
        var label1 = document.getElementById('label_' + id);
        if ( field.checked ) label1.style.backgroundColor = color1;
        else label1.style.backgroundColor = color2;
        if ( t == 'radio') {
            var words = id.split('_');
            if ( words[1]  == 'oui' ) {
                var label2 = document.getElementById('label_' + words[0] + '_non');
                if ( field.checked ) label2.style.backgroundColor = color2;
            }
            if ( words[1]  == 'non' ) {
                var label2 = document.getElementById('label_' + words[0] + '_oui');
                if ( field.checked ) label2.style.backgroundColor = color2;
            }
        }
        if ( id == 'reparti') {
            var label2 = document.getElementById('label_transport');
            if ( field.checked ) label2.style.backgroundColor = color2;
        }
        if ( id == 'transport') {
            var label2 = document.getElementById('label_reparti');
            if ( field.checked ) label2.style.backgroundColor = color2;
        }
    }
}
$(document).ready(function(){
    $('[data-toggle="popover"]').popover();
});
</script>
</head>
<?php


//=====================================================================
// traiter delete
//=====================================================================

if (isset ($_GET["victime"]) and $action=='delete' and $granted_update) {
    if (isset ($_GET["numinter"])) $numinter=intval($_GET["numinter"]);
    else $numinter = 0;
    $query="delete from victime where VI_ID=".$victime;
    $result=mysqli_query($dbc,$query);
    $query="delete from bilan_victime where V_ID=".$victime;
    $result=mysqli_query($dbc,$query);
    
    $_SESSION['from_interventions']=1;
    
    if ( $numinter > 0 ) echo "<body onload=\"ready('".$numinter."');\">";
    else echo "<body onload=\"readyliste2('".$evenement."');\">";
    exit;
}

//=====================================================================
// Sauver
//=====================================================================

if ( isset ($_POST["victime"])  and ($action=='update' or $action=='insert') and $granted_update) {
    
    $VI_NOM=secure_input($dbc,$_POST["nom"]);
    $VI_PRENOM=secure_input($dbc,$_POST["prenom"]);
    if ($_POST["age"] == '' ) $VI_AGE='null';
    else $VI_AGE=intval($_POST["age"]);
    if ( isset($_POST["sexe"])) $VI_SEXE=secure_input($dbc,$_POST["sexe"]);
    else $VI_SEXE='M';
    if ( isset($_POST["pays"])) $VI_PAYS=intval($_POST["pays"]); 
    else $VI_PAYS=$default_pays_id;
    $VI_BIRTHDATE=secure_input($dbc,$_POST["date_naissance"]);
    
    if (! $store_confidential_data ) $VI_NOM=substr($VI_NOM,0,1);
    if ( $VI_NOM == "" and $VI_PRENOM == "") $VI_NOM="?";
    if ( isset($_POST["address"])) $VI_ADDRESS=secure_input($dbc,$_POST["address"]);
    else $VI_ADDRESS='';
    if ( isset($_POST["numinter"]))  $EL_ID=intval($_POST["numinter"]);
    else $EL_ID = 0;
    if ( isset($_POST["numerotation"])) $VI_NUMEROTATION=intval($_POST["numerotation"]);
    else $VI_NUMEROTATION=0;

    if ( $VI_NUMEROTATION == '0' ) $VI_NUMEROTATION='null';
    if ( isset($_POST["modevictime"])) $modevictime=$_POST["modevictime"];
    else $modevictime='simple';
    if ( isset($_POST["detresse_vitale"])) $VI_DETRESSE_VITALE=1; else $VI_DETRESSE_VITALE=0;
    if ( isset($_POST["decede"])) $VI_DECEDE=1; else $VI_DECEDE=0;
    if ( isset($_POST["malaise"])) $VI_MALAISE=1; else $VI_MALAISE=0;
    if ( isset($_POST["soins"])) $VI_SOINS=1; else $VI_SOINS=0;
    if ( isset($_POST["medicalise"])) $VI_MEDICALISE=1; else $VI_MEDICALISE=0;
    if ( isset($_POST["transport"])) $VI_TRANSPORT=1; else $VI_TRANSPORT=0;
    if ( isset($_POST["alimentation"])) $VI_ALIMENTATION=1; else $VI_ALIMENTATION=0;
    if ( isset($_POST["traumatisme"])) $VI_TRAUMATISME=1; else $VI_TRAUMATISME=0;
    if ( isset($_POST["repos"])) $VI_REPOS=1; else $VI_REPOS=0;
    if ( isset($_POST["reparti"])) $VI_REPARTI=1; else $VI_REPARTI=0;
    if ( isset($_POST["vetements"])) $VI_VETEMENT=1; else $VI_VETEMENT=0;
    if ( isset($_POST["information"])) $VI_INFORMATION=1; else $VI_INFORMATION=0;
    if ( isset($_POST["refus"])) $VI_REFUS=1; else $VI_REFUS=0;
    if ( isset($_POST["implique"])) $VI_IMPLIQUE=1; else $VI_IMPLIQUE=0;
    if ( isset($_POST["commentaire"])) $VI_COMMENTAIRE=secure_input($dbc,$_POST["commentaire"]); else $VI_COMMENTAIRE='';
    if ( isset($_POST["destination"])) $D_CODE=secure_input($dbc,$_POST["destination"]);
    else  $D_CODE='NR';
    if ( isset($_POST["transporteur"])) $T_CODE=secure_input($dbc,$_POST["transporteur"]);
    else if ( $nbsections > 0 )  $T_CODE='SP';
    else  $T_CODE='ASS';
    $year=0;
    if ( $VI_BIRTHDATE <> '' ) {
        $tmp=explode ( "-",$VI_BIRTHDATE); $year=$tmp[2]; $month=$tmp[1]; $day=$tmp[0];
        $VI_BIRTHDATE="'".$year."-".$month."-".$day."'";
    }
    else $VI_BIRTHDATE='null';
    if ( isset($_POST["type_victime"])) $type_victime=intval($_POST["type_victime"]);
    else  $type_victime=0;
    if ( isset($_POST["regulated"])) $regulated=intval($_POST["regulated"]);
    else  $regulated=0;
    if ( isset($_POST["date_in"]) and $_POST["date_in"] <> "") {
            $date_in=secure_input($dbc,$_POST["date_in"]);
            $tmp=explode("-",$date_in); $year=$tmp[2]; $month=$tmp[1]; $day=$tmp[0];
            $date_in=$year."-".$month."-".$day;
    }
    else if (  $type_victime > 0 ) $date_in = date('Y-m-d');
    else $date_in="";
    if ( isset($_POST["time_in"]) and $_POST["time_in"] <> "") $time_in=secure_input($dbc,$_POST["time_in"]);
    else  $time_in=date('H:i');
    if ( isset($_POST["date_out"]) and $_POST["date_out"] <> "") {
            $date_out=secure_input($dbc,$_POST["date_out"]);
            $tmp=explode("-",$date_out); $year=$tmp[2]; $month=$tmp[1]; $day=$tmp[0];
            $date_out=$year."-".$month."-".$day;
    }
    else  $date_out="";
    if ( isset($_POST["time_out"])) $time_out=secure_input($dbc,$_POST["time_out"]);
    else  $time_out="";
    if ( isset($_POST["heure_hopital"])) $heure_hopital=secure_input($dbc,$_POST["heure_hopital"]);
    else  $heure_hopital="";
    if ( $heure_hopital == "" ) $heure_hopital='null';
    else $heure_hopital = "'".$heure_hopital."'";
    if ( isset($_POST["raison"])) $raison=secure_input($dbc,$_POST["raison"]);
    else  $raison="";
    if ( isset($_POST["identification"])) $identification=secure_input($dbc,$_POST["identification"]);
    else  $identification="";

    if ( $type_victime > 0 ) {
        $entree_cav=$date_in." ".$time_in;
        if ( $time_out <> "" ) {
            if ( $date_out == "" ) $date_out = $date_in;
        }
        $sortie_cav=$date_out." ".$time_out;
        if ( $entree_cav == " " ) $entree_cav='null';
        else $entree_cav = "'".$entree_cav."'";
        if ( $sortie_cav == " " ) $sortie_cav='null';
        else $sortie_cav = "'".$sortie_cav."'";
    }
    else {
        $entree_cav='null';
        $sortie_cav='null';
    }
    
    // on insert only
    if ( $action=='insert') {
        $query="select max(VI_NUMEROTATION) from victime where EL_ID in (select EL_ID from evenement_log where E_CODE=".$evenement.")
                or CAV_ID in (select CAV_ID from centre_accueil_victime where E_CODE=".$evenement.")";
        $result=mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        $VI_NUMEROTATION=intval($row[0]) + 1;
        
        $query="insert into victime (CAV_ID, EL_ID, VI_NOM, VI_PRENOM, VI_ADDRESS, VI_SEXE, VI_PAYS, VI_BIRTHDATE, VI_AGE, VI_NUMEROTATION)
                values (".$type_victime.",".$EL_ID.",\"".strtolower($VI_NOM)."\",\"".strtolower($VI_PRENOM)."\",\"".$VI_ADDRESS."\",'".$VI_SEXE."',".$VI_PAYS.",".$VI_BIRTHDATE.",".$VI_AGE.",".$VI_NUMEROTATION.")";
        $result=mysqli_query($dbc,$query);
        
        
        $query="select max(VI_ID) from victime where VI_NUMEROTATION =".$VI_NUMEROTATION." and VI_NOM=\"".strtolower($VI_NOM)."\" and VI_PRENOM=\"".strtolower($VI_PRENOM)."\"";
        $result=mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        $victime=intval($row[0]);
        
    } 
    else { // on update only
        $query="update victime set
            VI_NOM= \"".strtolower($VI_NOM)."\",
            VI_PRENOM=\"".strtolower($VI_PRENOM)."\",
            VI_SEXE='".$VI_SEXE."',
            VI_BIRTHDATE=".$VI_BIRTHDATE.",
            VI_ADDRESS=\"".$VI_ADDRESS."\",
            VI_NUMEROTATION=".$VI_NUMEROTATION.",
            VI_AGE=".$VI_AGE."
            where VI_ID=".$victime;
        $result=mysqli_query($dbc,$query);
        
    }
    
    // always
    $query="update victime set VI_AGE = (YEAR(CURRENT_DATE)-YEAR(VI_BIRTHDATE))- (RIGHT(CURRENT_DATE,5)<RIGHT(VI_BIRTHDATE,5)) 
        where VI_ID=".$victime." and VI_BIRTHDATE is not null";
    $result=mysqli_query($dbc,$query);
    
    if ( $modevictime == 'simple' ) {
        $query="update victime set
        VI_NOM= \"".strtolower($VI_NOM)."\",
        VI_PRENOM=\"".strtolower($VI_PRENOM)."\",
        VI_ADDRESS=\"".$VI_ADDRESS."\",
        VI_SEXE='".$VI_SEXE."',
        VI_BIRTHDATE=".$VI_BIRTHDATE.",
        VI_AGE=".$VI_AGE.",
        VI_DETRESSE_VITALE=".$VI_DETRESSE_VITALE.",
        VI_DECEDE=".$VI_DECEDE.",
        VI_MALAISE=".$VI_MALAISE.",
        VI_SOINS=".$VI_SOINS.",
        VI_MEDICALISE=".$VI_MEDICALISE.",
        VI_TRANSPORT=".$VI_TRANSPORT.",
        VI_VETEMENT=".$VI_VETEMENT.",
        VI_ALIMENTATION=".$VI_ALIMENTATION.",
        VI_TRAUMATISME=".$VI_TRAUMATISME.",
        VI_REPOS=".$VI_REPOS.",
        VI_REPARTI=".$VI_REPARTI.",
        VI_INFORMATION=".$VI_INFORMATION.",
        VI_REFUS=".$VI_REFUS.",
        VI_IMPLIQUE=".$VI_IMPLIQUE.",
        VI_NUMEROTATION=".$VI_NUMEROTATION.",
        VI_PAYS=".$VI_PAYS.",
        D_CODE='".$D_CODE."',
        T_CODE='".$T_CODE."',
        VI_COMMENTAIRE=\"".$VI_COMMENTAIRE."\",
        CAV_ID=".$type_victime.",
        CAV_ENTREE=".$entree_cav.",
        CAV_SORTIE=".$sortie_cav.",
        CAV_REGULATED=".$regulated.",
        CAV_RAISON=\"".$raison."\",
        IDENTIFICATION=\"".$identification."\",
        HEURE_HOPITAL=".$heure_hopital."
        where VI_ID=".$victime;
        $result=mysqli_query($dbc,$query);
    
        if ( $VI_DECEDE == 1 or $VI_DETRESSE_VITALE == 1 ) {
            $query2=" update evenement_log set EL_IMPORTANT = 1 where EL_ID=".$EL_ID;
            $result2=mysqli_query($dbc,$query2);
        }
        
    }
    else {
        // fiche victime compl�te
        if ( $modevictime == 'full' ) $BVC_PAGE='PSE';
        else $BVC_PAGE='PSSP';
            
        $query2="delete from bilan_victime where V_ID=".$victime." and BVP_ID in (select BVP_ID from bilan_victime_param where BVC_CODE in (select BVC_CODE from bilan_victime_category where BVC_PAGE='".$BVC_PAGE."')";
        $result2=mysqli_query($dbc,$query2);
        $query2="select BVP_ID, BVP_TYPE from bilan_victime_param order by BVP_ID";
        $result2=mysqli_query($dbc,$query2);
        while ( $row2=@mysqli_fetch_array($result2)) {
            if ( isset($_POST["bilan".$row2[0]])) {
                $value=secure_input($dbc,$_POST["bilan".$row2[0]]);
                if ($value <> "" ) {
                    $query3="insert into bilan_victime (V_ID, BVP_ID, BVP_VALUE)
                         values (".$victime.",".$row2[0].",\"".$value."\")";
                    $result3=mysqli_query($dbc,$query3);
                }
            }
        }
    }
    
    // synchroniser les stats entre fiche simple et fiche compl�te
    if ( $modevictime =='simple' ) {
            $query3="delete from bilan_victime where V_ID=".$victime." and BVP_ID in (1050,1060,1070,1072)";
            $result3=mysqli_query($dbc,$query3);
            if ( $VI_TRANSPORT == 1 ) {
                $query3="insert into bilan_victime (V_ID, BVP_ID, BVP_VALUE)
                        values (".$victime.",1050,1)";
                $result3=mysqli_query($dbc,$query3);
                $query3="insert into bilan_victime (V_ID, BVP_ID, BVP_VALUE)
                        select ".$victime.",1060, BVP_INDEX
                        from bilan_victime_values
                        where BVP_ID=1060
                        and BVP_SPECIAL = '".$T_CODE."'";
                $result3=mysqli_query($dbc,$query3);
                $query3="insert into bilan_victime (V_ID, BVP_ID, BVP_VALUE)
                        select ".$victime.",1070, BVP_INDEX
                        from bilan_victime_values
                        where BVP_ID=1070
                        and BVP_SPECIAL = '".$D_CODE."'";
                $result3=mysqli_query($dbc,$query3);
                $query3="insert into bilan_victime (V_ID, BVP_ID, BVP_VALUE)
                        values (".$victime.",1072,".$heure_hopital.")";
                $result3=mysqli_query($dbc,$query3);
            }
    }
    else { // synchroniser les stats entre fiche compl�te et fiche simple
        $query3="update victime set VI_TRANSPORT=0 where VI_ID=".$victime;
        $result3=mysqli_query($dbc,$query3);
        
        $query3="select bvv.BVP_SPECIAL from bilan_victime_values bvv, bilan_victime bv
                where bv.BVP_ID=1060 
                and bv.BVP_ID=bvv.BVP_ID
                and bv.BVP_VALUE=bvv.BVP_INDEX
                and bv.V_ID=".$victime;
        $result3=mysqli_query($dbc,$query3);
        $row3=@mysqli_fetch_array($result3);
        if ($row3["BVP_SPECIAL"] <> '' ) {
            $query4="update victime set VI_TRANSPORT=1, T_CODE='".$row3["BVP_SPECIAL"]."' where VI_ID=".$victime;
            $result4=mysqli_query($dbc,$query4);
        }
        $query3="select bvv.BVP_SPECIAL from bilan_victime_values bvv, bilan_victime bv
                where bv.BVP_ID=1070 
                and bv.BVP_ID=bvv.BVP_ID
                and bv.BVP_VALUE=bvv.BVP_INDEX
                and bv.V_ID=".$victime;
        $result3=mysqli_query($dbc,$query3);
        $row3=@mysqli_fetch_array($result3);
        if ($row3["BVP_SPECIAL"] <> '' ) {
            $query4="update victime set VI_TRANSPORT=1, D_CODE='".$row3["BVP_SPECIAL"]."' where VI_ID=".$victime;
            $result4=mysqli_query($dbc,$query4);
        }
        $query3="select BVP_VALUE from bilan_victime where BVP_ID=1072 and V_ID=".$victime;
        $result3=mysqli_query($dbc,$query3);
        $row3=@mysqli_fetch_array($result3);
        if ( $row3["BVP_VALUE"] <> '' ) {
            $query4="update victime set HEURE_HOPITAL='".$row3["BVP_VALUE"]."' where VI_ID=".$victime;
            $result4=mysqli_query($dbc,$query4);        
        }
    }
    
    $_SESSION['from_interventions']=1;
    
    update_main_stats($evenement);

    if ( $from == 'list' ) echo "<body onload=\"readyliste('".$evenement."','".$type_victime."');\">";
    else if ( $from == 'intervention' )  echo "<body onload=\"ready('".$EL_ID."');\">";
    else if ( $from == 'evenement' )  echo "<body onload=\"redirect('".$evenement."');\">";
    else if ( $EL_ID > 0 ) echo "<body onload=\"ready('".$EL_ID."');\">";
    else echo "<body onload=\"readyliste2('".$evenement."');\">";
    exit;
}
//=====================================================================
// Fiche victime
//=====================================================================

if  ( $action == 'insert' ) {
    $VI_NOM='';
    $VI_PRENOM='';
    $VI_ADDRESS='';
    $VI_SEXE='M';
    $VI_BIRTHDATE='';
    $VI_AGE='';
    $VI_DETRESSE_VITALE=0;
    $VI_DECEDE=0;
    $VI_MALAISE=0;
    $VI_SOINS=0;
    $VI_MEDICALISE=0;
    $VI_TRANSPORT=0;
    $VI_VETEMENT=0;
    $VI_ALIMENTATION=0;
    $VI_TRAUMATISME=0;
    $VI_REPOS=0;
    $VI_REPARTI=0;
    $VI_INFORMATION=0;
    $VI_REFUS=0;
    $VI_IMPLIQUE=0;
    $D_CODE='HOSP';
    if ( $nbsections > 0 )  $T_CODE='SP';
    else  $T_CODE='ASS';
    $VI_PAYS=$default_pays_id;
    $VI_COMMENTAIRE='';
    $DATE_ENTREE=date('d-m-Y');
    $HEURE_HOPITAL="";
    $HEURE_ENTREE="";
    $DATE_SORTIE="";
    $HEURE_SORTIE="";
    $CAV_REGULATED=0;
    $IDENTIFICATION="";
    if (isset ($_GET["qrcode"])) {
        $IDENTIFICATION=secure_input($dbc,$_GET["qrcode"]);
        // chercher si d�j� enregistr�
        if ( $IDENTIFICATION <> '' ) {
            $query="select VI_ID from victime where IDENTIFICATION=\"".$IDENTIFICATION."\"";
            $result=mysqli_query($dbc,$query);
            $row=mysqli_fetch_array($result);
            $VI_ID=$row["VI_ID"];
            if ( intval($VI_ID) > 0 ) {
                $msgstring = "Il y a d�j� une victime enregistr�e avec ce num�ro d'identification ".$IDENTIFICATION;
                $msgstring .= "<br><p align=center><a href=victimes.php?victime=".$VI_ID."&from=list><input type='submit' class='btn btn-default' value='Voir victime'></a>";
                $msgstring .= "<br><p align=center><a class='btn btn-default' href='scan_victime.php?evenement=".$evenement_victime."' 
                    title='Scanner QR Code pour cr�er la fiche victime' ><i class='fa fa-qrcode fa-lg' style='color:purple;'></i> Retour</a>";
                write_msgbox("ERREUR", $error_pic, $msgstring,10,0);
                exit;
            }
        }
    }
    if (! isset($numinter)) $numinter=0;
}

else {
    $query="select VI_ID, EL_ID as 'numinter', CAV_ID, VI_NOM, VI_PRENOM, VI_SEXE, VI_ADDRESS, date_format(VI_BIRTHDATE, '%d-%m-%Y') VI_BIRTHDATE, VI_SEXE,
        VI_DETRESSE_VITALE, VI_INFORMATION, VI_SOINS, VI_MEDICALISE, VI_TRANSPORT, VI_VETEMENT, VI_ALIMENTATION, VI_TRAUMATISME, VI_DECEDE, VI_MALAISE, VI_REPOS, VI_PAYS,
        victime.D_CODE,victime.T_CODE,VI_COMMENTAIRE, VI_REFUS, VI_IMPLIQUE, VI_NUMEROTATION, destination.D_NAME, transporteur.T_NAME, VI_AGE, VI_REPARTI,
        date_format(CAV_ENTREE, '%d-%m-%Y') DATE_ENTREE, date_format(CAV_ENTREE, '%H:%i') HEURE_ENTREE,
        date_format(CAV_SORTIE, '%d-%m-%Y') DATE_SORTIE, date_format(CAV_SORTIE, '%H:%i') HEURE_SORTIE,
        date_format(HEURE_HOPITAL, '%H:%i') HEURE_HOPITAL,
        CAV_REGULATED, IDENTIFICATION
        from victime, destination , transporteur
        where VI_ID=".$victime."
        and victime.D_CODE = destination.D_CODE
        and victime.T_CODE = transporteur.T_CODE
        order by VI_NOM,VI_PRENOM";

    $result=mysqli_query($dbc,$query);
    custom_fetch_array($result);
   
    $VI_NOM=strtoupper($VI_NOM);
    $VI_PRENOM=my_ucfirst($VI_PRENOM);
    $numcav=$CAV_ID;
}

if ( $victime > 0 )  {
    $S_ID=get_section_organisatrice($evenement);
    $pdf="<a href=pdf_document.php?evenement=".$evenement."&section=".$S_ID."&mode=17&numinter=".$numinter."&victime=".$victime." target=_blank
          title=\"Afficher la fiche victime.\"><i class='far fa-file-pdf fa-lg' style='color:red;' ></i></a>"; 
}
else $pdf="";

if ( $numcav > 0 or $numcav == 'cav') $t = "Victime au centre d'accueil";
else $t = "Victime ou Personne prise en charge";

if ( $action == 'insert' ) $modevictime='simple';

if ( $modevictime == 'full' ) $icon='heartbeat';
else if ( $modevictime == 'pssp' ) $icon='user-md';
else $icon='user';

echo "<body>";
echo "<form name=r action=victimes.php method=POST>";  
echo "<div align=center>
        <table class='noBorder'>
        <tr><td width=50><i class='fa fa-".$icon." fa-3x'></i></td>
            <td><font size=4><b>".$t."</b></font></td>
            <td width=30 align=right>".$pdf."</td></tr>";
    
echo "</table></div>"; 

echo "<input type=hidden name='numinter' value='".$numinter."'>";
echo "<input type=hidden name='action' value='".$action."'>";
echo "<input type=hidden name='victime' value='".$victime."'>";
echo "<input type=hidden name='from' value='".$from."'>";
echo "<input type=hidden name='modevictime' value='".$modevictime."'>";
 
//=====================================================================
// tabs
//=====================================================================
echo  "<p><ul class='nav nav-tabs noprint'>";

if ( $modevictime == 'simple' ) $class='active';
else $class='';

echo "<li class='nav-item'>
    <a class='nav-link $class' href='victimes.php?victime=$victime&modevictime=simple&action=$action' title='Information sur la victime et bilan simplifi�' role='tab' aria-controls='tab1' href='#tab1' >
    <i class='fa fa-user'></i> <span>Fiche victime</span></a></li>";
 

$t='Fiche bilan secouriste PSE2 complet';
$icon="<i class='fa fa-heartbeat'></i>";
if ( $action == 'insert' ) {
    $class='';
    $icon="<i class='fa fa-ban' style='color:#CD5C5C;'></i>";
    $t='Cet onglet sera disponible lorsque la fiche aura ete cr��e';
}
else if ( $modevictime == 'full' ) $class='active';
else $class='';
echo "<li class='nav-item'>
    <a class='nav-link $class' href='victimes.php?victime=$victime&modevictime=full&action=$action' title=\"".$t."\" role='tab' aria-controls='tab2' href='#tab2' >
    ".$icon." Bilan Secouriste</span></a></li>";
 

$t='Fiche bilan premiers secours socio-psychologiques';
$icon="<i class='fa fa-user-md'></i>";
if ( $action == 'insert' ) {
    $class='';
    $icon="<i class='fa fa-ban' style='color:#CD5C5C;'></i>";
    $t='Cet onglet sera disponible lorsque la fiche aura ete cr��e';
}
else if ( $modevictime == 'pssp' ) $class='active';
else $class='';
echo "<li class='nav-item'>
    <a class='nav-link $class' href='victimes.php?victime=$victime&modevictime=pssp&action=$action' title=\"".$t."\" role='tab' aria-controls='tab3' href='#tab3' >
    ".$icon." Bilan PSSP</span></a></li>";

echo "</ul>";
// fin tabs

echo "<div id='export' style='position: relative;' align=center>";
 
//=====================================================================
// mode simple
//=====================================================================
if ( $modevictime == 'simple' ) {
    echo "<div align=center id=simple >
          <p>
              <table cellspacing=0 border=0>";
                  
    echo "<tr><td colspan=4 class=TabHeader>Informations Centre d'accueil </td></tr>";
    echo "<tr  bgcolor=$mylightcolor><td>Localisation </td>
         <td ><select id='type_victime' name='type_victime' onchange='putInCav();'>";

    if ( $numinter > 0 ) {
        if ( $numcav == 0 ) $selected="selected";
        else $selected="";
        echo "<option value='0' $selected>Sur Intervention</option>";
    }
    $query2="select c.CAV_ID, c.CAV_NAME
                from centre_accueil_victime c
                where c.E_CODE=".$evenement_victime."
                and c.CAV_OUVERT=1 
                or c.CAV_ID = ".intval($numcav)."
                order by c.CAV_NAME";
    $result2=mysqli_query($dbc,$query2);
    if ( @mysqli_num_rows($result2) > 0 ) {
        while (custom_fetch_array($result2)) {
            if ( $numcav == $CAV_ID ) $selected="selected";
            else $selected="";
            echo "<option value='".$CAV_ID."' $selected>".$CAV_NAME."</option>";
        }
    }
    echo "</select></td>";

    if ($CAV_REGULATED == 1 ) $checked='checked';
    else  $checked='';
    echo "<td align=right><input type='checkbox' name='regulated' id='regulated' value='1' $checked $disabled title='cocher si la victime a �t� r�gul�e par le m�decin ou le PC'></td>
         <td ><label for='regulated'>R�gul� par le m�decin ou le PC</label></td></tr>";

    $nbcav=count_entities("centre_accueil_victime", "E_CODE=".$evenement_victime." and CAV_OUVERT=1");
    if ( $nbcav == 0 or ( $numinter > 0 and $numcav == 0 )) $style="style='display:none'";
    else $style="";

    echo "<tr bgcolor=$mylightcolor id='rowtime1' $style>
          <td>Date arriv�e $asterisk</td>
          <td>
            <input type='text' name='date_in' size='10' maxlength='10'  value='".$DATE_ENTREE."' onfocus=\"fillDate(form.date_in);\" class='datepicker' data-provide='datepicker'
            onchange='checkDate2(form.date_in)' $disabled placeholder='JJ-MM-AAAA'>
            </td>       
          <td>Heure arriv�e $asterisk</td>
          <td>
            <input type='text' name='time_in' value='".$HEURE_ENTREE."' onfocus=\"fillTime(form.time_in);\" 
            placeholder='hh:mm' size=5 style='width:60px;' maxlength='5' 
            onchange=\"checkTime(form.time_in,'');\" $disabled >
          </td>";       
    echo "</tr>";

    echo "<tr bgcolor=$mylightcolor id='rowtime2' $style>
          <td><i>Date Sortie</i></td>
          <td>
            <input type='text' name='date_out' size='10' maxlength='10' value='".$DATE_SORTIE."' onfocus=\"fillDate(form.date_out);\" class='datepicker' data-provide='datepicker'
            onchange='checkDate2(form.date_out)' $disabled placeholder='JJ-MM-AAAA'>
            </td>
          <td><i>Heure Sortie</i></td>
          <td>
            <input type='text' name='time_out' value='".$HEURE_SORTIE."' onfocus=\"fillTime(form.time_out);fillDate(form.date_out);\" 
            onchange=\"checkTime(form.time_out,'');\" $disabled 
            placeholder='hh:mm' size=5 style='width:60px;' maxlength='5' >
          </td>";       
    echo "</tr>";
         

    echo "<tr><td colspan=4 class=TabHeader>Informations Victime</td></tr>";

    if ( $store_confidential_data ) {
        $cnil_warning="";
        $maxlength=30;
    }
    else {
        $cnil_warning = write_cnil_warning();
        $maxlength=1;
    }
      
    if ( isset($_GET["qrcode"])) $class='yellow';
    else $class='';
    echo "<tr bgcolor=$mylightcolor><td>Nom ".$cnil_warning."</td>
        <td><input name='nom' id='nom' type=text size=20 value=\"".$VI_NOM."\" $disabled maxlength='".$maxlength."' ></td>
        <td >Pr�nom </td>
        <td ><input name='prenom' id='prenom' type=text size=20 value=\"".$VI_PRENOM."\" $disabled></td></tr>
        <tr bgcolor=$mylightcolor><td>Identification</td>
        <td colspan=3><input name='identification' id='identification' type=text size=25 value=\"".$IDENTIFICATION."\" $disabled  class='$class'
        title='Saisir un identifiant optionnel, exemple un num�ro de bracelet de suivi'><small> <i>identifiant optionnel</i></small>
        </td></tr>";


    echo "<tr bgcolor=$mylightcolor>
              <td>Date naissance</td>
              <td><input type='text' id='date_naissance' name='date_naissance' size='10' maxlength='10' value='".$VI_BIRTHDATE."'
              onchange='checkDate2(form.date_naissance)' $disabled placeholder='JJ-MM-AAAA'>
              </td>
              <td class=small>et/ou age </td><td><input type='text' id='age' name='age' size='3' maxlength='3' value='".$VI_AGE."' 
                    onchange=\"changeAge('".$VI_AGE."');\"  title=\"Si la date de naissance est inconnue, saisissez l'age\" $disabled></td>";
    echo "</tr>";


    $checkedM=''; $checkedF=''; 
    if ( $VI_SEXE == 'M' ) $checkedM='checked';
    else $checkedF='checked';
    echo "<tr bgcolor=$mylightcolor><td align=right>Sexe $asterisk</td>
              <td><input type=radio name='sexe' id='sexe_M' value='M' $checkedM title='Masculin' $disabled > <label for='sexe_M' >M </label>
                  <input type=radio name='sexe' id='sexe_F' $checkedF value='F' title='F�minin' $disabled > <label for='sexe_F' >F </label></td>";
    echo "<td>Nationalit� $asterisk";
    $query2="select ID, NAME from pays order by ID asc";
    $result2=mysqli_query($dbc,$query2);
    echo " <td align=left><select name='pays' id='pays' $disabled title=\"Choisissez le pays correspondant � la nationalit� de la personne\">";
    while ($row2=@mysqli_fetch_array($result2)) {
        $_ID=$row2["ID"];
        $_NAME=$row2["NAME"];
        if ( $_ID == $VI_PAYS ) $selected='selected';
        else $selected='';
        echo "<option value='$_ID' $selected>".$_NAME."</option>";
    }
    echo "</select></td></tr>";

    if ( $action == 'update' ) {
        echo "<tr bgcolor=$mylightcolor id='rowNumerotation'>
              <td><i>Num�rotation victime</i></td>
              <td colspan=3><input type='text' name='numerotation' size=5 maxlength='5' value=\"".$VI_NUMEROTATION."\" $disabled 
                    onchange='checkNumber(form.numerotation,0)' title=\"Ce champ permet de num�roter les diff�rentes victimes d'une intervention ou d'un �v�nement\">
              </td>";       
        echo "</tr>";
    }

    echo "<tr bgcolor=$mylightcolor id='rowAddress'>
              <td><i>Adresse</i></td>
              <td colspan=3><input type='text' name='address' id='address' size=60 maxlength='150' value=\"".$VI_ADDRESS."\" $disabled >
              </td>";       
    echo "</tr>";

    echo "<tr><td colspan=4 class=TabHeader>Bilan simplifi�</td></tr>";
    
    $textsize=strlen($VI_COMMENTAIRE);
    echo "<tr bgcolor=$mylightcolor >
              <td><i>Commentaire<br>victime,<br>Bilan m�dical</i>
              <br><input type='text' name='comptage' size='4' value='$textsize' readonly title='nombre de caract�res saisis'
                style='FONT-SIZE: 10pt;border:0px; background:$mylightcolor; color:$mydarkcolor; font-weight:bold;'>
                <br><span class=small>1000 max</td>
              </td>
              <td colspan=3>
                <textarea name='commentaire' cols='60' rows='8'
                style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;'
                value=\"$VI_COMMENTAIRE\" $disabled
                onFocus='CompterChar(this,1000,r.comptage)' 
                onKeyDown='CompterChar(this,1000,r.comptage)' 
                onKeyUp='CompterChar(this,1000,r.comptage)' 
                onBlur='CompterChar(this,1000,r.comptage)'
                >".$VI_COMMENTAIRE."</textarea>
                
              </td>";
    echo "</tr>";


    echo "<tr bgcolor=$mylightcolor>";
    if ( $VI_DETRESSE_VITALE == 1 ) {$checked='checked'; $class='highlighted';}
    else  {$checked=''; $class='normal';}
    echo "  <td rowspan=7><i>Bilan <br>intervention</i></td>
              <td ><input type='checkbox' name='detresse_vitale' id='detresse_vitale' value='1' $checked $disabled onchange='checkFilled(this);'
              title=\"H�morragie, inconscience, ACR\"> <label for='detresse_vitale' id='label_detresse_vitale' class='$class'> D�tresse vitale</label> 
              </td>";
    if ( $VI_SOINS == 1 ) {$checked='checked'; $class='highlighted';}
    else  {$checked=''; $class='normal';}
    echo "<td colspan=2><input type='checkbox' name='soins' id='soins' value='1' $checked $disabled onchange='checkFilled(this);'
            title=\"Des soins ont �t� r�alis�s par l'�quipe de secouristes\" > <label for='soins'  id='label_soins' class='$class'> Soins r�alis�s</label> 
          </td>";
    echo "</tr>";

    echo "<tr bgcolor=$mylightcolor>";
    if ( $VI_DECEDE == 1 ) {$checked='checked'; $class='highlighted';}
    else  {$checked=''; $class='normal';}
    echo "<td ><input type='checkbox' name='decede' id='decede' value='1' $checked $disabled onchange='checkFilled(this);'
            title=\"La victime est d�c�d�e\" > <label for='decede' id='label_decede' class='$class'> D�c�d�(e)</label> 
          </td>";

    if ( $VI_MALAISE == 1 ) {$checked='checked'; $class='highlighted';}
    else  {$checked=''; $class='normal';}
    echo "<td colspan=2><input type='checkbox' name='malaise' id='malaise' value='1' $checked $disabled onchange='checkFilled(this);'
            title=\"La victime eu un malaise avec ou sans perte de connaissance\"> <label for='malaise' id='label_malaise' class='$class'> Malaise</label> 
          </td>";
    echo "</tr>";

    if ( $VI_TRAUMATISME == 1 ) {$checked='checked'; $class='highlighted';}
    else  {$checked=''; $class='normal';}
    echo "<tr bgcolor=$mylightcolor>";
    echo "<td ><input type='checkbox' name='traumatisme' id='traumatisme' onchange='checkFilled(this);'
            title=\"La victime a eu un traumatisme\"
            value=1 $checked $disabled> <label for='traumatisme' id='label_traumatisme' class='$class'> Traumatisme</label> 
          </td>";
          
    if ( $VI_MEDICALISE == 1 ) {$checked='checked'; $class='highlighted';}
    else  {$checked=''; $class='normal';};
    echo "<td colspan=2><input type='checkbox' name='medicalise' id='medicalise' onchange='checkFilled(this);'
            title=\"La victime a �t� m�dicalis�e\"
            value=1 $checked $disabled> <label for='medicalise' id='label_medicalise' class='$class'> M�dicalis�e</label> 
          </td>";
    echo "</tr>";

    echo "<tr bgcolor=$mylightcolor>";
    if ( $VI_VETEMENT == 1 ) {$checked='checked'; $class='highlighted';}
    else  {$checked=''; $class='normal';};
    echo "<td ><input type='checkbox' name='vetements' id='vetements'  value='1' $checked $disabled onchange='checkFilled(this);'
            title=\"Des v�tements ou une couverture ont �t� offerts � la victime\"> <label for='vetements' id='label_vetements' class='$class'> V�tements donn�s</label> 
          </td>";
    if ( $VI_ALIMENTATION == 1 ) {$checked='checked'; $class='highlighted';}
    else  {$checked=''; $class='normal';};
    echo "<td colspan=2><input type='checkbox' name='alimentation' id='alimentation'  value='1' $checked $disabled onchange='checkFilled(this);'
            title=\"Des aliments ou une boisson ont �t� offerts � la victime\"> <label for='alimentation' id='label_alimentation' class='$class'> Alimentation (repas, boisson)</label> 
          </td>";
    echo "</tr>";

    echo "<tr bgcolor=$mylightcolor>";
    if ( $VI_INFORMATION == 1 ) {$checked='checked'; $class='highlighted';}
    else  {$checked=''; $class='normal';};
    echo "  <td><input type='checkbox' name='information' id='information' value='1' $checked $disabled onchange='checkFilled(this);'
            title=\"La personne a �t� assist�e ou des renseignements, informations lui ont �t� donn�s\"> <label for='information' id='label_information' class='$class'> Personne assist�e</label> 
            </td>";

    if ( $VI_REFUS == 1 ) {$checked='checked'; $class='highlighted';}
    else  {$checked=''; $class='normal';};
    echo "<td colspan=2><input type='checkbox' name='refus' id='refus' onchange='checkFilled(this);'
            title=\"La victime a refus� d'�tre prise en charge\"
            value=1 $checked $disabled> <label for='refus' id='label_refus' class='$class'> Refus de prise en charge</label> 
          </td>";
    echo "</tr>";

    if ( $VI_REPOS == 1 ) {$checked='checked'; $class='highlighted';}
    else  {$checked=''; $class='normal';};
    echo "<tr bgcolor=$mylightcolor>";
    echo "<td ><input type='checkbox' name='repos' id='repos' onchange='checkFilled(this);'
            title=\"La victime a �t� mise au repos sous surveillance\"
            value=1 $checked $disabled> <label for='repos' id='label_repos' class='$class'> Repos</label> 
          </td>";
          
    if ( $VI_IMPLIQUE == 1 ) {$checked='checked'; $class='highlighted';}
    else  {$checked=''; $class='normal';};
    echo "<td colspan=2><input type='checkbox' name='implique' id='implique' onchange='checkFilled(this);'
            title=\"Impliqu� indemne\"
            value=1 $checked $disabled> <label for='implique' id='label_implique' class='$class'> Impliqu�</label> 
          </td>";
    echo "</tr>";

    echo "<tr bgcolor=$mylightcolor>";
    if ($VI_TRANSPORT == 1) {$checked='checked'; $class='highlighted';}
    else  {$checked=''; $class='normal';};
    echo "<td><input type='checkbox' name='transport' id='transport'
            title=\"La victime a �t� transport�e\"
            value=1 $checked $disabled
            onchange=\"checkFilled(this);changedType();\"> <label for='transport' id='label_transport' class='$class'> Transport</label> 
          </td>";


    if ($VI_REPARTI == 1) {$checked='checked'; $class='highlighted';}
    else  {$checked=''; $class='normal';};
    echo "<td colspan=2><input type='checkbox' name='reparti' id='reparti' 
            title=\"La victime est repartie pas ses propres moyens\"
            value=1 $checked $disabled 
            onchange=\"checkFilled(this);ChangedReparti();\"> <label for='reparti' id='label_reparti' class='$class'> Repartie par ses propres moyens</label> 
          </td>";
    echo "</tr>";

    if ( $VI_TRANSPORT == '0' ) $style="style='display:none'";
    else  $style="";

    echo "<tr bgcolor=$mylightcolor id='rowDestination' $style>";

    echo "<td></td><td align=right>Par $asterisk";
    $query2="select T_CODE, T_NAME from transporteur order by T_NAME asc";
    $result2=mysqli_query($dbc,$query2);
    echo " <select name='transporteur' id='transporteur' $disabled onchange='checkFilled(this);'>";
    while ($row2=@mysqli_fetch_array($result2)) {
        $_T_CODE=$row2["T_CODE"];
        $_T_NAME=$row2["T_NAME"];
        if ( $_T_CODE == $T_CODE ) $selected='selected';
        else $selected='';
        echo "<option value='$_T_CODE' $selected>".$_T_NAME."</option>";
    }
    echo "</select></td>";

    echo "<td align=right >Destination $asterisk</td>";
    $query2="select D_CODE, D_NAME from destination order by D_NAME asc";
    $result2=mysqli_query($dbc,$query2);
    echo " <td><select name='destination' id='destination' $disabled onchange='checkFilled(this);'>";
    while ($row2=@mysqli_fetch_array($result2)) {
        $_D_CODE=$row2["D_CODE"];
        $_D_NAME=$row2["D_NAME"];
        if ( $_D_CODE == $D_CODE ) $selected='selected';
        else $selected='';
        echo "<option value='$_D_CODE' $selected>".$_D_NAME."</option>";
    }
    echo "</select>";
    echo "</td></tr>";


    echo "<tr bgcolor=$mylightcolor id='rowHeureHopital' $style>";
    echo "<td colspan=2></td><td align=right>Heure arriv�e </td>
        <td><input type=text name='heure_hopital' id='heure_hopital' value='".$HEURE_HOPITAL."'
        onfocus='fillTime(form.heure_hopital);' 
        onchange='checkFilled(this);'
        placeholder='hh:mm' size=5 style='width:60px;' maxlength='5' ></td>
        </tr>";


    echo "</table>";
    echo "</div>";
}

//=====================================================================
// mode full: fiche bilan compl�te
//=====================================================================

if ( $modevictime == 'full' or $modevictime == 'pssp') {
    echo "<div id=full align=center>";

    echo "<p>
              <table cellspacing=0 border=0>
              <tr>
                 <td colspan=4 class=TabHeader>Informations victime</td>
             </tr>";
             
    echo "<tr bgcolor=$mylightcolor id='rowNumerotation'>
              <td><i>Num�rotation victime</i></td>
              <td colspan=3><input type='text' name='numerotation' size=5 maxlength='5' value=\"".$VI_NUMEROTATION."\" $disabled 
                    onchange='checkNumber(form.numerotation,0)' title=\"Ce champ permet de num�roter les diff�rentes victimes d'une intervention ou d'un �v�nement\">
              </td>";       
    echo "</tr>";
             
    if ( $store_confidential_data ) {
        $cnil_warning="";
        $maxlength=30;
    }
    else {
        $cnil_warning = write_cnil_warning();
        $maxlength=1;
    }

    echo "<tr bgcolor=$mylightcolor><td>Nom  ".$cnil_warning."</td>
        <td><input name='nom' id='nom' type=text size=20 value=\"".$VI_NOM."\"  maxlength='".$maxlength."' $disabled></td>
        <td >Pr�nom $asterisk</td>
        <td ><input name='prenom' id='prenom' type=text size=20 value=\"".$VI_PRENOM."\" $disabled></td>";

    echo "<tr bgcolor=$mylightcolor>
              <td>Date naissance</td>
              <td><input type='text' id='date_naissance' name='date_naissance' size='10' value='".$VI_BIRTHDATE."' class='datepicker' data-provide='datepicker'
              onchange='checkDate2(form.date_naissance)' $disabled placeholder='JJ-MM-AAAA'>
              </td>
              <td class=small>et/ou age </td><td><input type='text' id='age' name='age' size='3' value='".$VI_AGE."' 
                    onchange=\"changeAge('".$VI_AGE."');\"  title=\"Si la date de naissance est inconnue, saisissez l'age\" $disabled></td>";
    echo "</tr>";


    $checkedM=''; $checkedF=''; 
    if ( $VI_SEXE == 'M' ) $checkedM='checked';
    else $checkedF='checked';
    echo "<tr bgcolor=$mylightcolor><td align=right>Sexe $asterisk</td>
              <td ><input type=radio name='sexe' id='sexe_M' value='M' $checkedM title='Masculin' $disabled> <label for='sexe_M'>M </label>
                  <input type=radio name='sexe' id='sexe_F'  value='F' $checkedF title='F�minin' $disabled> <label for='sexe_F'>F </label></td>";
    echo "<td>Nationalit� $asterisk</td>";
    $query2="select ID, NAME from pays order by ID asc";
    $result2=mysqli_query($dbc,$query2);
    echo " <td align=left><select name='pays1' id='pays' $disabled title=\"Choisissez le pays correspondant � la nationalit� de la personne\">";
    while ($row2=@mysqli_fetch_array($result2)) {
        $_ID=$row2["ID"];
        $_NAME=$row2["NAME"];
        if ( $_ID == $VI_PAYS ) $selected='selected';
        else $selected='';
        echo "<option value='$_ID' $selected>".$_NAME."</option>";
    }
    echo "</select></td></tr>";
    
    echo "<tr bgcolor=$mylightcolor id='rowAddress'>
              <td><i>Adresse</i></td>
              <td colspan=3><input type='text' name='address' id='address'  size=70 maxlength='150' value=\"".$VI_ADDRESS."\" $disabled >
              </td>";
    echo "</tr>";


    // special

    $nonred=array('Consciente','Ventilation','Pouls carotidien');

    // BILAN VICTIME
    $query2="select bvc.BVC_CODE,bvc.BVC_TITLE,bvc.BVC_ORDER,
                   bvp.BVP_ID,bvp.BVP_TITLE,bvp.BVP_COMMENT,bvp.BVP_TYPE, bvp.DOC_ONLY,";
    if ( $victime > 0 )              
        $query2 .= "  bv.BVP_VALUE";
    else
        $query2 .= " '' BVP_VALUE";
    $query2 .= " from bilan_victime_category bvc,
            bilan_victime_param bvp";
    if ( $victime > 0 ) 
    $query2 .= " left join bilan_victime bv on ( bv.BVP_ID = bvp.BVP_ID and bv.V_ID=".$victime.")";
    $query2 .= " where bvc.BVC_CODE = bvp.BVC_CODE";
    
    if ( $modevictime == 'full' ) $query2 .= " and bvc.BVC_PAGE='PSE'";
    else $query2 .= " and bvc.BVC_PAGE='PSSP'";
    
    $query2 .= "order by bvc.BVC_ORDER, bvp.BVP_ID";


    $result2=mysqli_query($dbc,$query2);
    $prevBVC_CODE="";
    $prevBVP_TYPE="";
    $i=0;

    while (custom_fetch_array($result2)) {
        if ( $DOC_ONLY == 1 ) {
            if ( $modevictime == 'full' ) $BVP_TITLE=$BVP_TITLE." <i class='fa fa-user-md fa-lg' title='A renseigner par un m�decin'></i>";
            else if ( $modevictime == 'pssp' ) $BVP_TITLE=$BVP_TITLE." <i class='fa fa-plus-square' style='color:red;' title='Si coch�, soins m�dico-psychologiques'></i>";
        }
        if ( $BVP_ID == 500 or  $BVP_ID == 531 or $BVP_ID == 535 )  $BVP_TITLE="<b>".$BVP_TITLE."<b>";
        if ( $prevBVC_CODE <> $BVC_CODE ) {
            if ( $i % 2 == 1 and $prevBVP_TYPE <> 'textarea') echo "<td colspan=2></td></tr>";
            echo "<tr>   
                <td colspan=4 class=MenuRub>".$BVC_TITLE."</td>
            </tr>";
            $prevBVC_CODE=$BVC_CODE;
            
            $i=0;
        }
        $prevBVP_TYPE=$BVP_TYPE;
        if ( $i % 2 == 0 ) {
            if ( $BVP_ID == 531 ) echo "\n<tr bgcolor=$mylightcolor2 id='t2a'>";
            else if ( $BVP_ID == 533 ) echo "\n<tr bgcolor=$mylightcolor2 id='t2b'>";
            else if ( $BVP_ID == 535 ) echo "\n<tr bgcolor=$mylightcolor3 id='t3a'>";
            else if ( $BVP_ID == 537 ) echo "\n<tr bgcolor=$mylightcolor3 id='t3b'>";
            else echo "\n<tr bgcolor=$mylightcolor>";
        }
        
        if ( $BVP_VALUE <> '' ) $class='highlighted';
        else $class='normal';
        
        // textarea
        if ( $BVP_TYPE == 'textarea' ) {
            if ( $i % 2 == 1 ) {
                // passer � la ligne suivante
                echo "<td colspan=2></td></tr>
                    <tr bgcolor=$mylightcolor>";
            }
            echo "<td>".$BVP_TITLE."</td>";
            echo "<td colspan=3>
                <textarea name='bilan".$BVP_ID."'  title=\"".$BVP_COMMENT."\" 
                    cols='80' rows='2'
                    style='FONT-SIZE: 10pt; FONT-FAMILY: Arial;'
                    onchange='checkFilled(this);'
                    class='$class'
                    >".$BVP_VALUE."</textarea></td>";
            $i=1;
        }
        // dropdown box
        else if ( $BVP_TYPE == 'dropdown' ) {
            echo "\n<td>".$BVP_TITLE."</td>";
            $object="<select name='bilan".$BVP_ID."' id='bilan".$BVP_ID."' title=\"".$BVP_COMMENT."\" onchange='checkFilled(this);' class='$class'>";
            // cas particuliers des traumatismes 2 et 3
            if ( $BVP_ID == 531 or $BVP_ID == 535 ) $bi = 500;
            else if ( $BVP_ID == 532 or $BVP_ID == 536 ) $bi = 510;
            else if ( $BVP_ID == 533 or $BVP_ID == 537 ) $bi = 520;
            else if ( $BVP_ID == 534 or $BVP_ID == 538 ) $bi = 530;
            else $bi = $BVP_ID;
            $query3="select BVP_ID, BVP_INDEX, BVP_TEXT, BVP_SPECIAL from bilan_victime_values where BVP_ID=".$bi;
            $result3=mysqli_query($dbc,$query3);
            $object .= "\n<option value='' selected class=RedClass> --- Choisissez --- </option>";
            while ($row3=@mysqli_fetch_array($result3)) {
                $_BVP_ID=$row3["BVP_ID"];
                $_BVP_INDEX=$row3["BVP_INDEX"];
                $_BVP_TEXT=$row3["BVP_TEXT"];
                $_BVP_SPECIAL=$row3["BVP_SPECIAL"];
                if ( $_BVP_INDEX == $BVP_VALUE ) $selected='selected';
                else $selected ="";
                if ( $_BVP_SPECIAL == 'doc' ) $class='PinkClass';
                else $class='BlueClass';
                $object .= "<option value='".$_BVP_INDEX."' $selected class='".$class."'>".$_BVP_TEXT."</option>";
            }
            $object .="</select>";
            echo "<td>".$object."</td>";
        }
        // text box
        else if ( $BVP_TYPE == 'text' ) {
            echo "<td>".$BVP_TITLE."</td>";
            $object = "<input type='text' name='bilan".$BVP_ID."' id='bilan".$BVP_ID."' size=20 value=\"".$BVP_VALUE."\" title=\"".$BVP_COMMENT."\" onchange='checkFilled(this);' class='$class'>";
            echo "<td>".$object."</td>";
        }
        // readonly text box
        else if ( $BVP_TYPE == 'readonlytext' ) {
            echo "<td>".$BVP_TITLE."</td>";
            $object = "<input type='text' 
                        name='bilan".$BVP_ID."' id='bilan".$BVP_ID."' size=5 value=\"".$BVP_VALUE."\" title=\"".$BVP_COMMENT."\"
                        readonly=readonly style='FONT-SIZE: 10pt;border:0px;color:$mydarkcolor;background-color:$mylightcolor; font-weight:bold;'>";
            echo "<td>".$object."</td>";
        }
        // numeric
        else if ( $BVP_TYPE == 'numeric' ) {
            echo "<td>".$BVP_TITLE."</td>";
            $object = "<input type='text' name='bilan".$BVP_ID."' id='bilan".$BVP_ID."' size=5 value=\"".$BVP_VALUE."\" title=\"".$BVP_COMMENT."\"  
            onchange=\"checkNumberNullAllowed(this,'');checkFilled(this);\" class='$class'>";
            echo "<td>".$object."</td>";
        }
        // float
        else if ( $BVP_TYPE == 'float' ) {
            echo "<td>".$BVP_TITLE."</td>";
            $object = "<input type='text' name='bilan".$BVP_ID."' id='bilan".$BVP_ID."' size=5 value=\"".$BVP_VALUE."\" title=\"".$BVP_COMMENT."\"  
            onchange=\"checkFloat(this,'".$BVP_VALUE."');checkFilled(this);\" class='$class'>";
            echo "<td>".$object."</td>";
        }
        // time
        else if ( $BVP_TYPE == 'time' ) {
            echo "<td>".$BVP_TITLE."</td>";
            $object = "<input type='text' name='bilan".$BVP_ID."' id='bilan".$BVP_ID."' value=\"".$BVP_VALUE."\" title=\"".$BVP_COMMENT."\"  
            onchange=\"checkTime(this,'".$BVP_VALUE."');checkFilled(this);\" class='$class'
            onfocus=\"fillTime(form.bilan".$BVP_ID.");checkFilled(this);\" 
            placeholder='hh:mm' size=5 style='width:60px;'
            >";
            echo "<td>".$object."</td>";
        }
        // checkbox
        else if ( $BVP_TYPE == 'checkbox' ) {
            echo "<td><label for='bilan".$BVP_ID."' id='label_bilan".$BVP_ID."' class='$class'>".$BVP_TITLE."</label></td>";
            if (intval($BVP_VALUE) == '1' ) $checked='checked';
            else $checked='';
            
            $gestes_soins=array('610','620','630','635','640','650','660','670','680','687','688','690','700','710','720','722','730','740','750','780');
            if (in_array($BVP_ID, $gestes_soins)) $class="class='gestes'";
            else $class='';
            
            $object = "<input type='checkbox' name='bilan".$BVP_ID."' id='bilan".$BVP_ID."' value='1' $class $checked title=\"".$BVP_COMMENT."\" onchange='checkFilled(this);'>";
            echo "<td>".$object."</td>";
        }
        // radio oui non
        else if ( $BVP_TYPE == 'radio' ) {
            echo "<td>".$BVP_TITLE."</td>";
            if ($BVP_VALUE == 'Oui' ) $checkedoui='checked';
            else $checkedoui='';
            if ($BVP_VALUE == 'Non' ) $checkednon='checked';
            else $checkednon='';
            $object = "<input type='radio' name='bilan".$BVP_ID."' id='bilan".$BVP_ID."_oui' class='radiobutton' value='Oui' $checkedoui title=\"".$BVP_COMMENT."\" onchange='checkFilled(this);'>
                        <label for='bilan".$BVP_ID."_oui' id='label_bilan".$BVP_ID."_oui' class='$class'> Oui</label>  ";
            
            if ( in_array($BVP_TITLE, $nonred)) $non= "<span class=RedClass><b>Non</b></span>";
            else $non="Non";
            
            $object .= "<input type='radio' name='bilan".$BVP_ID."' id='bilan".$BVP_ID."_non' class='radiobutton'  value='Non' $checkednon title=\"".$BVP_COMMENT."\" onchange='checkFilled(this);'>
                        <label for='bilan".$BVP_ID."_non' id='label_bilan".$BVP_ID."_non' class='$class'> $non </label>";
            echo "<td>".$object."</td>";
        }
        if ( $i % 2 == 1 ) {
            echo "</tr>";
        }
        $i++;
    }
    if ( $i % 2 == 1 ) echo "<td colspan=2></td></tr>";

    echo "</table>";
    echo "</div>";
}
//=====================================================================
// Save buttons
//=====================================================================

echo "<p><div align=center>";

if ( $granted_update ) {
    echo " <input type='submit' class='btn btn-success' value='sauver'>";
    if ( $action <> 'insert' ) {
        if ( $numinter > 0 ) echo " <input type='button' class='btn btn-danger' value='supprimer' onclick=\"deleteIt('".$victime."','".$numinter."');\">";
        else if ( $numcav > 0 ) echo " <input type='button' class='btn btn-danger' value='supprimer' onclick=\"deleteIt2('".$victime."','".$numcav."');\">";
    }
}
if ( $from == 'export' ) 
echo " <input type=submit class='btn btn-default' value='fermer cette page' onclick='fermerfenetre();'>";
else {
    if ( $numinter > 0 ) echo " <input type='button' class='btn btn-default' value='retour intervention' onclick=\"ready('".$numinter."');\">";
    if ( $numcav > 0 or $evenement_victime > 0 ) echo " <p><input type='button' class='btn btn-default' value='retour liste' onclick=\"readyliste('".$evenement_victime."','".$type_victime."');\">";
    echo " <input type='button' class='btn btn-default' value='retour �v�nement' onclick=\"redirect('".$evenement."');\">";
}
if (isset ($_GET["qrcode"]))
    echo " <a class='btn btn-default' href='scan_victime.php?evenement=".$evenement_victime."' 
                    title='Scanner QR Code pour cr�er la fiche victime' ><i class='fa fa-qrcode fa-lg' style='color:purple;'></i> retour</a>";
echo "</div></form><p><p>";

writefoot();
?>
