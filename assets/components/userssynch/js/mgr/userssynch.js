var UsersSynch = function (config) {
    config = config || {};
    UsersSynch.superclass.constructor.call(this, config);
};
Ext.extend(UsersSynch, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}
});
Ext.reg('userssynch', UsersSynch);

UsersSynch = new UsersSynch();