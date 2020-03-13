module.exports = {
    title: 'Cookie Handling',
    base: process.env.NODE_ENV === 'production'? '/Wysiwyg.CookieHandling/': '/',
    head: [
        ['link', { rel: 'icon', href: 'favicon/favicon.ico' }],
        ['link', { rel: 'shortcut icon', href: 'favicon/favicon.ico' }],
        ['link', { rel: 'apple-touch-icon', type:'image/png', href: 'favicon/apple-touch-icon' }],
        ['link', { rel: 'icon', type:'image/png', sizes:'32x32', href: 'favicon/favicon-32x32.png' }],
        ['link', { rel: 'icon', type:'image/png', sizes:'16x16', href: 'favicon/favicon-32x32.png' }],
        ['link', { rel: 'manifest', type: 'application/json', href: 'favicon/site.webmanifest' }],
        ['link', { rel: 'mask-icon', href: 'favicon/safari-pinned-tab.svg', color:'#005f83' }],
        ['meta', { name: 'theme-color', content: '#eee' }],
        ['meta', { name: 'msapplication-TileColor', content: '#eee' }],
        ['meta', { name: 'msapplication-config', content: 'favicon/browserconfig.xml' }]
    ],
    themeConfig: {
        nav: [
            {text: 'Github', link: 'https://github.com/wysiwyg-software-design/Wysiwyg.CookieHandling'}
        ],
        sidebar: [
            '/installation',
            '/usage',
            '/cookielayer',
            '/customization'
        ],
        theme: '@vuepress/theme-default',
        smoothScroll: true,
        docsRepo: 'wysiwyg-software-design/Wysiwyg.CookieHandling',
        docsDir: 'Documentation',
        docsBranch: 'master',
        editLinks: true,
        editLinkText: 'Help us improve this page!',
        lastUpdated: 'Last Updated',
        logo: 'wy_packages.svg'
    },

    plugins:
        [
            '@vuepress/medium-zoom'
        ]

};
