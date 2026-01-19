export const DiscountTypes = {
    PERCENTAGE: 'percentage',
    ABSOLUTE: 'absolute',
    FIXED: 'fixed',
    FIXED_UNIT: 'fixed_unit',
};

export const DiscountScopes = {
    CART: 'cart',
    DELIVERY: 'delivery',
    SET: 'set',
    SETGROUP: 'setgroup',
};

export const PromotionPermissions = {
    isEditingAllowed,
};


/**
 * @param {Object} promotion
 */
function isEditingAllowed(promotion) {
    if (promotion === null) {
        return false;
    }

    if (promotion === undefined) {
        return false;
    }

    return !promotion.hasOrders;
}

