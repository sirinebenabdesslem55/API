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
check_all(39);
$id=$_SESSION['id'];

if ( isset($_POST["evenement"])) $evenement=intval($_POST["evenement"]);
else $evenement=intval($_GET["evenement"]);
if ( isset($_POST["action"])) $action=secure_input($dbc,$_POST["action"]);
else $action=secure_input($dbc,$_GET["action"]);
if (isset ($_POST["accept"])) $accept=intval($_POST["accept"]);
else if (isset ($_GET["accept"])) $accept=intval($_GET["accept"]);
else $accept=0;
if (isset ($_POST["P_ID"])) $P_ID=intval($_POST["P_ID"]);
else if (isset ($_GET["P_ID"])) $P_ID=intval($_GET["P_ID"]);
else $P_ID=$id;
if (isset ($_POST["value"])) $value=secure_input($dbc,$_POST["value"]);
else if (isset ($_GET["value"])) $value=secure_input($dbc,$_GET["value"]);
else $value=0;
if (isset ($_POST["EP_FLAG1"])) $EP_FLAG1=intval($_POST["EP_FLAG1"]);
else if (isset ($_GET["EP_FLAG1"])) $EP_FLAG1=intval($_GET["EP_FLAG1"]);
else $EP_FLAG1=0;
$statut='';
if (isset ($_POST["statut"])) {
    if ( $_POST["statut"] == 'SAL' ) $statut='SAL';
    if ( $_POST["statut"] == 'BEN' ) $statut='BEN';
}
else if (isset ($_GET["statut"])) {
    if ( $_GET["statut"] == 'SAL' ) $statut='SAL';
    if ( $_GET["statut"] == 'BEN' ) $statut='BEN';
}

if (isset ($_GET["V_ID"])) $V_ID=intval($_GET["V_ID"]);
else $V_ID=0;
if (isset ($_GET["MA_ID"])) $MA_ID=intval($_GET["MA_ID"]);
else $MA_ID=0;

if ( isset($_GET["detail"])) 
    $detail=strip_tags(secure_input($dbc,str_replace("\"","",$_GET["detail"])));
else 
    $detail='';
if ( isset($_GET["reminder"])) $reminder=intval($_GET["reminder"]);
else $reminder=0;
if ( isset($_GET["asa"])) $asa=intval($_GET["asa"]);
else $asa=0;
if ( isset($_GET["das"])) $das=intval($_GET["das"]);
else $das=0;
if ( isset($_GET["statut_participation"])) $statut_participation=intval($_GET["statut_participation"]);
else $statut_participation=0;
writehead();
?>

<SCRIPT>
function redirect(url) {
    self.location.href = url;
}
</SCRIPT>
<?php

$query="select e.S_ID, e.E_PARENT, e.TE_CODE, te.ACCES_RESTREINT, te.TE_PERSONNEL, e.E_CREATED_BY, e.E_CONSIGNES
        from evenement e, type_evenement te 
        where e.TE_CODE = te.TE_CODE 
        and e.E_CODE=".$evenement;
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$TE_CODE= $row["TE_CODE"];
$E_PARENT= $row["E_PARENT"];
$S_ID=$row["S_ID"];
$ACCES_RESTREINT=$row["ACCES_RESTREINT"];
$TE_PERSONNEL=$row["TE_PERSONNEL"];
$E_CREATED_BY=$row["E_CREATED_BY"];
$E_CONSIGNES=rtrim($row["E_CONSIGNES"]);

$chefs=get_chefs_evenement($evenement);
$chefs_parent=get_chefs_evenement($E_PARENT);

$query="select P_PRENOM, P_NOM, P_SECTION, P_STATUT, TS_CODE, P_SEXE from pompier where P_ID=".$P_ID;
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$section_of=$row["P_SECTION"];
$statut_of=$row["P_STATUT"];
if ( $row["TS_CODE"] == 'SC' ) $SC=true;
else $SC=false;
$prenom=my_ucfirst($row["P_PRENOM"]);
$nom=strtoupper($row["P_NOM"]);
if ($row["P_SEXE"] == 'F' ) $e='e';
else $e='';

if ( $TE_CODE == 'GAR' and $gardes == 1 ) $gardeSP = true;
else $gardeSP=false;

$granted= false;
if ( $ACCES_RESTREINT == 1 ) {
    if ( check_rights($id, 26, "$S_ID")
        or in_array($id,$chefs) 
        or in_array($id,$chefs_parent)
        or $id == $E_CREATED_BY )
    $granted = true;
}
else if ( check_rights($id, 15, "$section_of") 
    or check_rights($id, 15, "$S_ID")
    or in_array($id,$chefs) 
    or in_array($id,$chefs_parent))
$granted = true;

if (  $gardeSP ) {
    if (( check_rights($id, 6, "$S_ID") and $sdis == 1 )
        or (  check_rights($id, 6 ) and $sdis == 0 ))
    $granted = true;
}

if ( $action == 'inscription' and $accept == 0) {
    $form="<form action='evenement_inscription.php' method='POST' style='display:inline;'>";
    $form .= "<input type='hidden' name='evenement' value='".$evenement."'>";
    $form .= "<input type='hidden' name='P_ID' value='".$P_ID."'>";
    $form .= "<input type='hidden' name='accept' value='1'>";
    $form .= "<input type='hidden' name='action' value='inscription'>";
    $endform="</form> ";
    
    // chiens et v�hicules associ�s?
    $query="select p.P_ID, p.P_NOM, p.P_PRENOM 
            from pompier p
            where p.P_OLD_MEMBER=0 
            and p.P_MAITRE=".$P_ID."
            and not exists (select 1 from evenement_participation ep where ep.E_CODE=".$evenement." and ep.P_ID = p.P_ID)";
    $result=mysqli_query($dbc,$query);
    $form1="";
    if ( mysqli_num_rows($result) > 0 )
        $form1 .= "<br>Engagement chien en m�me temps:<br>"; 
    while ( $row=mysqli_fetch_array($result)) {
        $form1 .="<input type='checkbox' name='chien_".$row["P_ID"]."' title='inscrire en m�me temps'> ".strtoupper($row["P_NOM"])." ".my_ucfirst($row["P_PRENOM"]);
        $form1 .="<br>";
    }
    $query="select v.V_ID, v.TV_CODE, v.V_MODELE, v.V_IMMATRICULATION
            from vehicule v , vehicule_position vp
            where vp.VP_OPERATIONNEL > 1 
            and v.VP_ID = vp.VP_ID
            and v.AFFECTED_TO=".$P_ID."
            and not exists (select 1 from evenement_vehicule ev where ev.E_CODE=".$evenement." and ev.V_ID=v.V_ID)";
    $result=mysqli_query($dbc,$query);
    if ( mysqli_num_rows($result) > 0 )
        $form1 .= "<br>Engagement v�hicule:<br>"; 
    while ( $row=mysqli_fetch_array($result)) {
        $form1 .="<input type='checkbox' name='vehic_".$row["V_ID"]."' title='inscrire en m�me temps'> ".$row["TV_CODE"]." ".$row["V_MODELE"]." ".$row["V_IMMATRICULATION"];
        $form1 .="<br>";
    }
    $form1 .="<br>";
  
    if ( $id == $P_ID ) $tab=1;
    else $tab=2;
    
    $message="";
    // cas inscription d'un salari�, pr�ciser sous quel statut, b�n�vole ou salari�
    if ( $statut_of == 'SAL' and $statut == '' ) {
        if ( $SC ) {
            $ss1 = "en service civique";
            $ss2 = "Service Civique";
        }
        else {
            $ss1 = "Salari�";
            $ss2 = "Salari�".$e;
        }
        if ( $id == $P_ID )
            $message .=$prenom.", vous faites partie du personnel ".$ss1.". Souhaitez vous vous inscrire en tant que:<p>";
        else 
            $message .=$prenom." ".$nom." <br>fait partie du personnel ".$ss1.". Souhaitez vous l'inscrire en tant que:<p>";
        $message .= $form."<input type='hidden' name='statut' value='SAL'>
            <input type='submit' class='btn btn-success' value='".$ss2."'>".$endform;
        if ( $syndicate == 1 ) 
            $message .= $form." <input type='hidden' name='statut' value='ADH'>
            <input type='submit' class='btn btn-info' value='Adh�rent".$e."'>".$endform;
        else
            $message .= $form." <input type='hidden' name='statut' value='BEN'>
            <input type='submit' class='btn btn-info' value='B�n�vole'>".$endform;
        $message .=" <a href=evenement_display.php?evenement=".$evenement."&tab=".$tab." class='btn btn-primary' >Annuler</a>";
        write_msgbox("Choix statut",$question_pic,$message,30,30);
        exit;
    }
    // cas du b�n�vole ou externe qui s'inscrit sur un �v�nement avec consigne
    else if ( ($statut_of == 'BEN' or $statut_of == 'EXT') and $P_ID == $id ) {
        $message .="Vous �tes sur le point de vous inscrire sur cet �v�nement.<p>";
        if ( $E_CONSIGNES <> '' )
            $message ="Les consignes suivantes ont �t� enregistr�es. Vous devez les accepter pour vous inscrire:<p><i>".$E_CONSIGNES."</i><p>";
        $message .= $form.$form1."<div align=center><input type='submit' class='btn btn-success' value='Accepter'>".$endform;
        if ( $E_CONSIGNES <> '' ) 
            $message .=" <a href=evenement_display.php?evenement=".$evenement."&tab=".$tab." class='btn btn-danger' >Refuser</a></div>";
        else
            $message .=" <a href=evenement_display.php?evenement=".$evenement."&tab=".$tab." class='btn btn-primary' >Annuler</a></div>";
        write_msgbox("Confirmation d'inscription",$question_pic,$message,30,30);
        exit;  
    }
    // cas plus g�n�ral ou on inscrit une personne sur un �v�nement non Garde
    else if (! $gardeSP) {
        if ( $id == $P_ID )
            $message .=$prenom.", vous �tes sur le point de vous inscrire sur cet �v�nement.<p>";
        else
            $message .="Vous �tes sur le point d'inscrire ".$prenom." ".$nom." sur cet �v�nement.<p>";
        $message .= $form.$form1."<div align=center><input type='submit' class='btn btn-success' value='Continuer'>".$endform." ";
        $message .=" <a href=evenement_display.php?evenement=".$evenement."&tab=".$tab." class='btn btn-primary' >Annuler</a></div>";
        write_msgbox("Confirmation d'inscription",$question_pic,$message,30,30);
        exit;     
    }
}

if ( $action == 'desinscription') {
    if ( isset($_GET['EC'])) $EC=$_GET['EC'];
    else $EC=$evenement;
    if ( $accept == 0 ) {
        $link="evenement_inscription.php?evenement=".$evenement."&EC=".$EC."&action=desinscription&P_ID=".$P_ID."&accept=1";
        if ( $id == $P_ID ) $message=$prenom.", vous �tes sur le point de vous d�sinscrire de cet �v�nement.<p>";
        else $message="Vous �tes sur le point de d�sinscrire ".$prenom." ".$nom." de cet �v�nement.<p>";
        $message .=" <div align=center><a href=".$link." class='btn btn-warning' >Continuer</a>";
        $message .=" <a href=evenement_display.php?evenement=".$evenement."&tab=2 class='btn btn-primary' >Annuler</a></div>";
        write_msgbox("Confirmation de d�sinscription",$question_pic,$message,30,30);
        exit;           
    }
    // on peut toujours se desinscrire le jour de l'inscription
    if ( $id == $P_ID ) {
        $query="select DATEDIFF(NOW(), ep.EP_DATE) as NB_DAYS 
               from evenement_participation ep, evenement e
               where ep.E_CODE = e.E_CODE
            and ( e.E_CODE=".$evenement." or e.E_PARENT=".$evenement.")
            and ep.P_ID=".$id;
           $r1=mysqli_query($dbc,$query);
           $num=mysqli_num_rows($r1);
           if ( $num > 0 ) {
               $row=mysqli_fetch_array($r1);
             if ( $row["NB_DAYS"] < 1 ) $granted=true;
           }
    }
    if (( check_rights($id, 10) and check_rights($id, 15)) or ( $granted )) {
        $query = "delete from personnel_formation where (E_CODE =".$evenement." or E_CODE=".$EC.")
                and E_CODE > 0
                and P_ID=".$P_ID;
        $result=mysqli_query($dbc,$query);
        
        $query="delete from evenement_option_choix
                where (E_CODE =".$evenement." or E_CODE=".$EC.")
                and P_ID=".$P_ID;
        $result=mysqli_query($dbc,$query);
        
        $query="delete from evenement_piquets_feu
                where E_CODE =".$evenement."
                and P_ID=".$P_ID;
        $result=mysqli_query($dbc,$query);
          
        $query="delete from evenement_participation
                where (E_CODE =".$evenement." or E_CODE=".$EC.")
                and P_ID=".$P_ID;
        insert_log('DESINSCP', $P_ID, "", $evenement);
    }
    else
         $query="select 'exception raised'";
}
elseif ( $action == 'inscription') {
    if ( $statut_of == 'EXT' ) $permission=37;
    else if ( $ACCES_RESTREINT == 1 ) $permission=26;
    else $permission=10;
    if ( $TE_PERSONNEL == 0 )  {
        $query="select 'exception raised'";
    }
    else if ( check_rights($id, $permission, $section_of) or $granted or $id == $P_ID) {
        $flag1=0;
        if ( $statut_of == 'SAL' and $statut == 'SAL') $flag1=1;
        else if ( $statut_of == 'SPP') $flag1=1;
        //suppression de : and $gardeSP
        $asa=0;
        $das=0;
        insert_log('INSCP', $P_ID, "", $evenement);
        
        $insert_done=false;
        if ( $statut_of == 'SPV' and $gardeSP) {
            // cas gardes SPV, inscrire en fonction de la disponibilit� J / N
            $queryG="select date_format(EH_DATE_DEBUT, '%d-%m-%Y'),
                EH_ID,
                case
                when EH_ID=2 then '3'
                else '1'
                end,
                EH_DUREE
                from evenement_horaire where E_CODE=".$evenement." order by EH_ID asc";
            $resultG=mysqli_query($dbc,$queryG);
            while ( $rowG=mysqli_fetch_array($resultG)) {
                $tmp=explode ( "-",$rowG[0]); $month1=$tmp[1]; $day1=$tmp[0]; $year1=$tmp[2];
                if ( is_dispo_period( $P_ID, $year1, $month1, $day1, $rowG[2] )) {
                    $reminder=get_reminder($P_ID, 72);
                    $query="insert into evenement_participation (E_CODE, EH_ID, P_ID, EP_DATE, EP_BY, EP_FLAG1, EP_REMINDER, EP_ASA, EP_DAS, EP_DUREE)
                            values ( ".$evenement.", ".$rowG[1].", ".$P_ID.", now() ,".$id.",".$flag1.",".$reminder.", ".$asa.",".$das.",".$rowG[3].")";
                    $result=mysqli_query($dbc,$query);
                    $query="select 'already inserted'";
                    $insert_done=true;
                }
            }
        }
        
        if ( ! $insert_done ) {
            // cas g�n�ral inscription sur toutes les parties
            $query="insert into evenement_participation (E_CODE, EH_ID, P_ID, EP_DATE, EP_BY, EP_FLAG1, EP_REMINDER, EP_ASA, EP_DAS, EP_DUREE)
            select E_CODE,EH_ID, ".$P_ID.", now() ,".$id.",".$flag1.",".$reminder.", ".$asa.",".$das.", EH_DUREE
            from evenement_horaire
            where E_CODE=".$evenement;
        }
        
        // inscrire les chiens associ�s aux ma�tres et les v�hicules si n�cessaire
        foreach ($_POST as $key => $value) {
            if ( substr($key,0,5) == 'chien' ) {
                $parts = explode("_", $key);
                $dog=intval($parts[1]);
                if ( $dog > 0 ) {
                    $query_dog="insert into evenement_participation (E_CODE, EH_ID, P_ID, EP_DATE, EP_BY, EP_FLAG1, EP_DUREE)
                    select E_CODE,EH_ID, ".$dog.", now() ,".$id.",".$flag1.",EH_DUREE
                    from evenement_horaire
                    where E_CODE=".$evenement;
                    $result_dog=mysqli_query($dbc,$query_dog);
                }
            }
            if ( substr($key,0,5) == 'vehic' ) {
                $parts = explode("_", $key);
                $car=intval($parts[1]);
                if ( $car > 0 ) {
                    $query_car="insert into evenement_vehicule (E_CODE, EH_ID, V_ID)
                    select E_CODE, EH_ID, ".$car."
                    from evenement_horaire
                    where E_CODE=".$evenement;
                    $result_car=mysqli_query($dbc,$query_car);
                }
            }
        }
    }
    else
        $query="select 'exception raised'";
}
else {
    $inscrits=array();
    $inscrits=explode(",",get_inscrits($evenement));

    if ( $action == "close"  and ( $granted )) {
        $query="update evenement set E_CLOSED=1 where E_CODE=".$evenement." or E_PARENT=".$evenement;
        insert_log("CLOTEVT", $evenement, $complement="", $code="");
    }
    elseif ( $action == "open"  and  $granted ) {
        $query="update evenement set E_CLOSED=0 where E_CODE=".$evenement;
        insert_log("OUVEVT", $evenement, $complement="", $code="");
    }
    elseif ( substr($action,0,2) == "nb" ) {
        $NUM=intval(substr($action,2,1));
        $_SESSION['from_interventions']=1;
        $query="insert into bilan_evenement (E_CODE, TB_NUM, BE_VALUE)
                select ".$evenement.",".$NUM.",".$value." from dual
                where not exists (select 1 from bilan_evenement where E_CODE=".$evenement." and TB_NUM=".$NUM.")";
        mysqli_query($dbc,$query);
        
        if ( mysqli_affected_rows($dbc) == 0 ) {
            $query="update bilan_evenement set BE_VALUE=".$value." where E_CODE=".$evenement." and TB_NUM=".$NUM;
            mysqli_query($dbc,$query);
        }
    }
    elseif ( $action == "statutParticipation" and ( $granted or $id == $P_ID  or check_rights($id, 10, $section_of)) ) {
        $query="update evenement_participation set TSP_ID=".$statut_participation."
        where P_ID=".$P_ID."
        and E_CODE in (select E_CODE from evenement 
                        where  E_CODE=".$evenement." or E_PARENT = ".$evenement.")";
        insert_log('DETINSCP', $P_ID, $detail." ".$auditkm, $evenement);
    }
    elseif (( $action == "detail" ) and ( $granted or $id == $P_ID  or check_rights($id, 10, $section_of))) {
        $km='';
        $updkm="EP_KM = null";
        $auditkm="km non renseign�";
        if ( isset($_GET["km"])) {
           $km=$_GET["km"];
           if ($km <> '') {
                $updkm="EP_KM = ".intval($km);
                $auditkm=intval($km)." km";
           }
        }
           $query="update evenement_participation set EP_COMMENT=\"".$detail."\", ".$updkm." , EP_FLAG1=".$EP_FLAG1." ,
                   EP_REMINDER=".$reminder.",EP_ASA=".$asa.",EP_DAS=".$das."
                   where P_ID=".$P_ID."
                and E_CODE in (select E_CODE from evenement 
                                where  E_CODE=".$evenement." or E_PARENT = ".$evenement.")";
        insert_log('DETINSCP', $P_ID, $detail." ".$auditkm, $evenement);
    }
    elseif (( $action == "fonction" ) and ( $granted )) {
        if ( isset($_GET["fonction"])) $fonction=intval($_GET["fonction"]);
        else $fonction=0;
        
        $queryZ="select E_CODE from evenement 
                where E_PARENT = ".$evenement;
        $resultZ=mysqli_query($dbc,$queryZ);
        $evts=$evenement;
        while ($rowZ=@mysqli_fetch_array($resultZ)) {
             $evts .= ",".$rowZ["E_CODE"];
        }
        if ( $V_ID > 0 ) {
            if ( $fonction == 0 ) 
                $query="update evenement_vehicule set TFV_ID=null
                       where V_ID=".$V_ID." and E_CODE in (".$evts.")";
            else {        
                $query="update evenement_vehicule set TFV_ID=".$fonction."
                       where V_ID=".$V_ID." and E_CODE in (".$evts.")";
            }
        }
        else {
            if ( $fonction == 0 ) 
                $query="update evenement_participation set TP_ID=0
                       where P_ID=".$P_ID." and E_CODE in (".$evts.")";
            else {        
                $query="update evenement_participation set TP_ID=".$fonction."
                       where P_ID=".$P_ID." and E_CODE in (".$evts.")";
                if ( $log_actions == 1 ) {                    
                    $queryf="select TP_LIBELLE from type_participation where TP_ID=".$fonction;
                    $resultf=mysqli_query($dbc,$queryf);
                    $rowf=@mysqli_fetch_array($resultf);
                    $TP_LIBELLE=$rowf["TP_LIBELLE"];
                    if ( $TP_LIBELLE == "" ) $TP_LIBELLE="Pas de fonction";
                    insert_log('FNINSCP', $P_ID, $TP_LIBELLE, $evenement);
                }
            }
        }
    }
    elseif (( $action == "equipe" ) and ( $granted )) {
        if ( isset($_GET["equipe"])) $equipe=intval($_GET["equipe"]);
        else $equipe=0;
        
        $queryZ="select E_CODE from evenement 
                where E_PARENT = ".$evenement;
        $resultZ=mysqli_query($dbc,$queryZ);
        $evts=$evenement;
        while ($rowZ=@mysqli_fetch_array($resultZ)) {
             $evts .= ",".$rowZ["E_CODE"];
        }
        
        if ( $V_ID > 0 ) {
            if ( $equipe == 0 ) 
                $query="update evenement_vehicule set EE_ID=null
                       where V_ID=".$V_ID." and E_CODE in (".$evts.")";
            else {        
                $query="update evenement_vehicule set EE_ID=".$equipe."
                       where V_ID=".$V_ID." and E_CODE in (".$evts.")";
            }
        }
        else if ( $MA_ID > 0 ) {
            if ( $equipe == 0 ) 
                $query="update evenement_materiel set EE_ID=null
                       where MA_ID=".$MA_ID." and E_CODE in (".$evts.")";
            else {        
                $query="update evenement_materiel set EE_ID=".$equipe."
                       where MA_ID=".$MA_ID." and E_CODE in (".$evts.")";
            }
        
        }
        else {
            if ( $equipe == 0 ) 
                $query="update evenement_participation set EE_ID=null
                       where P_ID=".$P_ID." and E_CODE in (".$evts.")";
            else {        
                $query="update evenement_participation set EE_ID=".$equipe."
                       where P_ID=".$P_ID." and E_CODE in (".$evts.")";
                if ( $log_actions == 1 ) {                    
                    $queryf="select EE_NAME from evenement_equipe where E_CODE =".$evenement." and  EE_ID=".$equipe;
                    $resultf=mysqli_query($dbc,$queryf);
                    $rowf=@mysqli_fetch_array($resultf);
                    $EE_NAME=$rowf["EE_NAME"];
                    if ( $EE_NAME == "" ) $EE_NAME="Pas d'�quipe";
                    insert_log('EEINSCP', $P_ID, $EE_NAME, $evenement);
                }
            }
        }
    }
    elseif ( $action == "cancel"  and  $granted ) {
         $renfort = intval($_GET["renfort"]);
           $query = "update evenement set E_PARENT=null where E_CODE=".$renfort;
    }
    elseif ( $action == "responsable"  and  $granted ) {
        if ( $P_ID == 0 ) $P_ID='null';
           $query = "insert into evenement_chef (E_CODE, E_CHEF) values(".$evenement.",".$P_ID.")";
    }
    elseif ( $action == "delresponsable"  and  $granted ) {
        if ( $P_ID == 0 ) $P_ID='null';
           $query = "delete from evenement_chef where E_CODE=".$evenement." and E_CHEF = ".$P_ID;
    }
    // add security
    if ( $id <> $P_ID and $action <> "detail")
        if (! $granted ) 
                 $query="select 'exception raised'";
}
$result=mysqli_query($dbc,$query);

if ( ($action == 'equipe' or $action == 'fonction') and $V_ID > 0) {
    echo "<body onload=redirect('evenement_display.php?evenement=".$evenement."&from=vehicule');>";
}
else if ( $action == 'equipe'  and $MA_ID > 0) {
    echo "<body onload=redirect('evenement_display.php?evenement=".$evenement."&from=materiel');>";
}
elseif ( $action == 'desinscription'  and isset ($_GET["P_ID"])) {
    echo "<body onload=redirect('evenement_notify.php?evenement=".$evenement."&action=desinscrit&P_ID=".$_GET["P_ID"]."');>";
}
elseif ( $action == 'cancel' ) {
    echo "<body onload=redirect('evenement_display.php?evenement=".$evenement."');>";
}
elseif ( $action == 'inscription' ) {
    $nboptions=count_entities("evenement_option", "E_CODE in(".$evenement.",".intval($E_PARENT).")");
    if ( $nboptions > 0 )
        echo "<body onload=redirect('evenement_option_choix.php?evenement=".$evenement."&inscription=1&pid=".$P_ID."');>";
    else if ( $syndicate == 1 )
        echo "<body onload=redirect('evenement_display.php?evenement=".$evenement."&from=inscription');>";
    else
        echo "<body onload=redirect('evenement_notify.php?evenement=".$evenement."&action=inscription&P_ID=".$P_ID."');>";
}
elseif ( $action == 'responsable' or $action == 'delresponsable') {
    echo "<body onload=redirect('evenement_display.php?evenement=".$evenement."');>";
}
elseif ( $action == 'diplomes' ) {
    echo "<body onload=redirect('evenement_display.php?evenement=".$evenement."&from=formation');>";
}
elseif ( $action == 'detail' or $action == 'fonction' or $action == 'equipe') {
    echo "<body onload=redirect('evenement_display.php?evenement=".$evenement."&from=inscription');>";
}
else {
    echo "<body onload=redirect('evenement_display.php?evenement=".$evenement."');>";
}

writefoot();
?>
