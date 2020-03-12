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

$printed_by="imprimé par ".my_ucfirst(get_prenom($id))." ".strtoupper(get_nom($id)). " le ".date("d-m-Y à H:i");

if ( isset($_GET["P_ID"])) $pid=intval($_GET["P_ID"]);
else $pid=0;

$his_section = get_section_of($pid);

if ( $pid <> $id )  {
    check_all(59);
    if (! check_rights($id, 59, $his_section )) check_all(24);
}

require_once("lib/fpdf/fpdf.php");
require_once("lib/fpdf/fpdi.php");
require_once("lib/fpdf/ebrigade.php");

$courrier_nouvel_adherent_prelevement=$basedir."/images/user-specific/courrier_nouvel_adherent_prelevement.pdf";
$courrier_nouvel_adherent_autre=$basedir."/images/user-specific/courrier_nouvel_adherent_autre.pdf";

$notemplate=1;
$no_address=true;
$special_template=$courrier_nouvel_adherent_prelevement;
$pdf=new PDFEB();
$pdf->AliasNbPages();
$pdf->SetCreator($cisname);
$pdf->SetAuthor($cisname);
$pdf->SetDisplayMode('fullpage','single');
$pdf->SetTitle("Attestation");
$pdf->SetAutoPageBreak(40);
$pdf->AddPage();

$query="select distinct p.P_CODE ,p.P_ID , p.P_NOM , p.P_PRENOM,p.P_SEXE,
        p.P_STATUT, s1.S_DESCRIPTION as P_DESC_STATUT,
        p.P_ADDRESS, p.P_ZIP_CODE, p.P_CITY, 
        DATE_FORMAT(p.P_DATE_ENGAGEMENT,'%d-%m-%Y' ) P_DATE_ENGAGEMENT, 
        tc.TC_LIBELLE,
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
    writehead();
    param_error_msg();
    exit;
}

custom_fetch_array($result);
$P_PRENOM=my_ucfirst($P_PRENOM);
$P_NOM=strtoupper($P_NOM);
$P_ADDRESS=stripslashes($P_ADDRESS);

// ==========================================
// generate PDF
// ==========================================

$GoX=45;
$GoDown=7;
$np=85;
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

$pdf->SetFont('times','',11); 
$pdf->SetXY(120,$y);
$pdf->MultiCell(80,6,$P_DATE_ENGAGEMENT,"0","C");
GoDown(5);

// =========================================================
// calculate amount
// =========================================================
$cotisation=get_montant_cotisation($pid);
$COTISATION_MENSUELLE=round($cotisation / 12, 2 );

// =========================================================
// location
// =========================================================
$query="select S_CITY from section where S_ID < 5 order by S_ID asc";
$result=mysqli_query($dbc,$query);
$city='';
while ($row=mysqli_fetch_array($result)) {
    if (  $row["S_CITY"] <> '' and $city == '') $city = $row["S_CITY"];
}

// =========================================================
// print text
// =========================================================
/* $pdf->SetFont('Helvetica','B',10); 
$pdf->Text($GoX,$y,$TC_LIBELLE." ".$P_NOM." ".$P_PRENOM);
GoDown(1);
$pdf->SetFont('Helvetica','',10); 
$pdf->Text($GoX,$y,"Demeurant : ");
$pdf->SetFont('Helvetica','B',10);
$pdf->Text($GoX + 20,$y,$P_ADDRESS." ".$P_ZIP_CODE." ".$P_CITY);
GoDown(1);
$pdf->SetFont('Helvetica','',10); 
$pdf->Text($GoX,$y,"Employé du Service Départemental d'Incendie et de Secours du: ");
$pdf->SetFont('Helvetica','B',10);
$pdf->Text($GoX + 102,$y,$DEPARTEMENT);
GoDown(1);
$pdf->SetFont('Helvetica','',10); 
$pdf->Text($GoX,$y,"A acquitté sa cotisation pour l'année ");
$pdf->SetFont('Helvetica','B',10);
$pdf->Text($GoX + 67,$y,"d'un montant de ".$cotisation." ".$default_money_symbol);
GoDown(3);
$pdf->SetFont('Helvetica','',10); 
$pdf->Text($GoX + 50,$y,"Fait à ".$city.", le ".$P_DATE_ENGAGEMENT); */

$no_header=true;
$pdf->AddPage();

// =========================================================
// FIN
// =========================================================
$pdf->SetDisplayMode('fullpage','single');
$pdf->Output(fixcharset("Courrier_Nouvel_Adherent_".$P_NOM."_".$P_PRENOM).".pdf",'I');

?>
