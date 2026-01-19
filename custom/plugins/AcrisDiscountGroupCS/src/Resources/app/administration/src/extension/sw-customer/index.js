const { Module } = Shopware;

Module.register('acris-discount-group-customer-tab', {
    routeMiddleware(next, currentRoute) {
        if (currentRoute.name === 'sw.customer.detail') {
            currentRoute.children.push({
                name: 'acris.discount.group.customer.tab',
                path: '/sw/customer/detail/:id/discount-group',
                component: 'acris-discount-group-customer-tab',
                meta: {
                    parentPath: "sw.customer.index"
                }
            });
        }
        next(currentRoute);
    }
});
