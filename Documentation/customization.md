# Customization

If you don't like the included cookie layer there are straightforward ways to customize it or use your own. 

## Adjust the packaged cookie layer

The cookie layer elements are written in Atomic Fusion, which makes it possible to override every 
component according to your preferences.

 
For example, if the markup of a checkbox does not suit your needs you can override the fusion component `Wysiwyg.CookieHandling:Component.Atom.GroupCookieCheckbox`.  
An overview of all components can be found in "/Resources/Private/Fusion/Component/". 

## Implement your own cookie layer and scripts

This package has two main scripts to interact with cookies:
* CookieLayer.js (jQuery)
* CookieHandling.js (vanilla JavaScript)

You can implement your own script instead of using `CookieLayer.js`.

### Recommendations for your custom layer:
* Your layer should have cookie groups
  * every cookie group should contain the same key as in the configuration  (a data-attribute is preferable)
* Call `WY.CookieHandling.init();`
* Catch the submit event and handle your cookies
  * Iterate through all groups
    * call `WY.CookieHandling.addCookieGroup(cookieGroupName)`
  * Check if the group is accepted and call the following functions:
     * `WY.CookieHandling.acceptCookieGroup(cookieGroupName)` 
     * `WY.CookieHandling.acceptAllCookiesFromGroup(cookieGroupName)`
  * If the group is not accepted, iterate through all accepted childCookies and check if childCookie has been accepted:
    * Accept a cookie: `WY.CookieHandling.acceptCookieFromGroup(cookieName, cookieGroupName)`
    * Disable a cookie: `WY.CookieHandling.disableCookieFromGroup(cookieName, cookieGroupName)`
  * At the end call
    * `WY.CookieHandling.saveConsentCookie();`
    * `WY.CookieHandling.removeUnacceptedCookie();` (optionally: deal with cookies which are not accepted)


Your own JavaScript file must be added to your page AFTER the original CookieLayer.js
```html
<script type="text/javascript" src="{f:uri.resource(path: 'resource://Wysiwyg.CookieHandling/Public/Javascript/CookieLayer.js')}"></script>
<script type="text/javascript" src="{f:uri.resource(path: 'resource://Your.Package/Public/Javascript/CookieLayer.Modified.js')}"></script>
```
