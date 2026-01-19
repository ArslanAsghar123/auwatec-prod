import template from './swpa-backup.html.twig';
import './swpa-backup.scss';

const {Component, Mixin} = Shopware;

Component.register('swpa-backup', {
    template,

    mixins: [
        Mixin.getByName('notification'),
    ],

    props: {},

    data() {
        return {
            isLoading: false,
            config: null,
            savingDisabled: false,
            isSaveSuccessful: false
        };
    },

    computed: {},

    watch: {},

    methods: {}
});
