var config = {
    deps: [
        'Printess_PrintessDesigner/js/printessEditor',
        'Printess_PrintessDesigner/js/printessMagento'
    ],
    paths: {

    },
    map: {
        '*': {
            'printessEditor': 'Printess_PrintessDesigner/js/printessEditor',
            'printessMagento': 'Printess_PrintessDesigner/js/printessMagento',
            'Magento_Checkout/template/minicart/item/default.html': 'Printess_PrintessDesigner/template/minicart/item/default.html'
        }
    }
};
