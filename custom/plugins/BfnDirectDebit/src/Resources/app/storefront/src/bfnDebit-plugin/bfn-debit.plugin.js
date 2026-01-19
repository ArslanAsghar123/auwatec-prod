import HttpClient from 'src/service/http-client.service';
import Plugin from 'src/plugin-system/plugin.class';

var getOwnerName;
var getIbanNumber;
var getSwiftNumber;
var getBaseUrl;
var getResponseData;
var getkeyupSwift;
var getkeyupIban;
var getIbanError;
var getSwiftError;
var ibanValid;
var swiftValid;
var mandateValid;
var activeRoute;
var editOwner;
var editIban;
var editSwift;
var editMandate;
var lastFourDigits;
var originalLength;

export default class BfnDebitPlugin extends Plugin {

    init() {
        // Initialize the HttpClient
        this._client = new HttpClient();
        getOwnerName = document.getElementById("bfn_direct_debit_owner_input") !== null ? document.getElementById("bfn_direct_debit_owner_input").value : null;
        getIbanNumber = document.getElementById("bfn_direct_debit_iban_input") !== null ? document.getElementById("bfn_direct_debit_iban_input").value : null;
        getSwiftNumber = document.getElementById("bfn_direct_debit_bic_input") !== null ? document.getElementById("bfn_direct_debit_bic_input").value : null;
        getBaseUrl = document.getElementById("af-base-url") !== null ? document.getElementById("af-base-url").value : null;
        getkeyupIban = document.getElementById("bfn_direct_debit_iban_input");
        getkeyupSwift = document.getElementById("bfn_direct_debit_bic_input");
        getIbanError = document.getElementById("iban-error");
        getSwiftError = document.getElementById("swift-error");
        activeRoute = document.getElementById("validate-active-route") !== null ? document.getElementById("validate-active-route").value : null;
        editOwner = document.getElementById("editDirectDebitOwner") !== null ? document.getElementById("editDirectDebitOwner") : null;
        editIban = document.getElementById("editDirectDebitIban") !== null ? document.getElementById("editDirectDebitIban") : null;
        editSwift = document.getElementById("editDirectDebitBicSwift") !== null ? document.getElementById("editDirectDebitBicSwift") : null;
        editMandate = document.getElementById("editDirectDebitMandate") !== null ? document.getElementById("editDirectDebitMandate") : null;
        lastFourDigits = document.getElementById("last-four") !== null ? document.getElementById("last-four").value : null;
        originalLength = document.getElementById("original-length") !== null ? document.getElementById("original-length").value : null;

        if (!this.isDirectDebitSelected()) {
            return;
        }

        if (getIbanError !== null) {
            getIbanError.style.display = 'none';
        }
        if (getSwiftError !== null) {
            getSwiftError.style.display = 'none';
        }

        if (getkeyupIban !== null) {
            if (!getkeyupIban.hasAttribute('data-keyup-attached')) {
                getkeyupIban.addEventListener('input', () => {
                    getIbanNumber = document.getElementById("bfn_direct_debit_iban_input").value;
                    this._ibanValidation();
                });

                getkeyupIban.setAttribute('data-keyup-attached', 'true');
            }
        } else {
            ibanValid = true;
        }

        if (getkeyupSwift !== null) {
            if (!getkeyupSwift.hasAttribute('data-keyup-attached')) {
                getkeyupSwift.addEventListener('input', () => {
                    getSwiftNumber = document.getElementById("bfn_direct_debit_bic_input").value;
                    this._swiftCodeValidation();
                });

                getkeyupSwift.setAttribute('data-keyup-attached', 'true');
            }
        } else {
            swiftValid = true;
        }

        if (document.getElementById("confirmFormSubmit") !== null) {
            document.getElementById("confirmFormSubmit").disabled = true;
        }

        const checkbox = document.getElementById("mandateCheck");
        if (checkbox) {
            checkbox.addEventListener('change', () => {
                this._checkboxValidation();
            });
            mandateValid = false;
        } else {
            mandateValid = true;
        }

        const confirmOrderForm = document.getElementById('confirmOrderForm');
        if (confirmOrderForm) {
            confirmOrderForm.addEventListener('submit', (event) => {
                this._handleFormSubmit();
            });
        }

        const pluginConfigElement = document.getElementById('plugin-config');
        if (pluginConfigElement) {
            this.maskIban = pluginConfigElement.getAttribute('data-mask-iban');
            if (this.maskIban) {
                this._addPasswordToggle();
            }
        }

        this._registerEvents();
    }

    _addPasswordToggle() {
        const ibanField = document.querySelector("input#bfn_direct_debit_iban_input[type=password]");
        if (ibanField && !ibanField.parentElement.querySelector('.passwordMask')) {
            const toggle = document.createElement("div");
            toggle.classList.add("passwordMask");

            toggle.addEventListener("click", () => {
                toggle.classList.toggle("isVisible");

                if (ibanField.getAttribute("type") == "text") {
                    ibanField.setAttribute("type", "password");
                } else {
                    ibanField.setAttribute("type", "text");
                }
            });

            ibanField.parentElement.insertBefore(toggle, ibanField);
        }
    }


    _handleFormSubmit() {
        getOwnerName = document.getElementById("bfn_direct_debit_owner_input") !== null ? document.getElementById("bfn_direct_debit_owner_input").value : null;
        getIbanNumber = document.getElementById("bfn_direct_debit_iban_input") !== null ? document.getElementById("bfn_direct_debit_iban_input").value : null;
        getSwiftNumber = document.getElementById("bfn_direct_debit_bic_input") !== null ? document.getElementById("bfn_direct_debit_bic_input").value : null;

        if (editOwner !== null) {
            editOwner.value = getOwnerName;
        }
        if (editIban !== null) {
            editIban.value = getIbanNumber;
        }
        if (editSwift !== null) {
            editSwift.value = getSwiftNumber;
        }
        const checkbox = document.getElementById("mandateCheck");
        if (editMandate !== null && checkbox !== null && checkbox.checked) {
            editMandate.value = 1;
        }
    }

    isDirectDebitSelected() {
        const directDebitValue = document.getElementById("directDebitPaymentMethodId") !== null ? document.getElementById("directDebitPaymentMethodId").value : null;

        if (directDebitValue === null) {
            return false;
        }

        const paymentMethods = document.querySelectorAll('input[name="paymentMethodId"]');
        for (let i = 0; i < paymentMethods.length; i++) {
            if (paymentMethods[i].checked && paymentMethods[i].value === directDebitValue) {
                return true;
            }
        }
        return false;
    }

    _registerEvents() {
        this._ibanValidation();
        if (getSwiftNumber !== null) {
            this._swiftCodeValidation();
        }
        const checkbox = document.getElementById("mandateCheck");
        if (checkbox) {
            this._checkboxValidation();
        }
    }

    _ibanValidation() {
        if ((getIbanNumber.match(/^X{10}/) && getIbanNumber.slice(-4) === lastFourDigits) && getIbanNumber.length == originalLength && getIbanNumber !== null && getIbanNumber.trim() !== '' && getIbanNumber.trim().length >= 15) {
            getIbanError.style.display = 'none';
            ibanValid = true;
            this._changeButtonAppearance();
        } else {
            if (getIbanNumber !== null && getIbanNumber.trim() !== '' && getIbanNumber.trim().length >= 15) {
                const getIbanObject = JSON.stringify({
                    getIbanNumber: getIbanNumber
                });
                this._client.post(getBaseUrl+'directDebit/validateIban/iban', getIbanObject, this._setContent.bind(this), 'application/json', true);
                this._changeButtonAppearance();
            } else {
                if (getIbanNumber.trim().length >= 1 && getIbanNumber.trim().length <= 15) {
                    ibanValid = false;
                    getIbanError.style.display = 'block';
                }
                this._changeButtonAppearance();
            }
        }
    }

    _setContent(getData) {
        if (getIbanError !== null) {
            if (getData == "true") {
                getIbanError.style.display = 'none';
                ibanValid = true;
            } else {
                getIbanError.style.display = 'block';
                ibanValid = false;
            }

            this._changeButtonAppearance();
        }
    }

    _swiftCodeValidation() {
        if (getSwiftNumber !== null && getSwiftNumber.trim() !== '' && getSwiftNumber.trim().length >= 8) {
            const getSwiftObject = JSON.stringify({
                getSwiftNumber: getSwiftNumber
            });
            this._client.post(getBaseUrl+'directDebit/validateSwift/swiftCode', getSwiftObject, this._setSwiftContent.bind(this), 'application/json', true);
        } else {
            if (getSwiftNumber.trim().length >= 1 && getSwiftNumber.trim().length <= 8) {
                swiftValid = false;
                getSwiftError.style.display = 'block';
            }
            this._changeButtonAppearance();
        }
    }

    _setSwiftContent(data) {
        if (getSwiftError !== null) {
            if (data == "true") {
                getSwiftError.style.display = 'none';
                swiftValid = true;
            } else {
                getSwiftError.style.display = 'block';
                swiftValid = false;
            }

            this._changeButtonAppearance();
        }
    }

    _checkboxValidation() {
        const checkbox = document.getElementById("mandateCheck");
        if (checkbox) {
            if (checkbox.checked) {
                mandateValid = true;
            } else {
                mandateValid = false;
            }

            this._changeButtonAppearance();
        }
    }

    _changeButtonAppearance() {
        if (ibanValid === true && swiftValid === true && mandateValid === true) {
            if (document.getElementById("confirmFormSubmit") !== null) {
                document.getElementById("confirmFormSubmit").disabled = false;
                if (document.getElementById("directDebitInfoMessage") !== null) {
                    document.getElementById("directDebitInfoMessage").style.display = 'none';
                }
            }
        } else {
            if (document.getElementById("confirmFormSubmit") !== null) {
                document.getElementById("confirmFormSubmit").disabled = true;
            }
            if (document.getElementById("directDebitInfoMessage") !== null) {
                document.getElementById("directDebitInfoMessage").style.display = 'block';
            }
        }
    }
}