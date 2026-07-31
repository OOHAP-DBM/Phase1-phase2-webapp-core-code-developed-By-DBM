// public/js/customer-offers-index.js
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('filter-toggle-btn').addEventListener('click', () => {
        document.getElementById('filter-panel').classList.toggle('hidden');
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
        reloadOffers(1);
    });

    document.getElementById('offer-search').addEventListener('input', debounce(() => reloadOffers(1), 300));
});

function debounce(fn, t) { let h; return (...a) => { clearTimeout(h); h = setTimeout(() => fn(...a), t); }; }

async function reloadOffers(page = 1) {
    const params = new URLSearchParams();
    params.set('page', page);
    const search = document.getElementById('offer-search').value.trim();
    if (search) params.set('search', search);
    [...document.querySelectorAll('.offer-status-cb:checked')].forEach(cb => params.append('status[]', cb.value));

    const res = await fetch(`${window.OFFERS_INDEX_URL}?${params.toString()}`, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    }).then(r => r.json());

    document.getElementById('offers-table-body').innerHTML = res.html;
    document.getElementById('offers-pagination').innerHTML = res.pagination_html;
    document.getElementById('filter-panel').classList.add('hidden');
}
// public/js/customer-offers-index.js — add these handlers to the existing file

document.addEventListener('click', async (e) => {
    const acceptBtn = e.target.closest('.customer-offer-accept-btn');
    if (acceptBtn) {
        if (!confirm('Accept this offer? This will reject any other offers on this enquiry.')) return;
        const id = acceptBtn.dataset.offerId;
        acceptBtn.disabled = true;
        try {
            const res = await fetch(`/customer/offers/${id}/accept`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': window.CSRF_TOKEN, Accept: 'application/json' },
            }).then(r => r.json());
            alert(res.message);
            if (res.success) reloadOffers(1);
        } finally {
            acceptBtn.disabled = false;
        }
        return;
    }

    const rejectBtn = e.target.closest('.customer-offer-reject-btn');
    if (rejectBtn) {
        const reason = prompt('Optional: reason for rejecting?') || '';
        const id = rejectBtn.dataset.offerId;
        rejectBtn.disabled = true;
        try {
            const res = await fetch(`/customer/offers/${id}/reject`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': window.CSRF_TOKEN, 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify({ reason }),
            }).then(r => r.json());
            alert(res.message);
            if (res.success) reloadOffers(1);
        } finally {
            rejectBtn.disabled = false;
        }
    }
});
