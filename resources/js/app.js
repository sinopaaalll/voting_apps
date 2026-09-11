import './bootstrap';
import $ from 'jquery';
import select2 from 'select2';
import 'select2/dist/css/select2.css';

select2(window, $);

document.addEventListener('DOMContentLoaded', () => {
    const departmentSelect = $('.js-department-select');

    if (departmentSelect.length) {
        departmentSelect.select2({
            allowClear: true,
            minimumResultsForSearch: 0,
            placeholder: departmentSelect.data('placeholder'),
            width: '100%',
            language: {
                noResults: () => 'Department tidak ditemukan',
                searching: () => 'Mencari...',
            },
        });
    }

    const importForm = document.querySelector('[data-import-form]');
    const importButton = document.querySelector('[data-import-button]');
    const importLoading = document.querySelector('[data-import-loading]');

    importForm?.addEventListener('submit', () => {
        if (importButton) {
            importButton.disabled = true;
            importButton.textContent = 'Memulai import...';
        }
        if (importLoading) {
            importLoading.hidden = false;
        }
    });

    const selectAll = document.querySelector('[data-select-all]');
    const rowCheckboxes = Array.from(document.querySelectorAll('[data-row-checkbox]:not(:disabled)'));
    const bulkToolbar = document.querySelector('[data-bulk-toolbar]');
    const selectedCount = document.querySelector('[data-selected-count]');
    const bulkDelete = document.querySelector('[data-bulk-delete]');

    if (!selectAll || !bulkToolbar || !selectedCount || !bulkDelete) {
        return;
    }

    const updateBulkState = () => {
        const checked = rowCheckboxes.filter((checkbox) => checkbox.checked).length;
        selectedCount.textContent = String(checked);
        bulkToolbar.hidden = checked === 0;
        bulkDelete.disabled = checked === 0;
        selectAll.checked = rowCheckboxes.length > 0 && checked === rowCheckboxes.length;
        selectAll.indeterminate = checked > 0 && checked < rowCheckboxes.length;
    };

    selectAll.disabled = rowCheckboxes.length === 0;
    selectAll.addEventListener('change', () => {
        rowCheckboxes.forEach((checkbox) => {
            checkbox.checked = selectAll.checked;
        });
        updateBulkState();
    });
    rowCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', updateBulkState));
    updateBulkState();
});
