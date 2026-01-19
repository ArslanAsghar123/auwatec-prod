try{
    window.PluginManager.getPluginInstances('AcrisFirstActiveDescriptionTab');
}catch (error){
    window.PluginManager.register('AcrisFirstActiveDescriptionTab', () => import('./plugin/acris-first-active-description-tab/acris-first-active-description-tab.plugin'), '#product-detail-tabs');
}