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

$printed_by="imprim� par ".my_ucfirst(get_prenom($id))." ".strtoupper(get_nom($id)). " le ".date("d-m-Y � H:i");

if ( isset($_GET["P_ID"])) $pid=intval($_GET["P_ID"]);
else $pid=0;
if ( isset($_GET["evenement"])) $evenement=intval($_GET["evenement"]);
else $evenement=0;
if ( isset($_GET["type"])) $type=$_GET["type"];
else $type='ASA'; // ASA ou OM

if ( $type == 'OM' ) $type_long='Ordre de Mission';
else $type_long='Autorisation Sp�ciale Absence';

$his_section = get_section_of($pid);

if ( $pid <> $id ) {
    check_all(2);
    if (! check_rights($id, 2, $his_section )) check_all(24);
}
if ( $syndicate == 0 )  check_all(14);

require_once("lib/fpdf/fpdf.php");
require_once("lib/fpdf/fpdi.php");
require_once("lib/fpdf/ebrigade.php");

$asa=true;
$no_address=true;
$pdf=new PDFEB();
$pdf->AliasNbPages();
$pdf->SetCreator($cisname);
$pdf->SetAuthor($cisname);
$pdf->SetDisplayMode('fullpage','single');
$pdf->SetTitle($type_long);
$pdf->SetAutoPageBreak(40);
$pdf->AddPage();

$query="select distinct p.P_CODE ,p.P_ID , p.P_NOM , p.P_PRENOM,p.P_SEXE,
        p.P_STATUT, s1.S_DESCRIPTION as P_DESC_STATUT,
        p.P_ADDRESS, p.P_ZIP_CODE, p.P_CITY, DATE_FORMAT(p.P_CREATE_DATE,'%d-%m-%Y' ) P_CREATE_DATE, tc.TC_LIBELLE,
        p.SERVICE, p.P_OLD_MEMBER,
        ANTENA_DISPLAY (s2.s_code) 'CENTRE',
        case
            when s2.NIV=3 then DEP_DISPLAY (s2.S_CODE, s2.S_DESCRIPTION)            
            when s2.NIV=4 then DEP_DISPLAY (sp.S_CODE, sp.S_DESCRIPTION)
        end
        as 'DEPARTEMENT'
        from pompier p ,statut s1, type_civilite tc,
        section_flat s2 left join section sp on sp.s_id = s2.s_parent
        where tc.TC_ID = p.P_CIVILITE
        and s2.S_ID=p.P_SECTION
        and s1.S_STATUT=p.P_STATUT
        and p.P_ID=".$pid;
  
$result=mysqli_query($dbc,$query);

// check input parameters
if ( mysqli_num_rows($result) <> 1 ) {
    param_error_msg();
    exit;
}

custom_fetch_array($result);

$P_PRENOM=my_ucfirst($P_PRENOM);
$P_NOM=strtoupper($P_NOM);
$P_ADDRESS=stripslashes($P_ADDRESS);

$query="select E_LIEU from evenement where E_CODE=".$evenement;
$result=mysqli_query($dbc,$query);
custom_fetch_array($result);

$query2= get_asa_query2($pid,$evenement);
$result2=mysqli_query($dbc,$query2);
custom_fetch_array($result2);
$dates=rtrim(ucfirst($dates),', ');

// chercher le chef ou pr�sident d�partemental
$query3="select p.P_PRENOM PRES_PRENOM, p.P_NOM PRES_NOM, g.GP_DESCRIPTION
        from pompier p, groupe g, section_role sr
        where sr.GP_ID = g.GP_ID
        and sr.P_ID = p.P_ID
        and sr.S_ID in (0,1)
        and sr.GP_ID = 101
        order by sr.S_ID desc, p.P_NOM asc";
$result3=mysqli_query($dbc,$query3);
custom_fetch_array($result3);

$txt1="En vertu des dispositions de la loi du 26 janvier 1984, portant sur les droits syndicaux dans la Fonction publique territoriale, je vous prie de bien vouloir accorder une Autorisation Sp�ciale d�Absence au titre des articles 15 et 16 du d�cret 85-397 (contingent individuel 10+10) � :";
$txt2="pour participer au Congr�s National de la ".$cisname." r�uni en Bureau National.";
$txt3="Avec mes remerciements anticip�s, veuillez agr�er, Monsieur le Pr�sident, l�expression de ma tr�s haute consid�ration.";

// ==========================================
// generate PDF
// ==========================================

$GoX=45;
$GoDown=7;
$np=60;
$y=$np;

function GoDown($returns=1, $ymax = 240) {
    global $pdf, $np, $y, $GoDown;
    if ( $y > $ymax ) {
        $pdf->AddPage();
        $y=$np;
    }
    else 
        $y = $y + $returns * $GoDown;
}

// =========================================================
// ASA
// =========================================================
if ( $type == 'ASA' ) {
    $pdf->SetFont('Times','',11); 
    $pdf->SetXY($GoX + 65,$y);
    $pdf->MultiCell(70,5,"MONSIEUR LE PRESIDENT\nDU CONSEIL D'ADMINISTRATION\nDU SERVICE DEPARTEMENTAL\nD'INCENDIE ET DE SECOURS\n".$DEPARTEMENT,"0","C");
    GoDown(5);
    $pdf->SetXY($GoX + 80,$y);
    $pdf->MultiCell(100,7,"Villeneuve-Loubet, le ".date('d-m-Y'),"0","L");
    GoDown(2); 
    $pdf->SetXY($GoX + 80,$y);
    $pdf->MultiCell(100,7,"Monsieur le Pr�sident,","0","L");
    GoDown(2);
    $pdf->SetXY($GoX,$y);
    $pdf->MultiCell(140,5,$txt1);
    GoDown(5);
    $pdf->SetFont('Times','B',12); 
    $pdf->Text($GoX + 40, $y, $TC_LIBELLE." ".$P_NOM." ".$P_PRENOM);
    GoDown(2);
    $pdf->SetFont('Times','',11); 
    $pdf->SetXY($GoX,$y);
    $pdf->MultiCell(140,5,$dates.", afin de se rendre � ".$E_LIEU." ".$txt2);
    GoDown(3);
    $pdf->SetXY($GoX,$y);
    $pdf->MultiCell(140,5,$txt3);
}
// =========================================================
// OM
// =========================================================
if ( $type == 'OM' ) {
    $pdf->SetFont('Times','',11); 
    $pdf->SetXY($GoX + 80,$y);
    $pdf->MultiCell(100,7,"Villeneuve-Loubet, le ".date('d-m-Y'),"0","L");
    GoDown(4);
    $pdf->SetFont('Times','B',15);
    $pdf->SetTextColor(0,0,200);
    $pdf->SetXY($GoX ,$y);
    $pdf->MultiCell(140,10,"ORDRE DE MISSION","1","C");
    GoDown(5);
    $pdf->SetFont('Times','',11); 
    $pdf->SetTextColor(0,0,0);
    $pdf->SetXY($GoX,$y);
    $pdf->MultiCell(140,7,"Je soussign� ".my_ucfirst($PRES_PRENOM)." ".strtoupper($PRES_NOM).", ".$GP_DESCRIPTION." F�d�ral, donne pour mission �:","0","L");
    GoDown(3);
    $pdf->SetFont('Times','B',12); 
    $pdf->Text($GoX + 40, $y, $TC_LIBELLE." ".$P_NOM." ".$P_PRENOM);
    GoDown(2);
    $pdf->SetFont('Times','',11); 
    $pdf->SetXY($GoX,$y);
    $pdf->MultiCell(140,5,$dates.", afin de se rendre � ".$E_LIEU." ".$txt2);
    
}

// =========================================================
// FIN
// =========================================================
$pdf->SetDisplayMode('fullpage','single');
$pdf->Output(fixcharset(str_replace(" ","_",$type_long)."_".$P_NOM."_".$P_PRENOM).".pdf",'I');

?>
