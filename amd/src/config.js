define([], function () {
    window.requirejs.config({
        baseUrl: M.cfg.wwwroot + '/blocks/stack/js/chartjs/dist/',
        paths: {
           chartjs : 'chart.min',
        }
    });
});