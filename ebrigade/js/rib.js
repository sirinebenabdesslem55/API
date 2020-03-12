function suivant(enCours, suivant, limite) { 
	if (enCours.value.length == limite && document.bic[suivant].value.length == 0)
		document.bic[suivant].focus();
	verificationIBAN();
}

function verificationIBAN() {
	$('#iban_success').hide();
	$('#iban_error').hide();
	$('#iban_warn').hide();	
	if ( $('#iban7').val().length == 0 ) {
		$('#iban_warn').fadeIn(300).show();	
		return false;	
	}
	if ( isIBAN() ) {
		$('#iban_success').fadeIn(300).show();
		return true;
	}
	$('#iban_error').fadeIn(300).show();
    return false;
}



function isIBAN() {
	var ibanstr = $('#iban1').val() + $('#iban2').val() + $('#iban3').val() + $('#iban4').val() + $('#iban5').val() + $('#iban6').val() + $('#iban7').val() + $('#iban8').val();
    var newIban = ibanstr.toUpperCase(),
        modulo = function (divident, divisor) {
            var cDivident = '';
            var cRest = '';

            for (var i in divident ) {
                var cChar = divident[i];
                var cOperator = cRest + '' + cDivident + '' + cChar;

                if ( cOperator < parseInt(divisor) ) {
                        cDivident += '' + cChar;
                } else {
                        cRest = cOperator % divisor;
                        if ( cRest == 0 ) {
                            cRest = '';
                        }
                        cDivident = '';
                }

            }
            cRest += '' + cDivident;
            if (cRest == '') {
                cRest = 0;
            }
            return cRest;
        };

    if (newIban.search(/^[A-Z]{2}/gi) < 0) {
        return false;
    }

    newIban = newIban.substring(4) + newIban.substring(0, 4);

    newIban = newIban.replace(/[A-Z]/g, function (match) {
        return match.charCodeAt(0) - 55;
    });

    return parseInt(modulo(newIban, 97), 10) === 1;
}


function eraser_iban() {
	$('#iban1').val('');
	$('#iban2').val('');
	$('#iban3').val('');
	$('#iban4').val('');
	$('#iban5').val('');
	$('#iban6').val('');
	$('#iban7').val('');
	$('#iban8').val('');
	$('#bic').val('');
}

function copy_to_clipboard(text) {
  window.prompt("Copy to clipboard: Ctrl+C, Enter", text);
}