"use strict";
/// <reference path="../../js/knockout.d.ts" />
/// <reference path="../../js/jquery.d.ts" />
/// <reference path="../../js/jqueryui.d.ts" />
/// <reference types="@types/lodash" />
/// <reference path="../../modules/actor-selector/actor-selector.ts" />
/// <reference path="../../vendor/yahnis-elsts/ajax-wrapper/ajax-action-wrapper.d.ts" />
let amePluginVisibility;
class AmePluginVisibilityModule {
    constructor(scriptData) {
        const _ = AmePluginVisibilityModule._;
        this.actorSelector = new AmeActorSelector(AmeActors, scriptData.isProVersion);
        //Wrap the selected actor in a computed observable so that it can be used with Knockout.
        let _selectedActor = ko.observable(this.actorSelector.selectedActor);
        this.selectedActor = ko.computed({
            read: function () {
                return _selectedActor();
            },
            write: (newActor) => {
                this.actorSelector.setSelectedActor(newActor);
            }
        });
        this.actorSelector.onChange((newSelectedActor) => {
            _selectedActor(newSelectedActor);
        });
        //Re-select the previously selected actor, or select "All" (null) by default.
        this.selectedActor(scriptData.selectedActor);
        this.canRoleManagePlugins = scriptData.canManagePlugins;
        this.isMultisite = scriptData.isMultisite;
        this.grantAccessByDefault = {};
        _.forEach(this.actorSelector.getVisibleActors(), (actor) => {
            this.grantAccessByDefault[actor.getId()] = ko.observable(_.get(scriptData.settings.grantAccessByDefault, actor.getId(), this.canManagePlugins(actor)));
        });
        this.plugins = _.map(scriptData.installedPlugins, (plugin) => {
            return new AmePlugin(plugin, _.get(scriptData.settings.plugins, plugin.fileName, {}), this);
        });
        //Normally, the plugin list is sorted by the (real) plugin name. Re-sort taking custom names into account.
        this.plugins.sort(function (a, b) {
            return a.name().localeCompare(b.name());
        });
        this.privilegedActors = [];
        const currentUser = this.actorSelector.getCurrentUserActor();
        if (currentUser) {
            this.privilegedActors.push(currentUser);
        }
        if (this.isMultisite) {
            this.privilegedActors.push(AmeActors.getSuperAdmin());
        }
        this.areNewPluginsVisible = ko.computed({
            read: () => {
                const selectedActor = this.selectedActor();
                if (selectedActor !== null) {
                    let canSeePluginsByDefault = this.getGrantAccessByDefault(selectedActor);
                    return canSeePluginsByDefault();
                }
                return _.every(this.actorSelector.getVisibleActors(), (actor) => {
                    //Only consider roles than can manage plugins.
                    if (!this.canManagePlugins(actor)) {
                        return true;
                    }
                    let canSeePluginsByDefault = this.getGrantAccessByDefault(actor.getId());
                    return canSeePluginsByDefault();
                });
            },
            write: (isChecked) => {
                const selectedActor = this.selectedActor();
                if (selectedActor !== null) {
                    let canSeePluginsByDefault = this.getGrantAccessByDefault(selectedActor);
                    canSeePluginsByDefault(isChecked);
                    return;
                }
                //Update everyone except the current user and Super Admin.
                _.forEach(this.actorSelector.getVisibleActors(), (actor) => {
                    let isAllowed = this.getGrantAccessByDefault(actor.getId());
                    if (!this.canManagePlugins(actor)) {
                        isAllowed(false);
                    }
                    else if (_.includes(this.privilegedActors, actor)) {
                        isAllowed(true);
                    }
                    else {
                        isAllowed(isChecked);
                    }
                });
            }
        });
        this.areAllPluginsChecked = ko.computed({
            read: () => {
                return _.every(this.plugins, (plugin) => {
                    return this.isPluginVisible(plugin);
                }) && this.areNewPluginsVisible();
            },
            write: (isChecked) => {
                this.areNewPluginsVisible(isChecked);
                _.forEach(this.plugins, (plugin) => {
                    this.setPluginVisibility(plugin, isChecked);
                });
            }
        });
        //This observable will be populated when saving changes.
        this.settingsData = ko.observable('');
    }
    isPluginVisible(plugin) {
        let actorId = this.selectedActor();
        if (actorId === null) {
            return plugin.isVisibleByDefault();
        }
        else {
            let canSeePluginsByDefault = this.getGrantAccessByDefault(actorId), isVisible = plugin.getGrantObservable(actorId, plugin.isVisibleByDefault() && canSeePluginsByDefault());
            return isVisible();
        }
    }
    setPluginVisibility(plugin, isVisible) {
        const selectedActor = this.selectedActor();
        if (selectedActor === null) {
            plugin.isVisibleByDefault(isVisible);
            //Show/hide from everyone except the current user and Super Admin.
            //However, don't enable plugins for roles that can't access the "Plugins" page in the first place.
            const _ = AmePluginVisibilityModule._;
            _.forEach(this.actorSelector.getVisibleActors(), (actor) => {
                let allowAccess = plugin.getGrantObservable(actor.getId(), isVisible);
                if (!this.canManagePlugins(actor)) {
                    allowAccess(false);
                }
                else if (_.includes(this.privilegedActors, actor)) {
                    allowAccess(true);
                }
                else {
                    allowAccess(isVisible);
                }
            });
        }
        else {
            //Show/hide from the selected role or user.
            let allowAccess = plugin.getGrantObservable(selectedActor, isVisible);
            allowAccess(isVisible);
        }
    }
    canManagePlugins(actor) {
        const _ = AmePluginVisibilityModule._;
        if ((actor instanceof AmeRole) && _.has(this.canRoleManagePlugins, actor.name)) {
            return this.canRoleManagePlugins[actor.name];
        }
        if (actor instanceof AmeSuperAdmin) {
            return true;
        }
        if (actor instanceof AmeUser) {
            //Can any of the user's roles manage plugins?
            let result = false;
            _.forEach(actor.roles, (roleId) => {
                if (_.get(this.canRoleManagePlugins, roleId, false)) {
                    result = true;
                    return false;
                }
            });
            return (result || (!!AmeActors.hasCap(actor.id, 'activate_plugins')));
        }
        return false;
    }
    getGrantAccessByDefault(actorId) {
        if (!this.grantAccessByDefault.hasOwnProperty(actorId)) {
            this.grantAccessByDefault[actorId] = ko.observable(this.canManagePlugins(AmeActors.getActor(actorId)));
        }
        return this.grantAccessByDefault[actorId];
    }
    getSettings() {
        const _ = AmePluginVisibilityModule._;
        let result = {};
        result.grantAccessByDefault = _.mapValues(this.grantAccessByDefault, (allow) => {
            return allow();
        });
        result.plugins = {};
        _.forEach(this.plugins, (plugin) => {
            result.plugins[plugin.fileName] = {
                isVisibleByDefault: plugin.isVisibleByDefault(),
                grantAccess: _.mapValues(plugin.grantAccess, (allow) => {
                    return allow();
                })
            };
            //Filter out grants that match the default settings.
            const grantAccess = result.plugins[plugin.fileName].grantAccess;
            if (typeof grantAccess !== 'undefined') {
                result.plugins[plugin.fileName].grantAccess = _.pickBy(grantAccess, (allowed, actorId) => {
                    if (typeof actorId === 'undefined') {
                        return false;
                    }
                    const defaultState = this.getGrantAccessByDefault(actorId)() && plugin.isVisibleByDefault();
                    return (allowed !== defaultState);
                });
            }
            //Don't store the "grantAccess" map if it's empty.
            if (_.isEmpty(result.plugins[plugin.fileName].grantAccess)) {
                delete result.plugins[plugin.fileName].grantAccess;
            }
            //All plugins are visible by default, so it's not necessary to store this flag if it's TRUE.
            if (result.plugins[plugin.fileName].isVisibleByDefault) {
                delete result.plugins[plugin.fileName].isVisibleByDefault;
            }
            for (let i = 0; i < AmePlugin.editablePropertyNames.length; i++) {
                let key = AmePlugin.editablePropertyNames[i], upperKey = key.substring(0, 1).toUpperCase() + key.substring(1), value = plugin.customProperties[key]();
                if (value !== '') {
                    result.plugins[plugin.fileName]['custom' + upperKey] = value;
                }
            }
        });
        return result;
    }
    //noinspection JSUnusedGlobalSymbols Used in KO template.
    saveChanges() {
        const settings = this.getSettings();
        //Remove settings associated with roles and users that no longer exist or are not visible.
        const _ = AmePluginVisibilityModule._, visibleActorIds = _.invokeMap(this.actorSelector.getVisibleActors(), 'getId');
        _.forEach(settings.plugins, (plugin) => {
            if (plugin.grantAccess) {
                plugin.grantAccess = _.pick(plugin.grantAccess, visibleActorIds);
            }
        });
        //Remove plugins that don't have any custom settings.
        settings.plugins = _.pickBy(settings.plugins, (value) => {
            return !_.isEmpty(value);
        });
        //Populate form field(s).
        this.settingsData(JSON.stringify(settings));
        return true;
    }
}
AmePluginVisibilityModule._ = wsAmeLodash;
class AmePlugin {
    constructor(details, settings, module) {
        this.defaultProperties = {};
        this.customProperties = {};
        this.editableProperties = {};
        const _ = AmePluginVisibilityModule._;
        for (let i = 0; i < AmePlugin.editablePropertyNames.length; i++) {
            let key = AmePlugin.editablePropertyNames[i], upperKey = key.substring(0, 1).toUpperCase() + key.substring(1);
            this.defaultProperties[key] = ko.observable(_.get(details, key, ''));
            this.customProperties[key] = ko.observable(_.get(settings, 'custom' + upperKey, ''));
            this.editableProperties[key] = ko.observable(this.defaultProperties[key]());
        }
        this.name = ko.computed(() => {
            let value = this.customProperties['name']();
            if (value === '') {
                value = this.defaultProperties['name']();
            }
            return AmePlugin.stripAllTags(value);
        });
        this.description = ko.computed(() => {
            let value = this.customProperties['description']();
            if (value === '') {
                value = this.defaultProperties['description']();
            }
            return AmePlugin.stripAllTags(value);
        });
        this.fileName = details.fileName;
        this.isActive = details.isActive;
        this.isBeingEdited = ko.observable(false);
        this.isVisibleByDefault = ko.observable(_.get(settings, 'isVisibleByDefault', true));
        const emptyGrant = {};
        this.grantAccess = _.mapValues(_.get(settings, 'grantAccess', emptyGrant), (hasAccess) => {
            return ko.observable(hasAccess);
        });
        this.isChecked = ko.computed({
            read: () => {
                return module.isPluginVisible(this);
            },
            write: (isVisible) => {
                return module.setPluginVisibility(this, isVisible);
            }
        });
    }
    getGrantObservable(actorId, defaultValue = true) {
        if (!this.grantAccess.hasOwnProperty(actorId)) {
            this.grantAccess[actorId] = ko.observable(defaultValue);
        }
        return this.grantAccess[actorId];
    }
    //noinspection JSUnusedGlobalSymbols Used in KO template.
    openInlineEditor() {
        for (let i = 0; i < AmePlugin.editablePropertyNames.length; i++) {
            let key = AmePlugin.editablePropertyNames[i], customValue = this.customProperties[key]();
            this.editableProperties[key](customValue === '' ? this.defaultProperties[key]() : customValue);
        }
        this.isBeingEdited(true);
    }
    //noinspection JSUnusedGlobalSymbols Used in KO template.
    cancelEdit() {
        this.isBeingEdited(false);
    }
    //noinspection JSUnusedGlobalSymbols Used in KO template.
    confirmEdit() {
        for (let i = 0; i < AmePlugin.editablePropertyNames.length; i++) {
            let key = AmePlugin.editablePropertyNames[i], customValue = this.editableProperties[key]();
            if (customValue === this.defaultProperties[key]()) {
                customValue = '';
            }
            this.customProperties[key](customValue);
        }
        this.isBeingEdited(false);
    }
    //noinspection JSUnusedGlobalSymbols Used in KO template.
    resetNameAndDescription() {
        for (let i = 0; i < AmePlugin.editablePropertyNames.length; i++) {
            let key = AmePlugin.editablePropertyNames[i];
            this.customProperties[key]('');
        }
        this.isBeingEdited(false);
    }
    static stripAllTags(input) {
        //Based on: http://phpjs.org/functions/strip_tags/
        const tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi, commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
        return input.replace(commentsAndPhpTags, '').replace(tags, '');
    }
}
AmePlugin.editablePropertyNames = ['name', 'description', 'author', 'siteUrl', 'version'];
jQuery(function ($) {
    amePluginVisibility = new AmePluginVisibilityModule(wsPluginVisibilityData);
    ko.applyBindings(amePluginVisibility, document.getElementById('ame-plugin-visibility-editor'));
    //Permanently dismiss the usage hint via AJAX.
    jQuery('#ame-pv-usage-notice').on('click', '.notice-dismiss', function () {
        AjawV1.getAction('ws_ame_dismiss_pv_usage_notice').request();
    });
});
//# sourceMappingURL=plugin-visibility.js.map