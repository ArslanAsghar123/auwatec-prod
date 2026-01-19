import Plugin from 'src/plugin-system/plugin.class';

export default class Seotext extends Plugin {
    init() {
        this.button = this.el.querySelector('[data-seo-text-trigger]');
        this.button.addEventListener('click', this.toggle.bind(this));
    }

    open(){
        this.el.classList.add('is-expanded');
    }

    close(){
        this.el.classList.remove('is-expanded');
    }

    toggle(){
        if(this.el.classList.contains('is-expanded')) {
            this.close();
        } else {
            this.open();
        }
    }

}