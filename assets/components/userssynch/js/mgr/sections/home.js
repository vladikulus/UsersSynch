UsersSynch.page.Home = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        components: [{
            xtype: 'userssynch-panel-home',
            renderTo: 'userssynch-panel-home-div'
        }]
    });
    UsersSynch.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(UsersSynch.page.Home, MODx.Component);
Ext.reg('userssynch-page-home', UsersSynch.page.Home);