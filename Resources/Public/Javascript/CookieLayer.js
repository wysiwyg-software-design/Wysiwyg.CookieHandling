var WY = WY || {}

/**
 * WY.CookieLayer
 * GDPR Cookie Layer integration
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
     * via data (WY.CookieHandling.settings.cookieSettingsDataAttribute)
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
        changeCookiesSelector: '#changeCookies',
    },

    /**
     * Options which are necessary during the lifetime of this component.
     */
    options: {
        cookieLayerIsHideAble: false,
        cookieLayerIsDisabled: false,
    },

    /**
     * Initialized the cookie form.
     *
     * This function initializes all parts of the cookie form, to add all necessary events to
     * cookie groups and their children and also set their status.
     * After that the cookie layer is able to be closed and will be hidden.
     */
    initCookieForm: function () {
        this.initCookieGroupCheckboxes()
        this.initChildCookieCheckboxes()
        this.initSaveSelectionButton()
        this.initSaveAllButton()
    },

    initSaveSelectionButton: function () {
        const scope = this
        const saveSelectedCookiesButton = document.querySelector(this.elements.saveSelectedCookies)

        saveSelectedCookiesButton.addEventListener('click', function (event) {
            event.preventDefault()

            const groupCheckboxes = [].slice.call(document.querySelectorAll(
                'input.group-check'
            ))

            groupCheckboxes.forEach(function (groupCheckbox) {
                const cookieGroupName = groupCheckbox.dataset.group
                WY.CookieHandling.addCookieGroup(cookieGroupName)

                if (groupCheckbox.checked) {
                    WY.CookieHandling.acceptCookieGroup(cookieGroupName)
                    WY.CookieHandling.acceptAllCookiesFromGroup(cookieGroupName)
                } else {
                    const childCookies = [].slice.call(document.querySelectorAll(
                        '.single-check[data-group="' + cookieGroupName + '"]'
                    ))

                    childCookies.forEach(function (childCookie) {
                        const cookieName = childCookie.dataset['cookie-name']

                        if (childCookie.checked) {
                            WY.CookieHandling.acceptCookieInGroup(cookieName, cookieGroupName)
                        } else {
                            WY.CookieHandling.removeCookieInGroup(cookieName, cookieGroupName)
                        }
                    })
                }
            })

            scope.submitCookieForm()
        })

    },

    initSaveAllButton: function () {
        const scope = this
        const saveAllCookiesButton = document.querySelector(this.elements.saveAllCookies)

        saveAllCookiesButton.addEventListener('click', function (event) {
            event.preventDefault()

            for (let groupName in scope.cookieSettings) {
                WY.CookieHandling.acceptAllCookiesFromGroup(groupName)
            }

            scope.submitCookieForm()
        })
    },

    /**
     * Submits the CookieForm
     *
     * Saves the state of the consentCookie and adds a corresponding cookie.
     * Reinitialize the form and hides the cookieLayer.
     */
    submitCookieForm: function () {
        this.options.cookieLayerIsHideAble = true

        WY.CookieHandling.saveConsentCookie()
        WY.CookieHandling.removeUnacceptedCookie()

        this.hideCookieLayer()
    },

    /**
     * Initializes the cookie changer.
     *
     * The cookie changer is a simple element which will be enhanced with a click event to trigger
     * the modal open event.
     */
    initCookieChanger: function () {
        const scope = this
        const changeCookiesButton = document.querySelector(
            this.elements.changeCookiesSelector
        )

        if (changeCookiesButton) {
            changeCookiesButton.addEventListener('click', function () {
                document.querySelector(scope.elements.cookieModalElement).modal()
            })
        }
    },

    /**
     * Initializes cookie group checkboxes.
     *
     * Adds an event to all group checkboxes which automatically set the status of the cookie group to
     * its child cookies to enable or disable a cookie group at the same time.
     */
    initCookieGroupCheckboxes: function () {
        const scope = this

        const cookieGroups = [].slice.call(document.querySelectorAll(
            this.elements.cookieLayerSelector + ' input.group-check')
        )

        cookieGroups.forEach(function (cookieGroup) {
            if (!cookieGroup.checked) {
                cookieGroup.checked = WY.CookieHandling.cookieGroupIsAccepted(
                    cookieGroup.dataset.group
                )
            }

            cookieGroup.addEventListener('click', function () {
                const groupChecked = cookieGroup.checked
                const childElements = [].slice.call(document.querySelectorAll(
                    scope.elements.cookieLayerSelector +
                    ' input.single-check[data-group="' +
                    cookieGroup.dataset.group + '"]'
                ))

                childElements.forEach(function (childElement) {
                    childElement.checked = groupChecked
                })
            })
        })
    },

    /**
     * Initializes child cookie checkboxes.
     *
     * Every child cookie checkbox gets an event which activates their parent cookie group, if the
     * cookie has been activated / checked.
     */
    initChildCookieCheckboxes: function () {
        const childCookieCheckboxes = [].slice.call(document.querySelectorAll(
            this.elements.cookieLayerSelector + ' input.single-check'
        ))

        childCookieCheckboxes.forEach(function (childCookieCheckbox) {
            if (!childCookieCheckbox.checked) {
                childCookieCheckbox.checked = WY.CookieHandling.cookieIsAccepted(
                    childCookieCheckbox.dataset['cookie-name']
                )
            }

            childCookieCheckbox.addEventListener('click', function () {
                const parentCookieCheckbox = document.querySelector(
                    '.group-check[data-group="' +
                    childCookieCheckbox.dataset.group + '"]'
                )
                parentCookieCheckbox.checked = false
            })
        })
    },

    /**
     * Initializes the cookieLayer modal.
     *
     * Overrides the hide event of the modal to make the cookieLayer only closeable if
     * the consent cookie has been set.
     */
    initCookieModal: function () {
        const scope = this

        if (WY.CookieHandling._getCookie(this.settings.cookieName)) {
            this.options.cookieLayerIsHideAble = true
        }

        const cookieModal = document.querySelector(
            this.elements.cookieModalSelector
        )

        cookieModal.addEventListener('hide.bs.modal', function (event) {
            if (!scope.options.cookieLayerIsHideAble) {
                event.preventDefault()
            }
        })
    },

    /**
     * Initializes toggle buttons.
     *
     * These buttons show and hide detail-cookies
     */
    initCookieDetailToggle: function () {
        const showDetailsToggles = [].slice.call(document.querySelectorAll('.detail-anchor-show'))
        const hideDetailsToggles = [].slice.call(document.querySelectorAll('.detail-anchor-hide'))

        const showDetailsCallback = function (event) {
            event.preventDefault()
            event.target.hidden = true

            const detailPanel = document.getElementById(event.target.dataset.control)
            detailPanel.classList.remove('collapse')
            detailPanel.classList.add('collapsed')

            const hideDetails = document.querySelector(
                '.detail-anchor-hide[data-control=' + event.target.dataset.control + ']'
            )

            hideDetails.hidden = false
        }

        const hideDetailsCallback = function (event) {
            event.preventDefault()
            event.target.hidden = true

            const detailPanel = document.getElementById(event.target.dataset.control)
            detailPanel.classList.remove('collapsed')
            detailPanel.classList.add('collapse')

            const showDetails = document.querySelector(
                '.detail-anchor-show[data-control=' + event.target.dataset.control + ']'
            )

            showDetails.hidden = false
        }

        showDetailsToggles.forEach(function (showDetailsToggle) {
            showDetailsToggle.addEventListener('click', showDetailsCallback)
        })

        hideDetailsToggles.forEach(function (hideDetailsToggle) {
            hideDetailsToggle.addEventListener('click', hideDetailsCallback)
        })
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
        if (this.options.cookieLayerIsDisabled === true || this.options.cookieLayerIsDisabled === 'true') {
            return
        }

        if (WY.CookieHandling._getCookie(this.settings.cookieName) !== document.cookieHandling.consentCookieVersion) {
            this.openCookieLayer()
        }
    },

    /**
     * open the cookie layer without checking for the current state
     */
    openCookieLayer: function () {
        if (document.getElementById("backdrop")) {
            document.getElementById("backdrop").style.display = "block"
        }

        this.elements.cookieModalElement.style.display = "block"
        this.elements.cookieModalElement.classList.add("show")
    },

    /**
     * Helper function to hide the cookieLayer modal.
     */
    hideCookieLayer: function () {
        if (document.getElementById("backdrop")) {
            document.getElementById("backdrop").style.display = "none"
        }

        this.elements.cookieModalElement.style.display = "none"
        this.elements.cookieModalElement.classList.remove("show")
    },

    /**
     * This function watches for changes on the ConsentCookie,
     * to make sure that the cookie-layer is displaying a correct state of the consentCookie.
     */
    observeConsentCookieChanges: function () {
        var scope = this

        document.addEventListener(WY.CookieHandling.settings.cookieConsentChangedEventName, function () {
            scope.initCookieGroupCheckboxes()
            scope.initChildCookieCheckboxes()
        });
    },

    /**
     * Initializes CookieLayer.
     */
    init: function () {

        if (document.cookieHandling === undefined) {
            return
        }

        this.elements.cookieLayerElement = document.querySelector(this.elements.cookieLayerSelector)
        this.elements.cookieModalElement = document.querySelector(this.elements.cookieModalSelector)
        this.elements.cookieFormElement = document.querySelector(this.elements.cookieFormSelector)

        if (this.elements.cookieLayerElement) {
            WY.CookieHandling.init()

            this.cookieSettings = JSON.parse(document.cookieHandling.cookieSettings)
            this.options.cookieLayerIsDisabled = document.cookieHandling.cookieLayerDisabled
            this.options.isInBackend = false

            this.initCookieModal()
            this.initCookieForm()
            this.initCookieDetailToggle()
            this.initCookieChanger()

            this.showCookieLayer()
            this.observeConsentCookieChanges()
        }
    }
}

window.addEventListener('load', function () {
    WY.CookieLayer.init()
})
