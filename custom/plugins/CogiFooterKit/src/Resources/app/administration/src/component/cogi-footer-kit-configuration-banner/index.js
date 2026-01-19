import template from './cogi-footer-kit-configuration-banner.html.twig';

const { Component } = Shopware;

Component.register('cogi-footer-kit-configuration-banner', {
    template,

    props: [
        'extension'
    ],

    created() {
        const bannerSelector = 'cogi-configuration-banner';
        let lang = Shopware.State.get('session').currentLocale.slice(0, 2);

        const xhttp = new XMLHttpRequest();
        xhttp.timeout = 5000;

        xhttp.open("GET", "https://shopware.codegiganten.de/configuration-banner/" + this.extension + "/" + lang + "/", true);
        xhttp.send();

        xhttp.onload = function () {
            if (xhttp.status !== 200) {
                hideConfigurationBanner();
            } else {
                try {
                    const parsedResponse = JSON.parse(this.responseText);
                    if (parsedResponse.success) {
                        document.getElementById(bannerSelector).innerHTML = parsedResponse.data;
                        return;
                    }
                } catch(e) {}
                hideConfigurationBanner();
            }
        }

        xhttp.onerror = function() {
            hideConfigurationBanner();
        };

        xhttp.ontimeout = function() {
            hideConfigurationBanner();
        };

        let hideConfigurationBanner = function() {
            document.getElementById(bannerSelector).closest('.sw-card-wrapper').style.display = 'none';
        }
    }
});