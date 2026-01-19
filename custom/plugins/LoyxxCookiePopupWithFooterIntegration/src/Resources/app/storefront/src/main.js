const PluginManager = window.PluginManager;
PluginManager.override(
    'CookieConfiguration',
    () => import('./cookie-configuration/cookie-configuration.plugin'),
    '[data-cookie-permission]'
);
PluginManager.register(
    'LoyxxModalCookie',
    () => import('./cookie-configuration/loyxx-modal-cookie.plugin'),
    '[data-loyxx-modal-cookie]'
);
PluginManager.override(
    'OffCanvasMenu',
    () => import('./cookie-configuration/offcanvas-menu.plugin'),
    '[data-off-canvas-menu]'
);