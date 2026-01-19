const { Module } = Shopware;

Module.register('acris-discount-group-product-tab', {
    routeMiddleware(next, currentRoute) {
        if (currentRoute.name === 'sw.product.detail') {
            currentRoute.children.push({
                name: 'acris.discount.group.product.tab',
                path: '/sw/product/detail/:id/discount-group',
                component: 'acris-discount-group-product-tab',
                meta: {
                    parentPath: "sw.product.index"
                }
            });
        }
        next(currentRoute);
    }
});
