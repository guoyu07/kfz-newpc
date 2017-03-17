(function (root, factory) {
    root.widgets || (root.widgets = {});
    if (typeof define === 'function' && define.amd) {
        // AMD. 注册匿名模块
        define(['widgets/nav/top/nav','widgets/category/main/category'], factory);
    } else {
        factory(root.widgets['nav/top/nav'],root.widgets['category/main/category'],root.widgets['banner/full/banner']);
    }
}(this, function (topNav,category,runImg) {
    topNav.init();
    category.config({id: 'category',scroll: true}).run();
    runImg.run();
}));