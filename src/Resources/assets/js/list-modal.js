class ListModal {
    openModal(href, itemsWrapper) {
        fetch(href)
            .then((response) => {
                return response.text();
            })
            .then((resultHtml) => {
                let modelContent = document.createElement('div');
                let readerType = itemsWrapper.dataset.listModalReaderType;
                let readerCssSelector = itemsWrapper.dataset.listModalReaderCssSelector;
                let readerModule = itemsWrapper.dataset.listModalReaderModule;
                let modalId = 'modal-' + itemsWrapper.closest('.wrapper').getAttribute('id');

                let reader = null;

                modelContent.innerHTML = resultHtml.trim();

                switch (readerType) {
                    case 'huh_reader':
                        reader = modelContent.querySelector('#huh-reader-' + readerModule);

                        if (null === reader) {
                            console.warn('Reader not found with selector: #huh-reader-' + readerModule);
                            return;
                        }

                        break;
                    case 'css_selector':
                        reader = modelContent.querySelector(readerCssSelector);

                        if (null === reader) {
                            console.warn('Reader not found with selector: ' + readerCssSelector);
                            return;
                        }

                        break;
                }

                if (null === reader) {
                    return;
                }

                let modalElement = document.getElementById(modalId);
                modalElement.querySelector('.modal-content .modal-body').innerHTML = reader.outerHTML;

                let head = document.getElementsByTagName("head")[0] || document.documentElement;
                modalElement.querySelectorAll('.modal-content .modal-body script').forEach(function(element) {
                    let script = document.createElement("script");
                    Array.from(element.attributes).forEach(attr => script.setAttribute(attr.name, attr.value));
                    script.appendChild(document.createTextNode(element.innerHTML || element.innerText));
                    head.insertBefore(script, head.firstChild);
                });

                // bootstrap 4 and below
                if ('undefined' !== typeof window.jQuery) {
                    window.jQuery('#' + modalId).modal('show');
                } else {
                    // bootstrap 5 and up
                    import(/* webpackChunkName: "bootstrap" */ 'bootstrap').then((bootstrap) => {
                        bootstrap.Modal.getInstance(modalElement).show();
                    });
                }

                item.dispatchEvent(new CustomEvent('huh.list.modal_show', {
                    bubbles: true,
                    detail: {
                        modalElement: modalElement,
                        modalId: modalId
                    }
                }));
            })
            .catch((error) => {
                // item.dispatchEvent(new CustomEvent('huh.list.modal_load_error', {
                //     bubbles: true,
                //     detail: {
                //         statusCode: request.status,
                //         statusText: request.statusText,
                //         response: request.response,
                //         responseText: request.responseText,
                //         url: request.responseURL
                //     }
                // }));
                console.log(error);
            });
    }
}

export default ListModal;