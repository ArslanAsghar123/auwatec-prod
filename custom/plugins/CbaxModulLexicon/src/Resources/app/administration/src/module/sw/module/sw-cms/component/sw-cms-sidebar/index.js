import template from './sw-cms-sidebar.html.twig';

Shopware.Component.override('sw-cms-sidebar', {
    template,

    methods: {
        createdComponent() {
            this.$super('createdComponent');
            let intervallCount = 0;
            const intervalId = setInterval(() => {
                intervallCount++;
                if (this.page.type && this.$refs) {
                    clearInterval(intervalId);
                    if (this.page.type === 'cbax_lexicon' && this.$refs.layoutAssignment) {
                        //his.$refs.layoutAssignment.disabled = ... does not work here
                        this.$refs.layoutAssignment._.props.disabled = true;
                    }
                } else {
                    if (intervallCount > 10) {
                        clearInterval(intervalId);
                    }
                }
            }, 200);
        }
    }
});
