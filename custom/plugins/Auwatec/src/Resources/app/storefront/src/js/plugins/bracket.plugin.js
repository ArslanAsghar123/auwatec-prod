import Plugin from 'src/plugin-system/plugin.class';

export default class Bracket extends Plugin {
    init() {
        //TODO: close all on body-click
        this.button = this.el.querySelector('.bracket-button');
        this.closeButton = this.el.querySelector('.bracket-close-button');
        if(this.el.classList.contains('is-expandable')){
            this.button.addEventListener('click', this.toggle.bind(this));
            if(this.closeButton){
                this.closeButton.addEventListener('click', this.close.bind(this));
            }
        }
    }

    open(){
        this._closeAllBrackets();
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

    _closeAllBrackets(){
        PluginManager.getPluginInstances('Bracket').forEach( el => {
            el.close()
        });
    }
}