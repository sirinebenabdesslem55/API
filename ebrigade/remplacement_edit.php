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
if ( $assoc or $army ) $perm=41;
else $perm=61;
check_all($perm);
$id=$_SESSION['id'];

$action = 'undefined';
if (isset ($_GET["action"])) $action = $_GET["action"];
if (isset($_GET["periode"])) $periode = intval($_GET["periode"]);
else $periode = -1;

$html = writehead();
forceReloadJS('js/remplacement_edit.js');

if (isset ($_GET["rid"])) $rid=intval($_GET["rid"]);
else $rid=0;
if (isset ($_GET["evenement"])) $evenement=intval($_GET["evenement"]);
else $evenement=0;
if (  $evenement == 0 ) {
    param_error_msg();
    exit;
}

$S_ID=get_section_organisatrice($evenement);
$admin_evenement=false;
$allowed_delete=false;
if ( $assoc or $army ) $perm=15;
else $perm=6;
if ( check_rights($id, $perm, "$S_ID")) $admin_evenement=true;
else if ( $nbsections > 0 and check_rights($id, $perm)) $admin_evenement=true;
if ( check_rights($id, 19)) $allowed_delete=true;
// -----------------------------------------------------
// save changes
// -----------------------------------------------------
if ($action <> 'undefined') {
    $error=0;
    if (isset($_GET["substitute"])) $substitute=intval($_GET["substitute"]); else $substitute=0;
    if (isset($_GET["periode"])) $eh_id=intval($_GET["periode"]); else $eh_id=0;
    if (isset($_GET["replaced"])) $replaced=intval($_GET["replaced"]); else $replaced=0;
    if ( $rid == 0 ) {
        if ( $replaced == 0 ) {
            $html .= "<div class='alert alert-danger' role='alert'> Aucune personne à remplacer choisie.</div><p>";
            $error=1; 
        }
        else {
            if (  $action == 'create_validate' and $admin_evenement ) {
                if ( $substitute == 0 ) {
                    $html .= "<div class='alert alert-danger' role='alert'> Aucun remplaçant sélectionné.</div><p>";
                    $error=1;
                }
                else {
                    $query="insert into remplacement(E_CODE, EH_ID, REPLACED, SUBSTITUTE, REQUEST_DATE, REQUEST_BY, APPROVED, APPROVED_DATE, APPROVED_BY)
                    values (".$evenement.",".$eh_id.",".$replaced.",".$substitute.",NOW(),".$id.", 1,NOW(),".$id." )";
                    replace_personnel($evenement,$eh_id,$replaced,$substitute);
                    replace_notify($evenement,$eh_id,'approved',$replaced,$substitute);
                }
            }
            else if (  $action == 'create' ) {
                $query="insert into remplacement(E_CODE, EH_ID, REPLACED, SUBSTITUTE, REQUEST_DATE, REQUEST_BY)
                values (".$evenement.",".$eh_id.",".$replaced.",".$substitute.",NOW(),".$id.")";
                replace_notify($evenement,$eh_id,'requested',$replaced,$substitute);
            }
            $result=mysqli_query($dbc,$query);
            
        }
    }
    else {
        $query="select replaced, substitute, accepted, approved, rejected from remplacement where R_ID=".$rid;
        $result=mysqli_query($dbc,$query);
        $row=@mysqli_fetch_array($result);
        $replaced=$row["replaced"];
        $current_substitute=$row["substitute"];
        $current_accepted=intval($row["accepted"]);
        $current_approved=intval($row["approved"]);
        $current_rejected=intval($row["rejected"]);
        
        if ( $action == "update" and ($admin_evenement or $id == $replaced) and $current_approved == 0) {
            $query="update remplacement
            set SUBSTITUTE=".$substitute.",
            EH_ID=".$eh_id."
            where R_ID=".$rid;
        }
        
        if ( $action == "accept" and $id == $current_substitute and $current_rejected == 0) {
            $query="update remplacement
            set ACCEPTED=1, ACCEPT_DATE=NOW(), ACCEPT_BY=".$id."
            where R_ID=".$rid;
            replace_notify($evenement,$eh_id,'accepted',$replaced,$substitute);
        }
        
        if ( $action == "refuse" and $id == $current_substitute and $current_approved == 0) {
            $query="update remplacement
            set ACCEPTED=0, ACCEPT_DATE=null, ACCEPT_BY=null, SUBSTITUTE=0
            where SUBSTITUTE= ".$id." and R_ID=".$rid;
            replace_notify($evenement,$eh_id,'refused',$replaced,$substitute);
        }
        
        if ( $action == "delete" and $allowed_delete and $admin_evenement) {
            $query="delete from remplacement where R_ID=".$rid;
        }
        
        if ( $action == "reject" and $admin_evenement and $current_approved == 0) {
            $query="update remplacement
            set REJECTED=1, REJECT_DATE=NOW(), REJECT_BY=".$id."
            where R_ID=".$rid;
            replace_notify($evenement,$eh_id,'rejected',$replaced,$substitute);
        }
        
        if ( $action == "validate" and $admin_evenement and $current_rejected == 0) {
            if ( $substitute == 0 ) {
                $html .= "<div class='alert alert-danger' role='alert'> Aucun remplaçant sélectionné.</div><p>";
                $error=1;
            }
            else {
                $query="update remplacement
                set APPROVED=1, SUBSTITUTE=".$substitute.", APPROVED_DATE=NOW(), APPROVED_BY=".$id."
                where R_ID=".$rid;
                replace_personnel($evenement,$eh_id,$replaced,$substitute);
                replace_notify($evenement,$eh_id,'approved',$replaced,$substitute);
            }
        }
        // main update
        $result=mysqli_query($dbc,$query);
    
        // sanity updates
        $query="update remplacement
        set ACCEPTED=0, ACCEPT_DATE=null, ACCEPT_BY=null
        where SUBSTITUTE <> ACCEPT_BY
        and SUBSTITUTE > 0
        and R_ID=".$rid;
        $result=mysqli_query($dbc,$query);
    }
    
    if ( $error == 0 ) {
        $html .= "<body onload='javascript:self.location.href=\"evenement_display.php?tab=10&evenement=".$evenement."\"'>";
        print $html;
        exit;
    }
}
// -----------------------------------------------------
// display
// -----------------------------------------------------

$query="select r.R_ID, r.EH_ID, 
    r.REPLACED, p1.P_NOM n1, p1.P_PRENOM p1, p1.P_GRADE g1,
    r.SUBSTITUTE, p8.P_NOM n8, p8.P_PRENOM p8, p8.P_GRADE g8,
    r.ACCEPT_BY, r.ACCEPTED, date_format(r.ACCEPT_DATE,'%d-%m-%Y %H:%i') ACCEPT_DATE, p2.P_NOM n2, p2.P_PRENOM p2, p2.P_GRADE g2,
    r.REQUEST_BY, date_format(r.REQUEST_DATE,'%d-%m-%Y %H:%i') REQUEST_DATE, p3.P_NOM n3, p3.P_PRENOM p3, p3.P_GRADE g3,
    r.APPROVED, date_format(r.APPROVED_DATE,'%d-%m-%Y %H:%i') APPROVED_DATE, r.APPROVED_BY, p4.P_NOM n4, p4.P_PRENOM p4, p4.P_GRADE g4,
     r.REJECTED, date_format(r.REJECT_DATE,'%d-%m-%Y %H:%i') REJECT_DATE, r.REJECT_BY, p5.P_NOM n5, p5.P_PRENOM p5, p5.P_GRADE g5   
    from remplacement r left join pompier p1 on p1.P_ID=r.REPLACED
    left join pompier p2 on p2.P_ID = r.ACCEPT_BY
    left join pompier p3 on p3.P_ID = r.REQUEST_BY
    left join pompier p4 on p4.P_ID = r.APPROVED_BY
    left join pompier p5 on p5.P_ID = r.REJECT_BY
    left join pompier p8 on p8.P_ID = r.SUBSTITUTE
    where r.R_ID = ".$rid ;

$result=mysqli_query($dbc,$query);
$nbR=mysqli_num_rows($result);
$row=@mysqli_fetch_array($result);
$replaced = $row["REPLACED"];
$replaced_name = my_ucfirst($row["p1"])." ".strtoupper($row["n1"]);
$substitute = $row["SUBSTITUTE"];
$substitute_name = my_ucfirst($row["p8"])." ".strtoupper($row["n8"]);
$date_request = $row["REQUEST_DATE"];
$requested_by = my_ucfirst($row["p3"])." ".strtoupper($row["n3"]);
$accepted_by = my_ucfirst($row["p2"])." ".strtoupper($row["n2"]);
$date_accept = $row["ACCEPT_DATE"];
$accepted = intval($row["ACCEPTED"]);
$approved = intval($row["APPROVED"]);
$rejected = intval($row["REJECTED"]);
$date_approve = $row["APPROVED_DATE"];
$date_reject = $row["REJECT_DATE"];
$approved_by = my_ucfirst($row["p4"])." ".strtoupper($row["n4"]);
$rejected_by = my_ucfirst($row["p5"])." ".strtoupper($row["n5"]);
$EH_ID = $row["EH_ID"];

$html .= "<div align=center><table class='noBorder'>
      <tr><td><i class='fa fa-user-times fa-3x' ></i></td>
      <td><font size=4><b> Demande de remplacement</b></font></td></tr>
      </table>";
      
$html .= "<table cellspacing=0 border=0 >
          <tr>
               <td colspan=2 width=400 class=TabHeader>Détails de la demande</td>
          </tr>";


$query="select S_ID from evenement where E_CODE=(select E_PARENT from evenement where E_CODE=".$evenement.")";
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$S_PRINCIPAL=$row["S_ID"];

// Remplacé
$html .= "<tr bgcolor=$mylightcolor><td width=150>A remplacer</td><td>";
if ( $rid > 0 ) {
    if ( $grades )  $html .="<img src=".$grades_imgdir."/".$row["g1"].".png height=20 title='".$row["g1"]."' style='PADDING:1px;' class='img-max-20'>";
    $html .= " <b>".$replaced_name."</b><input type='hidden' name='replaced' id='replaced' value='".$replaced."'>";
}
else {
    if ( $admin_evenement ) {
        if (isset($_GET["replaced"])) $replaced=intval($_GET["replaced"]); else $replaced=0;
        $html .= "<select name='replaced' id='replaced' onchange=\"javascript:reload('".$rid."','".$evenement."','".$action."');\">
        <option value='0'>Choisir une personne</option>";

        $query="select distinct p.P_ID, p.P_NOM, p.P_PRENOM, p.P_GRADE, p.P_STATUT, s.S_CODE from pompier p, evenement_participation ep, section s
            where p.P_ID = ep.P_ID
            and p.P_SECTION=s.S_ID
            and ep.EP_ABSENT = 0
            and ep.E_CODE=".$evenement;
        if ( $replaced > 0 ) 
        $query .= " union select distinct p.P_ID, p.P_NOM, p.P_PRENOM, p.P_GRADE, p.P_STATUT, s.S_CODE from pompier p, section s
            where p.P_SECTION = s.S_ID
            and P_ID=".$replaced;
        $query .= " order by P_STATUT, P_NOM, P_PRENOM";
        $result=mysqli_query($dbc,$query);
        while ($row=mysqli_fetch_array($result)) {
            $R = strtoupper($row["P_NOM"])." ".my_ucfirst($row["P_PRENOM"]);
            if ( $grades ) $R .= " - ".$row["P_GRADE"];
            if ( $nbsections == 0 ) $R .= " - ".$row["S_CODE"];
            if ( $row["P_ID"] == $replaced ) $selected='selected';
            else $selected='';
            $html .= "<option value='".$row["P_ID"]."' class='".$row["P_STATUT"]."' $selected>".$R."</option>";
        }
        $html .="</select>";
    }
    else {
        $replaced=$id;
        $html .= "<input type='hidden' name='replaced' id='replaced' value='".$id."'><b>".strtoupper($_SESSION['SES_NOM'])." ".my_ucfirst($_SESSION['SES_PRENOM'])."</b>";
    
    }
}
$html .="</td></tr>";

//Période
$nb_sessions = get_nb_sessions($evenement);
if ( $nb_sessions == 2 ) {
    $P=intval($EH_ID);
    if ( $replaced == 0 ) $disabled='disabled';
    else if ( $approved == 1 or $rejected ==  1 ) $disabled='disabled';
    else if ( $rid == 0 ) {
        $disabled='';
        $query="select sum(EH_ID) from evenement_participation where E_CODE=".$evenement." and P_ID=".$replaced;
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
        $P=$row[0];
        if ( $P > 2 ) $P=0;
    }
    else if ( $id <> $replaced and ! $admin_evenement ) $disabled='disabled';
    else $disabled='';
    $html .= "<tr bgcolor=$mylightcolor><td>Période</td><td>";
    if ( $periode == 0 ) $checked='checked';
    else if ( $P == 0 ) $checked='checked';
    else $checked='';
    
    if ( $gardes == 1 ) {
        $t1 = 'Jour'; $t2='Nuit';
    }
    else {
        $t1 = 'Partie 1'; $t2='Partie 2';
    }
    $html .= " <input type=radio name='periode' id='periode0' value='0' $checked $disabled title='Durée complète' onchange=\"javascript:reload('".$rid."','".$evenement."','".$action."');\"><label for='periode0'> Durée complète</label>";
    if ( $periode == 1 ) $checked='checked';
    elseif ( $P == 1 ) $checked='checked';
    else $checked='';
    $html .= " <input type=radio name='periode' id='periode1' value='1' $checked $disabled title='$t1 seulement' onchange=\"javascript:reload('".$rid."','".$evenement."','".$action."');\"><label for='periode1'> $t1 </label>";
    if ( $periode == 2 ) $checked='checked';
    elseif ( $P == 2 ) $checked='checked';
    else $checked='';
    $html .= " <input type=radio name='periode' id='periode2' value='2' $checked $disabled title='$t2 seulement' onchange=\"javascript:reload('".$rid."','".$evenement."','".$action."');\"><label for='periode2'> $t2</label></td></tr> "; 
}

// Remplaçant
$html .= "<tr bgcolor=$mylightcolor><td>Remplaçant proposé</td><td>";
if ( $approved == 1 or $rejected == 1 ) {
    if ( $grades and $substitute > 0 )  $html .="<img src=".$grades_imgdir."/".$row["g8"].".png height=20 title='".$row["g8"]."' style='PADDING:1px;' class='img-max-20'>";
    $html .= " <b>".$substitute_name."</b><input type='hidden' name='substitute' id='substitute' value=".$substitute.">";
}
else if ( $admin_evenement or 
         (($id == intval($replaced) or $rid == 0) and  $accepted == 0)
         ) {
    if ( $replaced == 0 ) $disabled='disabled';
    else $disabled='';
    $disabled='';
    
    $subquery="select S_ID from section where S_ID=".$S_ID." or S_PARENT=".$S_ID;
    if ( $S_PRINCIPAL <> '' ) $subquery .= " or S_ID = ".$S_PRINCIPAL." or S_PARENT=".$S_PRINCIPAL;
    
    $html .= "<select name='substitute' id='substitute' $disabled>
    <option value='0'>Proposer un remplaçant</option>";
    $query="select p.P_ID, p.P_NOM, p.P_PRENOM, p.P_GRADE, p.P_STATUT, s.S_CODE 
        from pompier p, section s
        where p.P_SECTION in (".$subquery.")
        and p.P_OLD_MEMBER = 0
        and p.P_SECTION=s.S_ID
        and p.P_STATUT <> 'EXT'
        and not exists (select 1 from evenement_participation ep where ep.E_CODE=".$evenement;
    if ( $nb_sessions == 2 ) {
        if ( intval($periode) > 0 ) $query .= " and ep.EH_ID = ".$periode;
        else if ( intval($P ) > 0 ) $query .= " and ep.EH_ID = ".$P;
    }
    $query .= " and ep.P_ID=p.P_ID)";
        
    $query .= "  union select p.P_ID, p.P_NOM, p.P_PRENOM, p.P_GRADE, p.P_STATUT, s.S_CODE
     from pompier p, section s, section_role sr, groupe
     where sr.P_ID = p.P_ID
     and p.P_SECTION = s.S_ID
     and groupe.GP_ID = sr.GP_ID
     and P_OLD_MEMBER = 0
     and p.P_STATUT <> 'EXT'
     and sr.S_ID in (".$subquery.")
     and not exists (select 1 from evenement_participation ep where ep.E_CODE=".$evenement;
    if ( $nb_sessions == 2 ) {
        if ( intval($periode) > 0 ) $query .= " and ep.EH_ID = ".$periode;
        else if ( intval($P ) > 0 ) $query .= " and ep.EH_ID = ".$P;
    }
    $query .= " and ep.P_ID=p.P_ID)";
    
    if ( $substitute > 0 )
    $query .= " union select p.P_ID, p.P_NOM, p.P_PRENOM, p.P_GRADE, p.P_STATUT, s.S_CODE from pompier p, section s
            where p.P_SECTION = s.S_ID
            and p.P_ID=".$substitute;
    $query .= " order by P_NOM, P_PRENOM";
    
    $result=mysqli_query($dbc,$query);
    while ($row=mysqli_fetch_array($result)) {
        $R = strtoupper($row["P_NOM"])." ".my_ucfirst($row["P_PRENOM"]);
        if ( $grades ) $R .= " (".$row["P_GRADE"].")";
        if ( $nbsections == 0 ) $R .= " - ".$row["S_CODE"];
        if ( $row["P_ID"] == $substitute ) $selected='selected';
        else $selected='';
        $html .= "<option value='".$row["P_ID"]."' class='".$row["P_STATUT"]."' $selected>".$R."</option>";
    }
    $html .="</select>";
    if ( $nbR == 0 ) $html .="<br><small>Le responsable pourra choisir une autre remplaçant.</small>";
}
else {
    if ( $grades and $substitute > 0 )  $html .="<img src=".$grades_imgdir."/".$row["g8"].".png height=20 title='".$row["g8"]."' style='PADDING:1px;' class='img-max-20'>";
    $html .= " <input type='hidden' name='substitute' id='substitute' value='".$substitute."'><b>".$substitute_name."</b>";
}
$html .= "</td></tr>";

// détails de la demande
if ( $nbR == 1 ) {
    $html .= "<tr bgcolor=$mylightcolor class='small'><td><span class='badge' style='background-color:orange;'>Demandé</span></td>";
    $html .= "<td>Le ".$date_request."</td></tr>";
    $html .= "<tr bgcolor=$mylightcolor class='small' ><td align='right'> </td>";
    $html .= "<td>Par ".$requested_by."</td></tr>";
    if ( $accepted == 1 ) {
        $html .= "<tr bgcolor=$mylightcolor class='small'><td><span class='badge' style='background-color:purple;'>Accepté</span></td>";
        $html .= "<td>Le ".$date_accept."</td></tr>";
        $html .= "<tr bgcolor=$mylightcolor class='small' ><td align='right'></td>";
        $html .= "<td>Par le remplaçant  ".$accepted_by."</td></tr>";
    }
    if ( $rejected == 1 ) {
        $html .= "<tr bgcolor=$mylightcolor class='small'><td><span class='badge' style='background-color:red;'>Refusé</span></td>";
        $html .= "<td>Le ".$date_reject."</td></tr>";
        $html .= "<tr bgcolor=$mylightcolor class='small' ><td align='right'></td>";
        $html .= "<td>Par  ".$rejected_by."</td></tr>";
    }
    else if ( $approved == 1 ) {
        $html .= "<tr bgcolor=$mylightcolor class='small'><td><span class='badge' style='background-color:green;'>Approuvé</span></td>";
        $html .= "<td>Le ".$date_approve."</td></tr>";
        $html .= "<tr bgcolor=$mylightcolor class='small'><td align='right'> </td>";
        $html .= "<td>Par ".$approved_by."</td></tr>";
    }
}
$html .= "</table><p>";
// créer un remplacement
if ( $nbR == 0  ) {
    $html .= "<input type='button' value='Demander'  class='btn btn-default' title='Sauver la demande de remplacement, un mail sera envoyé'
            onclick=\"javascript:create('".$evenement."','demande');\">";
    if ( $admin_evenement ) {
        $html .= " <p><input type='submit' value='Approuver' class='btn btn-success' title='Valider la demande de remplacement, le personnel sera remplacé'
                onclick=\"javascript:create('".$evenement."','validate');\">";   
    }
    $html .= " <input type='button' value='retour'  class='btn btn-default' title='Retour' onclick='javascript:self.location.href=\"evenement_display.php?tab=10&evenement=".$evenement."\"'>";
}
// modifier un remplacement
else if ($rejected == 0 and $approved == 0 ) {
    if ( ($replaced == $id and $accepted == 0 ) or $admin_evenement )
        $html .= " <input type='button' value='Sauver' class='btn btn-default' title='Sauver la demande de remplacement'
        onclick=\"javascript:update('".$rid."','".$evenement."','update');\">";
    if ( $id == $substitute and $accepted == 0 ) {
        $html .= " <input type='button' value='Accepter' class='btn btn-primary' title='Accepter de faire le remplacement' 
         onclick=\"javascript:update('".$rid."','".$evenement."','accept');\">";
        $html .= " <input type='button' value='Refuser' class='btn btn-warning' title='Refuser de faire le remplacement' 
         onclick=\"javascript:update('".$rid."','".$evenement."','refuse');\">";
    }
    $html .= " <input type='button' value='retour'  class='btn btn-default' title='Retour' onclick='javascript:self.location.href=\"evenement_display.php?tab=10&evenement=".$evenement."\"'>";
    if ( $admin_evenement ) {
        $html .= " <p><input type='submit' value='Approuver' class='btn btn-success' title='Valider la demande de remplacement, le personnel sera remplacé'
                onclick=\"javascript:update('".$rid."','".$evenement."','validate');\">";
        $html .= " <input type='submit' value='Rejeter' class='btn btn-danger' title='Rejeter la demande de remplacement' 
                onclick=\"javascript:update('".$rid."','".$evenement."','reject');\">";
        if ( $allowed_delete )
            $html .= " <input type='submit' value='Supprimer' class='btn btn-default' title='Supprimer la demande de remplacement' 
                onclick=\"javascript:update('".$rid."','".$evenement."','delete');\">";
    }
}
// déjà approuvé ou rejeté
else {
    $html .= " <input type='button' value='retour'  class='btn btn-default' title='Retour' onclick='javascript:self.location.href=\"evenement_display.php?tab=10&evenement=".$evenement."\"'>";
    if ( $allowed_delete and $admin_evenement)
        $html .= " <input type='submit' value='Supprimer' class='btn btn-default' title='Supprimer la demande de remplacement' 
                onclick=\"javascript:update('".$rid."','".$evenement."','delete');\">";
}   
print $html;
writefoot();

?>