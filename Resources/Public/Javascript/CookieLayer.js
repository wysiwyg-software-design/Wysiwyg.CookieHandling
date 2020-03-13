var WY = WY || {};

/**
 * WY.SafeCookieLayer
 * jQuery SafeCookie Layer integration.
 */
WY.CookieLayer = {

    /**
     * General settings for cookie attributes used by this component.
     */
    settings: {
        cookieSettingsDataAttribute: 'cookie-groups',
        cookieName: 'cookieConsentAccepted',
    },

    /**
     * CookieSettings which are set dynamically from the template
     * via data (WY.SafeCookieLayer.settings.cookieSettingsDataAttribute)
     */
    cookieSettings: {},

    /**
     * All necessary selectors and elements for the cookieLayer.
     */
    elements: {
        cookieLayerSelector: '#cookieLayer',
        cookieLayerElement: undefined,
        cookieModalSelector: '#modal-cookie',
        cookieModalElement: undefined,
        cookieFormSelector: '#cookieForm',
        cookieFormElement: undefined,
        saveSelectedCookies: '#saveCookies',
        saveAllCookies: '#saveAllCookies',
    },

    /**
     * Options which are necessary during the lifetime of this component.
     */
    options: {
        cookieLayerIsHideAble: false,
        cookieLayerIsDisabled: false
    },

    /**
     * Initialized the cookie form.
     *
     * This function initializes all parts of the cookie form, to add all necessary events to
     * cookie groups and their children and also set their status.
     * After that the cookie layer is able to be closed and will be hidden.
     */
    initCookieForm: function () {
        this.initCookieGroupCheckboxes();
        this.initChildCookieCheckboxes();
        this.initSaveSelectionButton();
        this.initSaveAllButton();
    },

    initSaveSelectionButton: function(){
        var scope = this;

        $(this.elements.saveSelectedCookies).on('click', function (event) {
            event.preventDefault();
            var groupCheckboxes = $(scope.elements.cookieFormElement).find('input.group-check');

            $(groupCheckboxes).each(function () {
                var cookieGroupName = $(this).data('group');
                WY.CookieHandling.addCookieGroup(cookieGroupName);

                if (this.checked) {
                    WY.CookieHandling.acceptCookieGroup(cookieGroupName);
                    WY.CookieHandling.acceptAllCookiesFromGroup(cookieGroupName);
                } else {
                    var childCookies = $('.single-check[data-group="' + cookieGroupName + '"]');

                    $(childCookies).each(function () {
                        if (this.checked) {
                            WY.CookieHandling.acceptCookieInGroup($(this).data('cookie-name'), cookieGroupName);
                        } else {
                            WY.CookieHandling.removeCookieInGroup($(this).data('cookie-name'), cookieGroupName);
                        }
                    });
                }
            });
            scope.submitCookieForm();
        });
    },

    initSaveAllButton: function(){
        var scope = this;

        $(this.elements.saveAllCookies).on('click', function (event) {
            event.preventDefault();
            for (var groupName in scope.cookieSettings) {
                WY.CookieHandling.acceptAllCookiesFromGroup(groupName);
            }
            scope.submitCookieForm();
        });
    },

    /**
     * Submits the CookieForm
     *
     * Saves the state of the consentCookie and adds a corresponding cookie.
     * Reinitialize the form and hides the cookieLayer.
     */
    submitCookieForm: function () {
        this.options.cookieLayerIsHideAble = true;
        WY.CookieHandling.saveConsentCookie();
        WY.CookieHandling.removeUnacceptedCookie();
        this.hideCookieLayer();
    },

    /**
     * Initializes the cookie changer.
     *
     * The cookie changer is a simple element which will be enhanced with a click event to trigger
     * the modal open event.
     */
    initCookieChanger: function () {
        var scope = this;
        $('#changeCookies').on({
            click: function () {
                $(scope.elements.cookieModalElement).modal();
            }
        });
    },

    /**
     * Initializes cookie group checkboxes.
     *
     * Adds an event to all group checkboxes which automatically set the status of the cookie group to
     * its child cookies to enable or disable a cookie group at the same time.
     */
    initCookieGroupCheckboxes: function () {
        var scope = this;
        var cookieGroups = $(this.elements.cookieLayerSelector + ' input.group-check');

        $(cookieGroups).each(function () {
            if (!this.checked) {
                this.checked = WY.CookieHandling.cookieGroupIsAccepted($(this).data('group'));
            }

            $(this).on({
                'click': function () {
                    var childElements = $(scope.elements.cookieLayerSelector + ' input.single-check[data-group="' + $(this).data('group') + '"]'),
                        groupChecked = this.checked;

                    $(childElements).each(function () {
                        this.checked = groupChecked;
                    })
                }
            })
        });
    },

    /**
     * Initializes child cookie checkboxes.
     *
     * Every child cookie checkbox gets an event which activates their parent cookie group, if the
     * cookie has been activated / checked.
     */
    initChildCookieCheckboxes: function () {
        var childCookieCheckboxes = $(this.elements.cookieLayerSelector + ' input.single-check');

        $(childCookieCheckboxes).each(function () {
            if (!this.checked) {
                this.checked = WY.CookieHandling.cookieIsAccepted($(this).data('cookie-name'));
            }

            $(this).on({
                click: function(){
                    $('.group-check[data-group="'+ $(this).data('group')+'"]')[0].checked = false;
                }
            });
        });
    },

    /**
     * Initializes the cookieLayer modal.
     *
     * Overrides the hide event of the modal to make the cookieLayer only closeable if
     * the consent cookie has been set.
     */
    initCookieModal: function () {
        var scope = this;

        if (WY.CookieHandling._getCookie(this.settings.cookieName)) {
            this.options.cookieLayerIsHideAble = true;
        }

        $(this.elements.cookieModalSelector).on({
            'hide.bs.modal': function (event) {
                if (!scope.options.cookieLayerIsHideAble) {
                    event.preventDefault();
                }
            }
        });
    },

    /**
     * Initializes toggle buttons.
     *
     * These buttons shows and hides detail-cookies.
     */
    initCookieDetailToggle: function(){
        $('.detail-anchor-show').off().on({
            click: function (event) {
                event.preventDefault();
                $(this).hide();
                var detailPanel = $('#' + $(this).data('control'));
                $(detailPanel).removeClass('collapse');
                $(detailPanel).addClass('collapsed');
                $('.detail-anchor-hide[data-control=' + $(this).data('control') + ']').show();
            }
        });

        $('.detail-anchor-hide').off().on({
            click: function (event) {
                event.preventDefault();
                $(this).hide();
                var detailPanel = $('#' + $(this).data('control'));
                $(detailPanel).removeClass('collapsed');
                $(detailPanel).addClass('collapse');
                $('.detail-anchor-show[data-control=' + $(this).data('control') + ']').show();
            }
        });
    },

    /**
     * Helper function to show the cookieLayer modal.
     *
     * A modal won't be opened in the following cases:
     *  - if the cookielayer has been disabled for the page (for example on privacy pages)
     *  - in Neos backend
     *  - the consentCookie has been set
     *
     */
    showCookieLayer: function () {
        if (this.options.cookieLayerIsDisabled === true) {
            return;
        }

        if (WY.CookieHandling._getCookie(this.settings.cookieName) !== document.cookieHandling.consentCookieVersion) {
            $(this.elements.cookieModalElement).modal();
        }
    },

    /**
     * open the cookie layer without checking for the current state
     */
    openCookieLayer: function () {
        $(this.elements.cookieModalElement).modal();
    },

    /**
     * Helper function to hide the cookieLayer modal.
     */
    hideCookieLayer: function () {
        $(this.elements.cookieModalElement).modal('hide');
    },

    /**
     * This function watches for changes on the ConsentCookie,
     * to make sure that the cookie-layer is displaying a correct state of the consentCookie.
     */
    observeConsentCookieChanges: function () {
        var scope = this;

        document.addEventListener(WY.CookieHandling.settings.cookieConsentChangedEventName, function (evt) {
            scope.initCookieGroupCheckboxes();
            scope.initChildCookieCheckboxes();
        });
    },

    /**
     * Initializes CookieLayer.
     */
    init: function () {

        if(document.cookieHandling === undefined) {
            return;
        }

        this.elements.cookieLayerElement = $(this.elements.cookieLayerSelector);
        this.elements.cookieModalElement = $(this.elements.cookieModalSelector);
        this.elements.cookieFormElement = $(this.elements.cookieFormSelector);

        if (this.elements.cookieLayerElement.length) {
            WY.CookieHandling.init();

            this.cookieSettings = JSON.parse(document.cookieHandling.cookieSettings);
            this.options.cookieLayerIsDisabled = document.cookieHandling.cookieLayerDisabled;
            this.options.isInBackend = false;

            this.initCookieModal();
            this.initCookieForm();
            this.initCookieDetailToggle();
            this.initCookieChanger();

            this.showCookieLayer();
            this.observeConsentCookieChanges();
        }
    }
};

$(document).ready(function () {
    WY.CookieLayer.init();
});
