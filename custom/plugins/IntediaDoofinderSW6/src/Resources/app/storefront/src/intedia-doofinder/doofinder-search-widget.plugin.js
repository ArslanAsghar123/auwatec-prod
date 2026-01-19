import SearchWidgetPlugin from 'src/plugin/header/search-widget.plugin'

export default class DoofinderSearchWidgetPlugin extends SearchWidgetPlugin {

    _suggest(value) {

        let options = this._getDooFinderOptions();

        if (options && options.engineHash && options.searchZone && options.layerType && options.layerType !== '0') {
            return; // handled by doofinder
        }

        super._suggest(value);
    }

    _getDooFinderOptions() {

        let instances = window.PluginManager.getPluginInstances('IntediaDoofinder');

        if (instances.length) {
            return instances[0].options;
        }

        return null;
    }
}