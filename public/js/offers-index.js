// document.addEventListener('DOMContentLoaded', () => {
//     document.getElementById('filter-toggle-btn').addEventListener('click', () => {
//         document.getElementById('filter-panel').classList.toggle('hidden');
//     });

//     document.querySelectorAll('input[name="date_preset"]').forEach(r => {
//         r.addEventListener('change', () => {
//             document.getElementById('custom-date-range').classList.toggle('hidden', r.value !== 'custom' || !r.checked);
//         });
//     });

//     document.querySelectorAll('.offer-status-cb').forEach(cb => {
//         cb.addEventListener('change', () => {
//             if (cb.value === 'all' && cb.checked) {
//                 document.querySelectorAll('.offer-status-cb').forEach(o => { if (o !== cb) o.checked = false; });
//             } else if (cb.checked) {
//                 document.querySelector('.offer-status-cb[value="all"]').checked = false;
//             }
//         });
//     });

//     document.getElementById('filter-apply-btn').addEventListener('click', () => reloadOffers(1));
//     document.getElementById('filter-reset-btn').addEventListener('click', () => {
//         document.querySelectorAll('.offer-status-cb').forEach(cb => cb.checked = cb.value === 'all');
//         document.querySelector('input[name="date_preset"][value="all"]').checked = true;
//         document.getElementById('custom-date-range').classList.add('hidden');
//         reloadOffers(1);
//     });

//     document.getElementById('offer-search').addEventListener('input', debounce(() => reloadOffers(1), 300));

//     document.getElementById('offers-table-body').addEventListener('click', handleTableClick);
//     document.addEventListener('click', (e) => {
//         if (!e.target.closest('.row-menu-trigger') && !e.target.closest('#row-menu')) closeRowMenu();
//     });

//     document.getElementById('archive-confirm-yes-btn').addEventListener('click', confirmArchive);
// });

// function debounce(fn, t) { let h; return (...a) => { clearTimeout(h); h = setTimeout(() => fn(...a), t); }; }

// function buildFilterParams(page = 1) {
//     const params = new URLSearchParams();
//     params.set('page', page);
//     const search = document.getElementById('offer-search').value.trim();
//     if (search) params.set('search', search);

//     const statuses = [...document.querySelectorAll('.offer-status-cb:checked')].map(cb => cb.value);
//     statuses.forEach(s => params.append('status[]', s));

//     const preset = document.querySelector('input[name="date_preset"]:checked')?.value || 'all';
//     params.set('date_preset', preset);
//     if (preset === 'custom') {
//         const from = document.getElementById('filter-from-date').value;
//         const to = document.getElementById('filter-to-date').value;
//         if (from) params.set('from_date', from);
//         if (to) params.set('to_date', to);
//     }
//     return params;
// }

// async function reloadOffers(page = 1) {
//     const params = buildFilterParams(page);
//     const res = await fetch(`${window.OFFERS_INDEX_URL}?${params.toString()}`, {
//         headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
//     }).then(r => r.json());

//     document.getElementById('offers-table-body').innerHTML = res.html;
//     document.getElementById('offers-pagination').innerHTML = res.pagination_html;
//     document.getElementById('archived-count').innerText = res.archived_count;
//     document.getElementById('filter-panel').classList.add('hidden');
// }

// /* ---------- row actions (Send Reminder / Create Quotation / Send Counter Offer) ---------- */
// function handleTableClick(e) {
//     const actionBtn = e.target.closest('.offer-action-btn');
//     if (actionBtn) {
//         const id = actionBtn.dataset.offerId;
//         const status = actionBtn.dataset.status;
//         if (status === 'sent') return sendReminder(id, actionBtn);
//         if (status === 'accepted') return window.location.href = `/vendor/quotations/create?offer_id=${id}`;
//         if (status === 'rejected' || status === 'expired') return window.location.href = `${window.OFFERS_CREATE_URL}?offer_id=${id}`;
//         return;
//     }

//     const menuTrigger = e.target.closest('.row-menu-trigger');
//     if (menuTrigger) {
//         e.stopPropagation();
//         openRowMenu(menuTrigger);
//     }
// }

// async function sendReminder(id, btn) {
//     btn.disabled = true;
//     const original = btn.innerText;
//     btn.innerText = 'Sending…';
//     try {
//         const res = await fetch(window.OFFERS_REMIND_URL_TEMPLATE.replace('__ID__', id), {
//             method: 'POST',
//             headers: { 'X-CSRF-TOKEN': window.CSRF_TOKEN, Accept: 'application/json' },
//         }).then(r => r.json());
//         alert(res.message || (res.success ? 'Reminder sent' : 'Failed to send reminder'));
//     } finally {
//         btn.disabled = false;
//         btn.innerText = original;
//     }
// }

// /* ---------- row "..." menu ---------- */
// let activeRowMenuOffer = null;

// function openRowMenu(trigger) {
//     const menu = document.getElementById('row-menu');
//     const rect = trigger.getBoundingClientRect();
//     menu.style.top = `${rect.bottom + window.scrollY + 4}px`;
//     menu.style.left = `${rect.right - 176 + window.scrollX}px`;
//     menu.classList.remove('hidden');
//     activeRowMenuOffer = {
//         id: trigger.dataset.offerId,
//         viewUrl: trigger.dataset.viewUrl,
//         modifyUrl: trigger.dataset.modifyUrl,
//     };
// }
// function closeRowMenu() { document.getElementById('row-menu').classList.add('hidden'); activeRowMenuOffer = null; }

// function rowMenuAction(action) {
//     if (!activeRowMenuOffer) return;
//     if (action === 'view') window.location.href = activeRowMenuOffer.viewUrl;
//     if (action === 'modify') window.location.href = activeRowMenuOffer.modifyUrl;
//     if (action === 'archive') {
//         document.getElementById('archiveConfirmModal').classList.remove('hidden');
//         document.getElementById('archiveConfirmModal').dataset.offerId = activeRowMenuOffer.id;
//     }
//     closeRowMenu();
// }

// function closeArchiveConfirm() { document.getElementById('archiveConfirmModal').classList.add('hidden'); }

// async function confirmArchive() {
//     const id = document.getElementById('archiveConfirmModal').dataset.offerId;
//     const btn = document.getElementById('archive-confirm-yes-btn');
//     btn.disabled = true;
//     try {
//         await fetch(window.OFFERS_ARCHIVE_URL_TEMPLATE.replace('__ID__', id), {
//             method: 'POST',
//             headers: { 'X-CSRF-TOKEN': window.CSRF_TOKEN, Accept: 'application/json' },
//         });
//         closeArchiveConfirm();
//         reloadOffers(1);
//     } finally {
//         btn.disabled = false;
//     }
// }

// function toggleArchivedSection() {
//     const section = document.getElementById('archived-section');
//     const chevron = document.getElementById('archived-chevron');
//     section.classList.toggle('hidden');
//     chevron.classList.toggle('rotate-180');
// }
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('filter-toggle-btn').addEventListener('click', () => {
        document.getElementById('filter-panel').classList.toggle('hidden');
    });

    document.querySelectorAll('input[name="date_preset"]').forEach(r => {
        r.addEventListener('change', () => {
            document.getElementById('custom-date-range').classList.toggle('hidden', r.value !== 'custom' || !r.checked);
        });
    });

    document.querySelectorAll('.offer-status-cb').forEach(cb => {
        cb.addEventListener('change', () => {
            if (cb.value === 'all' && cb.checked) {
                document.querySelectorAll('.offer-status-cb').forEach(o => { if (o !== cb) o.checked = false; });
            } else if (cb.checked) {
                document.querySelector('.offer-status-cb[value="all"]').checked = false;
            }
        });
    });

    document.getElementById('filter-apply-btn').addEventListener('click', () => reloadOffers(1));
    document.getElementById('filter-reset-btn').addEventListener('click', () => {
        document.querySelectorAll('.offer-status-cb').forEach(cb => cb.checked = cb.value === 'all');
        document.querySelector('input[name="date_preset"][value="all"]').checked = true;
        document.getElementById('custom-date-range').classList.add('hidden');
        reloadOffers(1);
    });

    document.getElementById('offer-search').addEventListener('input', debounce(() => reloadOffers(1), 300));

    document.getElementById('offers-table-body').addEventListener('click', handleTableClick);
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.row-menu-trigger') && !e.target.closest('#row-menu')) closeRowMenu();
    });

    document.getElementById('archive-confirm-yes-btn').addEventListener('click', confirmArchive);
});

function debounce(fn, t) { let h; return (...a) => { clearTimeout(h); h = setTimeout(() => fn(...a), t); }; }

function buildFilterParams(page = 1) {
    const params = new URLSearchParams();
    params.set('page', page);
    const search = document.getElementById('offer-search').value.trim();
    if (search) params.set('search', search);

    const statuses = [...document.querySelectorAll('.offer-status-cb:checked')].map(cb => cb.value);
    statuses.forEach(s => params.append('status[]', s));

    const preset = document.querySelector('input[name="date_preset"]:checked')?.value || 'all';
    params.set('date_preset', preset);
    if (preset === 'custom') {
        const from = document.getElementById('filter-from-date').value;
        const to = document.getElementById('filter-to-date').value;
        if (from) params.set('from_date', from);
        if (to) params.set('to_date', to);
    }
    return params;
}

async function reloadOffers(page = 1) {
    const params = buildFilterParams(page);
    const res = await fetch(`${window.OFFERS_INDEX_URL}?${params.toString()}`, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    }).then(r => r.json());

    document.getElementById('offers-table-body').innerHTML = res.html;
    document.getElementById('offers-pagination').innerHTML = res.pagination_html;
    document.getElementById('archived-count').innerText = res.archived_count;
    document.getElementById('filter-panel').classList.add('hidden');
}

/* ---------- row actions ---------- */
// function handleTableClick(e) {
//     const actionBtn = e.target.closest('.offer-action-btn');
//     if (actionBtn) {
//         const id = actionBtn.dataset.offerId;
//         const status = actionBtn.dataset.status;

//         // A pending customer modification request always routes to the modify
//         // screen, regardless of the offer's underlying status — this must be
//         // checked before the status-based branches below, since the button
//         // label/color was already swapped to "Modify Offer" server-side and
//         // the click behavior needs to match what the vendor is looking at.
//         if (actionBtn.dataset.hasModRequest === 'true') {
//             return window.location.href = `${window.OFFERS_CREATE_URL}?offer_id=${id}`;
//         }

//         if (status === 'sent') return sendReminder(id, actionBtn);
//         if (status === 'accepted') return window.location.href = `/vendor/quotations/create?offer_id=${id}`;
//         if (status === 'rejected' || status === 'expired') return window.location.href = `${window.OFFERS_CREATE_URL}?offer_id=${id}`;
//         return;
//     }

//     const menuTrigger = e.target.closest('.row-menu-trigger');
//     if (menuTrigger) {
//         e.stopPropagation();
//         openRowMenu(menuTrigger);
//     }
// }
// public/js/offers-index.js — handleTableClick(), add this branch first
function handleTableClick(e) {
    const actionBtn = e.target.closest('.offer-action-btn');
    if (actionBtn) {
        const id = actionBtn.dataset.offerId;
        const status = actionBtn.dataset.status;

        // Customer submitted their own version — vendor's action is a direct
        // accept, not a navigation to the modify screen.
        if (actionBtn.dataset.customerModified === 'true') {
            return acceptCustomerModification(id, actionBtn);
        }

        if (actionBtn.dataset.hasModRequest === 'true') {
            return window.location.href = `${window.OFFERS_CREATE_URL}?offer_id=${id}`;
        }

        if (status === 'sent') return sendReminder(id, actionBtn);
        if (status === 'accepted') return window.location.href = `/vendor/quotations/create?offer_id=${id}`;
        if (status === 'rejected' || status === 'expired') return window.location.href = `${window.OFFERS_CREATE_URL}?offer_id=${id}`;
        return;
    }

    const menuTrigger = e.target.closest('.row-menu-trigger');
    if (menuTrigger) {
        e.stopPropagation();
        openRowMenu(menuTrigger);
    }
}

async function acceptCustomerModification(id, btn) {
    if (!confirm("Accept the customer's modified offer? This will also reject other offers on this enquiry.")) return;
    btn.disabled = true;
    const original = btn.innerText;
    btn.innerText = 'Accepting…';
    try {
        const res = await fetch(window.OFFERS_ACCEPT_CUSTOMER_MOD_URL_TEMPLATE.replace('__ID__', id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': window.CSRF_TOKEN, Accept: 'application/json' },
        }).then(r => r.json());
        alert(res.message);
        if (res.success) reloadOffers(1);
    } finally {
        btn.disabled = false;
        btn.innerText = original;
    }
}

async function sendReminder(id, btn) {
    btn.disabled = true;
    const original = btn.innerText;
    btn.innerText = 'Sending…';
    try {
        const res = await fetch(window.OFFERS_REMIND_URL_TEMPLATE.replace('__ID__', id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': window.CSRF_TOKEN, Accept: 'application/json' },
        }).then(r => r.json());
        alert(res.message || (res.success ? 'Reminder sent' : 'Failed to send reminder'));
    } finally {
        btn.disabled = false;
        btn.innerText = original;
    }
}

/* ---------- row "..." menu ---------- */
let activeRowMenuOffer = null;

function openRowMenu(trigger) {
    const menu = document.getElementById('row-menu');
    const rect = trigger.getBoundingClientRect();
    menu.style.top = `${rect.bottom + window.scrollY + 4}px`;
    menu.style.left = `${rect.right - 176 + window.scrollX}px`;
    menu.classList.remove('hidden');
    activeRowMenuOffer = {
        id: trigger.dataset.offerId,
        viewUrl: trigger.dataset.viewUrl,
        modifyUrl: trigger.dataset.modifyUrl,
    };
}
function closeRowMenu() { document.getElementById('row-menu').classList.add('hidden'); activeRowMenuOffer = null; }

function rowMenuAction(action) {
    if (!activeRowMenuOffer) return;
    if (action === 'view') window.location.href = activeRowMenuOffer.viewUrl;
    if (action === 'modify') window.location.href = activeRowMenuOffer.modifyUrl;
    if (action === 'archive') {
        document.getElementById('archiveConfirmModal').classList.remove('hidden');
        document.getElementById('archiveConfirmModal').dataset.offerId = activeRowMenuOffer.id;
    }
    closeRowMenu();
}

function closeArchiveConfirm() { document.getElementById('archiveConfirmModal').classList.add('hidden'); }

async function confirmArchive() {
    const id = document.getElementById('archiveConfirmModal').dataset.offerId;
    const btn = document.getElementById('archive-confirm-yes-btn');
    btn.disabled = true;
    try {
        await fetch(window.OFFERS_ARCHIVE_URL_TEMPLATE.replace('__ID__', id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': window.CSRF_TOKEN, Accept: 'application/json' },
        });
        closeArchiveConfirm();
        reloadOffers(1);
    } finally {
        btn.disabled = false;
    }
}

function toggleArchivedSection() {
    const section = document.getElementById('archived-section');
    const chevron = document.getElementById('archived-chevron');
    section.classList.toggle('hidden');
    chevron.classList.toggle('rotate-180');
}
