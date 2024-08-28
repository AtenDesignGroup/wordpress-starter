"use strict";
/// <reference path="../../js/jquery.d.ts" />
/// <reference path="../../js/actor-manager.ts" />
class AmeActorSelector {
    constructor(actorManager, isProVersion, allOptionEnabled = true) {
        this.selectedActor = null;
        this.selectedDisplayName = 'All';
        this.visibleUsers = [];
        this.subscribers = [];
        this.isProVersion = false;
        this.allOptionEnabled = true;
        this.cachedVisibleActors = null;
        this.specialActors = [];
        this.selectorNode = null;
        this.isDomInitStarted = false;
        this.actorManager = actorManager;
        if (typeof isProVersion !== 'undefined') {
            this.isProVersion = isProVersion;
        }
        this.allOptionEnabled = allOptionEnabled;
        this.currentUserLogin = wsAmeActorSelectorData.currentUserLogin;
        this.visibleUsers = wsAmeActorSelectorData.visibleUsers;
        this.ajaxParams = wsAmeActorSelectorData;
        //Discard any users that don't exist / were not loaded by the actor manager.
        const _ = AmeActorSelector._;
        this.visibleUsers = _.intersection(this.visibleUsers, _.keys(actorManager.getUsers()));
        jQuery(() => {
            this.initDOM();
        });
    }
    initDOM() {
        if (this.isDomInitStarted) {
            return;
        }
        this.isDomInitStarted = true;
        this.selectorNode = jQuery('#ws_actor_selector');
        this.populateActorSelector();
        //Don't show the selector in the free version.
        if (!this.isProVersion) {
            this.selectorNode.hide();
            return;
        }
        //Select an actor on click.
        this.selectorNode.on('click', 'li a.ws_actor_option', (event) => {
            const href = jQuery(event.target).attr('href');
            const fragmentStart = href.indexOf('#');
            let actor = null;
            if (fragmentStart >= 0) {
                actor = href.substring(fragmentStart + 1);
            }
            if (actor === '') {
                actor = null;
            }
            this.setSelectedActor(actor);
            event.preventDefault();
        });
        //Display the user selection dialog when the user clicks "Choose users".
        this.selectorNode.on('click', '#ws_show_more_users', (event) => {
            event.preventDefault();
            AmeVisibleUserDialog.open({
                currentUserLogin: this.currentUserLogin,
                users: this.actorManager.getUsers(),
                visibleUsers: this.visibleUsers,
                actorManager: this.actorManager,
                save: (userDetails, selectedUsers) => {
                    this.actorManager.addUsers(userDetails);
                    this.visibleUsers = selectedUsers;
                    //The user list has changed, so clear the cache.
                    this.cachedVisibleActors = null;
                    //Display the new actor list.
                    this.populateActorSelector();
                    //Save the user list via AJAX.
                    this.saveVisibleUsers();
                }
            });
        });
    }
    setSelectedActor(actorId) {
        if ((actorId !== null) && !this.actorManager.actorExists(actorId)) {
            return;
        }
        const previousSelection = this.selectedActor;
        this.selectedActor = actorId;
        this.highlightSelectedActor();
        if (actorId !== null) {
            const actor = this.actorManager.getActor(actorId);
            if (actor !== null) {
                this.selectedDisplayName = actor.getDisplayName();
            }
            else {
                this.selectedDisplayName = '[' + actorId + ']';
            }
        }
        else {
            this.selectedDisplayName = 'All';
        }
        //Notify subscribers that the selection has changed.
        if (this.selectedActor !== previousSelection) {
            for (let i = 0; i < this.subscribers.length; i++) {
                this.subscribers[i](this.selectedActor, previousSelection);
            }
        }
    }
    onChange(callback) {
        this.subscribers.push(callback);
    }
    highlightSelectedActor() {
        //Set up and populate the selector element if we haven't done that yet.
        if (!this.isDomInitStarted) {
            this.initDOM();
        }
        if (this.selectorNode === null) {
            return; //Should never happen since initDOM() should have set this.
        }
        //Deselect the previous item.
        this.selectorNode.find('.current').removeClass('current');
        //Select the new one or "All".
        let selector;
        if (this.selectedActor === null) {
            selector = 'a.ws_no_actor';
        }
        else {
            selector = 'a[href$="#' + this.selectedActor + '"]';
        }
        this.selectorNode.find(selector).addClass('current');
    }
    populateActorSelector() {
        if (this.selectorNode === null) {
            return; //Not initialized yet.
        }
        const actorSelector = this.selectorNode, $ = jQuery;
        let isSelectedActorVisible = false;
        //Build the list of available actors.
        actorSelector.empty();
        if (this.allOptionEnabled) {
            actorSelector.append('<li><a href="#" class="current ws_actor_option ws_no_actor" data-text="All">All</a></li>');
        }
        const visibleActors = this.getVisibleActors();
        for (let i = 0; i < visibleActors.length; i++) {
            const actor = visibleActors[i], name = this.getNiceName(actor);
            actorSelector.append($('<li></li>').append($('<a></a>')
                .attr('href', '#' + actor.getId())
                .attr('data-text', name)
                .text(name)
                .addClass('ws_actor_option')));
            isSelectedActorVisible = (actor.getId() === this.selectedActor) || isSelectedActorVisible;
        }
        if (this.isProVersion) {
            const moreUsersText = 'Choose users\u2026';
            actorSelector.append($('<li>').append($('<a></a>')
                .attr('id', 'ws_show_more_users')
                .attr('href', '#more-users')
                .attr('data-text', moreUsersText)
                .text(moreUsersText)));
        }
        if (this.isProVersion) {
            actorSelector.show();
        }
        //If the selected actor is no longer on the list, select the first available option instead.
        if ((this.selectedActor !== null) && !isSelectedActorVisible) {
            if (this.allOptionEnabled) {
                this.setSelectedActor(null);
            }
            else {
                const availableActors = this.getVisibleActors();
                const firstActor = AmeActorSelector._.head(availableActors);
                if (firstActor) {
                    this.setSelectedActor(firstActor.getId());
                }
                else {
                    this.setSelectedActor(null);
                }
            }
        }
        this.highlightSelectedActor();
    }
    repopulate() {
        this.cachedVisibleActors = null;
        this.populateActorSelector();
    }
    getVisibleActors() {
        if (this.cachedVisibleActors) {
            return this.cachedVisibleActors;
        }
        const _ = AmeActorSelector._;
        let actors = [];
        //Include special actors, if any. Note that these must also be added to the actor manager;
        //the actor selector doesn't do that automatically.
        _.forEach(this.specialActors, function (actor) {
            actors.push(actor);
        });
        //Include all roles.
        //Idea: Sort roles either alphabetically or by typical privilege level (admin, editor, author, ...).
        _.forEach(this.actorManager.getRoles(), function (role) {
            actors.push(role);
        });
        //Include the Super Admin (multisite only).
        const user = this.actorManager.getUser(this.currentUserLogin);
        if (user && user.isSuperAdmin) {
            actors.push(this.actorManager.getSuperAdmin());
        }
        //Include the current user.
        const currentUser = this.actorManager.getUser(this.currentUserLogin);
        if (currentUser) {
            actors.push(currentUser);
        }
        //Include other visible users.
        _(this.visibleUsers)
            .without(this.currentUserLogin)
            .sortBy()
            .forEach((login) => {
            const user = this.actorManager.getUser(login);
            if (user) {
                actors.push(user);
            }
        });
        this.cachedVisibleActors = actors;
        return actors;
    }
    addSpecialActor(actor) {
        this.specialActors.push(actor);
    }
    saveVisibleUsers() {
        jQuery.post(this.ajaxParams.adminAjaxUrl, {
            'action': this.ajaxParams.ajaxUpdateAction,
            '_ajax_nonce': this.ajaxParams.ajaxUpdateNonce,
            'visible_users': JSON.stringify(this.visibleUsers)
        });
    }
    getCurrentUserActor() {
        return this.actorManager.getUser(this.currentUserLogin);
    }
    getNiceName(actor) {
        let name = actor.getDisplayName();
        if (actor.hasOwnProperty('userLogin')) {
            const user = actor;
            if (user.userLogin === this.currentUserLogin) {
                name = 'Current user (' + user.userLogin + ')';
            }
            else {
                name = user.getDisplayName() + ' (' + user.userLogin + ')';
            }
        }
        return name;
    }
    /**
     * Wrap the selected actor ID in a computed observable so that it can be used with Knockout.
     * @param ko
     */
    createKnockoutObservable(ko) {
        const internalObservable = ko.observable(this.selectedActor);
        const publicObservable = ko.computed({
            read: function () {
                return internalObservable();
            },
            write: (newActor) => {
                this.setSelectedActor(newActor);
            }
        });
        this.onChange((newSelectedActor) => {
            internalObservable(newSelectedActor);
        });
        return publicObservable;
    }
    createIdObservable(ko) {
        return this.createKnockoutObservable(ko);
    }
    createActorObservable(ko) {
        const internalObservable = ko.observable((this.selectedActor === null) ? null : this.actorManager.getActor(this.selectedActor));
        const publicObservable = ko.computed({
            read: function () {
                return internalObservable();
            },
            write: (newActor) => {
                this.setSelectedActor((newActor !== null) ? newActor.getId() : null);
            }
        });
        const self = this;
        this.onChange(function (newSelectedActor) {
            if (newSelectedActor === null) {
                internalObservable(null);
            }
            else {
                internalObservable(self.actorManager.getActor(newSelectedActor));
            }
        });
        return publicObservable;
    }
    /**
     * Select an actor based on the "selected_actor" URL parameter.
     *
     * Does nothing if the parameter is not set or the actor ID is invalid.
     */
    setSelectedActorFromUrl() {
        if (!URLSearchParams) {
            return;
        }
        //Select an actor based on the "selected_actor" URL parameter.
        const urlParams = new URLSearchParams(window.location.search);
        const selectedActor = urlParams.get('selected_actor');
        if (selectedActor !== null) {
            this.setSelectedActor(selectedActor);
        }
    }
}
AmeActorSelector._ = wsAmeLodash;
//# sourceMappingURL=actor-selector.js.map