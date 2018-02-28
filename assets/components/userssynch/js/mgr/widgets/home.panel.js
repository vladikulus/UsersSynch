UsersSynch.panel.Home = function (config) {
    config = config || {};
    Ext.apply(config, {
        baseCls: 'modx-formpanel',
        layout: 'anchor',
        /*
         stateful: true,
         stateId: 'userssynch-panel-home',
         stateEvents: ['tabchange'],
         getState:function() {return {activeTab:this.items.indexOf(this.getActiveTab())};},
         */
        hideMode: 'offsets',
        items: [{
            html: '<h2>' + _('userssynch') + '</h2>',
            cls: '',
            style: {margin: '15px 0'}
        }, {
            xtype: 'modx-tabs',
            defaults: {border: false, autoHeight: true},
            border: true,
            hideMode: 'offsets',
            items: [{
                title: _('userssynch_items'),
                layout: 'anchor',
                items: [{
                    html: _('userssynch_intro_msg'),
                    cls: 'panel-desc',
                }, {
                    xtype: 'userssynch-grid-items',
                    cls: 'main-wrapper',
                }]
            }]
        }]
    });
    UsersSynch.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(UsersSynch.panel.Home, MODx.Panel);
Ext.reg('userssynch-panel-home', UsersSynch.panel.Home);
