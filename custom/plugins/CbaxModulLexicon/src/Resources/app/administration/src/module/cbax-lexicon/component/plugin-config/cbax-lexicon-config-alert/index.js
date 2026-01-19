import template from './cbax-lexicon-config-alert.html.twig';
import './cbax-lexicon-config-alert.scss'

const { Component } = Shopware;

Component.register('cbax-lexicon-config-alert', {
    template,

    mixins: [
        Shopware.Mixin.getByName('sw-inline-snippet')
    ],

    props: {
        variant: {
            type: String,
            default: 'info',
            validValues: ['info', 'warning', 'error', 'success'],
            validator(value) {
                return ['info', 'warning', 'error', 'success'].includes(value);
            }
        },
        appearance: {
            type: String,
            default: 'default',
            validValues: ['default', 'notification', 'system'],
            validator(value) {
                return ['default', 'notification', 'system'].includes(value);
            }
        },
        title: {
            type: String,
            required: false,
            default: ''
        },
        showIcon: {
            type: Boolean,
            required: false,
            default: true
        },
        closable: {
            type: Boolean,
            required: false,
            default: false
        },
        notificationIndex: {
            type: String,
            required: false,
            default: null
        },
        alertName: {
            type: String,
            required: false,
            default: ''
        }
    },

    computed: {
        routerLink() {
            return 'sw.cms.index';
        }
    }
});
