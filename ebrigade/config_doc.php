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

//-------------------------------------------
// Attestations de formation continues
//-------------------------------------------
if ( ! isset($TF_CODE) ) $TF_CODE='I'; 
    
if ( $TF_CODE == 'R' ) {
    $attestation_complement1="";
    $attestation_complement2="� a suivi une session de formation continue sur les unit�s d'enseignement";
    $attestation_complement3="";
    $attestation_complement10="En foi de quoi, nous d�livrons � l�int�ress� la pr�sente attestation pour servir et valoir ce que de droit.";
    $attestation_arretes="";
    $libelle_competence1=$description." (".$type.")";
    $libelle_competence2="";
    $libelle_competence3="";
    $libelle_competence4="";
    $formation=$type;
    if ( $type == 'PSC1' ) {
        $libelle_competence1="pr�vention et secours civiques de niveau 1 (PSC1)";
        $attestation_arretes="
        � Vu l�arr�t� du 24 mai 2000 portant organisation de la formation continue dans le domaine des premiers secours;
        � Vu l�arr�t� du 24 juillet 2007 fixant le r�f�rentiel national de comp�tences de s�curit� civile relatif � l�unit� d�enseignement � pr�vention et secours civiques de niveau 1 �;
        � Vu la d�cision d�agr�ment PSC1-1707B11 d�livr�e le 5 juillet 2017 relative aux r�f�rentiels internes de formation et de certification � l�unit� d�enseignement � pr�vention et secours civiques de niveau 1 �;
        � Vu le proc�s-verbal de formation n�".$evenement.", �tabli en date du ".$date_pv;
        $attestation_complement1="� La formation continue n�est valable que pour les titulaires d�un certificat de comp�tences PSC 1 datant de 5 ans au plus tard apr�s son obtention.";
    }
    else if ( $type == 'PSE1' ) {
        $libelle_competence1="premiers secours en �quipe de niveau 1 (PSE1)";
        $attestation_arretes="
        � Vu l�arr�t� du 24 mai 2000 portant organisation de la formation continue dans le domaine des premiers secours ;
        � Vu l�arr�t� du 24 ao�t 2007 modifi� fixant le r�f�rentiel national de comp�tences de s�curit� civile relatif � l�unit� d�enseignement � premiers secours en �quipe de niveau 1 �;
        � Vu la d�cision d�agr�ment PSE1-1805A12 d�livr�e le 17 mai 2018 relative aux r�f�rentiels internes de formation et de certification � l�unit� d�enseignement � premiers secours en �quipe de niveau 1 �;
        � Vu le proc�s-verbal de formation n�".$evenement.", �tabli en date du ".$date_pv;
        $attestation_complement1="� Sa comp�tence de secouriste (PSE 1) peut �tre exerc�e jusqu�� une prochaine formation continue ou au plus tard au 31 d�cembre N+1 inclus sous l��gide d�une association agr��e ou d�un organisme habilit� � la formation aux premiers secours conform�ment aux textes r�glementaires en vigueur ou dans le cadre d�une activit� professionnelle lorsque celle-ci est exig�e.";
    }
    else if ( $type == 'PSE2' ) {
        $libelle_competence1="premiers secours en �quipe de niveau 1 (PSE1)";
        $libelle_competence2="premiers secours en �quipe de niveau 2 (PSE2)";
        $attestation_arretes="
        � Vu l�arr�t� du 24 mai 2000 portant organisation de la formation continue dans le domaine des premiers secours ;
        � Vu l�arr�t� du 14 novembre 2007 modifi� fixant le r�f�rentiel national de comp�tences de s�curit� civile relatif � l�unit� d�enseignement � premiers secours en �quipe de niveau 2 � ;
        � Vu la d�cision d�agr�ment PSE1-1805A12 d�livr�e le 17 mai 2018 relative aux r�f�rentiels internes de formation et de certification � l�unit� d�enseignement � premiers secours en �quipe de niveau 1 �
        � Vu la d�cision d�agr�ment PSE2-1805A12 d�livr�e le 17 mai 2018 relative aux r�f�rentiels internes de formation et de certification � l�unit� d�enseignement � premiers secours en �quipe de niveau 2 �
        � Vu le proc�s-verbal de formation n�".$evenement.", �tabli en date du ".$date_pv;
        $attestation_complement1="� Ses comp�tences de secouriste (PSE 1) et d��quipier-secouriste (PSE 2) peuvent �tre exerc�es jusqu�� une prochaine formation continue ou au plus tard au 31 d�cembre ".$next_year." inclus sous l��gide d�une association agr��e ou d�un organisme habilit� � la formation aux premiers secours conform�ment aux textes r�glementaires en vigueur ou dans le cadre d�une activit� professionnelle lorsque celles-ci sont exig�es.";
    }
    else if ( $type == 'PAEPSC' ) {
        $formation="FPSC/PSC 1";
        $libelle_competence1="p�dagogie appliqu�e � l�emploi de formateur en pr�vention et secours civiques (FPSC)";
        $libelle_competence2="pr�vention et secours civiques de niveau 1 (PSC 1)";
        $attestation_arretes="
        � Vu l�arr�t� du 24 mai 2000 portant organisation de la formation continue dans le domaine des premiers secours ;
        � Vu l�arr�t� du 4 septembre 2012 fixant le r�f�rentiel national de comp�tences de s�curit� civile relatif � l�unit� d�enseignement � p�dagogie appliqu�e � l�emploi de formateur en pr�vention et secours civiques � ;
        � Vu la d�cision d�agr�ment PAEFPSC-1705B92 d�livr�e le 17 mai 2019 relative aux r�f�rentiels internes de formation et de certification � l�unit� d�enseignement � p�dagogie appliqu�e � l�emploi de formateur en pr�vention et secours civiques �
        � Vu la d�cision d�agr�ment PSC1-1707B11 d�livr�e le 05 juillet 2017 relative aux r�f�rentiels internes de formation et de certification � l�unit� d�enseignement � pr�vention et secours civiques de niveau 1 �
        � Vu le proc�s-verbal de formation n�".$evenement.", �tabli en date du ".$date_pv;
        $attestation_complement1="� Ses comp�tences de formateur en pr�vention et secours civiques (FPSC) et de sauveteur (PSC 1) peuvent �tre jusqu�� une prochaine formation continue ou au plus tard au 31 d�cembre ".$next_year." inclus sous l��gide d�une association agr��e ou d�un organisme habilit� � la formation aux premiers secours conform�ment aux textes r�glementaires en vigueur.";
    }
    else if ( $type == 'PAEPS' ) {
        $formation="FPS/PSE1/PSE2";
        $libelle_competence1="p�dagogie appliqu�e � l�emploi de formateur aux premiers secours (FPS)";
        $libelle_competence2="premiers secours en �quipe de niveau 1 (PSE 1)";
        $libelle_competence3="premiers secours en �quipe de niveau 2 (PSE 2)";
        $attestation_arretes="
        � Vu l�arr�t� du 24 mai 2000 portant organisation de la formation continue dans le domaine des premiers secours ;
        � Vu l�arr�t� du 3 septembre 2012 fixant le r�f�rentiel national de comp�tences de s�curit� civile relatif � l�unit� d�enseignement � p�dagogie appliqu�e � l�emploi de formateur aux premiers secours � ;
        � Vu la d�cision d�agr�ment PAEFPS-1802B01 d�livr�e le 13 f�vrier 2018 relative aux r�f�rentiels internes de formation et de certification � l�unit� d�enseignement � p�dagogie appliqu�e � l�emploi de formateur aux premiers secours �
        � Vu la d�cision d�agr�ment PSE1-1805A12 d�livr�e le 17 mai 2018 relative aux r�f�rentiels internes de formation et de certification � l�unit� d�enseignement � premiers secours en �quipe de niveau 1 �
        � Vu la d�cision d�agr�ment PSE2-1805A12 d�livr�e le 17 mai 2018 relative aux r�f�rentiels internes de formation et de certification � l�unit� d�enseignement � premiers secours en �quipe de niveau 2 �
        � Vu le proc�s-verbal de formation n�".$evenement.", �tabli en date du ".$date_pv;
        $attestation_complement1="� Ses comp�tences de formateur aux premiers secours (FPS), de secouriste (PSE 1) et  d��quipier-secouriste (PSE 2) peuvent �tre exerc�es jusqu�� une prochaine formation continue ou au plus tard au 31 d�cembre ".$next_year." inclus sous l��gide d�une association agr��e ou d�un organisme habilit� � la formation aux premiers secours conform�ment aux textes r�glementaires en vigueur ou dans le cadre d�une activit� professionnelle lorsque celles-ci sont exig�es.";
    }
    else if ( $type == 'FDFPSC' ) {
        $formation="FDF/FPSC/PSC1";
        $libelle_competence1="p�dagogie appliqu�e � l�emploi de formateur de formateurs (FDF)";
        $libelle_competence2="p�dagogie appliqu�e � l�emploi de formateur en pr�vention et secours civiques (FPSC)";
        $libelle_competence3="pr�vention et secours civiques de niveau 1 (PSC 1 )";
        $attestation_arretes="
        � Vu l�arr�t� du 24 mai 2000 portant organisation de la formation continue dans le domaine des premiers secours ;
        � Vu l�arr�t� du 17 ao�t 2012 fixant le r�f�rentiel national de comp�tences de s�curit� civile relatif � l�unit� d�enseignement � p�dagogie appliqu�e � l�emploi de formateur de formateurs � ;
        � Vu la d�cision d�agr�ment PAEFDF-1609A03 d�livr�e le 21 septembre 2016 relative aux r�f�rentiels internes de formation et de certification � l�unit� d�enseignement � p�dagogie appliqu�e � l�emploi de formateur de formateurs � ;
        � Vu la d�cision d�agr�ment PAEFPSC-1705B92 d�livr�e le 17 mai 2019 relative aux r�f�rentiels internes de formation et de certification � l�unit� d�enseignement � p�dagogie appliqu�e � l�emploi de formateur en pr�vention et secours civiques � ;
        � Vu la d�cision d�agr�ment PSC1-1707B11 d�livr�e le 05 juillet 2017 relative aux r�f�rentiels internes de formation et de certification � l�unit� d�enseignement � pr�vention et secours civiques de niveau 1 � ;
        � Vu le proc�s-verbal de formation n�".$evenement.", �tabli en date du ".$date_pv;
        $attestation_complement1="� Ses comp�tences de formateur de formateurs (FDF), de formateur en pr�vention et  secours civiques (FPSC) et de sauveteur (PSC 1) peuvent �tre exerc�es jusqu�� une prochaine formation continue ou au plus tard au 31 d�cembre ".$next_year." inclus sous l��gide d�une association agr��e ou d�un organisme habilit� � la formation aux premiers secours conform�ment aux textes r�glementaires en vigueur.";
    }
    else if ( $type == 'FDFPSE' ) {
        $formation="FDF/FPS/PSE1/PSE2";
        $libelle_competence1="p�dagogie appliqu�e � l�emploi de formateur de formateurs (FDF)";
        $libelle_competence2="p�dagogie appliqu�e � l�emploi de formateur aux premiers secours (FPS)";
        $libelle_competence3="premiers secours en �quipe de niveau 1 (PSE 1)";
        $libelle_competence4="premiers secours en �quipe de niveau 2 (PSE 2)";
        $attestation_arretes="
        � Vu l�arr�t� du 24 mai 2000 portant organisation de la formation continue dans le domaine des premiers secours ;
        � Vu l�arr�t� du 17 ao�t 2012 fixant le r�f�rentiel national de comp�tences de s�curit� civile relatif � l�unit� d�enseignement � p�dagogie appliqu�e � l�emploi de formateur de formateurs � ;
        � Vu la d�cision d�agr�ment PAEFDF-1609A03 d�livr�e le 21 septembre 2016 relative aux r�f�rentiels internes de formation et de certification � l�unit� d�enseignement � p�dagogie appliqu�e � l�emploi de formateur de formateurs � ;
        � Vu la d�cision d�agr�ment PAEFPS-1802B01 d�livr�e le 13 f�vrier 2018 relative aux r�f�rentiels internes de formation et de certification � l�unit� d�enseignement � p�dagogie appliqu�e � l�emploi de formateur aux premiers secours �
        � Vu la d�cision d�agr�ment PSE1-1805A12 d�livr�e le 17 mai 2018 relative aux r�f�rentiels internes de formation et de certification � l�unit� d�enseignement � premiers secours en �quipe de niveau 1 �
        � Vu la d�cision d�agr�ment PSE2-1805A12 d�livr�e le 17 mai 2018 relative aux r�f�rentiels internes de formation et de certification � l�unit� d�enseignement � premiers secours en �quipe de niveau 2 �
        � Vu le proc�s-verbal de formation n�".$evenement.", �tabli en date du ".$date_pv;
        $attestation_complement1="� Ses comp�tences de formateur de formateurs (FDF), de formateur en premiers secours (FPS), de secouriste (PSE 1) et  d��quipier-secouriste (PSE 2) peuvent �tre exerc�es jusqu�� une prochaine formation continue ou au plus tard au 31 d�cembre ".$next_year." inclus sous l��gide d�une association agr��e ou d�un organisme habilit� � la formation aux premiers secours conform�ment aux textes r�glementaires en vigueur ou dans le cadre d�une activit� professionnelle lorsque celles-ci sont exig�es.";
    }
    
    $notification_formation_continue="
    Vous avez suivi une session de formation continue relative � � ".$description."�  (".$type."), organis�e ".$periode." � ".$lieu.".\n
    L��quipe p�dagogique d�sign�e pour cette session a proc�d� � votre �valuation conform�ment aux dispositions r�glementaires en vigueur.\n
    Celle-ci n�a pas permis de donner un avis favorable pour permettre la reconduction de vos fonctions en qualit� de [secouriste, �quipier secouriste, formateur en pr�vention et secours civiques, formateur aux premiers secours, formateur de formateurs] � compter de ce jour jusqu�� une nouvelle �valuation favorable lors d�une formation continue.\n
    Cette d�cision entra�ne une incapacit� temporaire � exercer [votre/vos] fonction(s) dans les organismes habilit�s, associations agr��es ou dans toute autre activit� professionnelle lorsque cette/ces comp�tence(s) est (sont) exig�e(s).\n
    Vous pouvez suivre une mise � niveau de vos connaissances afin de proc�der � une nouvelle �valuation. Si l��valuation est favorable, vous pourrez exercer imm�diatement [votre/vos] fonction(s) apr�s l�obtention de l�attestation de formation continue.\n
    Enfin, il vous appartient de transmettre cette d�cision � l�ensemble de vos autorit�s d�emploi dont vous assurez des missions qui requi�rent des comp�tences en mati�re de premiers secours.";
}
//-------------------------------------------
// Attestations de formation initiales
//-------------------------------------------
else {
    $attestation_complement1="Cette attestation autorise l�autorit� d�emploi de";
    $attestation_complement2="� l�inscrire sur une liste d�aptitude permettant l�emploi en qualit� de";

    $attestation_complement3="� condition que les autres modalit�s de l�arr�t� du 24/05/2000 soient satisfaites.";
    $attestation_arretes="� Vu la loi n� 2004-811 du 13/08/04 de la modernisation de la S�curit� Civile�;
    � Vu le d�cret n� 91-834 du 30/08/91 modifi�, relatif � la formation aux premiers secours�;
    � Vu le d�cret n�97-48 du 20/01/97 portant diverses mesures relatives au secourisme�;
    � Vu le d�cret n� 2006-237 du 27/02/06 relatif � la proc�dure d�agr�ment de s�curit� civile, notamment ses articles 1 et 3�;
    � Vu l�arr�t� du 08/07/92 modifi�, relatif aux conditions d�habilitation ou d�agr�ment pour les formations aux premiers secours�;
    � Vu l�arr�t� du 24/05/00 portant organisation de la formation continue dans le domaine des premiers secours�;";
}


//-------------------------------
// factures individuelles
//-------------------------------
if ( $mode == 7 ) {
    $facture1="Suite � votre participation � la formation suivante, veuillez trouver votre facture.";
    $facture2="Facture non assujettie � la TVA.";
    $facture3="Dans l'attente de votre aimable r�glement.";
    $facture4="Facture r�gl�e";
}

//-------------------------------
// attestation de pr�sence SST
//-------------------------------
if ( $mode == 10 ) {
    if ( $type == 'SST' ) {
        $lig1="En r�f�rence au proc�s-verbal, r�dig� le $maxdate � l'issue de la formation de Sauvetage Secourisme du Travail.";
        $lig2="Je soussign�, ".$responsable_evt.",  Formateur en Sauvetage Secourisme du Travail de la F�d�ration Nationale de Protection Civile,";
        $lig3="Certifie que :";
        $lig4="A particip� � la Formation de Sauveteurs Secouristes du Travail";
    }
    if ( $type == 'PRAP' ) {
        $lig1="En r�f�rence au proc�s-verbal, r�dig� le $maxdate � l'issue de la formation de Pr�vention des Risques li�s � l'Activit� Physique.";
        $lig2="Je soussign�, ".$responsable_evt.",  Formateur en Pr�vention des Risques li�s � l'Activit� Physique de la F�d�ration Nationale de Protection Civile,";
        $lig3="Certifie que :";
        $lig4="A particip� � la Formation de Pr�vention des Risques li�s � l'Activit� Physique";
    }        
    $lig5="Pour servir et valoir ce que de droit.";
    $lig6="Cette attestation n'est pas une attestation de r�ussite";
}
//-------------------------------
// convention Formation
//-------------------------------
if ( $mode == 26 ) {
$ct = array ();
$cs = array ();
$y_after_new_page=46;
$cs[]="addpage";$ct[]="";
$cs[]="setxy";  $ct[]="15,".$y_after_new_page;
if ( $num_convention <> "" ) $d = " n�".$num_convention; else $d=' n� non d�fini';
$cs[]="title1"; $ct[]="CONVENTION ".$d;
$cs[]="setxy";  $ct[]="15,60";
$cs[]="bold";  $ct[]=" Entre : ".$company;
$association=ucfirst($section_prefix)." ".$section_affiche.$antenne_affiche;
$cs[]="bold"; $ct[]=" Et : ".$association;
$cs[]="title2";  $ct[]="\n1�) ".$association." s'engage:";
$cs[]="small";  $ct[]=" 1.1) Type de formation: ".$TF_LIBELLE." ".$type;
$cs[]="small";  $ct[]=" 1.2) Dur�e de la formation: ".$E_DUREE_TOTALE." h";
$cs[]="small";  $ct[]=" 1.3) Date de la formation: ".$dates_heures;
$cs[]="small";  $ct[]=" 1.4) Lieu de la formation: ".$lieu;
$cs[]="small";  $ct[]=" 1.5) Nombre de participants: ".$nb_stagiaires;
$cs[]="small";  $ct[]=" 1.6) Noms des participants: ";
$inscrits=get_noms_inscrits($evenement,$mode='stagiaires');
$cs[]="chapter";  $ct[]=$inscrits;
$cs[]="small";  $ct[]=" 1.7) Nom des intervenants pr�vus: ";
$formateurs=get_noms_inscrits($evenement,$mode='formateurs');
$cs[]="chapter";  $ct[]=$formateurs;
$cs[]="small";  $ct[]=" 1.8) Nom et adresse de facturation: ".$company." ( Adresse : ".$company_address." ".$company_cp." ".$company_city." )\n";
$cs[]="title2";  $ct[]="\n2�) ".$company." s'engage �:";
$cs[]="small";  $ct[]=" 2.1) Verser � ".$association.", apr�s la r�alisation de l�action de formation et � �mission de sa facture, la somme de ".$E_TARIF." ".$default_money_symbol." par personne pour la prestation (exon�r� de TVA) par mandat administratif.";
$total_amount = floatval($E_TARIF) * intval($nb_stagiaires);
$cs[]="small";  $ct[]=" 2.2)  Soit un total de ".$total_amount." ".$default_money_symbol." pour la session de ".$nb_stagiaires." candidats au total.";
$cs[]="small";  $ct[]="\n\n\nFait � ".$organisateur_city.", le ".date('d-m-Y');

$cs[]="italic"; $ct[]="Pour ".$section_prefix." ".$section_affiche.",\n".$chef;
if ( $company == "" ) $o="l'organisateur";
else $o=$company ;
$cs[]="righti"; $ct[]="Fait � . . . . . . . . . . . . . . . . . . . . . le . . . . . . . . . . . . . . . . . . . \nPour $o".
"\n\n\n";
$cs[]="image_signature";$ct[]="20,235";
$cs[]="setxy";  $ct[]="15,260";
$cs[]="italic"; $ct[]="Convention �tablie en trois exemplaires originaux, dont deux � retourner sign�s pour accord au si�ge de ".$association.".";
}

//-------------------------------
// conventions DPS
//-------------------------------
if ( $mode == 6 ) {
$ct = array ();
$cs = array ();
$y_after_new_page=46;
$cs[]="addpage";$ct[]="";
$cs[]="setxy";  $ct[]="15,".$y_after_new_page;
$cs[]="right";  $ct[]="".$company.""."\n ".$company_address.""."\n ".$company_cp."  ". "".$company_city."";
$t="Objet : Dispositif secouriste ".$description."\nAffaire suivie par : ";
if ( $responsable_evt == "" ) 
    $t .=$responsable_antenne."\nT�l. ".$S_PHONE."\nEmail : ".$S_EMAIL;
else
    $t .=$responsable_evt."\nT�l. ".$responsable_evt_phone."\nEmail : ".$S_EMAIL;
$cs[]="bold"; $ct[]=$t;
$cs[]="right";  $ct[]="\n".$organisateur_city." le ".date('d-m-Y')."";
$cs[]="normal"; $ct[]="\n\n"."Madame, Monsieur,"."\n"."\nSuite � votre demande de mise en place d'un dispositif pr�visionnel de secours (DPS), vous trouverez ci-joint :";
$cs[]="normal"; $ct[]="Deux exemplaires de la convention pr�cisant les modalit�s de notre accord. Vous voudrez bien les compl�ter et nous retourner un exemplaire sign�.";
$cs[]="normal"; $ct[]="Dans l'attente, veuillez, Madame, Monsieur, accepter nos salutations les meilleures.";
$cs[]="righti"; $ct[]="\n "."\n "."\n ".$titre_prefix." ".$titre.", ".$chef."\n "."\n "."";
$cs[]="image_signature";$ct[]="150,183";
$cs[]="addpage";$ct[]="";
$cs[]="setxy";  $ct[]="15,".$y_after_new_page;
$d = $description;
if ( $num_convention <> "" ) $d = $description." - convention n�".$num_convention;
$cs[]="title1"; $ct[]="Convention pour la mise en place d'un Dispositif Pr�visionnel de Secours \n".$d;
$cs[]="setxy";  $ct[]="15,70";
$cs[]="title1"; $ct[]="1. Association Prestataire";
$cs[]="normal"; $ct[]=" ".ucfirst($section_prefix)." ".$section_affiche." ".$antenne_affiche.
"\n Adresse : ".$S_ADDRESS." ".$S_ADDRESS_COMPLEMENT." ".$S_ZIP_CODE." - ".$S_CITY.
"\n T�l�phone : ".$S_PHONE.
"\n Courriel : ".$S_EMAIL.
"\n Ci-apr�s d�sign�e : Association prestataire".
"\n Repr�sent� par (Pr�nom, Nom) : ".$responsable_antenne;
$cs[]="italic"; $ct[]="Association ayant re�u notamment une autorisation d�exercice d�concentr�e pour les missions de s�curit� civile de type D (Certificat Original d�Affiliation en annexe) par sa r�guli�re affiliation � la F�d�ration Nationale de Protection Civile (".$cisname."), association de s�curit� civile agr��e au plan national par arr�t� minist�riel.\n\n";
$cs[]="title1";  $ct[]="2. Organisateur de l'�v�nement";
$cs[]="normal";  $ct[]=" Raison sociale de l'organisateur : ".$company."".
"\n Adresse : ".$company_address." ".$company_cp." - ".$company_city.
"\n T�l�phone : ".$company_phone."".
"\n Courriel : ".$company_email."".    
"\n Ci-apr�s d�sign�e : l'organisateur";

$cs[]="normal";
if ( strlen (preg_replace('/\s+/', '', $representant_legal)) > 0)
    $ct[]=" Repr�sentant L�gal: ".$representant_legal."\n\n";
else 
    $ct[]=" Repr�sent� par (Pr�nom, Nom) : ".$company_representant."\n\n";

$cs[]="title1"; $ct[]="3. Objet de la convention";
$cs[]="title2"; $ct[]="3.1 Objet";
$cs[]="normal"; $ct[]="La pr�sente convention a pour but de fixer les modalit�s de fonctionnement entre�:";
$cs[]="italic"; $ct[]=ucfirst($section_prefix)." ".rtrim($section_affiche)." ".rtrim($antenne_affiche).", qui peut r�guli�rement exercer, d'une mani�re d�concentr�e les missions de Dispositifs pr�visionnels de Secours.";
$cs[]="normal"; $ct[]="et";
$cs[]="italic"; $ct[]=" ".$company;
$cs[]="normal"; $ct[]="pour la mise en place d�un Dispositif Pr�visionnel de Secours, ceci afin de bien clarifier le cadre juridique de la prestation de service assur�e."; 
$a="La mise en place du Dispositif Pr�ventif de Secours concerne";
if ($is_acteurs > 0)
     $a .=" les acteurs de la manifestation (joueurs, comp�titeurs, com�diens, ...) et le public.";
else
    $a .=" le public seulement.";
$cs[]="normal"; $ct[]= $a;
$cs[]="addpage";$ct[]="";
$cs[]="setxy";  $ct[]="15,".$y_after_new_page;
$cs[]="title2"; $ct[]="3.2 Descriptif de l'�v�nement";
if ( strlen (preg_replace('/\s+/', '', $custom_horaire)) > 0) $periode_long =   str_replace(array("\n","\r"), ' ', $custom_horaire);

$cs[]="normal"; $ct[]=" Nom de l'�v�nement : ".$description.""."\n Date(s) : ".$periode_long.""."\n Lieu : ".$lieu."\n Adresse pr�cise : ".$E_ADDRESS;
$cs[]="title2"; $ct[]="3.3 Grille d'�valuation des risques";
$cs[]="normal"; $ct[]="Cet �v�nement a fait l'objet par l'organisateur d'une �valuation des risques dont la grille figure en annexe de la pr�sente convention.";
$cs[]="title2"; $ct[]="3.4 Autorisations";
$cs[]="normal"; $ct[]="L'organisateur reconnait poss�der toutes les autorisations n�cessaires au d�roulement de la dite manifestation et avoir souscrit une assurance responsabilit� civile organisateur.";
$cs[]="title2"; $ct[]="3.5 Responsabilit�s";
$cs[]="normal"; $ct[]="Conform�ment aux textes r�glementaires, l'organisateur est responsable de l'ensemble de l'organisation et des mesures prises en liaison avec l'autorit� de police comp�tente (maire, pr�fet).".
"\nLa mise en place d'un dispositif de secours ne peut avoir pour cons�quence un transfert de responsabilit� vers l'association prestataire.\n\n";

$cs[]="title1"; $ct[]="4. Prestations fournies par le prestataire";
$cs[]="title2"; $ct[]="4.1 Type du dispositif mis en place";
$cs[]="normal"; $ct[]="Pour r�pondre � la demande �crite formul�e par ".$company.", et au vu du r�sultat de la grille d��valuation des risques renseign�e en fonction des �l�ments d��valuation fournis par l�organisateur et co-sign�e (voir annexes), ".$section_prefix." ".$section_affiche.", conform�ment aux directives du R�f�rentiel National relatif aux Dispositifs Pr�visionnels de Secours (RNDPS) � Minist�re de l�int�rieur � arr�t�  NOR : INTE0600910A du 7 novembre 2006, applicables en la mati�re et opposables aux parties � la convention, et des prescriptions de l'association prestataire, s�engage � mettre en place�le  Dispositif Pr�visionnel de Secours suivant�:";   
$cs[]="bold"; $ct[]=" ".$type_evenement." : ".$tdps."";
$cs[]="title2"; $ct[]="4.2 : Composition du dispositif ";
$cs[]="normal"; $ct[]="Nombre d'intervenant secouriste : ".intval($nb_is)."".
"\nV�hicules de Premier Secours : ".intval($nb_vpsp)."".
"\nAutres v�hicules :  ".intval($nb_autres_vehicules);
$cs[]="addpage";$ct[]="";
$cs[]="setxy";  $ct[]="15,".$y_after_new_page;
$cs[]="title2"; $ct[]="4.3 : Informations concernant le dispositif ";
$cs[]="title3"; $ct[]="4.3.1 : Les intervenants ";
$cs[]="normal"; $ct[]="- Les �quipiers secouristes sont titulaires du Dipl�me de premier Secours en �quipe de niveau 2 (PSE2), valid�s dans leur aptitude op�rationnelle conform�ment � la r�glementation en vigueur et port�s sur les listes d�aptitude op�rationnelles.
- Les secouristes sont titulaires du Dipl�me de premier Secours en �quipe de niveau 1 (PSE1), valid�s dans leur aptitude op�rationnelle et port�s sur les listes d�aptitudes op�rationnelles.
- Un membre de  chaque �quipe exerce les fonctions de chef d��quipe.
- En cas de besoin des Logisticiens Administratifs et Techniques (LAT) assurent les fonctions pour lesquelles ils ont comp�tence.
- En fonction de la taille du DPS�, un ou des�chef(s) de  poste, chef(s) de section, chef(s) de secteur, chef de dispositifs, cadres op�rationnels  (est  ou sont) d�sign�(s)  par l'association prestataire.";
 $cs[]="title3"; $ct[]="4.3.2 : Moyens mat�riels ";
$cs[]="normal"; $ct[]="- Les  diff�rents lots de mat�riels mis � disposition sont conformes au RNDPS du 7 novembre 2006.";
$cs[]="normal"; $ct[]="- Les V�hicules de Premiers Secours � Personnes (V.P.S),  utilis�s  comme Postes de Secours Mobiles ou Fixes, sont  dot�s d�une cellule de soins  adapt�e et des mat�riels permettant d�assurer les premiers secours ainsi que le conditionnement d�une victime.\n";
$cs[]="title2"; $ct[]="4.4 Missions ";
$cs[]="normal"; $ct[]="Les moyens mis en place par l'association prestataire sont destin�s � assurer une pr�sence pr�ventive pendant la manifestation faisant l'objet de cette convention :";
$cs[]="bold";   $ct[]="Points d'alertes et de premiers secours :";
$cs[]="normal"; $ct[]="1� Reconna�tre et analyser la situation accidentelle,".
" 2� Prendre les premi�res mesures adapt�es de s�curit� et de protection,".
" 3� Alerter les secours publics,".
" 4� Prodiguer � la victime des gestes de premier secours r�alisables � 2 intervenants,".
" 5� Accueillir les secours et faciliter leur intervention.";
$cs[]="bold";   $ct[]="Poste de secours : ";
$cs[]="normal"; $ct[]="1� Reconna�tre et analyser la situation accidentelle,".
" 2� Prendre les premi�res mesures adapt�es de s�curit� et de protection,".
" 3� Faire un bilan et porter les premiers secours n�cessaire � une victime,".
" 4� Prodiguer des conseils adapt�s � une victime qui pourrait partir par ses propres moyens,".
" 5� Contribuer � la mise en place de la cha�ne des secours allant de l'alerte jusqu'� la prise en charge de la victime par les pouvoirs publics,".
" 6� Accueillir les secours et faciliter leur intervention";
$cs[]="bold"; $ct[]="Une �quipe de secours peut prendre en charge : ";
$cs[]="normal"; $ct[]="- Une seule victime atteinte d'une d�tresse vitale
- Un nombre de victimes sans gravit�s, �quivalent � celui des intervenants qui la composent";
$cs[]="addpage";$ct[]="";
$cs[]="setxy";  $ct[]="15,".$y_after_new_page;
$cs[]="title2"; $ct[]="4.5 Transport des victimes ";
$cs[]="normal";
if ($transport == 1)
    $ct[]="Les V�hicules de Premiers Secours � Personnes (V.P.S) de l'association prestataire, conventionn�e au titre de l'article L725-4 du Code de la S�curit� Int�rieure, peuvent, apr�s accord du m�decin r�gulateur du service d�aide m�dicale d�urgence et sous son autorit�, participer en compl�ment des secours publics, � l�acheminement des victimes vers une structure d�accueil, de soins ou un point relais.";
else
    $ct[]="L'association prestataire n'assurera pas le transport des victimes vers un centre hospitalier. Les �ventuelles �vacuations des bless�s ou malades sont assur�es par les services publics de secours";
$cs[]="title2"; $ct[]="4.6 Modalit�s op�rationnelles ";
$cs[]="normal"; $ct[]="- Les intervenants  sont rev�tus de leur tenue officielle.
- Ils interviennent sous la direction de l�encadrement mis en place par ".$section_prefix." ".$section_affiche." ".$antenne_affiche.".
- L'association est repr�sent�e op�rationnellement par ".$responsable_evt.", qui est joignable au: ".$responsable_evt_phone.", qui a proc�d� � la d�signation du chef d'�quipe (ou chef de poste, ou chef de section).
- Le chef de poste prendra contact avec le b�n�ficiaire d�s son arriv�e sur site pour v�rifier la concordance avec les clauses techniques de la convention, mettre en place le dispositif et d�terminer les modalit�s op�ratoires li�es � l��v�nement.
- Les intervenants et v�hicules sont dot�s de moyens radio sur fr�quence propres. Ces moyens peuvent constituer un r�seau qui n�cessite la mise en place de mat�riels sp�cifiques et la pr�sence d�op�rateurs radio.\n\n";
$cs[]="title1"; $ct[]="5. Engagements de l'organisateur"; 
$cs[]="title2"; $ct[]="5.1 Aspects logistique";
$cs[]="title3"; $ct[]="5.1.1 Locaux, mat�riels, moyens de communication";
$cs[]="normal"; 
if ( $moyen_installation_1 <> "" ) 
    $ct[]="L'organisateur s'engage � mettre � la disposition des �quipes de secours, afin que celles ci puissent travailler dans des conditions optimales�:"."\n".$moyen_installation_1;
else 
    $ct[]="Pas de moyens particuliers pr�vus.";
    
$cs[]="addpage";$ct[]="";
$cs[]="setxy";  $ct[]="15,".$y_after_new_page;
$cs[]="title3"; $ct[]="5.1.2 Dispositf d'alerte des secours publics";
$cs[]="normal"; $ct[]="L'organisateur s'engage � mettre � la disposition des �quipes de secours, un moyen d'appel des secours publics.";
$cs[]="title3"; $ct[]="5.1.3 Conditions de vie";
if ($repas == 1) {
    $cs[]="normal"; $ct[]="L�organisateur s�engage � fournir des repas ou paniers-repas �quilibr�s et boissons sans alcool en quantit�s suffisantes pour l�ensemble des intervenants."; }
else {
    $cs[]="normal"; $ct[]="Les repas et les boissons des secouristes pr�sents ne seront pas pris en charge par l'organisateur."; }

$cs[]="title2"; $ct[]="5.2 Modalit�s op�rationnelles";
$cs[]="title3"; $ct[]="5.2.1 Correspondant de l'organisateur";
$cs[]="normal"; $ct[]="".$contact." membre de l'organisateur, est d�sign� comme interlocuteur de l'association prestataire le jour de la manifestation.";

$cs[]="title3"; $ct[]="5.2.2 Cha�ne de commandement du DPS";
$cs[]="normal"; $ct[]="Le commandement du dispositif sera assur� par�l'association prestataire.";

if ( $interassociatif ) {
    $cs[]="title3"; $ct[]="5.2.3 Cas particulier d'un DPS Inter associatif";
    $cs[]="normal"; $ct[]="L'organisateur d�signera le coordinateur inter-associatif.";
}
$cs[]="title2"; $ct[]="5.3 Modalit�s financi�res";
$cs[]="title3"; $ct[]="5.3.1 Montant de la participation";
$a="L'intervention des secouristes demeure b�n�vole et l'action de l'association prestataire est � but non lucratif. \nToutefois, l'organisateur d�dommage l'association des frais engendr�s (d�placements, mat�riel, oxyg�ne, produits pharmaceutiques...)";
if ( $montant_devis <> "" ) $a .= ", estim�s � ".$montant_devis." ".$default_money_symbol.".";
else $a .= ", pour un montant d�fini dans le devis remis par l'association.";
$cs[]="normal";$ct[]=$a;

$cs[]="title3"; $ct[]="5.3.2 Conditions de paiement";
$cs[]="normal"; $ct[]="Cette somme sera r�gl�e par virement ou par ch�que libell� � l'ordre de�: ".$section_prefix." ".$section_affiche." ".$antenne_affiche;    

$cs[]="title1"; $ct[]="6. Engagement des deux parties";
$cs[]="title2"; $ct[]="6.1 Dur�e de la convention";
$cs[]="normal"; $ct[]="Cette convention est sign�e pour la dur�e de l'�v�nement objet de la pr�sente.";    
$cs[]="title2"; $ct[]="6.2 Condition de r�alisation";
$cs[]="normal"; $ct[]="L'engagement de l'association prestataire est li�:".
"\n- � l'acceptation de la pr�sente convention par l'organisateur.".
"\n- � l'autorisation de l'�v�nement par les pouvoirs publics.\n\n";

$cs[]="title1"; $ct[]="7. Grille d'�valution des risques";
$cs[]="normal"; $ct[]="Cette grille remplie sous la responsabilit� de l'organisateur figure en annexe de la pr�sente convention.\n\n";    

$cs[]="addpage";$ct[]="";
$cs[]="setxy";  $ct[]="15,".$y_after_new_page;
$cs[]="title1"; $ct[]="8. Clauses particuli�res";
$cs[]="normal"; $ct[]="".$clause_particuliere_1.""."\n\n".$clause_particuliere_2."\n";

$cs[]="title1"; $ct[]="9. Litiges";
$cs[]="normal"; $ct[]="En cas de litige pendant et apr�s la manifestation, � d�faut d'entente entre l'association prestataire et l'organisateur, le contentieux pourra faire l'objet de recours devant les tribunaux comp�tents.";
$cs[]="normal"; $ct[]="\n\n\n\nConvention �tablie en double exemplaires � ".$organisateur_city.", le ".date('d-m-Y');
if ( $company == "" ) $o="l'organisateur";
else $o=$company ;
$cs[]="italic"; $ct[]="\n\n\n\nPour $o".
"\n(Cachet, nom et pr�nom,fonction du signataire)"."\n\n\n\n\n\n";
$cs[]="righti"; $ct[]="Pour ".$section_prefix." ".$section_affiche.",\n".$chef."\n  "."\n  "."\n";
$k = strlen($clause_particuliere_1.""."\n\n".$clause_particuliere_2) / 20 ;
$_y = 210 +  $k;
$cs[]="image_signature";$ct[]="150,".$_y;
}

?>
