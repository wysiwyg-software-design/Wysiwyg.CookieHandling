var WY = WY || {};

/**
 * WY.SafeCookieHandling
 * jQuery Component to handle cookies safely.
 */
WY.CookieHandling = {
    /**
     * Settings for this component which can be overwritten for own use.
     *
     * Example: Override cookieLayerSelector
     *  WY.CookieHandling.settings.cookieLayerSelector = '#otherCookieLayerSelector'
     */
    settings: {
        cookieLayerSelector: '#cookieLayer',
        cookieSuffix: '_cookies',
        consentCookieName: 'cookieConsent',
        consentCookieDomain: null,
        cookieConsentChangedEventName: 'cookieConsentChanged'
    },

    /**
     * Main settings for cookies which is used for handling all cookies.
     * All functions to enable, disable cookies or cookieGroups use this settings
     * to not use unwanted cookies.
     */
    cookieSettings: {},

    /**
     * This object will be used to save the state of the consentCookie.
     * All cookies and their group will be added to this property and
     * saved to a cookie after calling saveConsentCookie()
     */
    consentCookie: {},

    /**
     * Enables a group of cookies.
     *
     * A cookieGroup will be enabled by adding a cookie with a specific prefix.
     * The following pattern will be used for the cookieGroupName:
     * {groupname}{prefix}
     *
     * Example:
     *  Input:
     *   cookieGroupToEnableName: test
     *   prefix: '_cookies'
     *
     *  Built Cookie: test_cookies
     *
     * @param cookieGroupToEnableName
     */
    addCookieGroup: function (cookieGroupToEnableName) {
        if (!cookieGroupToEnableName) {
            return;
        }
        this.consentCookie[cookieGroupToEnableName] = {
            cookies: [],
            accepted: false
        };
    },

    /**
     * Enables a group of cookies.
     *
     * A cookieGroup will be enabled by adding a cookie with a specific prefix.
     * The following pattern will be used for the cookieGroupName:
     * {groupname}{prefix}
     *
     * Example:
     *  Input:
     *   cookieGroupToEnableName: test
     *   prefix: '_cookies'
     *
     *  Built Cookie: test_cookies
     *
     * @param cookieGroupToEnableName
     */
    acceptCookieGroup: function (cookieGroupToEnableName) {
        this.consentCookie[cookieGroupToEnableName].accepted = true;
    },

    /**
     * Disables a group of cookies.
     *
     * A cookieGroup will be removed and all cookies in the given group aswell.
     *
     * @param cookieGroupToDisableName
     */
    removeCookieGroup: function (cookieGroupToDisableName) {
        if (!cookieGroupToDisableName) {
            return;
        }
        delete this.consentCookie[cookieGroupToDisableName];
    },

    /**
     * Accept a cookie of a group.
     *
     * This functions searches the settings for a cookie name and if any group has been found
     * the cookieGroup will be updated with the cookie.
     *
     * @param cookieToAcceptName
     * @param groupName
     */
    acceptCookieInGroup: function (cookieToAcceptName, groupName) {
        if (!cookieToAcceptName || !groupName) {
            return;
        }

        if (!this.cookieSettings.hasOwnProperty(groupName)) {
            return;
        }

        this.consentCookie[groupName].cookies.push(cookieToAcceptName);
    },

    /**
     * Disables a cookie from a group.
     *
     * This functions searches the settings for a cookie name and if any group has been found
     * for the given cookie will be removed from the cookieGroup.
     *
     * @param cookieToDisableName
     * @param groupName
     */
    removeCookieInGroup: function (cookieToDisableName, groupName) {
        if (!cookieToDisableName || !groupName) {
            return;
        }

        if (!this.cookieSettings.hasOwnProperty(groupName)) {
            return;
        }

        var cookieGroup = this.consentCookie[groupName],
            indexOfCookie = cookieGroup.cookies.indexOf(cookieToDisableName);

        if (indexOfCookie !== -1) {
            this.consentCookie[groupName].cookies = cookieGroup.splice(indexOfCookie, 1);
        }
    },

    /**
     * Check if a cookieGroup is enabled.
     *
     * This function checks if a cookie with the cookieGroup exists.
     * If a cookie exists, then the group is enabled, otherwise it's not.
     *
     * @param cookieGroupName
     * @returns {boolean}
     */
    cookieGroupIsAccepted: function (cookieGroupName) {
        if (!this.consentCookie.hasOwnProperty(cookieGroupName)) {
            return false;
        }
        return this.consentCookie[cookieGroupName].accepted;
    },

    /**
     * Check if a cookie is enabled.
     *
     * This function checks if a cookie with the cookieGroup exists.
     * If a cookie exists, then the group is enabled, otherwise it's not.
     *
     * @param cookieName
     * @param cookieGroupName
     * @returns {boolean}
     */
    cookieInGroupIsAccepted: function (cookieName, cookieGroupName) {

        if (!this.consentCookie.hasOwnProperty(cookieGroupName)) {
            return false;
        }

        if (!this.consentCookie[cookieGroupName].hasOwnProperty('cookies')) {
            return false;
        }

        return this.consentCookie[cookieGroupName].cookies.indexOf(cookieName) !== -1;
    },

    /**
     * Check if a cookie is enabled.
     *
     * This function checks if a cookie exists, by iterating over all
     * cookieGroups.
     *
     * @param cookieName
     * @returns {boolean}
     */
    cookieIsAccepted: function (cookieName) {
        var cookieGroups = Object.keys(this.consentCookie);

        for (var i = 0; i < cookieGroups.length; i++) {
            if (this.cookieInGroupIsAccepted(cookieName, cookieGroups[i])) {
                return true;
            }
        }

        return false;
    },

    /**
     * Enables all cookies from a group.
     *
     * This function activates all cookies from a group by just using the cookieGroupName.
     * It will then enable all configured child-cookies.
     *
     * @param cookieGroupName
     */
    acceptAllCookiesFromGroup: function (cookieGroupName) {
        this.addCookieGroup(cookieGroupName);
        this.acceptCookieGroup(cookieGroupName);

        for (var cookieKey in this.cookieSettings[cookieGroupName].cookies) {
            if (!this.cookieSettings[cookieGroupName].cookies.hasOwnProperty(cookieKey)) {
                continue;
            }

            var cookieName = this.cookieSettings[cookieGroupName].cookies[cookieKey].cookieName;
            WY.CookieHandling.acceptCookieInGroup(cookieName, cookieGroupName);
        }
    },

    /**
     * This functions should be used, to set a cookie.
     * It will check if a cookie has been accepted.
     * The cookie will be added, when there is a consent regarding this cookie.
     *
     * @param cookieName
     * @param cookieValue
     * @param path - is ignored!
     * @param expireDays
     * @param domain
     */
    tryAddCookie: function (cookieName, cookieValue, path, expireDays, domain) {
        if (this.cookieIsAccepted(cookieName)) {
            this._setCookie(cookieName, cookieValue, expireDays, domain);
        }
    },

    /**
     * Set a cookie.
     *
     * This functions adds directly a cookie with a given cookieName, cookieValue and lifetime in days.
     *
     * @param cookieName - cookie name
     * @param cookieValue - cookie value
     * @param expireDays - expiry in days
     * @param domain - domain to use, optional
     */
    _setCookie: function (cookieName, cookieValue, expireDays, domain) {
        var date = new Date();
        date.setTime(date.getTime() + (expireDays * 24 * 60 * 60 * 1000));
        var expires = "expires=" + date.toUTCString();
        var cookie = cookieName + "=" + cookieValue + ";" + expires + ";path=/";
        if (domain !== null) {
            cookie += ";domain=" + domain;
        }
        document.cookie = cookie;
    },

    /**
     * Get a cookie
     *
     *
     * @param cookieName - cookie name
     * @returns string
     */
    _getCookie: function (cookieName) {
        var rawCookieName = cookieName + "=";
        var rawCookies = document.cookie.split(';');
        for (var i = 0; i < rawCookies.length; i++) {
            var cookie = rawCookies[i];
            while (cookie.charAt(0) == ' ') {
                cookie = cookie.substring(1);
            }
            if (cookie.indexOf(rawCookieName) == 0) {
                return decodeURIComponent(cookie.substring(rawCookieName.length, cookie.length));
            }
        }
        return undefined;
    },

    /**
     * This function removes a specific cookie, by its name.
     * It also removes cookies from a domain to clean up safely from a domain.
     *
     * @param cookieName
     * @param domain
     */
    _removeCookie: function (cookieName, domain) {
        if (!domain) {
            domain = window.location.hostname;
        }
        document.cookie = cookieName + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; domain=' + domain + '; path=/;';

        var firstIndexOfDot = domain.indexOf('.'),
            lastIndexOfDot = domain.lastIndexOf('.');

        if (firstIndexOfDot !== -1 && firstIndexOfDot !== lastIndexOfDot) {
            this._removeCookie(cookieName, domain.substr(domain.indexOf('.') + 1))
        }
    },

    /**
     * Removes all cookies which haven't been accepted.
     *
     * Iterates through all cookies found in `document.cookie` and checks if
     * each cookie is accepted and deletes them if not accepted.
     */
    removeUnacceptedCookie: function () {
        var rawCookies = document.cookie.split(';');
        for (var i = 0; i < rawCookies.length; i++) {
            var cookie = rawCookies[i];
            while (cookie.charAt(0) == ' ') {
                cookie = cookie.substring(1);
            }

            var cookieName = decodeURIComponent(cookie.substr(0, cookie.indexOf('=')));

            if (!this.cookieIsAccepted(cookieName)) {
                this._removeCookie(cookieName);
            }
        }
    },

    /**
     * Saves the ConsentCookie-Object as a persistent cookie and triggers an event.
     */
    saveConsentCookie: function () {
        var previousCookieValue = this._getCookie(this.settings.consentCookieName);
        var newCookieValue = encodeURIComponent(JSON.stringify(this.consentCookie));

        this._setCookie(this.settings.consentCookieName, newCookieValue, this.getExpireDaysForTimeString(this.settings.consentCookieLifeTime), this.settings.consentCookieDomain);
        this._setCookie(this.settings.consentCookieAcceptedName, this.settings.consentCookieVersion, this.getExpireDaysForTimeString(this.settings.cookieConsentAcceptedLifetime), this.settings.consentCookieDomain);

        if (previousCookieValue !== newCookieValue) {
            var event;

            if (typeof window.CustomEvent === 'function') {
                event = new CustomEvent(this.settings.cookieConsentChangedEventName, {
                    detail: this.consentCookie
                });
            } else {
                event = document.createEvent('CustomEvent');
                event.initCustomEvent(this.settings.cookieConsentChangedEventName, true, true, this.consentCookie);
            }

            document.dispatchEvent(event);
        }
    },

    /**
     *
     * @param timeString
     * @returns {number}
     */
    getExpireDaysForTimeString: function (timeString) {
        var expireDays = 30;
        var months = 1;
        var years = 1;

        if (timeString === undefined) {
            return expireDays;
        }

        if (timeString.indexOf('months') !== -1) {
            months = timeString.replace(/(^\d+)(.+$)/i, '$1');
            expireDays = expireDays * months;
        }
        if (timeString.indexOf('years') !== -1) {
            years = timeString.replace(/(^\d+)(.+$)/i, '$1');
            expireDays = expireDays * months * (12 * years);
        }

        return expireDays;
    },

    /**
     * Updates the lifetime for a cookie, if the cookie by the given cookieName is outdated
     *
     * @param cookieName
     */
    updateCookieLifetimeForCookie: function (cookieName) {
        var updateLifetimeRequest = new XMLHttpRequest();
        updateLifetimeRequest.open('GET', '/cookie-services/cookies/update-lifetime?cookieName=' + cookieName);
        updateLifetimeRequest.send(null);
    },

    /**
     * Updates the lifetime for all cookies
     */
    updateCookieLifetimeForAllCookies: function () {
        var updateLifetimeRequest = new XMLHttpRequest();
        updateLifetimeRequest.open('GET', '/cookie-services/cookies/update-lifetimes');
        updateLifetimeRequest.send(null);
    },

    init: function () {
        if (document.cookieHandling === undefined) {
            return;
        }

        var savedCookie = this._getCookie(this.settings.consentCookieName) || '{}';
        this.consentCookie = JSON.parse(savedCookie);
        this.cookieSettings = JSON.parse(document.cookieHandling.cookieSettings);
        this.settings.consentCookieName = document.cookieHandling.consentCookieName;
        this.settings.consentCookieDomain = document.cookieHandling.consentCookieDomain;
        this.settings.consentCookieLifeTime = document.cookieHandling.consentCookieLifeTime;
        this.settings.consentCookieAcceptedName = document.cookieHandling.consentCookieAcceptedName;
        this.settings.consentCookieAcceptedLifetime = document.cookieHandling.consentCookieAcceptedLifetime;
        this.settings.consentCookieVersion = document.cookieHandling.consentCookieVersion;
    }
};
