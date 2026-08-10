/**
 * JavaScript Repeater Utility for Admin Forms (Intuitive & Robust)
 */
document.addEventListener('DOMContentLoaded', () => {
    // Helper to register remove events for existing rows
    const registerRemoveEvents = (selector, rowSelector) => {
        document.querySelectorAll(selector).forEach(btn => {
            btn.onclick = (e) => {
                e.preventDefault();
                const parent = btn.closest(rowSelector).parentElement;
                const rows = parent.querySelectorAll(rowSelector);
                if (rows.length > 1) {
                    btn.closest(rowSelector).remove();
                } else {
                    // If it is the last remaining row, reset input values instead of removing
                    btn.closest(rowSelector).querySelectorAll('input, select, textarea').forEach(input => {
                        input.value = '';
                    });
                }
            };
        });
    };

    // Generic function to initialize a repeater
    const initRepeater = (addButtonId, wrapperId, rowClass) => {
        const addButton = document.getElementById(addButtonId);
        const wrapper = document.getElementById(wrapperId);

        if (!addButton || !wrapper) return;

        addButton.addEventListener('click', (e) => {
            e.preventDefault();
            const rows = wrapper.querySelectorAll(`.${rowClass}`);
            if (rows.length === 0) return;

            // Clone the first row as template
            const templateRow = rows[0];
            const newRow = templateRow.cloneNode(true);

            // Clear values in the new row
            newRow.querySelectorAll('input, select, textarea').forEach(input => {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = false;
                } else if (input.tagName === 'SELECT') {
                    input.selectedIndex = 0;
                } else {
                    input.value = '';
                }
            });

            // Register remove button event for the new row
            const removeBtn = newRow.querySelector('.remove-row');
            if (removeBtn) {
                removeBtn.onclick = (ev) => {
                    ev.preventDefault();
                    const currentRows = wrapper.querySelectorAll(`.${rowClass}`);
                    if (currentRows.length > 1) {
                        newRow.remove();
                    } else {
                        newRow.querySelectorAll('input, select, textarea').forEach(inp => inp.value = '');
                    }
                };
            }

            wrapper.appendChild(newRow);
        });

        // Register for existing rows
        registerRemoveEvents(`#${wrapperId} .remove-row`, `.${rowClass}`);
    };

    // Initialize repeaters across all forms
    initRepeater('add-itinerary', 'itinerary-wrapper', 'itinerary-row');
    initRepeater('add-inclusion', 'inclusions-wrapper', 'inclusion-row');
    initRepeater('add-inclusion', 'inclusion-wrapper', 'inclusion-row');
    initRepeater('add-price', 'prices-wrapper', 'price-row');
    initRepeater('add-option', 'options-wrapper', 'option-row');
});
