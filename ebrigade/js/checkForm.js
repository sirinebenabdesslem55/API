function isValid(form)
{   
    var s = form.value;
    var re = /^([\.a-zA-Z0-9_-]+)$/;
    if (! re.test(s)) {
         alert ("Seul des lettres non accentuees et numeros sont autorises: '"+ s + "' ne convient pas.");
        form.value = '';
        return false;
    }
    if ( s.length > 18 ) {
        alert ("Maximum 18 caracteres: '"+ s + "' ne convient pas.");
        form.value = '';
        return false;
    }

    // All characters are letters or numbers.
    return true;
}

function isValid2(form, defaultvalue)
{   
    var s = form.value;
    var re = /^([\.a-zA-Z0-9_-]*)$/;
    if (! re.test(s)) {
        alert ("Seul des lettres non accentuees et numeros sont autorises: '"+ s + "' ne convient pas.");
        form.value = defaultvalue;
        return false;
    }
    if ((s=="mysql")||(s=="information_schema")) {
        alert (s + "est une base systeme. Choisissez un autre nom pour la base de donnees eBrigade");
        form.value = defaultvalue;
        return false;
    }
    // All characters are letters or numbers.
    return true;
}

function countLetters(str) {
  var count=0,len=str.length;
  for(var i=0;i<len;i++) {
    if(/[a-z��������A-Z]/.test(str.charAt(i))) count++;
  }
  return count;
}

function isValid3(form, defaultvalue)
{   
    var s = form.value;
    var re = /^([\'\ a-z��������A-Z0-9_-]*)$/;
    if (! re.test(s)) {
         alert ("Attention seuls des lettres et des numeros sont autorises: '"+ s + "' ne convient pas.");
        form.value = defaultvalue;
        return false;
    }
    if ( s.length == 0 ) {
        alert ("Attention une chaine vide n'est pas possible ici.");
        form.value = defaultvalue;
        return false;
    }
    if ( countLetters(s) == 0 ) {
        alert ("Attention une chaine sans aucune lettre n'est pas possible ici.");
        form.value = defaultvalue;
        return false;     
    }
    // All characters are letters or numbers.
    return true;
}

function isValid4(form, defaultvalue)
{   
    var s = form.value;
    var re = /^([\'\ a-zA-Z0-9_-]*)$/;
    if (! re.test(s)) {
         alert ("Seuls des lettres non accentuees et des numeros sont autorises: '"+ s + "' ne convient pas.");
        form.value = defaultvalue;
        return false;
    }
    // All characters are letters or numbers.
    return true;
}

function isValidSMSUser(form, defaultvalue)
{   
    var s = form.value;
    var re = /^([@\.a-zA-Z0-9_-]*)$/;
    if (! re.test(s)) {
         alert ("Seuls des lettres non accentuees et des numeros sont autorises: '"+ s + "' ne convient pas.");
        form.value = defaultvalue;
        return false;
    }
    // All characters are letters or numbers.
    return true;
}

function isValidIPPort(form, defaultvalue)
{   
    var s = form.value;
    var re = /^([0-9\.\:]*)$/;
    if (! re.test(s)) {
         alert ("Seuls des des numeros, des points '.' et des deux points ':' sont autorises: '"+ s + "' ne convient pas.");
        form.value = defaultvalue;
        return false;
    }
    return true;
}

function isValid5(form, defaultvalue, numchars)
{   
    var s = form.value;
    var re = /^([A-Z0-9]*)$/;
    if (! re.test(s)) {
         alert ("Seuls des lettres majuscules et des numeros sont autorises: '"+ s + "' ne convient pas.");
        form.value = defaultvalue;
        return false;
    }
    if ( s.length != numchars && s.length > 0) {
        alert ("Saisissez un nombre � " + numchars + " caracteres: '"+ s + "' ne convient pas.");
        form.value = defaultvalue;
        return false;
    }
    // All characters are letters or numbers.
    return true;
}

function isValid6(form, defaultvalue)
{   
    var s = form.value;
    var re = /^([A-Z0-9_]*)$/;
    if (! re.test(s)) {
         alert ("Seuls des lettres majuscules non accentuees et des numeros sont autorises: '"+ s + "' ne convient pas.");
        form.value = defaultvalue;
        return false;
    }
    if ( s.length < 1 ) {
        alert ("Attention une chaine vide n'est pas possible ici.");
        form.value = defaultvalue;
        return false;
    }
    // All characters are letters or numbers.
    return true;
}

function checkPhone(form,defaultvalue,min_len)
{   
    var s = form.value;
    var re = /^([0-9\ ]+)$/;
    if ( s.length > 0 ) {
        if (! re.test(s)) {
             alert ("Seuls des numeros et des espaces sont autorises: '"+ s + "' ne convient pas.");
            form.value = defaultvalue;
            return false;
        }
    }
    if ( s.length > 16 ) {
        alert ("Maximum 16 caracteres: '"+ s + "' ne convient pas.");
        form.value = defaultvalue;
        return false;
    }
    // count numbers if starts with 0
    var nbnum = s.split(" ").join("").length;
    if ( min_len > 0 && nbnum < min_len && s[0] == '0') {
        alert ("Minimum " + min_len + " numeros requis. "+ s + " ne convient pas.");
        form.value = defaultvalue;
        return false;
    }

    // All characters are numbers.
    return true;
}

function checkNumber(form,defaultvalue)
{   
    var s = form.value;
    var re = /^([0-9]+)$/;
    if (! re.test(s)) {
         alert ("Saisissez un nombre entier: '"+ s + "' ne convient pas.");
        form.value = defaultvalue;
        return false;
    }
    return true;
}

function checkNumberNullAllowed(form,defaultvalue)
{   
    var s = form.value;
    var re = /^([0-9]+)$/;
    if (! re.test(s) && s.length > 0 ) {
         alert ("Saisissez un nombre entier: '"+ s + "' ne convient pas.");
        form.value = defaultvalue;
        return false;
    }
    return true;
}

function checkNumberOrNothing(form,expectedlength,defaultvalue)
{   
    var s = form.value;
    var re = /^([0-9]*)$/;
    if (! re.test(s)) {
         alert ("Saisissez un nombre entier: '"+ s + "' ne convient pas.");
        form.value = defaultvalue;
        return false;
    }
    if ( s.length != expectedlength && s.length > 0 ) {
        alert ("Saisissez un nombre � " + expectedlength + " chiffres: '"+ s + "' ne convient pas.");
        form.value = defaultvalue;
        return false;
    }
    return true;
}

function checkFloat(form,defaultvalue)
{   
    var s = form.value;
    var re1 = /^([0-9]+)$/;
    var re2 = /^([\-0-9\.]+)$/;
    if (! re1.test(s) && ! re2.test(s)) {
        alert ("Saisissez un nombre decimal (separateur .): '"+ s + "' ne convient pas.");
        form.value = defaultvalue;
        return false;
    }
    // All characters are numbers.
    return true;
}

function checkFloatOrNothing(form,defaultvalue) 
{
    var s = form.value;
    if ( s == '' )return true;
    checkFloat(form,defaultvalue);
}

function checkTime(form,defaultvalue) {
    // Cette fonction verifie le format HH:mi saisi et la validite de l'heure.
    // si format HHmi accepte quand meme
    var s = form.value;
    var re = /^[0-9][0-9]\:[0-9][0-9]$/;
    var re2 = /^[0-9][0-9][0-9][0-9]$/;
    if (! re.test(s) && ! re2.test(s) && s != '' ) {
         alert ("Saisissez heures:minutes '"+ s + "' ne convient pas.");
        form.value = defaultvalue;
        return false;
    }
    if ( re2.test(s) ) {
        var a1=(s.substring(0,2));
        var a2=(s.substring(2,4));
        s = a1 + ':' + a2;
        form.value = s;
    }
    var ok=1;
    var h=(s.substring(0,2));
    var m=(s.substring(3,5));
    if ( h > 23 && (ok==1) ) {
         alert("L'heure n'est pas correcte (doit etre comprise entre 00 et 23."); ok=0;
    }
    if ( m > 59 && (ok==1) ) {
         alert("Les minutes ne sont pas correctes (doivent etre comprises entre 00 et 59."); ok=0;
    }
    if (ok==0) {
          form.value = defaultvalue;
          return false;    
    }
    return true;
}

function checkDate2(form) {
    var d = form.value;
    if (d=='') return true; 
    // Cette fonction verifie le format JJ-MM-AAAA saisi et la validite de la date.
    // Le separateur est defini dans la variable separateur
    var amin=1901; // annee mini
    var amax=2050; // annee maxi
    var separateur="-"; // separateur entre jour-mois-annee
    var j=(d.substring(0,2));
    var m=(d.substring(3,5));
    var a=(d.substring(6));
    var ok=1;
    if ( ((isNaN(j))||(j<1)||(j>31)) && (ok==1) ) {
        alert("Le jour n'est pas correct."); ok=0;
    }
    if ( ((isNaN(m))||(m<1)||(m>12)) && (ok==1) ) {
        alert("Le mois n'est pas correct."); ok=0;
    }
    if ( ((isNaN(a))||(a<amin)||(a>amax)) && (ok==1) ) {
        alert("L'annee n'est pas correcte."); ok=0;
    }
    if ( ((d.substring(2,3)!=separateur)||(d.substring(5,6)!=separateur)) && (ok==1) ) {
        alert("Les separateurs doivent etre des "+separateur); ok=0;
    }
    if (ok==1) {
        var d2=new Date(a,m-1,j);
        j2=d2.getDate();
        m2=d2.getMonth()+1;
        a2=d2.getFullYear();
        if (a2<=100) {a2=1900+a2}
        if ( (j!=j2)||(m!=m2)||(a!=a2) ) {
            alert("La date "+d+" n'existe pas !");
            ok=0;
        }
    }
    if (ok==0) {
          form.value = '';
          return false;    
    }
    return true;
}


function mailCheck(form,defaultvalue) {

    var s = form.value;
    var re = /^[\w_.~-]+@[\w][\w.\-]*[\w]\.[\w][\w.]*[a-zA-Z]$/;
    if ((! re.test(s)) && ( s != '' )){
         alert ("L'adresse email saisie est incorrecte: '"+ s + "' ne convient pas.");
        form.value = defaultvalue;
        return false;
    }
    // All characters are letters or numbers.
    return true;
}

function fillTime(textbox) {
    if ( textbox.value == '' ) {
        var currentTime = new Date();
        var hours = currentTime.getHours();
        var minutes = currentTime.getMinutes();
        if (minutes < 10)
            minutes = "0" + minutes;
        if (hours < 10)
            hours = "0" + hours;
        textbox.value=hours + ":" + minutes;
    }
}

function fillDate(textbox) {
    if ( textbox.value == '' ) {
        var currentDate = new Date();
        var month = '' + (currentDate.getMonth() + 1);
        var day = '' + currentDate.getDate();
        var year = currentDate.getFullYear();
        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;
        textbox.value = [day, month, year].join('-');
    }
}