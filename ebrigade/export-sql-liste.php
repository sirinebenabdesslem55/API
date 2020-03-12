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

check_all(27);
$id=$_SESSION['id'];
$OptionsExport = "";
$OptionsExport .= "\n"."<option value=''".(($exp=="")?" selected":"").">choisissez un rapport</option>";

// check if veille op�rationnelle
$query="select count(*) as NB from groupe
        where GP_DESCRIPTION='Veille op�rationnelle'";
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
if ( $row["NB"] <> 0 ) $veille=true;
else $veille=false;

// check if personnel sante
$query="select count(*) as NB from equipe
        where EQ_NOM='Personnels de Sant�'";
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
if ( $row["NB"] <> 0 ) $personnelsante=true;
else $personnelsante=false;

// check if code conducteurs
$query="select count(1) as NB from custom_field
        where CF_ID=1 and CF_TITLE='Code conducteur'";
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
if ( $row["NB"] <> 0 ) $code_conducteur_active=true;
else $code_conducteur_active=false;

// check if ASIGCS
$query="select count(1) as NB from poste where TYPE='ASIGCS'";
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
if ( $row["NB"] <> 0 ) $asigcs=true;
else $asigcs=false;


switch ($nbsections){
case 0:
if ( $syndicate == 1 ) {

// adh�rents
$OptionsExport .= "\n<OPTGROUP LABEL=\"FA adh�rents\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"adhmodepaiement\"".(($exp=="adhmodepaiement")?" selected":"").">FA - Liste des adh�rents actifs avec le mode de paiement</option>";
$OptionsExport .= "\n"."<option value=\"adhpayantparcheque\"".(($exp=="adhpayantparcheque")?" selected":"").">FA - Liste des adh�rents payant par ch�que</option>";
$OptionsExport .= "\n"."<option value=\"adhpayantparvirement\"".(($exp=="adhpayantparvirement")?" selected":"").">FA - Liste des adh�rents payant par virement</option>";
$OptionsExport .= "\n"."<option value=\"adhpayantparprelevement\"".(($exp=="adhpayantparprelevement")?" selected":"").">FA - Liste des adh�rents payant par pr�l�vement</option>";

$OptionsExport .= "\n"."<option value=\"adhsuspendus\"".(($exp=="adhsuspendus")?" selected":"").">FA - Liste des adh�rents suspendus</option>";
$OptionsExport .= "\n"."<option value=\"adhretraites\"".(($exp=="adhretraites")?" selected":"").">FA - Liste des adh�rents retrait�s</option>";
$OptionsExport .= "\n"."<option value=\"adhactifsretraites\"".(($exp=="adhactifsretraites")?" selected":"").">FA - Liste des adh�rents non retrait�s dans les sections Retraite</option>";
$OptionsExport .= "\n"."<option value=\"1radiations\"".(($exp=="1radiations")?" selected":"").">FA - Liste des radiations d'adh�rents pour suppression identifiants site internet</option>";
$OptionsExport .= "\n"."<option value=\"1nouveauxadherents\"".(($exp=="1nouveauxadherents")?" selected":"").">FA - Liste des nouveaux adh�rents pour cr�ation identifiants site internet</option>";
$OptionsExport .= "\n"."<option value=\"nbadherentspardep\"".(($exp=="nbadherentspardep")?" selected":"").">FA - Nombre d'adh�rents par d�partement</option>";
$OptionsExport .= "\n"."<option value=\"0nbadherentspardep\"".(($exp=="0nbadherentspardep")?" selected":"").">FA - Nombre d'adh�rents par d�partement actifs � une date donn�e</option>";
$OptionsExport .= "\n"."<option value=\"nbtotaladhparprof\"".(($exp=="nbtotaladhparprof")?" selected":"").">FA - Nombre total d'adh�rents SPP et PATS</option>";
$OptionsExport .= "\n"."<option value=\"0nbtotaladhparprof\"".(($exp=="0nbtotaladhparprof")?" selected":"").">FA - Nombre total d'adh�rents SPP et PATS actifs � une date donn�e</option>";
$OptionsExport .= "\n"."<option value=\"nbadherents\"".(($exp=="nbadherents")?" selected":"").">FA - Nombre d'adh�rents par centre et par profession</option>";
$OptionsExport .= "\n"."<option value=\"adhNPAI\"".(($exp=="adhNPAI")?" selected":"").">FA - Liste des adh�rents en NPAI</option>";
$OptionsExport .= "\n"."<option value=\"cordonneesAdherents\"".(($exp=="cordonneesAdherents")?" selected":"").">FA - Coordonn�es des adh�rents non suspendus</option>";

$OptionsExport .= "\n"."<option value=\"adhdistribution\"".(($exp=="adhdistribution")?" selected":"").">FA - Liste des adh�rents pour distribution agendas - stylos</option>";
$OptionsExport .= "\n"."<option value=\"adhcarte\"".(($exp=="adhcarte")?" selected":"").">FA - Liste des adh�rents pour imprimeurs pour cartes adh�rents</option>";
$OptionsExport .= "\n"."<option value=\"1changementmail\"".(($exp=="1changementmail")?" selected":"").">FA - Liste des changements d'adresses email</option>";
$OptionsExport .= "\n"."<option value=\"1changementtel\"".(($exp=="1changementtel")?" selected":"").">FA - Liste des changements de num�ro de t�l�phone</option>";
if ( $bank_accounts == 1 and check_rights($id,29)) {
    $OptionsExport .= "\n"."<option value=\"1ribmodifie\"".(($exp=="1ribmodifie")?" selected":"").">FA - Liste des changements de coordonn�es bancaires pour adh�rents existants</option>";
}
$OptionsExport .= "\n"."<option value=\"1changementcentre\"".(($exp=="1changementcentre")?" selected":"").">FA - Liste des changements d'affectation ou de SDIS</option>";
$OptionsExport .= "\n"."<option value=\"1changementgrade\"".(($exp=="1changementgrade")?" selected":"").">FA - Liste des changements de grades</option>";
$OptionsExport .= "\n"."<option value=\"adherentsajourcotisation\"".(($exp=="adherentsajourcotisation")?" selected":"").">FA - Liste des adh�rents actifs � jour de leurs cotisations</option>";
$OptionsExport .= "\n"."<option value=\"1cotisationCheque\"".(($exp=="1cotisationCheque")?" selected":"").">FA - Liste des cotisations pay�es par ch�que entre deux dates</option>";
$OptionsExport .= "\n"."<option value=\"1cotisationVirPrev\"".(($exp=="1cotisationVirPrev")?" selected":"").">FA - Liste des cotisations pay�es par virement ou pr�l�vement entre deux dates</option>";
$OptionsExport .= "\n"."<option value=\"adressesEnvoiColis\"".(($exp=="adressesEnvoiColis")?" selected":"").">FA - Liste des adresses pour envoi colis</option>";

$OptionsExport .= "\n<OPTGROUP LABEL=\"FA REVERSEMENT\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"nombrePrelevementParDep\"".(($exp=="nombrePrelevementParDep")?" selected":"").">FA REVERSEMENT - NOMBRE D�ADHERENTS EN DATE D�AUJOURD�HUI EN PRELEVEMENT OU VIREMENT</option>";
$OptionsExport .= "\n"."<option value=\"1nombrePrelevementParDep\"".(($exp=="1nombrePrelevementParDep")?" selected":"").">FA REVERSEMENT - NOMBRE D�ADHERENTS EN PRELEVEMENT OU VIREMENT ENTRE DEUX DATES</option>";
$OptionsExport .= "\n"."<option value=\"nombrePrelevementParDeptt\"".(($exp=="nombrePrelevementParDeptt")?" selected":"").">FA REVERSEMENT - NOMBRE D�ADHERENTS EN DATE D�AUJOURD�HUI TOUS TYPES DE PAIEMENT</option>";
$OptionsExport .= "\n"."<option value=\"1rejetsetregul\"".(($exp=="1rejetsetregul")?" selected":"").">FA REVERSEMENT � REJETS ET REGUL PAR DATE</option>";
$OptionsExport .= "\n"."<option value=\"rejetsencours\"".(($exp=="rejetsencours")?" selected":"").">FA REVERSEMENT � REJETS EN COURS DE REGULARISATION</option>";
$OptionsExport .= "\n"."<option value=\"nbsuspendupardep\"".(($exp=="nbsuspendupardep")?" selected":"").">FA REVERSEMENT � NB DE SUSPENDU EN PRELEVEMENT PAR DEPARTEMENT</option>";
$OptionsExport .= "\n"."<option value=\"nomssuspendupardep\"".(($exp=="nomssuspendupardep")?" selected":"").">FA REVERSEMENT � NOM DES ADHERENTS SUSPENDUS EN PRELEVEMENT PAR DEPARTEMENT</option>";


$OptionsExport .= "\n<OPTGROUP LABEL=\"SA\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"adhtournee\"".(($exp=="adhtournee")?" selected":"").">SA - Liste des adh�rents pour tourn�es syndicales</option>";
$OptionsExport .= "\n"."<option value=\"nbadherentsparcentre\"".(($exp=="nbadherentsparcentre")?" selected":"").">SA - Nombre d'adh�rents par centre</option>";
$OptionsExport .= "\n"."<option value=\"cordonneesAdherentsparcentre\"".(($exp=="cordonneesAdherentsparcentre")?" selected":"").">SA - Coordonn�es des adh�rents par centre</option>";
$OptionsExport .= "\n"."<option value=\"cordonneesAdherentsparGTetService\"".(($exp=="cordonneesAdherentsparGTetService")?" selected":"").">SA � Coordonn�es des adh�rents par GT et Service (pour AG, Formation�)";
$OptionsExport .= "\n"."<option value=\"cordonneesAdherentsparGTetServicesansNPAI\"".(($exp=="cordonneesAdherentsparGTetServicesansNPAI")?" selected":"").">SA � Coordonn�es des adh�rents par GT et Service (pour AG, Formation�) pour les courriers sans les NPAI ";
$OptionsExport .= "\n"."<option value=\"adhtournee_off\"".(($exp=="adhtournee_off")?" selected":"").">SA 06 � Liste des adh�rents Officiers pour tourn�es syndicales</option>";
$OptionsExport .= "\n"."<option value=\"adhtournee_non_off\"".(($exp=="adhtournee_non_off")?" selected":"").">SA 06 � Liste des adh�rents non Officiers pour tourn�es syndicales</option>";
$OptionsExport .= "\n"."<option value=\"adhtournee_pats\"".(($exp=="adhtournee_pats")?" selected":"").">SA 06 � Liste des adh�rents PATS pour tourn�es syndicales</option>";

$OptionsExport .= "\n<OPTGROUP LABEL=\"FAFPT\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"1majchgtadresse\"".(($exp=="1majchgtadresse")?" selected":"").">FAFPT - Pour MAJ changement d'adresse</option>";
$OptionsExport .= "\n"."<option value=\"1majradiation\"".(($exp=="1majradiation")?" selected":"").">FAFPT - Pour MAJ radiations</option>";

$OptionsExport .= "\n<OPTGROUP LABEL=\"POUR ANDRE\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"nbadherentspardep2\"".(($exp=="nbadherentspardep2")?" selected":"").">POUR ANDRE - Nombre d'adh�rents par d�partement</option>";
$OptionsExport .= "\n"."<option value=\"1adherentsradies06\"".(($exp=="1adherentsradies06")?" selected":"").">POUR ANDRE - D�tail des radiations du SA 06</option>";
$OptionsExport .= "\n"."<option value=\"1nouveauxadherents2\"".(($exp=="1nouveauxadherents2")?" selected":"").">POUR ANDRE - Nombre de nouveaux adh�rents par d�partement</option>";
$OptionsExport .= "\n"."<option value=\"1adherentsradies2\"".(($exp=="1adherentsradies2")?" selected":"").">POUR ANDRE - Nombre de radiations par d�partement</option>";
$DD=date('Y') - 1;
$OptionsExport .= "\n"."<option value=\"adherentsradies3\"".(($exp=="adherentsradies3")?" selected":"").">POUR ANDRE - Nombre de radiations au 31/12/".$DD."</option>";
$DD=date('Y');
$OptionsExport .= "\n"."<option value=\"adherentsradies4\"".(($exp=="adherentsradies4")?" selected":"").">POUR ANDRE - Nombre de radiations au 31/12/".$DD."</option>";

$OptionsExport .= "\n<OPTGROUP LABEL=\"POUR LES PRESIDENTS\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"1nbNouveauxAdherentsParDep\"".(($exp=="1nbNouveauxAdherentsParDep")?" selected":"").">POUR LES PRESIDENTS - Nombre de nouveaux adh�rents par d�partement</option>";
$OptionsExport .= "\n"."<option value=\"1nouveauxadherentsPres\"".(($exp=="1nouveauxadherentsPres")?" selected":"").">POUR LES PRESIDENTS - Nouveaux adh�rents</option>";
$OptionsExport .= "\n"."<option value=\"1nbRadiationsAdherentsParDep\"".(($exp=="1nbRadiationsAdherentsParDep")?" selected":"").">POUR LES PRESIDENTS - Nombre de radiations par d�partement et par motif</option>";
$OptionsExport .= "\n"."<option value=\"1radiationsmotifPres\"".(($exp=="1radiationsmotifPres")?" selected":"").">POUR LES PRESIDENTS - Radiations</option>";

$OptionsExport .= "\n<OPTGROUP LABEL=\"DIVERS adh�rents\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"1verifmontants\"".(($exp=="1verifmontants")?" selected":"").">ATTESTATION - v�rification montant en fonction date adh�sion</option>";

$OptionsExport .= "\n"."<option value=\"2attestationsImpots\"".(($exp=="2attestationsImpots")?" selected":"").">ATTESTATION  - Cotisations pay�es pour une ann�e</option>";
$OptionsExport .= "\n"."<option value=\"2attestationsImpotsRejets\"".(($exp=="2attestationsImpotsRejets")?" selected":"").">ATTESTATION � Cotisations avec rejets pay�es pour une ann�e</option>";

$d=date("Y") -1;
$OptionsExport .= "\n"."<option value=\"impayesN-1\"".(($exp=="impayesN-1")?" selected":"").">ATTESTATION � Rejets ".$d." non r�gularis�s ou pr�lev�s sur ".date("Y")."</option>";
$OptionsExport .= "\n"."<option value=\"departementannuaire\"".(($exp=="departementannuaire")?" selected":"").">Annuaire des d�partements</option>";
$OptionsExport .= "\n"."<option value=\"president_syndicate\"".(($exp=="president_syndicate")?" selected":"").">Pr�sidents d�partementaux </option>";
$OptionsExport .= "\n"."<option value=\"sectionannuaire\"".(($exp=="sectionannuaire")?" selected":"").">Annuaire des centres</option>";
$OptionsExport .= "\n"."<option value=\"adresses\"".(($exp=="adresses")?" selected":"").">Liste des adresses des adh�rents</option>";
$OptionsExport .= "\n"."<option value=\"effectifadherents\"".(($exp=="effectifadherents")?" selected":"").">Liste des adh�rents</option>";
$OptionsExport .= "\n"."<option value=\"1abonnejournal\"".(($exp=="1abonnejournal")?" selected":"").">B�n�ficiaires Echos FA-FPT</option>";
$OptionsExport .= "\n"."<option value=\"1demandejournal\"".(($exp=="1demandejournal")?" selected":"").">Souhaitent recevoir Echos FA-FPT</option>";
$OptionsExport .= "\n"."<option value=\"droitBureauDE\"".(($exp=="droitBureauDE")?" selected":"").">Droits d�acc�s Bureau D�partemental par D�partement</option>";

if ( $cotisations ) {
    // cotisations
    $OptionsExport .= "\n<OPTGROUP LABEL=\"COTISATIONS adh�rents\" style=\"background-color:$background\">";
    $OptionsExport .= "\n"."<option value=\"2sommecotisations\"".(($exp=="2sommecotisations")?" selected":"").">Somme des cotisations par d�partement et profession pour l'ann�e</option>";
    $OptionsExport .= "\n"."<option value=\"montantactuel\"".(($exp=="montantactuel")?" selected":"").">Montant actuel des cotisations</option>";
    $OptionsExport .= "\n"."<option value=\"rejets\"".(($exp=="rejets")?" selected":"").">Liste des rejets des pr�l�vement</option>";
    if ( $bank_accounts == 1 and check_rights($id,29)) {
        $OptionsExport .= "\n"."<option value=\"fichierExtractionSG\"".(($exp=="fichierExtractionSG")?" selected":"").">Fichier d�extraction pour Soci�t� G�n�rale </option>";
        $OptionsExport .= "\n"."<option value=\"1fichierExtractionSG\"".(($exp=="1fichierExtractionSG")?" selected":"").">Fichier d�extraction pour Soci�t� G�n�rale selon date adh�sion</option>";
        $OptionsExport .= "\n"."<option value=\"SEPAcourrierRUM\"".(($exp=="SEPAcourrierRUM")?" selected":"").">SEPA � Liste des adh�rents pour courrier RUM</option>";
    }
    if (multi_check_rights_notes($id)) {
        $OptionsExport .= "\n<OPTGROUP LABEL=\"NOTES de frais\" style=\"background-color:$background\">";
        $OptionsExport .= "\n"."<option value=\"1note_ATTV\"".(($exp=="1note_ATTV")?" selected":"").">Notes de frais en attente de validation</option>";
        $OptionsExport .= "\n"."<option value=\"1note_ANN\"".(($exp=="1note_ANN")?" selected":"").">Notes de frais annul�es</option>";
        $OptionsExport .= "\n"."<option value=\"1note_CRE\"".(($exp=="1note_CRE")?" selected":"").">Notes de frais en cours de cr�ation</option>";
        $OptionsExport .= "\n"."<option value=\"1note_REJ\"".(($exp=="1note_REJ")?" selected":"").">Notes de frais rejet�es</option>";
        $OptionsExport .= "\n"."<option value=\"1note_VAL\"".(($exp=="note_VAL")?" selected":"").">Notes de frais valid�es</option>";
        $OptionsExport .= "\n"."<option value=\"1note_VAL2\"".(($exp=="1note_VAL2")?" selected":"").">Notes de frais valid�es 2 fois</option>";
        $OptionsExport .= "\n"."<option value=\"1note_REMB\"".(($exp=="1note_REMB")?" selected":"").">Notes de frais rembours�es</option>";
        $OptionsExport .= "\n"."<option value=\"1note_toutes\"".(($exp=="1note_toutes")?" selected":"").">Notes de frais (toutes)</option>";
    }
}

if (check_rights($id,13)) {
    $OptionsExport .= "\n<OPTGROUP LABEL=\"HORAIRES r�alis�s du personnel salari�\" style=\"background-color:$background\">";
    $OptionsExport .= "\n"."<option value=\"salarie\"".(($exp=="salarie")?" selected":"").">Liste du personnel fonctionnaire ou salari�</option>";
    $OptionsExport .= "\n"."<option value=\"horairesavalider\"".(($exp=="horairesavalider")?" selected":"").">Horaires � valider</option>";
    $OptionsExport .= "\n"."<option value=\"1horaires\"".(($exp=="1horaires")?" selected":"").">Horaires entre 2 dates (tous)</option>";
}
}
else {

// =======================================
// ASSOCIATION
// =======================================
// �v�nements
$OptionsExport .= "\n<OPTGROUP LABEL=\"�v�nements\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"1nbparticipants\"".(($exp=="1nbparticipants")?" selected":"").">Nombre de participants</option>";
$OptionsExport .= "\n"."<option value=\"1evenement_annule_liste\"".(($exp=="1evenement_annule_liste")?" selected":"").">Ev�nements Annul�s (justificatifs)</option>";
$OptionsExport .= "\n"."<option value=\"1evenement_annule\"".(($exp=="1evenement_annule")?" selected":"").">Ev�nements Annul�s par type</option>";
$OptionsExport .= "\n"."<option  value=\"1tcd_activite_annee\" ".(($exp=="1tcd_activite_annee")?" selected":"").">Ev�nements par type et par section</option>";
$OptionsExport .= "\n"."<option value=\"1renforts\"".(($exp=="1renforts")?" selected":"").">Ev�nements Renforts</option>";
$OptionsExport .= "\n"."<option value=\"1conventions\"".(($exp=="1conventions")?" selected":"").">Etat des Conventions - COA</option>";
$OptionsExport .= "\n"."<option value=\"1conventionsmanquantes\"".(($exp=="1conventionsmanquantes")?" selected":"").">Conventions manquantes - COA</option>";
$OptionsExport .= "\n"."<option value=\"1statsmanquantes\"".(($exp=="1statsmanquantes")?" selected":"").">Statistiques manquantes</option>";
$OptionsExport .= "\n"."<option value=\"1dps\"".(($exp=="1dps")?" selected":"").">DPS r�alis�s</option>";
$OptionsExport .= "\n"."<option value=\"1dpsre\"".(($exp=="1dpsre")?" selected":"").">DPS r�alis�s (renforts exclus)</option>";
$OptionsExport .= "\n"."<option value=\"1maraudes\"".(($exp=="1maraudes")?" selected":"").">Maraudes r�alis�es</option>";
$OptionsExport .= "\n"."<option value=\"1heb\"".(($exp=="1heb")?" selected":"").">H�bergements d'urgence r�alis�s</option>";
if ( $asigcs )
$OptionsExport .= "\n"."<option value=\"1asigcs\"".(($exp=="1asigcs")?" selected":"").">Actions de Sensibilisation Initiation aux Gestes et Comportements qui Sauvent </option>";
$OptionsExport .= "\n"."<option value=\"1accueilRefugies\"".(($exp=="1accueilRefugies")?" selected":"").">Accueils des r�fugi�s</option>";
$OptionsExport .= "\n"."<option value=\"1ogripa\"".(($exp=="1ogripa")?" selected":"").">Grippe A - divers</option>";
$OptionsExport .= "\n"."<option value=\"1vacci\"".(($exp=="1vacci")?" selected":"").">Grippe A - vaccination</option>";
$OptionsExport .= "\n"."<option value=\"1horairesdouteux\"".(($exp=="1horairesdouteux")?" selected":"").">Horaires douteux � corriger</option>";
$OptionsExport .= "\n"."<option value=\"1datecre\"".(($exp=="1datecre")?" selected":"").">Dates de cr�ation des �v�nements</option>";
$OptionsExport .= "\n"."<option value=\"1promocom\"".(($exp=="1promocom")?" selected":"").">Ev�nements Promotion - Communication</option>";
$OptionsExport .= "\n"."<option value=\"1horsdep\"".(($exp=="1horsdep")?" selected":"").">Ev�nements hors d�partement</option>";
$OptionsExport .= "\n"."<option value=\"1Tevtpardep\"".(($exp=="1Tevtpardep")?" selected":"").">Nombre �v�nements par d�partement - type au choix</option>";

// formations
$OptionsExport .= "\n<OPTGROUP LABEL=\"formations\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"1formations\"".(($exp=="1formations")?" selected":"").">Formations r�alis�es</option>";
$OptionsExport .= "\n"."<option value=\"1formations_sd\"".(($exp=="1formations_sd")?" selected":"").">Formations: nombres de stagiaires et de valid�s</option>";
$OptionsExport .= "\n"."<option value=\"1formationsnontraitees\"".(($exp=="1formationsnontraitees")?" selected":"").">Formations non trait�es</option>";
$OptionsExport .= "\n"."<option value=\"1sst\"".(($exp=="1sst")?" selected":"").">Formations SST r�alis�es</option>";
$OptionsExport .= "\n"."<option value=\"1gqs\"".(($exp=="1gqs")?" selected":"").">Formations GQS r�alis�es</option>";
$OptionsExport .= "\n"."<option value=\"1formationsCE\"".(($exp=="1formationsCE")?" selected":"").">Formations chef d'�quipe ou chef de poste r�alis�es</option>";
$OptionsExport .= "\n"."<option value=\"sstexpiration\"".(($exp=="sstexpiration")?" selected":"").">Expiration des Dipl�mes SST</option>";

if(check_rights($id, 29)){ // autoriser seulement au personnes avec la comp�tence 29 : comptabilit�
$OptionsExport .= "\n<OPTGROUP LABEL=\"facturation\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"1facturation\"".(($exp=="1facturation")?" selected":"").">Suivi commercial</option>";
$OptionsExport .= "\n"."<option value=\"1facturationRecap\"".(($exp=="1facturationRecap")?" selected":"").">D�tail du suivi commercial</option>";
$OptionsExport .= "\n"."<option value=\"fafacturer\"".(($exp=="fafacturer")?" selected":"").">Ev�nements termin�s a facturer</option>";
$OptionsExport .= "\n"."<option value=\"1tnonpaye\"".(($exp=="1tnonpaye")?" selected":"").">Evenements termin�s non pay�s</option>";
$OptionsExport .= "\n"."<option value=\"1fnonpaye\"".(($exp=="1fnonpaye")?" selected":"").">Ev�nements factur�s non pay�s</option>";
$OptionsExport .= "\n"."<option value=\"1paye\"".(($exp=="1paye")?" selected":"").">Ev�nements pay�s</option>";
$OptionsExport .= "\n"."<option value=\"1facturestoutes\"".(($exp=="1facturestoutes")?" selected":"").">Listes des factures</option>";
$OptionsExport .= "\n"."<option value=\"1cadps\"".(($exp=="1cadps")?" selected":"").">Chiffre d'affaire DPS</option>";
$OptionsExport .= "\n"."<option value=\"1cadps_sansR\"".(($exp=="1cadps_sansR")?" selected":"").">Chiffre d'affaire DPS hors renforts</option>";
$OptionsExport .= "\n"."<option value=\"1cafor\"".(($exp=="1cafor")?" selected":"").">Chiffre d'affaire Formations</option>";
$OptionsExport .= "\n"."<option value=\"1facturepayeedps\"".(($exp=="1facturepayeedps")?" selected":"").">Factures de DPS pay�es</option>";
$OptionsExport .= "\n"."<option value=\"1facturepayeefor\"".(($exp=="1facturepayeefor")?" selected":"").">Factures de Formations pay�es</option>";
}

// v�hicules / mat�riel 
$OptionsExport .= "\n<OPTGROUP LABEL=\"v�hicules / mat�riel\" style=\"background-color:$background\">";
if ( $code_conducteur_active ) $OptionsExport .= "\n"."<option value=\"code_conducteur\"".(($exp=="code_conducteur")?" selected":"").">Codes conducteurs</option>";
if ( $vehicules == 1 ) $OptionsExport .= "\n"."<option value=\"vehicule\"".(($exp=="vehicule")?" selected":"").">Liste des v�hicules</option>";
if ( $vehicules == 1 ) $OptionsExport .= "\n"."<option value=\"1vehicule_km\"".(($exp=="1vehicule_km")?" selected":"").">Kilom�trage r�alis� par v�hicule (bilan)</option>";
if ( $vehicules == 1 ) $OptionsExport .= "\n"."<option value=\"1associat_km\"".(($exp=="1associat_km")?" selected":"").">Kilom�trage r�alis�s par les v�hicules (d�tail)</option>";
$OptionsExport .= "\n"."<option value=\"1perso_km\"".(($exp=="1perso_km")?" selected":"").">Kilom�trage d�taill� en v�hicule personnel</option>";
$OptionsExport .= "\n"."<option value=\"1perso_km_total\"".(($exp=="1perso_km_total")?" selected":"").">Kilom�trage total en avec v�hicule personnel</option>";
if ( $vehicules == 1 ) $OptionsExport .= "\n"."<option value=\"1missing_km\"".(($exp=="1missing_km")?" selected":"").">Kilom�trage non renseign�s</option>";
if ( $vehicules == 1 ) $OptionsExport .= "\n"."<option value=\"1evenement_km\"".(($exp=="1evenement_km")?" selected":"").">Kilom�trage par type d'�v�nement</option>";
if ( $vehicules == 1 ) $OptionsExport .= "\n"."<option value=\"vehicule_a_dispo\"".(($exp=="vehicule_a_dispo")?" selected":"").">V�hicules mis � disposition</option>";
if ( $materiel == 1 ) $OptionsExport .= "\n"."<option value=\"materiel_a_dispo\"".(($exp=="materiel_a_dispo")?" selected":"").">Mat�riel mis � disposition</option>";
if ( $consommables == 1 ) $OptionsExport .= "\n"."<option value=\"1consommation_produits\"".(($exp=="1consommation_produits")?" selected":"").">Consommation de produits</option>";
if ( $consommables == 1 ) $OptionsExport .= "\n"."<option value=\"stock_consommables\"".(($exp=="stock_consommables")?" selected":"").">Stock de produits consommables</option>";
if ( $materiel == 1 ) $OptionsExport .= "\n"."<option value=\"tenues_personnel\"".(($exp=="tenues_personnel")?" selected":"").">Tenues du personnel</option>";

// personnel
$OptionsExport .= "\n<OPTGROUP LABEL=\"personnel\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"nbadherentspardep\"".(($exp=="nbadherentspardep")?" selected":"").">Nombre de personnel b�n�voles et salari�s par d�partement</option>";
$OptionsExport .= "\n"."<option value=\"effectif\"".(($exp=="effectif")?" selected":"").">Liste du personnel</option>";
$OptionsExport .= "\n"."<option value=\"salarie\"".(($exp=="salarie")?" selected":"").">Liste du personnel salari�</option>";
$OptionsExport .= "\n"."<option value=\"1civique\"".(($exp=="1civique")?" selected":"").">Liste du personnel en service civique par date</option>";
$OptionsExport .= "\n"."<option value=\"1snu\"".(($exp=="1snu")?" selected":"").">Liste du personnel en service national universel par date</option>";
$OptionsExport .= "\n"."<option value=\"chiens\"".(($exp=="chiens")?" selected":"").">Chiens de recherche avec comp�tences valides</option>";
$OptionsExport .= "\n"."<option value=\"creationfiches\"".(($exp=="creationfiches")?" selected":"").">Cr�ation des fiches personnel</option>";
$OptionsExport .= "\n"."<option value=\"provenantautres\"".(($exp=="provenantautres")?" selected":"").">Personnel ayant chang� de section</option>";
$OptionsExport .= "\n"."<option value=\"adresses\"".(($exp=="adresses")?" selected":"").">Liste des adresses du personnel</option>";
$OptionsExport .= "\n"."<option value=\"1anniversaires\" ".(($exp=="1anniversaires")?" selected":"").">Anniversaires des membres</option>";
$OptionsExport .= "\n"."<option value=\"1heuressections\"".(($exp=="1heuressections")?" selected":"").">Heures r�alis�es / section</option>";
$OptionsExport .= "\n"."<option value=\"1absences\"".(($exp=="1absences")?" selected":"").">Absences sur les �v�nements </option>";
$OptionsExport .= "\n"."<option value=\"1nombreabsences\"".(($exp=="1nombreabsences")?" selected":"").">Nombre d'absences / personne</option>";
$OptionsExport .= "\n"."<option value=\"1anciens\"".(($exp=="1anciens")?" selected":"").">Anciens membres avec date de sortie</option>";
$OptionsExport .= "\n"."<option value=\"engagement\"".(($exp=="engagement")?" selected":"").">Ann�es d'engagement du personnel </option>";

$OptionsExport .= "\n"."<option value=\"1inactif2\"".(($exp=="1inactif2")?" selected":"").">Personnel inactif</option>";
$OptionsExport .= "\n"."<option value=\"skype\"".(($exp=="skype")?" selected":"").">Identifiants de contact Skype </option>";
$OptionsExport .= "\n"."<option value=\"zello\"".(($exp=="zello")?" selected":"").">Identifiants de contact Zello </option>";
$OptionsExport .= "\n"."<option value=\"whatsapp\"".(($exp=="whatsapp")?" selected":"").">Identifiants de contact Whatsapp </option>";
$OptionsExport .= "\n"."<option value=\"typeemail\"".(($exp=="typeemail")?" selected":"").">R�partition par type d'email</option>";
$OptionsExport .= "\n"."<option value=\"sans2emeprenom\"".(($exp=="sans2emeprenom")?" selected":"").">Personnel actif sans deuxi�me pr�nom renseign�</option>";
$OptionsExport .= "\n"."<option value=\"sansdatenaissance\"".(($exp=="sansdatenaissance")?" selected":"").">Personnel actif sans date de naissance renseign�e</option>";
$OptionsExport .= "\n"."<option value=\"sanslieunaissance\"".(($exp=="sanslieunaissance")?" selected":"").">Personnel actif sans lieu de naissance renseign�</option>";
$OptionsExport .= "\n"."<option value=\"sansphoto\"".(($exp=="sansphoto")?" selected":"").">Personnel actif sans photo d'identit�</option>";
$OptionsExport .= "\n"."<option value=\"sansemail\"".(($exp=="sansemail")?" selected":"").">Personnel sans email valide</option>";
$OptionsExport .= "\n"."<option value=\"sansadresse\"".(($exp=="sansadresse")?" selected":"").">Personnel sans adresse valide</option>";
$OptionsExport .= "\n"."<option value=\"sanstel\"".(($exp=="sanstel")?" selected":"").">Personnel sans num�ro de t�l�phone valide</option>";
$OptionsExport .= "\n"."<option value=\"1perso_km\"".(($exp=="1perso_km")?" selected":"").">Kilom�trage d�taill� en v�hicule personnel</option>";
$OptionsExport .= "\n"."<option value=\"1perso_km_total\"".(($exp=="1perso_km_total")?" selected":"").">Kilom�trage total en v�hicule personnel</option>";
$OptionsExport .= "\n"."<option value=\"homonymes\"".(($exp=="homonymes")?" selected":"").">Liste des homonymes (nom, pr�nom)</option>";
$OptionsExport .= "\n"."<option value=\"doublons\"".(($exp=="doublons")?" selected":"").">Liste des fiches personnel en double (nom,pr�nom,date de naissance)</option>";
$OptionsExport .= "\n"."<option value=\"doubleaffect\"".(($exp=="doubleaffect")?" selected":"").">Liste personnes avec plusieurs affectations</option>";
if ( $licences )
$OptionsExport .= "\n"."<option value=\"doublonlicence\"".(($exp=="doublonlicence")?" selected":"").">Liste des num�ros de licences affect�s � plusieurs fiches actives</option>";

// participations
$OptionsExport .= "\n<OPTGROUP LABEL=\"participations du personnel\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"1heurespersonne\"".(($exp=="1heurespersonne")?" selected":"").">Participations / personne</option>";
$OptionsExport .= "\n"."<option value=\"1heurespersonnetous\"".(($exp=="1heurespersonnetous")?" selected":"").">Participations / personne (avec les externes)</option>";
$OptionsExport .= "\n"."<option value=\"1participations\"".(($exp=="1participations")?" selected":"").">Nombre de participations sur la p�riode</option>";
$OptionsExport .= "\n"."<option value=\"1participationsformateurs\"".(($exp=="1participationsformateurs")?" selected":"").">Participations des formateurs</option>";
$OptionsExport .= "\n"."<option value=\"1participationsadresses\"".(($exp=="1participationsadresses")?" selected":"").">Adresses du personnel ayant particip�</option>";
$OptionsExport .= "\n"."<option value=\"1participationssalaries\"".(($exp=="1participationssalaries")?" selected":"").">Participations des salari�s</option>";
$OptionsExport .= "\n"."<option value=\"1participationsprompcom\"".(($exp=="1participationsprompcom")?" selected":"").">Participations aux Ev�nements Promotion - Communication</option>";
$OptionsExport .= "\n"."<option value=\"1participationsnautique\"".(($exp=="1participationsnautique")?" selected":"").">Participations aux Ev�nements Activit� nautique</option>";
$OptionsExport .= "\n"."<option value=\"tempsconnexion\"".(($exp=="tempsconnexion")?" selected":"").">Temps de connexion ".$application_title." par personne</option>";
$OptionsExport .= "\n"."<option value=\"tempconnexionparsection\"".(($exp=="tempconnexionparsection")?" selected":"").">Temps de connexion ".$application_title." par d�partement</option>";
$OptionsExport .= "\n"."<option value=\"1participationsannules\"".(($exp=="1participationsannules")?" selected":"").">Participations aux Ev�nements Annul�s</option>";
$OptionsExport .= "\n"."<option value=\"1participationsparjour\"".(($exp=="1participationsparjour")?" selected":"").">Nombre de participations par jour des b�n�voles</option>";
$OptionsExport .= "\n"."<option value=\"1heurespersonneSNU\"".(($exp=="1heurespersonneSNU")?" selected":"").">Participations du personnel Service National Universel</option>";


// personnel externe
if(check_rights($id, 37) and $externes == 1){ // autoriser seulement au personnes avec la comp�tence 37, gestion des externes
$OptionsExport .= "\n<OPTGROUP LABEL=\"personnel externe\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"adressesext\"".(($exp=="adressesext")?" selected":"").">Liste des adresses des externes</option>";
$OptionsExport .= "\n"."<option value=\"1participationsext\"".(($exp=="1participationsext")?" selected":"").">Participations des externes par dates</option>";
$OptionsExport .= "\n"."<option value=\"1heurespersonneexternes\"".(($exp=="1heurespersonneexternes")?" selected":"").">Participations / personne externe</option>";
$OptionsExport .= "\n"."<option value=\"1participationsadressesext\"".(($exp=="1participationsadressesext")?" selected":"").">Adresses des externes ayant particip� entre deux dates</option>";
}

// permissions
$OptionsExport .= "\n<OPTGROUP LABEL=\"permissions\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"groupes\"".(($exp=="groupes")?" selected":"").">Permissions du personnel</option>";
$OptionsExport .= "\n"."<option value=\"roles\"".(($exp=="roles")?" selected":"").">R�les dans l'organigramme du personnel</option>";

// comp�tences
$OptionsExport .= "\n<OPTGROUP LABEL=\"secourisme\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"secouristesPSE\"".(($exp=="secouristesPSE")?" selected":"").">Liste des secouristes PSE1 ou PSE2</option>";
$OptionsExport .= "\n"."<option value=\"secouristesparsection\"".(($exp=="secouristesparsection")?" selected":"").">Nombre de secouristes PSE2 ou PSE1 seulement</option>";
$OptionsExport .= "\n"."<option value=\"secouristesPSE1\"".(($exp=="secouristesPSE1")?" selected":"").">Liste des secouristes seulement PSE1</option>";
$OptionsExport .= "\n"."<option value=\"moniteurs\"".(($exp=="moniteurs")?" selected":"").">Liste des moniteurs de secourisme</option>";
$OptionsExport .= "\n"."<option value=\"moniteursPSC\"".(($exp=="moniteursPSC")?" selected":"").">Liste des moniteurs seulement PSC</option>";
$OptionsExport .= "\n"."<option value=\"moniteursparsection\"".(($exp=="moniteursparsection")?" selected":"").">Nombre de moniteurs de secourisme</option>";
if ($personnelsante)
$OptionsExport .= "\n"."<option value=\"personnelsante\"".(($exp=="personnelsante")?" selected":"").">Liste du personnel de sant�</option>";
$OptionsExport .= "\n"."<option value=\"competence_expire\"".(($exp=="competence_expire")?" selected":"").">Comp�tences expir�es</option>";

// dipl�mes 
$OptionsExport .= "\n<OPTGROUP LABEL=\"dipl�mes\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"diplomesPSC1\"".(($exp=="diplomesPSC1")?" selected":"").">Liste des dipl�mes PSC1</option>";
$OptionsExport .= "\n"."<option value=\"1diplomesPSC1\"".(($exp=="1diplomesPSC1")?" selected":"").">Liste des dipl�mes PSC1 par dates</option>";
$OptionsExport .= "\n"."<option value=\"diplomesPSE1\"".(($exp=="diplomesPSE1")?" selected":"").">Liste des dipl�mes PSE1</option>";
$OptionsExport .= "\n"."<option value=\"diplomesPSE2\"".(($exp=="diplomesPSE2")?" selected":"").">Liste des dipl�mes PSE2</option>";

// sections
$OptionsExport .= "\n<OPTGROUP LABEL=\"sections\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"sectionannuaire\"".(($exp=="sectionannuaire")?" selected":"").">Annuaire des sections</option>";
$OptionsExport .= "\n"."<option value=\"depannuaire\"".(($exp=="depannuaire")?" selected":"").">Annuaire des d�partements</option>";
$OptionsExport .= "\n"."<option value=\"IDRadio\"".(($exp=="IDRadio")?" selected":"").">Codes ID Radio des d�partements et antennes</option>";
$OptionsExport .= "\n"."<option value=\"agrements\"".(($exp=="agrements")?" selected":"").">Liste des agr�ments</option>";
$OptionsExport .= "\n"."<option value=\"agrements_dps\"".(($exp=="agrements_dps")?" selected":"").">Liste des agr�ments DPS</option>";
$OptionsExport .= "\n"."<option value=\"SMSsections\"".(($exp=="SMSsections")?" selected":"").">Comptes SMS</option>";
$OptionsExport .= "\n"."<option value=\"1updateorganigramme\"".(($exp=="1updateorganigramme")?" selected":"").">Nouveaux �lus d�partementaux</option>";
$OptionsExport .= "\n"."<option value=\"1interdictions\"".(($exp=="1interdictions")?" selected":"").">Interdictions de cr�er certains �v�nements</option>";

// entreprises clientes
if(check_rights($id, 37) and $externes == 1){
$OptionsExport .= "\n<OPTGROUP LABEL=\"entreprises\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"entreprisesannuaire\"".(($exp=="entreprisesannuaire")?" selected":"").">Annuaire des entreprises</option>";
$OptionsExport .= "\n"."<option value=\"medecinsreferents\"".(($exp=="medecinsreferents")?" selected":"").">M�decins r�f�rents</option>";
$OptionsExport .= "\n"."<option value=\"1entreprisesDPS\"".(($exp=="1entreprisesDPS")?" selected":"").">Entreprises DPS</option>";
$OptionsExport .= "\n"."<option value=\"1entreprisesFOR\"".(($exp=="1entreprisesFOR")?" selected":"").">Entreprises Formations</option>";
}
// bilans
$OptionsExport .= "\n<OPTGROUP LABEL=\"bilans\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"1dps\"".(($exp=="1dps")?" selected":"").">DPS r�alis�s</option>";
$OptionsExport .= "\n"."<option value=\"1dpsre\"".(($exp=="1dpsre")?" selected":"").">DPS r�alis�s (hors renforts)</option>";
$OptionsExport .= "\n"."<option value=\"1garde\"".(($exp=="1garde")?" selected":"").">Gardes r�alis�es</option>";
$OptionsExport .= "\n"."<option value=\"1gardere\"".(($exp=="1gardere")?" selected":"").">Gardes r�alis�es (hors renforts)</option>";
$OptionsExport .= "\n"."<option value=\"1ah\"".(($exp=="1ah")?" selected":"").">Bilan actions humanitaires</option>";
$OptionsExport .= "\n"."<option value=\"1soutienpopulations\"".(($exp=="1soutienpopulations")?" selected":"").">Bilan aide aux populations</option>";
$OptionsExport .= "\n"."<option value=\"1heuresparticipations\"".(($exp=="1heuresparticipations")?" selected":"").">Bilan participations tous �v�nements</option>";
$OptionsExport .= "\n"."<option value=\"1heuresparticipationspartype\"".(($exp=="1heuresparticipationspartype")?" selected":"").">Bilan heures participations par type d'�v�nement</option>";


if ( $cotisations and (check_rights($id, 53)) ) {
// cotisations
$OptionsExport .= "\n<OPTGROUP LABEL=\"cotisations adh�rents\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"2cotisationsPayees\"".(($exp=="2cotisationsPayees")?" selected":"").">Cotisations pay�es pour une ann�e</option>";
$OptionsExport .= "\n"."<option value=\"montantactuel\"".(($exp=="montantactuel")?" selected":"").">Montant actuel des cotisations</option>";
$OptionsExport .= "\n"."<option value=\"cotisationspayees\"".(($exp=="cotisationspayees")?" selected":"").">Cotisations pay�es par d�partement pour ".date('Y')."</option>";
$OptionsExport .= "\n"."<option value=\"cotisationspayeesparpers\"".(($exp=="cotisationspayeesparpers")?" selected":"").">Cotisations pay�es par personne pour ".date('Y')."</option>";
$OptionsExport .= "\n"."<option value=\"1cotisationspayees\"".(($exp=="1cotisationspayees")?" selected":"").">Cotisations pay�es entre deux dates</option>";
$OptionsExport .= "\n"."<option value=\"2cotisationsimpayees\"".(($exp=="2cotisationsimpayees")?" selected":"").">Cotisations non pay�es pour l'ann�e</option>";
}

if ( check_rights($id, 15)) {
// interventions et victimes
$OptionsExport .= "\n<OPTGROUP LABEL=\"interventions / victimes (main courante)\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"1intervictime\"".(($exp=="1intervictime")?" selected":"").">Nombre d'interventions par jour</option>";
$OptionsExport .= "\n"."<option value=\"1intervictimeparevt\"".(($exp=="1intervictimeparevt")?" selected":"").">Nombre d'interventions par �v�nement</option>";

$OptionsExport .= "\n"."<option value=\"1victimenationalite\"".(($exp=="1victimenationalite")?" selected":"").">Nombre de personnes prises en charge par nationalit�</option>";
$OptionsExport .= "\n"."<option value=\"1victimeage\"".(($exp=="1victimeage")?" selected":"").">Nombre de personnes prises en charge par �ge</option>";
$OptionsExport .= "\n"."<option value=\"1victimesexe\"".(($exp=="1victimesexe")?" selected":"").">Nombre de personnes prises en charge par sexe</option>";
$OptionsExport .= "\n"."<option value=\"1statdetailvictime\"".(($exp=="1statdetailvictime")?" selected":"").">Statistiques personnes prises en charge et actions r�alis�es par jour</option>";
$OptionsExport .= "\n"."<option value=\"1statdetailvictimeparevt\"".(($exp=="1statdetailvictimeparevt")?" selected":"").">Statistiques personnes prises en charge et actions r�alis�es par �v�nement</option>";
$OptionsExport .= "\n"."<option value=\"1transportdest\"".(($exp=="1transportdest")?" selected":"").">Nombre de Transports de victimes selon destination</option>";
$OptionsExport .= "\n"."<option value=\"1transportpar\"".(($exp=="1transportpar")?" selected":"").">Nombre de Transports de victimes selon transporteur</option>";
$OptionsExport .= "\n"."<option value=\"1listevictime\"".(($exp=="1listevictime")?" selected":"").">Liste des personnes prises en charge</option>";
$OptionsExport .= "\n"."<option value=\"1listevictimeCAV\"".(($exp=="1listevictimeCAV")?" selected":"").">Liste des Victimes au Centre d'Accueil</option>";
}

// veille op�rationnelle
$OptionsExport .= "\n<OPTGROUP LABEL=\"veille op�rationnelle\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"pointdujour\"".(($exp=="pointdujour")?" selected":"").">Point de situation du jour</option>";
$OptionsExport .= "\n"."<option value=\"1activite\"".(($exp=="1activite")?" selected":"").">Point de situation par date</option>";
$OptionsExport .= "\n"."<option value=\"maincourantejour\"".(($exp=="maincourantejour")?" selected":"").">Rapports d'interventions renseign�s ce jour</option>";
$OptionsExport .= "\n"."<option value=\"maincourantehier\"".(($exp=="maincourantehier")?" selected":"").">Rapports d'interventions renseign�s hier</option>";
$OptionsExport .= "\n"."<option value=\"compterendujour\"".(($exp=="compterendujour")?" selected":"").">Rapports de comptes rendus renseign�s ce jour</option>";
$OptionsExport .= "\n"."<option value=\"compterenduhier\"".(($exp=="compterenduhier")?" selected":"").">Rapports de comptes rendus renseign�s hier</option>";
$OptionsExport .= "\n"."<option value=\"personneldisponiblea\"".(($exp=="personneldisponiblea")?" selected":"").">Personnel disponible aujourd'hui</option>";
$OptionsExport .= "\n"."<option value=\"personneldisponibled\"".(($exp=="personneldisponibled")?" selected":"").">Personnel disponible demain</option>";
if ( $veille ) {
    $OptionsExport .= "\n"."<option value=\"veille\"".(($exp=="veille")?" selected":"").">Personnel de veille op�rationnelle </option>";
    $OptionsExport .= "\n"."<option value=\"presidents\"".(($exp=="presidents")?" selected":"").">Pr�sidents d�partementaux </option>";
    $OptionsExport .= "\n"."<option value=\"responsablesformations\"".(($exp=="responsablesformations")?" selected":"").">Directeur des Formations d�partementaux </option>";
    $OptionsExport .= "\n"."<option value=\"responsablesoperationnels\"".(($exp=="responsablesoperationnels")?" selected":"").">Directeur des Op�rations d�partementaux </option>";
 }

if ( $cotisations == 1 and multi_check_rights_notes($id)) {
    $OptionsExport .= "\n<OPTGROUP LABEL=\"notes de frais\" style=\"background-color:$background\">";
    $OptionsExport .= "\n"."<option value=\"1note_ATTV\"".(($exp=="1note_ATTV")?" selected":"").">Notes de frais en attente de validation</option>";
    $OptionsExport .= "\n"."<option value=\"1note_ANN\"".(($exp=="1note_ANN")?" selected":"").">Notes de frais annul�es</option>";
    $OptionsExport .= "\n"."<option value=\"1note_REF\"".(($exp=="1note_REF")?" selected":"").">Notes de frais refus�es</option>";
    $OptionsExport .= "\n"."<option value=\"1note_VAL\"".(($exp=="1note_VAL")?" selected":"").">Notes de frais valid�es</option>";
    $OptionsExport .= "\n"."<option value=\"1note_VAL2\"".(($exp=="1note_VAL2")?" selected":"").">Notes de frais valid�es deux fois</option>";
    $OptionsExport .= "\n"."<option value=\"1note_REMB\"".(($exp=="1note_REMB")?" selected":"").">Notes de frais rembours�es (ou dons � l'association)</option>";
    $OptionsExport .= "\n"."<option value=\"1note_toutes\"".(($exp=="1note_toutes")?" selected":"").">Notes de frais (toutes)</option>";
    if ( multi_check_rights_notes($id) ) {
        $OptionsExport .= "\n<OPTGROUP LABEL=\"notes de frais niveau national\" style=\"background-color:$background\">";
        $OptionsExport .= "\n"."<option value=\"1notN_ATTV\"".(($exp=="1notN_ATTV")?" selected":"").">Notes de frais nationales en attente de validation</option>";
        $OptionsExport .= "\n"."<option value=\"1notN_ANN\"".(($exp=="1notN_ANN")?" selected":"").">Notes de frais nationales annul�es</option>";
        $OptionsExport .= "\n"."<option value=\"1notN_REF\"".(($exp=="1notN_REF")?" selected":"").">Notes de frais nationales refus�es</option>";
        $OptionsExport .= "\n"."<option value=\"1notN_VAL\"".(($exp=="1notN_VAL")?" selected":"").">Notes de frais nationales valid�es</option>";
        $OptionsExport .= "\n"."<option value=\"1notN_VAL2\"".(($exp=="1notN_VAL2")?" selected":"").">Notes de frais nationales valid�es deux fois</option>";
        $OptionsExport .= "\n"."<option value=\"1notN_REMB\"".(($exp=="1notN_REMB")?" selected":"").">Notes de frais nationales rembours�es</option>";
        $OptionsExport .= "\n"."<option value=\"1notN_toutes\"".(($exp=="1notN_toutes")?" selected":"").">Notes de frais nationales (toutes)</option>";
    }
}
if (check_rights($id,13)) {
    $OptionsExport .= "\n<OPTGROUP LABEL=\"horaires r�alis�s du personnel salari�\" style=\"background-color:$background\">";
    $OptionsExport .= "\n"."<option value=\"salarie\"".(($exp=="salarie")?" selected":"").">Liste du personnel salari�</option>";
    $OptionsExport .= "\n"."<option value=\"horairesavalider\"".(($exp=="horairesavalider")?" selected":"").">Horaires � valider</option>";
    $OptionsExport .= "\n"."<option value=\"1horaires\"".(($exp=="1horaires")?" selected":"").">Horaires entre 2 dates (tous)</option>";
}

// COMPETENCES
$OptionsExport .= "\n<OPTGROUP LABEL=\"Comp�tences du personnel\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"competencesope\"".(($exp=="competencesope")?" selected":"").">Comp�tences op�rationnelles</option>";
$OptionsExport .= "\n"."<option value=\"competencesfor\"".(($exp=="competencesfor")?" selected":"").">Comp�tences formation</option>";

if ( $webservice_key <> '' and check_rights($id,9)) {
$OptionsExport .= "\n<OPTGROUP LABEL=\"Acc�s Webservice\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"1soapcallsj\"".(($exp=="1soapcallsj")?" selected":"").">Nombre appels Webservice par jour</option>";
$OptionsExport .= "\n"."<option value=\"1soaperrorsj\"".(($exp=="1soaperrorsj")?" selected":"").">Nombre erreurs appels Webservice par jour</option>";
$OptionsExport .= "\n"."<option value=\"1soapcalls\"".(($exp=="1soapcalls")?" selected":"").">Acc�s Webservice</option>";
$OptionsExport .= "\n"."<option value=\"1soaperrors\"".(($exp=="1soaperrors")?" selected":"").">Erreurs Webservice</option>";
}

}

break;
default:

// =======================================
// POMPIERS
// =======================================

// personnel
$OptionsExport .= "\n<OPTGROUP LABEL=\"personnel\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"effectif\"".(($exp=="effectif")?" selected":"").">Liste du personnel</option>";
$OptionsExport .= "\n"."<option value=\"adresses\"".(($exp=="adresses")?" selected":"").">Liste des adresses du personnel</option>";
$OptionsExport .= "\n"."<option value=\"typeemail\"".(($exp=="typeemail")?" selected":"").">R�partition par type d'email</option>";
$OptionsExport .= "\n<OPTGROUP LABEL=\"�v�nements\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"1activite\"".(($exp=="1activite")?" selected":"").">Ev�nements - participants</option>";
$OptionsExport .= "\n"."<option value=\"1nbparticipants\"".(($exp=="1nbparticipants")?" selected":"").">Nombre de participants</option>";
$OptionsExport .= "\n<OPTGROUP LABEL=\"participations du personnel\" style=\"background-color:$background\">";
$OptionsExport .= "\n"."<option value=\"1heurespersonneforco\"".(($exp=="1heurespersonneforco")?" selected":"").">Maintien des acquis / personne (tous)</option>";
$OptionsExport .= "\n"."<option value=\"1heurespersonne\"".(($exp=="1heurespersonne")?" selected":"").">Participations / personne</option>";
$OptionsExport .= "\n"."<option value=\"1participations\"".(($exp=="1participations")?" selected":"").">Nombre de participations sur la p�riode</option>";
$OptionsExport .= "\n"."<option value=\"1absences\"".(($exp=="1absences")?" selected":"").">Absences sur les �v�nements </option>";
$OptionsExport .= "\n"."<option value=\"1nombreabsences\"".(($exp=="1nombreabsences")?" selected":"").">Nombre d'absences / personne</option>";
$OptionsExport .= "\n<OPTGROUP LABEL=\"v�hicules / mat�riel\" style=\"background-color:$background\">";
if ( $vehicules == 1 ) $OptionsExport .= "\n"."<option value=\"vehicule\"".(($exp=="vehicule")?" selected":"").">Liste des v�hicules</option>";
}

?>
