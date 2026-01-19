import './page/swpa-backup';
import './view/swpa-backup-settings';
import './view/swpa-backup-log';
import './service';
import './componennts/swpa-backup-general';
import './componennts/swpa-backup-local';
import './componennts/swpa-backup-sftp';
import './componennts/swpa-backup-ftp';
import './componennts/swpa-backup-aws';

import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

const {Module} = Shopware;

Module.register('swpa-backup', {
    type: 'plugin',
    name: 'SwpaBackup',
    title: 'swpa-backup.general.mainMenuItemGeneral',
    description: 'swpa-backup.general.descriptionTextModule',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#9AA8B5',
    icon: 'regular-harddisk',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        index: {
            component: 'swpa-backup',
            path: 'index',
            meta: {
                parentPath: 'sw.settings.index'
            },
            redirect: {
                name: 'swpa.backup.index.settings'
            },
            children: {
                settings: {
                    component: 'swpa-backup-settings',
                    path: 'settings',
                    meta: {
                        parentPath: 'sw.settings.index'
                    }
                },
                log: {
                    component: 'swpa-backup-log',
                    path: 'log',
                    meta: {
                        parentPath: 'sw.settings.index'
                    }
                }
            }
        }
    },
    settingsItem: {
        group: 'plugins',
        to: 'swpa.backup.index.settings',
        icon: 'regular-harddisk',
        backgroundEnabled: true,
        privilege: 'swpa_backup.viewer'
    }
});
