import template from './acris-gpsr-info-text.html.twig';
import './acris-gpsr-info-text.scss';

const { Component, Mixin } = Shopware;
const registry = Component.getComponentRegistry();

if (!registry.has('acris-gpsr-info-text')) {
    Component.register('acris-gpsr-info-text', {
        template,

        mixins: [
            Mixin.getByName('sw-form-field')
        ]
    });
}
