let mix = require('laravel-mix');
let build = require('./tasks/build.js');

mix.disableSuccessNotifications();
mix.setPublicPath('source/assets/build');
mix.webpackConfig({
    plugins: [
        build.jigsaw,
        build.browserSync(),
        build.watch(['source/**/*.md', 'source/**/*.php', 'source/_assets/**/*.css', '!source/**/_tmp/*']),
    ]
});

mix.js('source/_assets/js/main.js', 'js')
    .postCss('source/_assets/css/main.css', 'css', [
        require('tailwindcss'),
    ])
    // .options({
    //     processCssUrls: false,
    // })
    .version();
