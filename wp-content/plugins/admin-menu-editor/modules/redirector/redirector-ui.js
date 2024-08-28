"use strict";
/// <reference path="../../js/knockout.d.ts" />
/// <reference path="../../js/jquery.d.ts" />
/// <reference path="../../js/jqueryui.d.ts" />
/// <reference path="../../js/actor-manager.ts" />
/// <reference path="../actor-selector/actor-selector.ts" />
/// <reference path="../../js/common.d.ts" />
/// <reference path="../../vendor/yahnis-elsts/ajax-wrapper/ajax-action-wrapper.d.ts" />
var AmeRedirectorUi;
(function (AmeRedirectorUi) {
    const AllKnownTriggers = {
        login: null,
        logout: null,
        registration: null,
        firstLogin: null
    };
    const _ = wsAmeLodash;
    class AbstractTriggerDictionary {
    }
    const DefaultActorId = 'special:default';
    const defaultActor = {
        getDisplayName() {
            return 'Default';
        },
        getId() {
            return DefaultActorId;
        },
        isUser() {
            return false;
        },
        hasOwnCap(_) {
            return null;
        }
    };
    class Redirect {
        constructor(properties, actorProvider = null) {
            this.actorId = properties.actorId;
            this.trigger = properties.trigger;
            this.urlTemplate = ko.observable(properties.urlTemplate);
            this.menuTemplateId = ko.observable((typeof properties.menuTemplateId === 'string') ? properties.menuTemplateId : '');
            this.canToggleShortcodes = ko.pureComputed(() => {
                return (this.menuTemplateId().trim() === '');
            });
            this.inputHasFocus = ko.observable(false);
            const internalShortcodesEnabled = ko.observable(properties.shortcodesEnabled);
            this.shortcodesEnabled = ko.computed({
                read: () => {
                    //All of the menu items use shortcodes to generate the admin page URL,
                    //so shortcodes must be enabled when a menu item is selected.
                    const menu = this.menuTemplateId().trim();
                    if (menu !== '') {
                        return true;
                    }
                    return internalShortcodesEnabled();
                },
                write: (value) => {
                    if (!this.canToggleShortcodes()) {
                        return;
                    }
                    internalShortcodesEnabled(value);
                },
                deferEvaluation: true
            });
            if (this.actorId === DefaultActorId) {
                this.actor = defaultActor;
            }
            else {
                const provider = actorProvider ? actorProvider : AmeActors;
                const actor = provider.getActor(this.actorId);
                if (actor !== null) {
                    this.actor = actor;
                }
                else {
                    if (console && console.warn) {
                        console.warn('Redirect constructor - Actor not found: ', this.actorId);
                    }
                    const missingActorId = this.actorId;
                    this.actor = {
                        getDisplayName() {
                            return 'Missing role or user';
                        },
                        getId() {
                            return missingActorId;
                        },
                        isUser() {
                            return false;
                        },
                        hasOwnCap(_) {
                            return null;
                        }
                    };
                }
            }
            this.actorTypeNoun = ko.pureComputed(() => {
                const prefix = this.actorId.substring(0, this.actorId.indexOf(':'));
                if (prefix === 'user') {
                    return 'user';
                }
                else if (prefix === 'role') {
                    return 'role';
                }
                return 'item';
            });
            this.urlDropdownEnabled = ko.pureComputed(() => {
                //If a menu item is already selected in the dropdown, the dropdown has to be enabled
                //to give the user the ability to select something else.
                const menu = this.menuTemplateId().trim();
                if (menu !== '') {
                    return true;
                }
                //The dropdown only contains admin menu items, so it's only useful if the user
                //can access the admin dashboard after the trigger happens.
                //Note: This may need to change if we add other options to the dropdown.
                return (this.trigger === 'login') || (this.trigger === 'firstLogin');
            });
            Redirect.inputCounter++;
            this.inputElementId = 'ame-rui-unique-input-' + Redirect.inputCounter;
        }
        toJs() {
            let result = {
                actorId: this.actorId,
                urlTemplate: this.urlTemplate().trim(),
                shortcodesEnabled: this.shortcodesEnabled(),
                trigger: this.trigger
            };
            const menu = this.menuTemplateId().trim();
            if (menu !== '') {
                result.menuTemplateId = menu;
            }
            return result;
        }
        displayName() {
            if (this.actor.hasOwnProperty('userLogin')) {
                const user = this.actor;
                return user.userLogin;
            }
            else {
                return this.actor.getDisplayName();
            }
        }
    }
    Redirect.inputCounter = 0;
    AmeRedirectorUi.Redirect = Redirect;
    class TriggerView {
        constructor(trigger, supportsUserSettings = null, supportsRoleSettings = null) {
            this.users = ko.observableArray([]);
            this.roles = ko.observableArray([]);
            this.supportsUserSettings = true;
            this.supportsRoleSettings = true;
            if (supportsUserSettings !== null) {
                this.supportsUserSettings = supportsUserSettings;
            }
            if (supportsRoleSettings !== null) {
                this.supportsRoleSettings = supportsRoleSettings;
            }
            this.supportsActorSettings = ko.pureComputed(() => {
                return this.supportsUserSettings || this.supportsRoleSettings;
            });
            this.defaultRedirect = ko.observable(new Redirect({
                actorId: 'special:default',
                trigger: trigger,
                shortcodesEnabled: true,
                urlTemplate: ''
            }));
        }
        add(item) {
            const actorId = item.actorId;
            if (actorId === DefaultActorId) {
                this.defaultRedirect(item);
            }
            else if (actorId === 'special:super_admin') {
                this.roles.push(item);
            }
            else {
                const actorType = actorId.substring(0, actorId.indexOf(':'));
                switch (actorType) {
                    case 'user':
                        this.users.push(item);
                        break;
                    case 'role':
                        this.roles.push(item);
                        break;
                    default:
                        console.log('Unknown actor type for a trigger view: ' + actorType);
                }
            }
        }
        toArray() {
            let results = [];
            results.push(...this.users());
            results.push(...this.roles());
            //Include the default redirect only if it's not empty.
            const defaultRedirect = this.defaultRedirect();
            const url = defaultRedirect.urlTemplate().trim();
            if (url !== '') {
                results.push(defaultRedirect);
            }
            return results;
        }
    }
    class MenuCollection {
        constructor(usableMenuItems) {
            this.menusByTemplate = {};
            this.menusByTemplate = {};
            for (let i = 0; i < usableMenuItems.length; i++) {
                this.menusByTemplate[usableMenuItems[i].templateId] = usableMenuItems[i];
            }
        }
        findSelectedMenu(redirect) {
            const templateId = redirect.menuTemplateId();
            if (templateId === '') {
                return null;
            }
            if (!this.menusByTemplate.hasOwnProperty(templateId)) {
                return null;
            }
            const menu = this.menusByTemplate[templateId];
            const url = redirect.urlTemplate();
            if (menu.url === url) {
                return menu;
            }
            return null;
        }
    }
    class RedirectsByTrigger extends AbstractTriggerDictionary {
        constructor() {
            super();
            this.login = new TriggerView('login');
            this.logout = new TriggerView('logout');
            this.registration = new TriggerView('registration', false, false);
            this.firstLogin = new TriggerView('firstLogin', false, true);
        }
        static fromArray(redirects) {
            const instance = new RedirectsByTrigger();
            const length = redirects.length;
            for (let i = 0; i < length; i++) {
                const item = redirects[i];
                if (instance.hasOwnProperty(item.trigger)) {
                    const view = instance[item.trigger];
                    view.add(item);
                }
            }
            return instance;
        }
        toArray() {
            let results = [];
            let key;
            for (key in AllKnownTriggers) {
                if (this.hasOwnProperty(key)) {
                    const view = this[key];
                    results.push(...view.toArray());
                }
            }
            //Remove redirects that don't have a URL.
            results = results.filter(function (redirect) {
                const url = redirect.urlTemplate().trim();
                return ((typeof url) === 'string') && (url !== '');
            });
            return results;
        }
    }
    class RedirectUrlInputComponent {
        constructor(params) {
            this.redirect = ko.unwrap(params.redirect);
            this.menuItems = params.menuItems;
            this.displayValue = ko.computed({
                read: () => {
                    const menu = this.menuItems.findSelectedMenu(this.redirect);
                    if (menu) {
                        return menu.title;
                    }
                    else {
                        return this.redirect.urlTemplate();
                    }
                },
                write: (value) => {
                    const menu = this.menuItems.findSelectedMenu(this.redirect);
                    if (menu !== null) {
                        //Can't manually edit the URL because a menu item is selected.
                        return;
                    }
                    this.redirect.urlTemplate(value);
                }
            });
            this.isUrlReadonly = ko.pureComputed(() => {
                if (this.menuItems.findSelectedMenu(this.redirect) !== null) {
                    return true;
                }
                return null;
            });
        }
    }
    AmeRedirectorUi.RedirectUrlInputComponent = RedirectUrlInputComponent;
    /**
     * Proxy class that automatically creates placeholders for missing actors.
     */
    class ActorProviderProxy {
        constructor(realProvider) {
            this.provider = realProvider;
            this.placeholders = {};
        }
        getActor(actorId) {
            if (actorId === DefaultActorId) {
                return defaultActor;
            }
            const existingActor = this.provider.getActor(actorId);
            if (existingActor) {
                return existingActor;
            }
            else if (this.placeholders.hasOwnProperty(actorId)) {
                return this.placeholders[actorId];
            }
            //If the actor hasn't been loaded or created by now, that means it has been deleted
            //or it was invalid to begin with. Let's use a placeholder object to represent it.
            let missingActor;
            if (_.startsWith(actorId, 'user:')) {
                missingActor = new MissingUserPlaceholder(actorId);
            }
            else if (_.startsWith(actorId, 'role:')) {
                missingActor = new MissingRolePlaceholder(actorId);
            }
            else {
                missingActor = new MissingActorPlaceholder(actorId);
            }
            this.placeholders[actorId] = missingActor;
            return missingActor;
        }
    }
    class MinimalUser extends AmeUser {
        static createFromProperties(properties) {
            return new MinimalUser(properties.user_login, properties.display_name, {}, [], false);
        }
    }
    AmeRedirectorUi.MinimalUser = MinimalUser;
    class MissingActorPlaceholder {
        constructor(id, displayName = null) {
            this.actorId = id;
            if (displayName !== null) {
                this.displayName = displayName;
            }
            else {
                this.displayName = this.idWithoutPrefix(id);
            }
        }
        getDisplayName() {
            return this.displayName;
        }
        getId() {
            return this.actorId;
        }
        idWithoutPrefix(actorId) {
            const delimiterPos = actorId.indexOf(':');
            if (delimiterPos < 0) {
                return actorId;
            }
            return actorId.substring(delimiterPos + 1);
        }
        isUser() {
            return false;
        }
        hasOwnCap(_) {
            return null;
        }
    }
    class MissingRolePlaceholder extends MissingActorPlaceholder {
    }
    class MissingUserPlaceholder extends MissingActorPlaceholder {
        constructor(actorId) {
            super(actorId);
            this.isSuperAdmin = false;
            this.userLogin = this.idWithoutPrefix(actorId);
        }
        isUser() {
            return true;
        }
        getRoleIds() {
            return [];
        }
    }
    class App {
        constructor(settings) {
            this.isLoaded = ko.observable(false);
            this.availableTriggers = [
                { trigger: 'login', label: 'Login Redirect' },
                { trigger: 'logout', label: 'Logout Redirect' },
                { trigger: 'registration', label: 'Registration Redirect' },
                { trigger: 'firstLogin', label: 'First Login Redirect' }
            ];
            this.customUrlOption = {
                templateId: '',
                url: '',
                title: '[ Custom URL ]'
            };
            this.ignoreNextDropdownClick = null;
            this.userSelectionUi = 'dropdown';
            const self = this;
            this.actorProvider = new ActorProviderProxy(AmeActors);
            //Users need to be loaded before redirects because redirects use actor objects.
            let loadedUsers = settings.users.map((props) => {
                const existingInstance = AmeActors.getUser(props.user_login);
                if (existingInstance) {
                    return existingInstance;
                }
                else {
                    const newUser = MinimalUser.createFromProperties(props);
                    AmeActors.addUsers([newUser]);
                    return newUser;
                }
            });
            loadedUsers.sort(function (a, b) {
                return a.userLogin.localeCompare(b.userLogin);
            });
            this.redirects = ko.observableArray(settings.redirects.map(props => new Redirect(props, this.actorProvider)));
            this.menuItems = new MenuCollection(settings.usableMenuItems);
            this.menuDropdownOptions = [this.customUrlOption].concat(settings.usableMenuItems);
            this.menuDropdownParent = ko.observable(null);
            this.selectedMenuDropdownItem = ko.computed({
                read: () => {
                    const currentRedirect = this.menuDropdownParent();
                    if (currentRedirect === null) {
                        return this.customUrlOption;
                    }
                    else {
                        //Find the option that matches this template ID and URL.
                        let foundMenu = this.menuItems.findSelectedMenu(currentRedirect);
                        if (foundMenu === null) {
                            foundMenu = this.customUrlOption;
                        }
                        return foundMenu;
                    }
                },
                write: (newValue) => {
                    const currentRedirect = this.menuDropdownParent();
                    if (!currentRedirect) {
                        return; //Nothing to do!
                    }
                    if (!newValue) {
                        newValue = this.customUrlOption;
                    }
                    currentRedirect.menuTemplateId(newValue.templateId);
                    if (newValue.templateId !== '') {
                        currentRedirect.urlTemplate(newValue.url);
                    }
                },
                owner: self,
                deferEvaluation: true
            });
            this.menuDropdown = jQuery('#ame-rui-menu-items');
            //Hide the dropdown when it loses focus.
            this.menuDropdown.on('blur', () => {
                this.closeMenuDropdown();
            });
            this.menuDropdown.on('keydown', (event) => {
                //Also hide the dropdown if the user presses Esc.
                if (event.which === 27) {
                    this.closeMenuDropdown(true);
                }
                else if (event.which === 13) {
                    //Close the dropdown when the user presses Enter.
                    //Since we currently update the redirect on every change, there's no difference between
                    //this and pressing Esc.
                    this.closeMenuDropdown(true);
                }
            });
            //Close the dropdown when the user selects an option by clicking it.
            this.menuDropdown.on('click', 'option', () => {
                this.closeMenuDropdown();
            });
            //this.addTestData();
            this.byTrigger = ko.observable(RedirectsByTrigger.fromArray(this.redirects()));
            //Reselect the previous trigger, or just the first trigger.
            this.selectedTrigger = ko.observable(settings.selectedTrigger ? settings.selectedTrigger : this.availableTriggers[0].trigger);
            this.currentTriggerView = ko.pureComputed(() => {
                const trigger = this.selectedTrigger();
                const mapping = this.byTrigger();
                if (mapping.hasOwnProperty(trigger) && (mapping[trigger] instanceof TriggerView)) {
                    return mapping[trigger];
                }
                else {
                    return mapping.login;
                }
            });
            this.addableRoles = ko.pureComputed(() => {
                const allRoles = _.values(AmeActors.getRoles());
                const usedRoles = _.map(this.currentTriggerView().roles(), (redirect) => {
                    return redirect.actor;
                });
                return _.difference(allRoles, usedRoles);
            });
            this.selectedRoleToAdd = ko.observable(void 0);
            this.roleSelectorHasFocus = ko.observable(false);
            this.addableUsers = ko.pureComputed(() => {
                const usedUsers = _.map(this.currentTriggerView().users(), (redirect) => {
                    return redirect.actor;
                });
                return _.difference(loadedUsers, usedUsers);
            });
            this.selectedUserToAdd = ko.observable(void 0);
            this.userSelectorHasFocus = ko.observable(false);
            this.selectedRoleToAdd.subscribe((newSelection) => {
                this.addSelectedActorTo(newSelection, this.currentTriggerView().roles);
                this.roleSelectorHasFocus(false);
                this.selectedRoleToAdd(void 0);
            });
            this.selectedUserToAdd.subscribe((newSelection) => {
                this.addSelectedActorTo(newSelection, this.currentTriggerView().users);
                this.userSelectorHasFocus(false);
                this.selectedUserToAdd(void 0);
            });
            this.userLoginQuery = ko.observable('');
            this.addUserButtonEnabled = ko.pureComputed(() => {
                return (this.userLoginQuery().trim() !== '');
            });
            if (settings.hasMoreUsers) {
                this.userSelectionUi = 'search';
            }
            this.isSaving = ko.observable(false);
            this.settingsData = ko.observable('');
            this.isLoaded(true);
        }
        getSettings() {
            return {
                redirects: this.byTrigger().toArray().map(redirect => redirect.toJs())
            };
        }
        onDropdownTrigger(event) {
            //Note: There probably is some jQuery feature or library that makes dropdowns easier,
            //but I already did this the hard way.
            const $input = jQuery(event.target).closest('.ame-rui-url-template,ame-redirect-url-input').find('input').first();
            const $node = $input.closest('.ame-rui-redirect');
            if ($node.length < 1) {
                return;
            }
            const redirect = ko.dataFor($node.get(0));
            if (!(redirect instanceof AmeRedirectorUi.Redirect)) {
                return;
            }
            //Clicking the same trigger a second time closes the dropdown.
            if (event.type === 'mousedown') {
                const isSameTrigger = this.menuDropdown.is(':visible') && (this.menuDropdownParent() === redirect);
                if (isSameTrigger) {
                    //The dropdown will be automatically closed by its "blur" event handler,
                    //but we need to ignore the next click event on this element.
                    this.ignoreNextDropdownClick = event.target;
                }
                else {
                    this.ignoreNextDropdownClick = null;
                }
                return;
            }
            if ((event.type === 'click') && (event.target === this.ignoreNextDropdownClick)) {
                return;
            }
            //Move the drop-down near the input box.
            this.menuDropdown
                .css({
                position: 'absolute',
                zIndex: 100 //The dropdown should be displayed above other elements. This may not be required.
            })
                .show()
                .outerWidth(Math.max($input.outerWidth(), 100))
                .position({
                my: 'right top',
                at: 'right bottom',
                of: $input
            });
            //Move focus to the dropdown.
            let $select = this.menuDropdown;
            if (!this.menuDropdown.is('select, input')) {
                $select = this.menuDropdown.find('select, input').first();
            }
            $select.trigger('focus');
            //Select the current option and scroll it into view. It looks like the browser will automatically
            //scroll to the selected option, but only if the select element is already visible, so we need to
            //do this *after* we show the dropdown.
            this.menuDropdownParent(redirect);
        }
        closeMenuDropdown(moveFocusToInput = false) {
            const currentRedirect = this.menuDropdownParent();
            this.menuDropdown.hide();
            this.menuDropdownParent(null);
            //Refocus on the URL input after closing the dropdown.
            if (moveFocusToInput && currentRedirect) {
                currentRedirect.inputHasFocus(true);
            }
        }
        addSelectedActorTo(actor, list) {
            //The list includes a caption item that is displayed when nothing is selected.
            //The value of that option is supposed to be undefined.
            if ((typeof actor === 'undefined') || (actor === null) || !this.currentTriggerView()) {
                return;
            }
            //Add a redirect for the selected role.
            let newRedirect = new Redirect({
                actorId: actor.getId(),
                shortcodesEnabled: true,
                urlTemplate: '',
                trigger: this.selectedTrigger()
            }, this.actorProvider);
            list.push(newRedirect);
            newRedirect.inputHasFocus(true);
        }
        addEnteredUserLogin() {
            const userLogin = this.userLoginQuery().trim();
            if (userLogin === '') {
                return;
            }
            const actorId = 'user:' + userLogin;
            if (!AmeActors.actorExists(actorId)) {
                if (console && console.warn) {
                    console.warn('User "' + userLogin + '" has not been initialized. Creating a minimal actor now.');
                }
                AmeActors.addUsers([
                    MinimalUser.createFromProperties({
                        user_login: userLogin,
                        display_name: userLogin
                    })
                ]);
            }
            //Only add each user once.
            const alreadyAdded = _.some(this.currentTriggerView().users(), function (redirect) {
                return redirect.actorId === actorId;
            });
            if (alreadyAdded) {
                alert('Error: Duplicate entry. User "' + userLogin + '" has already been added.');
                return;
            }
            let newRedirect = new Redirect({
                actorId: actorId,
                shortcodesEnabled: true,
                urlTemplate: '',
                trigger: this.selectedTrigger()
            }, this.actorProvider);
            this.currentTriggerView().users.push(newRedirect);
            this.userLoginQuery('');
        }
        filterUserAutocompleteResults(results) {
            //Filter out users that are already in the current list.
            const usedLogins = _.keyBy(this.currentTriggerView().users(), (redirect) => {
                return redirect.actor.userLogin;
            });
            return _.filter(results, function (props) {
                return !(usedLogins.hasOwnProperty(props.user_login));
            });
        }
        isMissingActor(actor) {
            return (actor instanceof MissingActorPlaceholder);
        }
        saveChanges() {
            this.isSaving(true);
            this.settingsData(ko.toJSON(this.getSettings()));
            return true;
        }
        addTestData() {
            //Add some test data.
            this.redirects.push(new Redirect({
                actorId: 'role:editor',
                urlTemplate: '[wp-admin]edit.php',
                trigger: 'login',
                shortcodesEnabled: true
            }, this.actorProvider));
            this.redirects.push(new Redirect({
                actorId: 'role:author',
                urlTemplate: '[wp-admin]profile.php',
                trigger: 'login',
                shortcodesEnabled: true
            }, this.actorProvider));
            this.redirects.push(new Redirect({
                actorId: 'user:admin',
                urlTemplate: '[wp-admin]index.php',
                trigger: 'login',
                shortcodesEnabled: true
            }, this.actorProvider));
            this.redirects.push(new Redirect({
                actorId: 'role:contributor',
                urlTemplate: '[wp-admin]index.php',
                trigger: 'login',
                shortcodesEnabled: true
            }, this.actorProvider));
            this.redirects.push(new Redirect({
                actorId: 'role:nonexistent',
                urlTemplate: '[wp-admin]options-general.php',
                trigger: 'login',
                shortcodesEnabled: true
            }, this.actorProvider));
            this.redirects.push(new Redirect({
                actorId: 'user:notarealuser',
                urlTemplate: '[wp-admin]index.php',
                trigger: 'login',
                shortcodesEnabled: true
            }, this.actorProvider));
            this.redirects.push(new Redirect({
                actorId: DefaultActorId,
                urlTemplate: '[wp-admin]index.php?this-is-the-default=yep',
                trigger: 'login',
                shortcodesEnabled: true
            }, this.actorProvider));
            this.redirects.push(new Redirect({
                actorId: 'role:administrator',
                urlTemplate: '[wp-admin]options-general.php',
                trigger: 'login',
                shortcodesEnabled: true
            }, this.actorProvider));
        }
    }
    AmeRedirectorUi.App = App;
})(AmeRedirectorUi || (AmeRedirectorUi = {}));
jQuery(function ($) {
    ko.components.register('ame-redirect-url-input', {
        viewModel: AmeRedirectorUi.RedirectUrlInputComponent,
        template: { element: 'ame-redirect-url-component' }
    });
    //The user autocomplete feature is implemented as a custom binding only because that makes it easier
    //to correctly initialise it when Knockout changes the DOM. The binding is not intended to be reusable.
    ko.bindingHandlers.ameRuiUserAutocomplete = {
        init: function (element, valueAccessor) {
            let options = ko.unwrap(valueAccessor());
            options = wsAmeLodash.defaults(options, {
                filter: function (suggestions) {
                    return suggestions;
                }
            });
            jQuery(element).autocomplete({
                minLength: 2,
                source: function (request, response) {
                    const action = AjawV1.getAction('ws-ame-rui-search-users');
                    action.get({ term: request.term }, function (results) {
                        //Filter received users.
                        if (options.filter) {
                            results = options.filter(results);
                        }
                        response(results);
                    }, function (error) {
                        response([]);
                        if (console && console.error) {
                            console.error(error);
                        }
                    });
                },
                select: function (unusedEvent, ui) {
                    const props = ui.item;
                    const existingUser = AmeActors.getUser(props.user_login);
                    if (existingUser === null) {
                        AmeActors.addUsers([AmeRedirectorUi.MinimalUser.createFromProperties(props)]);
                    }
                },
                classes: {
                    'ui-autocomplete': 'ame-rui-found-users'
                }
            });
            ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
                jQuery(element).autocomplete('destroy');
            });
        }
    };
    const $container = jQuery('#ame-redirector-ui-root');
    const ameRedirectorApp = new AmeRedirectorUi.App(wsAmeRedirectorSettings);
    ko.applyBindings(ameRedirectorApp, $container.get(0));
    //Open the menu dropdown when the user clicks the trigger icon or presses
    //the down arrow key in the redirect input field.
    $container.on('mousedown click', '.ame-rui-url-dropdown-trigger', function (event) {
        ameRedirectorApp.onDropdownTrigger(event);
    });
    /*
    Releasing the "down" key only opens the dropdown if the key was pressed in the same input.
    This is to avoid a confusing situation where the user selects a role from the "add a role"
    dropdown using arrow keys and then the menu dropdown immediately shows up because the focus
    moved to the redirect input before the user could release the key.
    */
    const redirectInputSelector = '.ame-rui-url-template input[type=text].ame-rui-has-url-dropdown';
    let lastDownArrowTarget = null;
    $container.on('focus', redirectInputSelector, function () {
        lastDownArrowTarget = null;
    });
    $container.on('keydown', redirectInputSelector, function (event) {
        //Ignore repeated "keydown" events. These will happen even if the key was originally
        //pressed in a different element.
        if (event.originalEvent instanceof KeyboardEvent) {
            if ((typeof event.originalEvent['repeat'] !== 'undefined') && event.originalEvent['repeat']) {
                return;
            }
        }
        if (event.which === 40) {
            lastDownArrowTarget = event.target;
        }
    });
    $container.on('keyup', redirectInputSelector, function (event) {
        if ((event.which === 40) && (event.target === lastDownArrowTarget)) {
            ameRedirectorApp.onDropdownTrigger(event);
        }
    });
});
//# sourceMappingURL=redirector-ui.js.map