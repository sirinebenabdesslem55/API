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

 
ini_set('log_errors','1'); 
ini_set('display_errors','0');
require_once ("lib/nusoap/src/nusoap.php");
require_once ("config.php");
require_once ("fonctions.php");
$dbc=connect();

if ( $webservice_key == "" ) {
    writehead();
    write_msgbox("Attention",$error_pic,"Les webservices ne sont pas activés.",30,30);
    writefoot();
    exit;
}

// ====================================================================
// function 
// ====================================================================

function get_webservice_section($key) {
    global $dbc;
    $query="select S_ID from section where WEBSERVICE_KEY = '".secure_input($dbc,$key)."'";
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    return intval($row[0]);
}

function log_soap($service, $param, $ret, $message) {
    global $dbc;
    $query="insert into log_soap (LS_DATE,LS_SERVICE,LS_PARAM,LS_RET,LS_MESSAGE)
            values(NOW(),\"".$service."\",\"".$param."\",".$ret.",\"".$message."\")";
    $result=mysqli_query($dbc,$query);
}

// ====================================================================
// Define Webservice
// ====================================================================

$server = new soap_server();
$server->configureWSDL("ebrigadeWSDL", "urn:ebrigadeWSDL");

// ====================================================================
// getUserInfo
// INPUT:  key,login,password
// OUTPUT: user info
// ====================================================================

function getUserInfo($key, $login, $password) {
    global $dbc,$webservice_key, $password_failure, $passwordblocktime, $maintenance_mode, $cisname;

    $login=secure_input($dbc,$login);
    $password=secure_input($dbc,$password);
    
    // handle security through a secret key, must be environment specific
    if ( $webservice_key == "" ) {
        return new soap_fault('-1', '', 'ERROR: Webservice is not activated');
    }
    if ($key <> $webservice_key) {
        $section = get_webservice_section($key);
        if ( $section == 0 ) {
            $key=secure_input($dbc,$key);
            log_soap('getUserInfo', $login, 1, "ERROR: Access to this webservice forbidden ".$key);
            return new soap_fault('-1', '', 'ERROR: Access to this webservice forbidden');
        }
    }
    else $section = 0;
    
    // check if maintenance mode, forbid connection
    if ( $maintenance_mode == 1 ) {
        log_soap('getUserInfo', $login, 1, "ERROR: Server is in maintenance mode.");
        return new soap_fault('-1', '', 'ERROR: Server is in maintenance mode.');
    }
    
    $query="select P_ID, P_MDP, P_PASSWORD_FAILURE, round((UNIX_TIMESTAMP() - UNIX_TIMESTAMP(P_LAST_CONNECT)) / 60) 'LAST'
    from pompier 
    where P_CODE=\"".$login."\"";
    if ( $section > 0 ) $query .= " and P_SECTION in (".get_family($section).")";
    $save_query=$query;
    $result=mysqli_query($dbc,$query);
    $row=mysqli_fetch_array($result);
    $P_MDP=$row['P_MDP'];
    $P_ID=intval($row['P_ID']);
    $P_PASSWORD_FAILURE=intval($row['P_PASSWORD_FAILURE']);
    $LAST=$row['LAST'];
    $valid = my_validate_password($password, $P_MDP);
    
    if ( ! $valid ) {
        if ( $password_failure > 0 and $P_ID > 0 ) {
            if ( $P_PASSWORD_FAILURE > 0 )
                $query="update pompier set P_PASSWORD_FAILURE=P_PASSWORD_FAILURE + 1, P_LAST_CONNECT=NOW() where P_ID=".$P_ID;
            else
                $query="update pompier set P_PASSWORD_FAILURE=1, P_LAST_CONNECT=NOW() where P_CODE=".$P_ID;
            $result=mysqli_query($dbc,$query);
        }
        log_soap('getUserInfo', $login, 1, "ERROR: Invalid credentials.");
        return new soap_fault('-1', '', 'ERROR: Invalid credentials.');
    }
    else if ( $P_ID > 0 ) {
        if ( $password_failure > 0 ) {
             if (( $P_PASSWORD_FAILURE >= $password_failure ) and ( $LAST <= $passwordblocktime )) {
                log_soap('getUserInfo', $login, 1, "ERROR: User locked.");
                return new soap_fault('-1', '', 'ERROR: User locked.');
             }
             else if ( $P_PASSWORD_FAILURE > 0 ) {
                 $query="update pompier set P_PASSWORD_FAILURE=null where P_ID=".$P_ID."
                 and P_PASSWORD_FAILURE is not null";
                 $result=mysqli_query($dbc,$query);
             }
        }
    
        $query="select P_NOM, P_PRENOM, P_EMAIL, P_BIRTHDATE, P_PHONE, P_PHONE2, P_SEXE, P_OLD_MEMBER, GP_ID, GP_ID2, P_SECTION
                from pompier where P_ID=".$P_ID;
        $result=mysqli_query($dbc,$query);
        $row=mysqli_fetch_array($result);
        if ( $row["P_OLD_MEMBER"] > 0 ) {
            log_soap('getUserInfo', $login, 1, "ERROR: Old user.");
            return new soap_fault('-1', '', 'ERROR: Old user.');
        }
        if ( $row["GP_ID"] == -1 or $row["GP_ID2"] == -1) {
            log_soap('getUserInfo', $login, 1, "ERROR: Connection forbidden for this user.");
            return new soap_fault('-1', '', 'ERROR: Connection forbidden for this user.');
        }
        
        // search competences
        $competences="";
        $query2="select p.TYPE from qualification q, poste p
                where p.PS_ID = q.PS_ID
                and ( q.Q_EXPIRATION is null or q.Q_EXPIRATION >= NOW() )
                and q.P_ID=".$P_ID;
        
        if ( $cisname == 'Protection Civile' ) {
            $complist=array('AFNPC','D.N.A.','D.N.F.','D.N.C.','D.N.T.','D.S.A.','D.N.O.','Prési','S.G.A.','S.G.D.','T.A.D','Réd.Web.','Siég.féd');
            $showlist=array('AFNPC','DNA',   'DNF',   'DNC',   'DNT',   'DSA',   'DNO',   'PRESI','SGA',   'SGD' ,  'SGA'  , 'REDWEB' ,'SIEGE');
        }
                
        $result2=mysqli_query($dbc,$query2);
        while ($row2=@mysqli_fetch_array($result2)) {
            if ( $cisname == 'Protection Civile' ) {
                if (in_array($row2["TYPE"],$complist)) {
                    $key = array_search($row2["TYPE"],$complist);
                    $competences.=$showlist[$key].",";
                }
            }
            else 
            $competences.=$row2["TYPE"].",";
        }
        $competences=rtrim($competences,',');
        
        // create a ticket for SSO
        $ticket = generateSecretString();
        $query="delete from demande
                  where P_ID = '".$P_ID."'
                  and D_TYPE = 'sso'";
        $result=mysqli_query($dbc,$query);
       
        $query="insert into demande ( P_ID, D_TYPE, D_SECRET , D_DATE )
                  values ( '".$P_ID."' , 'sso', '".$ticket."', NOW() )";
        $result=mysqli_query($dbc,$query);
        
        // return data
        log_soap('getUserInfo', $login, 0, "SUCCESS for ".my_ucfirst($row["P_PRENOM"])." ".strtoupper($row["P_NOM"]));
        return array(    "SUCCESS",
                        fixcharset($row["P_NOM"]),
                        fixcharset($row["P_PRENOM"]),
                        $P_ID,
                        $row["P_EMAIL"],
                        $row["P_BIRTHDATE"],
                        $row["P_PHONE"],
                        $row["P_PHONE2"],
                        $row["P_SEXE"],
                        $row["P_SECTION"],
                        $competences,
                        $ticket);
    }
    log_soap('getUserInfo', $login, 1, "ERROR: Unexpected. ".$save_query);
    return array("ERROR: Unexpected.");
}

$doc = "Pass credentials for one user, and in case these credentials are correct and belong to a valid user, return a set of user information. The first parameter, key, is a secret string which allows you to use the web service. If you do not have it , ask eBrigade's administrator.";
$server->register("getUserInfo",
    // input
    array("key"         => "xsd:string", 
          "login"         => "xsd:string", 
          "password"     => "xsd:string"),
    // output
    array("SoapStatus"  => "xsd:string",
         "p_nom"         => "xsd:string",
         "p_prenom"     => "xsd:string",
         "p_id"         => "xsd:integer",
         "p_email"          => "xsd:string",
         "p_birthdate"     => "xsd:date",
         "p_phone"         => "xsd:string",
         "p_phone2"     => "xsd:string",
         "p_sexe"         => "xsd:string",
         "s_id"         => "xsd:integer",
         "competences"  => "xsd:string",
         "ticket"          => "xsd:string"),
    "urn:userinfo",                // namespace
    "urn:userinfo#getUserInfo",    // soapaction
    "rpc",                        // style
    "encoded",                    // use
    $doc );                        // documentation

// ====================================================================
// getSections
// INPUT:  key
// OUTPUT: details on all sections
// ====================================================================    

function getSections($key) {
    global $dbc,$webservice_key, $levels;
    
    // handle security through a secret key, must be environment specific
    if ( $webservice_key == "" ) {
        return new soap_fault('-1', '', 'ERROR: Webservice is not activated');
    }
    if ($key <> $webservice_key) {
        $section = get_webservice_section($key);
        if ( $section == 0 ) return new soap_fault('-1', '', 'ERROR: Access to this webservice forbidden');
    }
    else $section = 0;
    
    $query="select distinct s.S_ID, s.S_CODE, s.S_DESCRIPTION, sf.NIV, s.S_PHONE , s.S_PHONE2 , s.S_PHONE3, s.S_FAX, s.S_URL, s.WEBSERVICE_KEY,
            s.S_ADDRESS, s.S_ADDRESS_COMPLEMENT, s.S_ZIP_CODE, s.S_CITY, s.S_EMAIL, s.S_EMAIL2, s.S_EMAIL3, s.S_PARENT,
            s.SHOW_PHONE3, s.SHOW_EMAIL3, s.SHOW_URL
            from section_flat sf, section s
            where sf.S_ID=s.S_ID
            and s.S_INACTIVE = 0";
    if ( $section > 0 ) $query .= " and s.S_ID in (".get_family($section).") ";
    $query .= " order by sf.S_CODE asc";
    $result=mysqli_query($dbc,$query);
    if ( mysqli_num_rows($result ) == 0 ) return new soap_fault('-1', '', 'ERROR: No data found');
    while ( $row=mysqli_fetch_array($result)) {
        $output[] = array(
            's_id' => $row['S_ID'],
            's_code' => fixcharset($row['S_CODE']),
            's_description' => fixcharset($row['S_DESCRIPTION']),
            's_type' => fixcharset($levels[$row['NIV']]),
            's_phone' => $row['S_PHONE'],
            's_phone2' => $row['S_PHONE2'],
            's_phone3' => $row['S_PHONE3'],
            's_fax' => $row['S_FAX'],
            's_address' => fixcharset($row['S_ADDRESS']),
            's_address_complement' => fixcharset($row['S_ADDRESS_COMPLEMENT']),
            's_zip_code' => $row['S_ZIP_CODE'],
            's_city' => fixcharset($row['S_CITY']),
            's_email' => $row['S_EMAIL'],
            's_email2' => $row['S_EMAIL2'],
            's_email3' => $row['S_EMAIL3'],
            's_parent' => $row['S_PARENT'],
            's_url' => $row['S_URL'],
            's_webservice_key' => $row['WEBSERVICE_KEY'],
            's_show_phone3' => intval($row['SHOW_PHONE3']),
            's_show_email3' => intval($row['SHOW_EMAIL3']),
            's_show_url' => intval($row['SHOW_URL']),
        );
    }
    
    return $output;
}

$server->wsdl->addComplexType(
    'Section',
    'complexType',
    'struct',
    'all','',
    array (
        's_id' => array('name' => 's_id', 'type' => 'xsd:integer'),
        's_code' => array('name' => 's_code', 'type' => 'xsd:string'),
        's_description' => array('name' => 's_description', 'type' => 'xsd:string'),
        's_type' => array('name' => 's_type', 'type' => 'xsd:string'),
        's_phone' => array('name' => 's_phone', 'type' => 'xsd:string'),
        's_phone2' => array('name' => 's_phone2', 'type' => 'xsd:string'),
        's_phone3' => array('name' => 's_phone3', 'type' => 'xsd:string'),
        's_fax' => array('name' => 's_fax', 'type' => 'xsd:string'),
        's_address' => array('name' => 's_address', 'type' => 'xsd:string'),
        's_address_complement' => array('name' => 's_address_complement', 'type' => 'xsd:string'),
        's_zip_code' => array('name' => 's_zip_code', 'type' => 'xsd:string'),
        's_city' => array('name' => 's_city', 'type' => 'xsd:string'),
        's_email' => array('name' => 's_email', 'type' => 'xsd:string'),
        's_email2' => array('name' => 's_email2', 'type' => 'xsd:string'),
        's_email3' => array('name' => 's_email3', 'type' => 'xsd:string'),
        's_parent' => array('name' => 's_parent', 'type' => 'xsd:integer'),
        's_url' => array('name' => 's_url', 'type' => 'xsd:string'),
        's_webservice_key' => array('name' => 's_webservice_key', 'type' => 'xsd:string'),
        's_show_phone3' => array('name' => 's_show_phone3', 'type' => 'xsd:integer'),
        's_show_email3' => array('name' => 's_show_email3', 'type' => 'xsd:integer'),
        's_show_url' => array('name' => 's_show_url', 'type' => 'xsd:integer'),
    )
);

$server->wsdl->addComplexType(
   'SectionArray',
   'complexType',
   'array','',
   'SOAP-ENC:Array',array(),
   array(
      array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:Section[]')
   ),
   'tns:Section'
);


$doc = "Return info on all sections. Input parameter, key, is a secret string which allows you to use the web service. If you do not have it , ask eBrigade's administrator.";
$server->register("getSections",
    // input
    array(    "key"             => "xsd:string"),
    // output
    array(    "Section"         => "tns:SectionArray"),
    "urn:sections",                // namespace
    "urn:sections#getSections",    // soapaction
    "rpc",                        // style
    "encoded",                    // use
    $doc );                        // documentation

    
// ====================================================================
// getEvents
// INPUT:  key
// OUTPUT: details on all future events
// ====================================================================    

function getEvents($key) {
    global $dbc,$webservice_key;
    
    // handle security through a secret key, must be environment specific
    if ( $webservice_key == "" ) {
        return new soap_fault('-1', '', 'ERROR: Webservice is not activated');
    }
    if ($key <> $webservice_key) {
        $section = get_webservice_section($key);
        if ( $section == 0 ) return new soap_fault('-1', '', 'ERROR: Access to this webservice forbidden');
    }
    else $section = 0;
    
    $query="select e.E_CODE, eh.EH_ID, e.E_LIBELLE, e.E_LIEU, e.E_NB_STAGIAIRES, e.E_OPEN_TO_EXT, e.E_CLOSED, e.TF_CODE, e.E_COMMENT2,
            eh.EH_ID, eh.EH_DATE_DEBUT, eh.EH_DEBUT, eh.EH_DATE_FIN, eh.EH_FIN, eh.EH_DUREE, e.E_ADDRESS,
            tf.TF_LIBELLE, p.TYPE, e.E_PARTIES, e.E_TARIF, e.E_VISIBLE_OUTSIDE, e.S_ID, e.E_URL
            from evenement e left join type_formation tf on e.TF_CODE = tf.TF_CODE
                             left join poste p on p.PS_ID = e.PS_ID,
            evenement_horaire eh
            where e.E_CODE = eh.E_CODE
            and e.E_CANCELED=0
            and e.E_VISIBLE_OUTSIDE=1
            and e.TE_CODE='FOR'
            and eh.EH_DATE_DEBUT >= '".date('Y-m-d')."'";
    if ( $section > 0 ) $query .= " and e.S_ID in (".get_family($section).") ";
    $query .= " order by e.E_CODE, eh.EH_ID asc";
    $result=mysqli_query($dbc,$query);
    if ( mysqli_num_rows($result ) == 0 ) return new soap_fault('-1', '', 'ERROR: No data found');
    while ( $row=mysqli_fetch_array($result)) {
        $parties = $row['EH_ID']."/".$row['E_PARTIES'];
        if ( $row['E_VISIBLE_OUTSIDE'] == 1 ) $interne_externe='externe';
        else $interne_externe='interne';
        if ( intval($row['E_NB_STAGIAIRES']) > 0 ) $stagiaires = $row['E_NB_STAGIAIRES'];
        else  $stagiaires= '';
        if ( $row['TYPE'] <> "" ) {
            if ( $row['TF_LIBELLE'] == '' ) $f = 'formation';
            else $f = $row['TF_LIBELLE'];
            $libelle = $f." ".$row['TYPE'];
        }
        else $libelle=$row['E_LIBELLE'];
        
        $output[] = array(
            'e_code' => $row['E_CODE'],
            'e_partie' => $parties,
            'e_libelle' => fixcharset($libelle),
            'date_debut' => $row['EH_DATE_DEBUT'],
            'heure_debut' => $row['EH_DEBUT'],
            'date_fin' => $row['EH_DATE_FIN'],
            'heure_fin' => $row['EH_FIN'],
            'e_lieu' => fixcharset($row['E_LIEU']),
            'e_address' => fixcharset($row['E_ADDRESS']),
            'interne_externe' => $interne_externe,
            'commentaire_externe' => fixcharset($row['E_COMMENT2']),
            'type_formation' => fixcharset($row['TF_LIBELLE']),
            'competence' => $row['TYPE'],
            'stagiaires' => $row['E_NB_STAGIAIRES'],
            's_id' => $row['S_ID'],
            'url' => $row['E_URL']
        );
    }
    
    return $output;
}

$server->wsdl->addComplexType(
    'Event',
    'complexType',
    'struct',
    'all','',
    array (
        'e_code' => array('name' => 'e_code', 'type' => 'xsd:integer'),
        'e_partie' => array('name' => 'e_partie', 'type' => 'xsd:string'),
        'e_libelle' => array('name' => 'e_libelle', 'type' => 'xsd:string'),
        'date_debut' => array('name' => 'date_debut', 'type' => 'xsd:date'),
        'heure_debut' => array('name' => 'heure_debut', 'type' => 'xsd:string'),
        'date_fin' => array('name' => 'date_fin', 'type' => 'xsd:date'),
        'heure_fin' => array('name' => 'heure_fin', 'type' => 'xsd:string'),
        'e_lieu' => array('name' => 'e_lieu', 'type' => 'xsd:string'),
        'e_address' => array('name' => 'e_address', 'type' => 'xsd:string'),
        'interne_externe' => array('name' => 'interne_externe', 'type' => 'xsd:string'),
        'commentaire_externe' => array('name' => 'commentaire_externe', 'type' => 'xsd:string'),
        'type_formation' => array('name' => 'type_formation', 'type' => 'xsd:string'),
        'competence' => array('name' => 'competence', 'type' => 'xsd:string'),
        'stagiaires' => array('name' => 'stagiaires', 'type' => 'xsd:integer'),
        's_id' => array('name' => 's_id', 'type' => 'xsd:integer'),
        'url' => array('name' => 'url', 'type' => 'xsd:string'),
    )
);

$server->wsdl->addComplexType(
   'EventArray',
   'complexType',
   'array','',
   'SOAP-ENC:Array',array(),
   array(
      array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:Event[]')
   ),
   'tns:Event'
);


$doc = "Return info on all future training Events. Input parameter, key, is a secret string which allows you to use the web service. If you do not have it , ask eBrigade's administrator.";
$server->register("getEvents",
    // input
    array(    "key"             => "xsd:string"),
    // output
    array(    "Event"         => "tns:EventArray"),
    "urn:events",                // namespace
    "urn:events#getEvents",        // soapaction
    "rpc",                        // style
    "encoded",                    // use
    $doc );                        // documentation
    
    
// ====================================================================
// Return data to soap service
// ====================================================================
$server->service(file_get_contents("php://input"));
?>
