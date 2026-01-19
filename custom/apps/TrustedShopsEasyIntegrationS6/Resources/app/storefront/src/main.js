const widgetExtension = document.getElementsByTagName('etrusted-product-review-list-widget-product-star-extension');

if(widgetExtension.length) {
    const element = widgetExtension[0];

    element.addEventListener('click', function (e) {
        const tabs = document.getElementsByClassName('product-detail-tab-navigation-link');

        for(let node of tabs) {
            if(node.id === 'ts-review-tab') {
                node.classList.add('active');
            } else {
                node.classList.remove('active');
            }
        }

        const tabPanes = document.getElementsByClassName('tab-pane');

        for(let node of tabPanes) {
            if(node.id === 'ts-review-tab-pane') {
                node.classList.add('active', 'show');
                node.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            } else {
                node.classList.remove('active', 'show');
            }
        }

    });
}