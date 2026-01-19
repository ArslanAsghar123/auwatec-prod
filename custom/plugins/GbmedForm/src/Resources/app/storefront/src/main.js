const PluginManager = window.PluginManager;
PluginManager.register(
    'GbmedForm',
    () => import('./js/gbmed-form.plugin'),
    '[data-gbmed-recaptcha]'
);
