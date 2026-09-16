import './bootstrap';
import $ from 'jquery';
import select2 from 'select2';
import Swal from 'sweetalert2';

select2(window, $);

document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('[data-confirm-delete], [data-confirm-action]');

        if (!form || form.dataset.confirmationAccepted === 'true') {
            return;
        }

        event.preventDefault();
        const selectedOption = form.querySelector('[name="kandidat_id"] option:checked');
        const confirmationText = (form.dataset.confirmText || 'Data yang sudah dihapus tidak dapat dipulihkan.')
            .replace(':candidate', selectedOption?.textContent.trim() || 'kandidat yang dipilih');
        const result = await Swal.fire({
            title: form.dataset.confirmTitle || 'Hapus data ini?',
            text: confirmationText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: form.dataset.confirmButton || 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d92d20',
            cancelButtonColor: '#667085',
            focusCancel: true,
            reverseButtons: true,
            heightAuto: false,
        });

        if (result.isConfirmed) {
            form.dataset.confirmationAccepted = 'true';
            form.submit();
        }
    });

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

    const employmentStatusSelect = $('.js-employment-status-select');

    if (employmentStatusSelect.length) {
        employmentStatusSelect.select2({
            minimumResultsForSearch: Infinity,
            placeholder: employmentStatusSelect.data('placeholder'),
            width: '100%',
        });
    }

    const votingFilterSelect = $('.js-voting-filter-select');

    if (votingFilterSelect.length) {
        votingFilterSelect.select2({
            minimumResultsForSearch: Infinity,
            width: '100%',
        });
    }

    document.querySelectorAll('[data-image-upload]').forEach((imageUpload) => {
        const input = imageUpload.querySelector('[data-image-input]');
        const preview = imageUpload.querySelector('[data-image-preview]');
        const previewImage = imageUpload.querySelector('[data-image-preview-image]');
        const placeholder = imageUpload.querySelector('[data-image-placeholder]');
        const fileName = imageUpload.querySelector('[data-image-file-name]');

        input?.addEventListener('change', () => {
            const file = input.files?.[0];

            if (!file) {
                return;
            }

            fileName.textContent = file.name;
            fileName.title = file.name;
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                return;
            }

            const previewUrl = URL.createObjectURL(file);
            previewImage.src = previewUrl;
            previewImage.hidden = false;
            placeholder.hidden = true;
            preview.classList.add('has-image');
            previewImage.addEventListener('load', () => URL.revokeObjectURL(previewUrl), { once: true });
        });
    });

    const initializeBulkSelection = (scope = document) => {
        const selectAll = scope.querySelector('[data-select-all]');
        const rowCheckboxes = Array.from(scope.querySelectorAll('[data-row-checkbox]:not(:disabled)'));
        const bulkToolbar = scope.querySelector('[data-bulk-toolbar]');
        const selectedCount = scope.querySelector('[data-selected-count]');
        const bulkDelete = scope.querySelector('[data-bulk-delete]');

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
    };

    const employeeFilterForm = document.querySelector('[data-employee-filter-form]');
    const employeeSearch = document.querySelector('[data-employee-search]');
    const filterReset = document.querySelector('[data-filter-reset]');
    let employeeResults = document.querySelector('[data-employee-results]');
    let searchTimer;
    let filterRequest;

    const updateEmployeeResults = async () => {
        if (!employeeFilterForm || !employeeResults) {
            return;
        }

        window.clearTimeout(searchTimer);
        const parameters = new URLSearchParams(new FormData(employeeFilterForm));
        Array.from(parameters.entries()).forEach(([key, value]) => {
            if (!value.trim()) {
                parameters.delete(key);
            }
        });

        const url = `${employeeFilterForm.action}${parameters.size ? `?${parameters.toString()}` : ''}`;
        filterRequest?.abort();
        const currentRequest = new AbortController();
        filterRequest = currentRequest;
        employeeResults.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: currentRequest.signal,
            });

            if (!response.ok) {
                throw new Error('Pencarian employee gagal dimuat.');
            }

            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            const newResults = page.querySelector('[data-employee-results]');

            if (!newResults) {
                throw new Error('Hasil pencarian employee tidak ditemukan.');
            }

            employeeResults.replaceWith(newResults);
            employeeResults = newResults;
            initializeBulkSelection(employeeResults);
            window.history.replaceState({}, '', url);

            if (filterReset) {
                filterReset.hidden = parameters.size === 0;
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                window.location.assign(url);
            }
        } finally {
            if (filterRequest === currentRequest) {
                employeeResults?.removeAttribute('aria-busy');
            }
        }
    };

    if (employeeFilterForm) {
        employeeFilterForm.addEventListener('submit', (event) => {
            event.preventDefault();
            updateEmployeeResults();
        });
        employeeSearch?.addEventListener('keyup', () => {
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(updateEmployeeResults, 250);
        });
        departmentSelect.on('change', updateEmployeeResults);
        votingFilterSelect.on('change', updateEmployeeResults);
    }

    const candidateResultFilterForm = document.querySelector('[data-candidate-result-filter-form]');
    const candidateResultSearch = document.querySelector('[data-candidate-result-search]');
    const candidateResultReset = document.querySelector('[data-candidate-result-reset]');
    let candidateResultList = document.querySelector('[data-candidate-result-list]');
    let candidateSearchTimer;
    let candidateSearchRequest;

    const updateCandidateResults = async () => {
        if (!candidateResultFilterForm || !candidateResultList) {
            return;
        }

        window.clearTimeout(candidateSearchTimer);
        const parameters = new URLSearchParams(new FormData(candidateResultFilterForm));
        Array.from(parameters.entries()).forEach(([key, value]) => {
            if (!value.trim()) {
                parameters.delete(key);
            }
        });

        const url = `${candidateResultFilterForm.action}${parameters.size ? `?${parameters.toString()}` : ''}`;
        candidateSearchRequest?.abort();
        const currentRequest = new AbortController();
        candidateSearchRequest = currentRequest;
        candidateResultList.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: currentRequest.signal,
            });

            if (!response.ok) {
                throw new Error('Daftar pemilih kandidat gagal dimuat.');
            }

            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            const newResultList = page.querySelector('[data-candidate-result-list]');

            if (!newResultList) {
                throw new Error('Daftar pemilih kandidat tidak ditemukan.');
            }

            candidateResultList.replaceWith(newResultList);
            candidateResultList = newResultList;
            window.history.replaceState({}, '', url);
            candidateResultReset.hidden = parameters.size === 0;
        } catch (error) {
            if (error.name !== 'AbortError') {
                window.location.assign(url);
            }
        } finally {
            if (candidateSearchRequest === currentRequest) {
                candidateResultList?.removeAttribute('aria-busy');
            }
        }
    };

    if (candidateResultFilterForm) {
        candidateResultFilterForm.addEventListener('submit', (event) => {
            event.preventDefault();
            updateCandidateResults();
        });
        candidateResultSearch?.addEventListener('input', () => {
            window.clearTimeout(candidateSearchTimer);
            candidateSearchTimer = window.setTimeout(updateCandidateResults, 250);
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

    initializeBulkSelection();
});
