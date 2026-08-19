// const selected = new Map();          // hoarding_id -> item
// let hoardingsCache = [];
// let currentPage = 1, totalPages = 1;
// let heatmapMap = {}, currentEditId = null, dpFp = null;

// const fmt = v => new Intl.NumberFormat('en-IN',{style:'currency',currency:'INR',maximumFractionDigits:0}).format(v||0);
// const toYMD = d => { d=new Date(d); return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; };
// // const monthsBetween = (s,e) => Math.max(1, Math.ceil((Math.ceil((new Date(e)-new Date(s))/86400000)+1)/30));
// const monthsBetween = (s, e) => {
//     if (!s || !e) return 1;

//     const start = new Date(s);
//     const end = new Date(e);

//     let months =
//         (end.getFullYear() - start.getFullYear()) * 12 +
//         (end.getMonth() - start.getMonth());

//     if (end.getDate() > start.getDate()) {
//         months += 1;
//     }

//     return Math.max(1, months);
// };
// const fetchJSON = (url,opts={}) => fetch(url,{
//     headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':window.CSRF_TOKEN,'X-Requested-With':'XMLHttpRequest'},
//     ...opts,
// }).then(r=>r.json());
// function debounce(fn,t){let h;return(...a)=>{clearTimeout(h);h=setTimeout(()=>fn(...a),t);};}

// /* ---------- init ---------- */
// document.addEventListener('DOMContentLoaded', () => {
//     (window.OFFER_SEED_ITEMS || []).forEach(it => selected.set(it.hoarding_id, it));
//     renderSummary();
//     loadHoardings();

//     document.getElementById('hoarding-search').addEventListener('input', debounce(() => { currentPage = 1; loadHoardings(); }, 250));
//     document.getElementById('offer-preview-btn').addEventListener('click', openPreview);
//     // document.getElementById('op-confirm-btn').addEventListener('click', confirmAndSend);

//     const validTill = document.getElementById('offer-valid-till');
//     const d = new Date(); d.setDate(d.getDate() + (window.VALID_UNTIL_DEFAULT_DAYS || 8));
//     validTill.value = toYMD(d);
// });

// document.getElementById('op-confirm-btn').addEventListener('click', () => {
//     const sendEmail = document.getElementById('op-send-email').checked;
//     const sendWhatsapp = document.getElementById('op-send-whatsapp').checked;
//     if (!sendEmail && !sendWhatsapp) {
//         document.getElementById('op-send-error').classList.remove('hidden');
//         return;
//     }
//     document.getElementById('op-send-error').classList.add('hidden');
//     document.getElementById('offerConfirmModal').classList.remove('hidden');
// });

// function closeOfferConfirm() { document.getElementById('offerConfirmModal').classList.add('hidden'); }

// document.getElementById('offer-confirm-yes-btn').addEventListener('click', async () => {
//     closeOfferConfirm();
//     await confirmAndSend();
// });
// /* ---------- Inventory browse (right panel) ---------- */
// async function loadHoardings() {
//     const search = document.getElementById('hoarding-search').value.trim();
//     const q = new URLSearchParams({ page: currentPage, per_page: 12, ...(search ? { search } : {}) }).toString();
//     const res = await fetchJSON(`${window.HOARDINGS_API}?${q}`);
//     hoardingsCache = res.data || [];
//     totalPages = res.last_page || 1;
//     document.getElementById('available-count').innerText = res.count ?? hoardingsCache.length;
//     renderHoardingsGrid();
//     renderPagination();
// }

// function renderHoardingsGrid() {
//     const grid = document.getElementById('hoardings-grid');
//     if (!hoardingsCache.length) {
//         grid.innerHTML = `<div class="col-span-full text-center text-xs text-gray-400 py-10">No hoardings found</div>`;
//         return;
//     }
//     grid.innerHTML = hoardingsCache.map(h => {
//         const isSel = selected.has(h.id);
//         const isDooh = (h.type || '').toUpperCase() === 'DOOH';
//         return `<div class="hoarding-card border rounded-lg overflow-hidden bg-white ${isSel?'is-selected':''}" onclick="toggleHoarding(${h.id})">
//             <div class="relative">
//                 <img src="${h.image_url||'/placeholder.png'}" class="w-full h-[72px] object-cover" onerror="this.src='/placeholder.png'">
//                 ${isSel?'<span class="absolute top-1 left-1 bg-green-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded">✓</span>':''}
//                 ${isDooh?'<span class="absolute top-1 right-1 bg-purple-600 text-white text-[8px] font-bold px-1.5 py-0.5 rounded">DOOH</span>':''}
//             </div>
//             <div class="p-2">
//                 <p class="text-[10px] font-bold text-gray-800 truncate">${h.title}</p>
//                 <p class="text-[9px] text-gray-400 truncate">${h.location_city||''}</p>
//                 <p class="text-[10px] font-bold text-gray-700 mt-0.5">${fmt(h.price_per_month)}<span class="font-normal text-gray-400">/mo</span></p>
//             </div>
//         </div>`;
//     }).join('');
// }

// function renderPagination() {
//     const el = document.getElementById('hoardings-pagination');
//     if (totalPages <= 1) { el.innerHTML=''; return; }
//     let html = '';
//     for (let i=1;i<=totalPages;i++) {
//         html += `<button onclick="changePage(${i})" class="px-2.5 py-1 border rounded text-xs font-medium ${i===currentPage?'bg-green-600 text-white border-green-600':'bg-white hover:bg-gray-50'}">${i}</button>`;
//     }
//     el.innerHTML = html;
// }
// function changePage(p) { currentPage = p; loadHoardings(); }

// function toggleHoarding(id) {
//     if (selected.has(id)) {
//         selected.delete(id);
//         renderSummary();
//         renderHoardingsGrid();
//         return;
//     }
//     const h = hoardingsCache.find(x => x.id === id);
//     if (!h) return;
//     selected.set(id, {
//         hoarding_id: h.id, title: h.title, city: h.location_city, location: h.location_address,
//         hoarding_type: (h.type || 'ooh').toLowerCase(), price_per_month: h.price_per_month,
//         image_url: h.image_url, total_slots_per_day: h.total_slots_per_day,
//         startDate: null, endDate: null, source: 'added', enquiry_item_id: null,
//     });
//     renderSummary();
//     renderHoardingsGrid();
//     setTimeout(() => openDatePickerFor(id), 100);
// }

// /* ---------- Date picker + availability ---------- */
// // async function openDatePickerFor(id) {
// //     if (typeof flatpickr === 'undefined') { alert('Calendar library not loaded.'); return; }
// //     currentEditId = id;
// //     const item = selected.get(id);
// //     if (!item) return;

// //     document.getElementById('datePickerTitle').innerText = item.title;
// //     document.getElementById('datePickerModal').classList.remove('hidden');
// //     document.getElementById('date-picker-inline').innerHTML = '<p class="text-xs text-gray-400 py-6 text-center">Loading calendar…</p>';
// //     document.getElementById('dp-summary').innerText = item.startDate ? `${item.startDate} → ${item.endDate}` : '— Pick a date';

// //     const today = toYMD(new Date());
// //     const far = new Date(); far.setDate(far.getDate() + 730);

// //     try {
// //         const res = await fetch(`/api/v1/hoardings/${id}/availability/heatmap?start_date=${today}&end_date=${toYMD(far)}`, { headers: { Accept: 'application/json' } }).then(r => r.json());
// //         heatmapMap = {};
// //         const disabled = [];
// //         (res.data?.heatmap || []).forEach(d => {
// //             heatmapMap[d.date] = d.status;
// //             if (d.status && !['available', 'blocked'].includes(d.status)) disabled.push(d.date);
// //         });

// //         document.getElementById('date-picker-inline').innerHTML = '';
// //         if (dpFp) dpFp.destroy();
// //         dpFp = flatpickr('#date-picker-input', {
// //             mode: 'range', inline: true, appendTo: document.getElementById('date-picker-inline'),
// //             minDate: today, disable: disabled,
// //             defaultDate: item.startDate ? [item.startDate, item.endDate] : [],
// //             showMonths: window.innerWidth < 668 ? 1 : 2,
// //             onDayCreate(d, ds, fp, el) {
// //                 const date = toYMD(el.dateObj);
// //                 const st = heatmapMap[date];
// //                 if (!st || st === 'available') el.classList.add('avail-day');
// //                 else if (st !== 'blocked') el.classList.add('notAllowed');
// //             },
// //             onChange(dates) {
// //                 if (dates.length === 2) {
// //                     const s = toYMD(dates[0]), e = toYMD(dates[1]);
// //                     document.getElementById('dp-summary').innerText = `${s} → ${e} (${monthsBetween(s, e)} month(s)) · Est. ${fmt(item.price_per_month * monthsBetween(s, e))}`;
// //                 }
// //             },
// //         });
// //     } catch (e) {
// //         console.error(e);
// //         alert('Could not load availability data.');
// //         closeDatePickerModal();
// //     }
// // }

// // function closeDatePickerModal() {
// //     document.getElementById('datePickerModal').classList.add('hidden');
// //     if (dpFp) { dpFp.destroy(); dpFp = null; }
// //     currentEditId = null;
// // }

// // function confirmDateSelection() {
// //     if (!dpFp || dpFp.selectedDates.length < 2) { alert('Pick a start and end date.'); return; }
// //     const s = toYMD(dpFp.selectedDates[0]), e = toYMD(dpFp.selectedDates[1]);

// //     const dates = []; let cur = new Date(s); const end = new Date(e);
// //     while (cur <= end) { dates.push(toYMD(cur)); cur.setDate(cur.getDate() + 1); }
// //     const bad = dates.some(d => heatmapMap[d] && !['available', 'blocked'].includes(heatmapMap[d]));
// //     if (bad) { alert('Selected range includes unavailable dates.'); return; }

// //     const item = selected.get(currentEditId);
// //     item.startDate = s; item.endDate = e;
// //     selected.set(currentEditId, item);
// //     closeDatePickerModal();
// //     renderSummary();
// // }
// /* ---------- date helpers (identical to POS) ---------- */
// function getDurationMonths(startISO, endISO) {
//     return monthsBetween(startISO, endISO);
// }
// // function getDurationMonths(startISO, endISO) {
// //     if (!startISO || !endISO) return 1;
// //     const diffDays = Math.ceil((new Date(endISO) - new Date(startISO)) / 86400000) + 1;
// //     return Math.max(1, Math.ceil(diffDays / 30));
// // }
// function snapToMonths(startISO, rawEndISO) {
//     const monthsN = getDurationMonths(startISO, rawEndISO);

//     const snapped = new Date(startISO);
//     snapped.setMonth(snapped.getMonth() + monthsN);

//     return {
//         endISO: toYMD(snapped),
//         months: monthsN
//     };
// }
// // function snapToMonths(startISO, rawEndISO) {
// //     const monthsN = getDurationMonths(startISO, rawEndISO);
// //     const snapped = new Date(startISO);
// //     snapped.setDate(snapped.getDate() + monthsN * 30 - 1);
// //     return { endISO: toYMD(snapped), months: monthsN };
// // }
// // function endForMonths(startISO, n) {
// //     const d = new Date(startISO);
// //     d.setDate(d.getDate() + n * 30 - 1);
// //     return toYMD(d);
// // }
// function endForMonths(startISO, n) {
//     const d = new Date(startISO);
//     d.setMonth(d.getMonth() + n);
//     return toYMD(d);
// }
// function friendlyRange(startISO, endISO) {
//     const opts = { day:'2-digit', month:'short', year:'numeric' };
//     const s = new Date(startISO).toLocaleDateString('en-IN', opts);
//     const e = new Date(endISO).toLocaleDateString('en-IN', opts);
//     const m = getDurationMonths(startISO, endISO);
//     const lbl = m === 1 ? '1 Month' : `${m} Months`;
//     return { s, e, m, lbl, full: `${s} – ${e}`, badge: lbl };
// }
// function enumerateDates(startISO, endISO) {
//     const dates = []; const cur = new Date(startISO); const last = new Date(endISO);
//     while (cur <= last) { dates.push(toYMD(cur)); cur.setDate(cur.getDate() + 1); }
//     return dates;
// }

// let dpCurrentStart = null;

// function _updateDpBar(startISO, endISO, ppm) {
//     const rangeEl = document.getElementById('dp-range-label');
//     const monthsEl = document.getElementById('dp-months-label');
//     const costEl = document.getElementById('dp-cost-label');
//     if (!startISO) {
//         rangeEl.innerText = '— Pick a date'; monthsEl.innerText = '—'; costEl.innerText = '—';
//         _setActiveChip(null); return;
//     }
//     const r = friendlyRange(startISO, endISO || startISO);
//     rangeEl.innerHTML = `${r.s}&nbsp;–&nbsp;${endISO ? r.e : '…'}`;
//     monthsEl.innerText = endISO ? r.badge : '—';
//     costEl.innerText = (endISO && ppm) ? fmt(ppm * getDurationMonths(startISO, endISO)) : '—';
//     _setActiveChip(endISO ? r.m : null);
// }
// function _setActiveChip(monthsN) {
//     document.querySelectorAll('.dp-quick-chip').forEach(btn => {
//         btn.classList.toggle('chip-active', monthsN !== null && parseInt(btn.dataset.months) === monthsN);
//     });
// }
// function quickSelectMonths(n) {
//     if (!dpFp) return;
//     const start = dpCurrentStart || toYMD(new Date());
//     const end = endForMonths(start, n);
//     dpFp.setDate([start, end], false);
//     dpCurrentStart = start;
//     const item = selected.get(currentEditId);
//     _updateDpBar(start, end, item?.price_per_month);
// }

// /* ---------- open picker (full POS parity) ---------- */
// async function openDatePickerFor(id) {
//     if (typeof flatpickr === 'undefined') { alert('Calendar library not loaded.'); return; }
//     currentEditId = id;
//     const item = selected.get(id);
//     if (!item) return;

//     dpCurrentStart = item.startDate || null;
//     const titleEl = document.getElementById('datePickerTitle');
//     const fullTitle = item.title || 'Select Booking Dates';
//     titleEl.innerText = fullTitle.length > 40 ? fullTitle.slice(0, 40).trimEnd() + '...' : fullTitle;
//     titleEl.title = fullTitle;

//     document.getElementById('datePickerModal').classList.remove('hidden');
//     document.getElementById('date-picker-inline').innerHTML = '<div class="text-center py-8 text-sm text-gray-400 animate-pulse">Loading calendar…</div>';
//     _updateDpBar(item.startDate || null, item.endDate || null, item.price_per_month);

//     const defaultDate = item.startDate ? (item.endDate ? [item.startDate, item.endDate] : [item.startDate]) : [];
//     const today = toYMD(new Date());
//     const far = new Date(); far.setDate(far.getDate() + 730);

//     try {
//         const res = await fetch(`/api/v1/hoardings/${id}/availability/heatmap?start_date=${today}&end_date=${toYMD(far)}`, { headers: { Accept: 'application/json' } }).then(r => r.json());
//         heatmapMap = {};
//         const disabled = [];
//         (res.data?.heatmap || []).forEach(d => {
//             heatmapMap[d.date] = d.status;
//             if (d.status && !['available', 'blocked'].includes(d.status)) disabled.push(d.date);
//         });

//         document.getElementById('date-picker-inline').innerHTML = '';
//         if (dpFp) { dpFp.destroy(); dpFp = null; }

//         dpFp = flatpickr('#date-picker-input', {
//             mode: 'range', inline: true, appendTo: document.getElementById('date-picker-inline'),
//             minDate: today, disable: disabled, defaultDate,
//             showMonths: window.innerWidth < 768 ? 1 : 2,

//             onReady(selectedDates, dateStr, fp) {
//                 // smart-reset: clicking a fresh start after a range (or across a blocked day) resets the range,
//                 // instead of extending it — identical to POS
//                 fp.calendarContainer.addEventListener('mousedown', (event) => {
//                     const target = event.target;
//                     if (!(target instanceof Element)) return;
//                     const dayElem = target.closest('.flatpickr-day');
//                     if (!dayElem || !dayElem.dateObj) return;
//                     if (dayElem.classList.contains('flatpickr-disabled')) return;

//                     const selectedCount = fp.selectedDates.length;
//                     if (selectedCount === 0) return;

//                     const clickedISO = toYMD(dayElem.dateObj);
//                     const currentStartISO = fp.selectedDates[0] ? toYMD(fp.selectedDates[0]) : null;
//                     const currentEndISO = fp.selectedDates[1] ? toYMD(fp.selectedDates[1]) : null;
//                     if (!currentStartISO || clickedISO === currentStartISO || clickedISO === currentEndISO) return;

//                     let shouldResetToFreshStart = selectedCount >= 2 || dayElem.classList.contains('notAllowed');
//                     if (!shouldResetToFreshStart && selectedCount === 1) {
//                         const fromISO = clickedISO < currentStartISO ? clickedISO : currentStartISO;
//                         const toISO = clickedISO < currentStartISO ? currentStartISO : clickedISO;
//                         shouldResetToFreshStart = enumerateDates(fromISO, toISO).some((dateISO) => {
//                             if (dateISO === clickedISO) return false;
//                             const status = heatmapMap[dateISO];
//                             return status && status !== 'available';
//                         });
//                     }
//                     if (!shouldResetToFreshStart) return;

//                     event.preventDefault();
//                     if (typeof event.stopImmediatePropagation === 'function') event.stopImmediatePropagation();
//                     event.stopPropagation();
//                     fp.clear(false);
//                     fp.setDate([clickedISO], true);
//                 }, true);
//             },

//             onDayCreate(dObj, dStr, fp, dayElem) {
//                 const date = toYMD(dayElem.dateObj);
//                 const status = heatmapMap[date];
//                 if (!status || status === 'available') { dayElem.classList.add('avail-day'); dayElem.title = 'Available'; }
//                 else if (status === 'booked')  { dayElem.classList.add('day-booked');  dayElem.title = 'Booked';  }
//                 else if (status === 'blocked') { dayElem.classList.add('day-blocked'); dayElem.title = 'Blocked'; }
//                 else if (status === 'hold')    { dayElem.classList.add('day-hold');    dayElem.title = 'On Hold'; }
//                 else if (status === 'partial') { dayElem.classList.add('day-partial'); dayElem.title = 'Partial'; }
//             },

//             onChange(selectedDates) {
//                 if (!selectedDates.length) return;
//                 const start = toYMD(selectedDates[0]);
//                 dpCurrentStart = start;

//                 if (selectedDates.length === 1) { _updateDpBar(start, null, item.price_per_month); return; }

//                 const rawEnd = toYMD(selectedDates[1]);
//                 const { endISO } = snapToMonths(start, rawEnd === start ? endForMonths(start, 1) : rawEnd);
//                 _updateDpBar(start, endISO, item.price_per_month);

//                 if (endISO !== rawEnd) setTimeout(() => dpFp?.setDate([start, endISO], false), 0);
//             },
//         });
//     } catch (e) {
//         console.error(e);
//         alert('Could not load availability data.');
//         closeDatePickerModal();
//     }
// }

// function closeDatePickerModal() {
//     document.getElementById('datePickerModal').classList.add('hidden');
//     if (dpFp) { dpFp.destroy(); dpFp = null; }
//     document.getElementById('date-picker-inline').innerHTML = '';
//     currentEditId = null;
//     dpCurrentStart = null;
// }

// function confirmDateSelection() {
//     if (!dpFp || !currentEditId) { closeDatePickerModal(); return; }
//     const dates = dpFp.selectedDates;
//     if (!dates?.length) { alert('Please select a start date first.'); return; }

//     const startISO = toYMD(dates[0]);
//     const rawEnd = dates.length >= 2 ? toYMD(dates[1]) : startISO;
//     const { endISO } = snapToMonths(startISO, rawEnd === startISO ? endForMonths(startISO, 1) : rawEnd);

//     const bad = enumerateDates(startISO, endISO).some(d => {
//         const st = heatmapMap[d];
//         return st === 'booked' || st === 'hold' || st === 'partial';
//     });
//     if (bad) { alert('Selected range includes unavailable dates. Please choose a different period.'); return; }

//     const item = selected.get(currentEditId);
//     item.startDate = startISO; item.endDate = endISO;
//     selected.set(currentEditId, item);
//     closeDatePickerModal();
//     renderSummary();
//     renderHoardingsGrid();
// }
// /* ---------- Left-panel summary tables ---------- */
// function removeItem(id) { selected.delete(id); renderSummary(); renderHoardingsGrid(); }

// function renderSummary() {
//     const oohItems = [...selected.values()].filter(i => i.hoarding_type === 'ooh');
//     const doohItems = [...selected.values()].filter(i => i.hoarding_type === 'dooh');

//     document.getElementById('ooh-count').innerText = oohItems.length;
//     document.getElementById('dooh-count').innerText = doohItems.length;
//     document.getElementById('offer-selected-count').innerText = selected.size;

//     document.getElementById('ooh-selected-list').innerHTML = oohItems.length
//         ? oohItems.map(rowOOH).join('')
//         : `<tr><td colspan="5" class="px-4 py-6 text-center text-gray-400 italic text-xs">No OOH hoardings selected</td></tr>`;

//     document.getElementById('dooh-selected-list').innerHTML = doohItems.length
//         ? doohItems.map(rowDOOH).join('')
//         : `<tr><td colspan="6" class="px-4 py-6 text-center text-gray-400 italic text-xs">No DOOH screens selected</td></tr>`;
// }

// function sourceBadge(i) {
//     return i.source === 'enquiry'
//         ? '<span class="text-[9px] bg-blue-50 text-blue-600 px-1 rounded ml-1">Enquiry</span>'
//         : '<span class="text-[9px] bg-emerald-50 text-emerald-600 px-1 rounded ml-1">Added</span>';
// }
// function rowOOH(i) {
//     const total = i.startDate ? i.price_per_month * monthsBetween(i.startDate, i.endDate) : null;
//     return `<tr><td class="px-3 py-3"><p class="font-bold text-gray-800">${i.title}${sourceBadge(i)}</p><p class="text-[10px] text-gray-400">${i.city||''}</p></td>
//         <td class="px-3 py-3">${fmt(i.price_per_month)}</td>
//         <td class="px-3 py-3">${i.startDate?`${i.startDate} – ${i.endDate}`:`<button class="text-orange-600 font-semibold" onclick="openDatePickerFor(${i.hoarding_id})">Select dates</button>`}</td>
//         <td class="px-3 py-3 font-bold text-green-700">${total?fmt(total):'—'}</td>
//         <td class="px-3 py-3 text-right"><button class="text-red-500 font-bold" onclick="removeItem(${i.hoarding_id})">Remove</button></td></tr>`;
// }
// function rowDOOH(i) {
//     const total = i.startDate ? i.price_per_month * monthsBetween(i.startDate, i.endDate) : null;
//     return `<tr><td class="px-3 py-3"><p class="font-bold text-gray-800">${i.title}${sourceBadge(i)}</p><p class="text-[10px] text-gray-400">${i.city||''}</p></td>
//         <td class="px-3 py-3">${fmt(i.price_per_month)}</td>
//         <td class="px-3 py-3">${i.total_slots_per_day||300}</td>
//         <td class="px-3 py-3">${i.startDate?`${i.startDate} – ${i.endDate}`:`<button class="text-orange-600 font-semibold" onclick="openDatePickerFor(${i.hoarding_id})">Select dates</button>`}</td>
//         <td class="px-3 py-3 font-bold text-green-700">${total?fmt(total):'—'}</td>
//         <td class="px-3 py-3 text-right"><button class="text-red-500 font-bold" onclick="removeItem(${i.hoarding_id})">Remove</button></td></tr>`;
// }

// /* ---------- Preview screen toggle ---------- */
// function openPreview() {
//     if (!selected.size) { alert('Add at least one hoarding.'); return; }
//     const missing = [...selected.values()].find(i => !i.startDate || !i.endDate);
//     if (missing) { alert(`Select dates for: ${missing.title}`); openDatePickerFor(missing.hoarding_id); return; }

//     const items = [...selected.values()];
//     const ooh = items.filter(i => i.hoarding_type === 'ooh');
//     const dooh = items.filter(i => i.hoarding_type === 'dooh');
//     const cust = window.OFFER_CUSTOMER || {};

//     document.getElementById('op-cust-name').innerText = cust.name || '—';
//     document.getElementById('op-cust-business').innerText = cust.business || '—';
//     document.getElementById('op-cust-gstin').innerText = cust.gstin || '—';
//     document.getElementById('op-cust-mobile').innerText = cust.mobile || '—';
//     document.getElementById('op-cust-email').innerText = cust.email || '—';
//     document.getElementById('op-cust-address').innerText = cust.address || '—';

//     document.getElementById('op-total').innerText = document.getElementById('op-total-2').innerText = items.length;
//     document.getElementById('op-ooh-count').innerText = document.getElementById('op-ooh-count-2').innerText = ooh.length;
//     document.getElementById('op-dooh-count').innerText = document.getElementById('op-dooh-count-2').innerText = dooh.length;
//     document.getElementById('op-cities').innerText = [...new Set(items.map(i => i.city).filter(Boolean))].join(', ');
//     document.getElementById('op-valid-till').innerText = document.getElementById('offer-valid-till').value || '—';

//     document.getElementById('op-ooh-rows').innerHTML = ooh.map((i, idx) => `
//         <tr class="border-t"><td class="px-3 py-2">${idx+1}</td>
//         <td class="px-3 py-2">${i.title}<br><span class="text-gray-400">${i.city||''}</span></td>
//         <td class="px-3 py-2">${fmt(i.price_per_month)}/M</td>
//         <td class="px-3 py-2">${i.startDate} – ${i.endDate}<br>${monthsBetween(i.startDate,i.endDate)} Month(s)</td>
//         <td class="px-3 py-2 font-bold">${fmt(i.price_per_month*monthsBetween(i.startDate,i.endDate))}</td></tr>`).join('');

//     document.getElementById('op-dooh-rows').innerHTML = dooh.map((i, idx) => `
//         <tr class="border-t"><td class="px-3 py-2">${idx+1}</td>
//         <td class="px-3 py-2">${i.title}<br><span class="text-gray-400">${i.city||''}</span></td>
//         <td class="px-3 py-2">${fmt(i.price_per_month)}</td>
//         <td class="px-3 py-2">${i.total_slots_per_day||300}</td>
//         <td class="px-3 py-2">${i.startDate} – ${i.endDate}</td>
//         <td class="px-3 py-2 font-bold">${fmt(i.price_per_month*monthsBetween(i.startDate,i.endDate))}</td></tr>`).join('');

//     document.getElementById('selection-screen').classList.add('hidden');
//     document.getElementById('preview-screen').classList.remove('hidden');
//     window.scrollTo({ top: 0, behavior: 'smooth' });
// }

// function backToSelection() {
//     document.getElementById('preview-screen').classList.add('hidden');
//     document.getElementById('selection-screen').classList.remove('hidden');
// }

// // document.getElementById('op-confirm-btn').addEventListener('click', () => {
// //     const sendEmail = document.getElementById('op-send-email').checked;
// //     const sendWhatsapp = document.getElementById('op-send-whatsapp').checked;
// //     if (!sendEmail && !sendWhatsapp) {
// //         document.getElementById('op-send-error').classList.remove('hidden');
// //         return;
// //     }
// //     document.getElementById('op-send-error').classList.add('hidden');
// //     document.getElementById('offerConfirmModal').classList.remove('hidden');
// // });
// let isSubmittingOffer = false; // hard lock — prevents ANY double-submit, regardless of listener bugs

// document.getElementById('op-confirm-btn').addEventListener('click', () => {
//     const sendEmail = document.getElementById('op-send-email').checked;
//     const sendWhatsapp = document.getElementById('op-send-whatsapp').checked;
//     if (!sendEmail && !sendWhatsapp) {
//         document.getElementById('op-send-error').classList.remove('hidden');
//         return;
//     }
//     document.getElementById('op-send-error').classList.add('hidden');
//     document.getElementById('offerConfirmModal').classList.remove('hidden');
// });

// // function closeOfferConfirm() { document.getElementById('offerConfirmModal').classList.add('hidden'); }

// // document.getElementById('offer-confirm-yes-btn').addEventListener('click', async () => {
// //     closeOfferConfirm();
// //     await confirmAndSend();
// // });
// function closeOfferConfirm() { document.getElementById('offerConfirmModal').classList.add('hidden'); }

// document.getElementById('offer-confirm-yes-btn').addEventListener('click', async () => {
//     if (isSubmittingOffer) return; // second/third fire is swallowed here, synchronously, before any await
//     isSubmittingOffer = true;
//     closeOfferConfirm();
//     try {
//         await confirmAndSend();
//     } finally {
//         isSubmittingOffer = false;
//     }
// });
// // async function confirmAndSend() {
// //     const sendEmail = document.getElementById('op-send-email').checked;
// //     const sendWhatsapp = document.getElementById('op-send-whatsapp').checked;

// //     const items = [...selected.values()].map(i => {
// //         const finalPrice = i.price_per_month * monthsBetween(i.startDate, i.endDate);
// //         return {
// //             hoarding_id: i.hoarding_id,
// //             enquiry_item_id: i.enquiry_item_id || null,
// //             hoarding_type: i.hoarding_type,
// //             start_date: i.startDate,
// //             end_date: i.endDate,
// //             unit_price: finalPrice,
// //             discount_amount: 0,
// //             final_price: finalPrice,
// //         };
// //     });

// //     const btn = document.getElementById('op-confirm-btn');
// //     btn.disabled = true; btn.innerText = 'Sending…';

// //     try {
// //         const res = await fetchJSON(window.OFFER_STORE_URL, {
// //             method: 'POST',

// //             body: JSON.stringify({
// //     enquiry_id: window.ENQUIRY_ID,
// //     offer_id: window.EDITING_OFFER_ID,
// //     valid_until: document.getElementById('offer-valid-till').value,
// //     send_email: sendEmail,
// //     send_whatsapp: sendWhatsapp,
// //     items,
// // }),
// //         });

// //         if (!res.success) {
// //             if (res.unavailable_hoardings) {
// //                 backToSelection();
// //                 document.getElementById('availability-alert').classList.remove('hidden');
// //                 document.getElementById('availability-alert-body').innerHTML =
// //                     res.unavailable_hoardings.map(u => `${u.hoarding_name}: ${u.reasons.join(', ')}`).join('<br>');
// //             }
// //             alert(res.message || 'Failed to send offer.');
// //             return;
// //         }

// //         document.getElementById('success-offer-id').innerText = res.data.id;
// //         document.getElementById('success-manage-offers-btn').href = res.data.manage_offers_url;
// //         document.getElementById('offerSuccessModal').classList.remove('hidden');
// //     } catch (e) {
// //         console.error(e);
// //         alert('Something went wrong. Please try again.');
// //     } finally {
// //         btn.disabled = false; btn.innerText = 'Confirm & Send offer';
// //     }
// // }
// async function confirmAndSend() {
//     const sendEmail = document.getElementById('op-send-email').checked;
//     const sendWhatsapp = document.getElementById('op-send-whatsapp').checked;

//     const items = [...selected.values()].map(i => {
//         const finalPrice = i.price_per_month * monthsBetween(i.startDate, i.endDate);
//         return {
//             hoarding_id: i.hoarding_id,
//             enquiry_item_id: i.enquiry_item_id || null,
//             hoarding_type: i.hoarding_type,
//             start_date: i.startDate,
//             end_date: i.endDate,
//             unit_price: finalPrice,
//             discount_amount: 0,
//             final_price: finalPrice,
//         };
//     });

//     const btn = document.getElementById('op-confirm-btn');
//     btn.disabled = true; btn.innerText = 'Sending…';

//     try {
//         const res = await fetchJSON(window.OFFER_STORE_URL, {
//             method: 'POST',
//             body: JSON.stringify({
//                 enquiry_id: window.ENQUIRY_ID,
//                 offer_id: window.EDITING_OFFER_ID,
//                 valid_until: document.getElementById('offer-valid-till').value,
//                 send_email: sendEmail,
//                 send_whatsapp: sendWhatsapp,
//                 items,
//             }),
//         });

//         if (!res.success) {
//             if (res.unavailable_hoardings) {
//                 backToSelection();
//                 document.getElementById('availability-alert').classList.remove('hidden');
//                 document.getElementById('availability-alert-body').innerHTML =
//                     res.unavailable_hoardings.map(u => `${u.hoarding_name}: ${u.reasons.join(', ')}`).join('<br>');
//             }
//             alert(res.message || 'Failed to send offer.');
//             return;
//         }

//         document.getElementById('success-offer-id').innerText = res.data.id;
//         document.getElementById('success-manage-offers-btn').href = res.data.manage_offers_url;
//         document.getElementById('offerSuccessModal').classList.remove('hidden');
//     } catch (e) {
//         console.error(e);
//         alert('Something went wrong. Please try again.');
//     } finally {
//         btn.disabled = false; btn.innerText = 'Confirm & Send offer';
//     }
// }
const selected = new Map();          // hoarding_id -> item
let hoardingsCache = [];
let currentPage = 1, totalPages = 1;
let heatmapMap = {}, currentEditId = null, dpFp = null;
let dpCurrentStart = null;
let isSubmittingOffer = false; // hard lock — the ONLY guard against double-submit now

const fmt = v => new Intl.NumberFormat('en-IN',{style:'currency',currency:'INR',maximumFractionDigits:0}).format(v||0);
const toYMD = d => { d=new Date(d); return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; };

const monthsBetween = (s, e) => {
    if (!s || !e) return 1;
    const start = new Date(s);
    const end = new Date(e);
    let months = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth());
    if (end.getDate() > start.getDate()) months += 1;
    return Math.max(1, months);
};

const fetchJSON = (url,opts={}) => fetch(url,{
    headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':window.CSRF_TOKEN,'X-Requested-With':'XMLHttpRequest'},
    ...opts,
}).then(r=>r.json());
function debounce(fn,t){let h;return(...a)=>{clearTimeout(h);h=setTimeout(()=>fn(...a),t);};}

/* ---------- init — every listener bound exactly once, here ---------- */
document.addEventListener('DOMContentLoaded', () => {
    (window.OFFER_SEED_ITEMS || []).forEach(it => selected.set(it.hoarding_id, it));
    renderSummary();
    loadHoardings();

    document.getElementById('hoarding-search').addEventListener('input', debounce(() => { currentPage = 1; loadHoardings(); }, 250));
    document.getElementById('offer-preview-btn').addEventListener('click', openPreview);

    document.getElementById('op-confirm-btn').addEventListener('click', () => {
        const sendEmail = document.getElementById('op-send-email').checked;
        const sendWhatsapp = document.getElementById('op-send-whatsapp').checked;
        if (!sendEmail && !sendWhatsapp) {
            document.getElementById('op-send-error').classList.remove('hidden');
            return;
        }
        document.getElementById('op-send-error').classList.add('hidden');
        document.getElementById('offerConfirmModal').classList.remove('hidden');
    });

    document.getElementById('offer-confirm-yes-btn').addEventListener('click', async () => {
        if (isSubmittingOffer) return; // second/third fire (double-click, stray listener, etc.) is a no-op
        isSubmittingOffer = true;
        closeOfferConfirm();
        try {
            await confirmAndSend();
        } finally {
            isSubmittingOffer = false;
        }
    });

    const validTill = document.getElementById('offer-valid-till');
    const d = new Date(); d.setDate(d.getDate() + (window.VALID_UNTIL_DEFAULT_DAYS || 8));
    validTill.value = toYMD(d);
});

function closeOfferConfirm() { document.getElementById('offerConfirmModal').classList.add('hidden'); }

/* ---------- Inventory browse (right panel) ---------- */
async function loadHoardings() {
    const search = document.getElementById('hoarding-search').value.trim();
    const q = new URLSearchParams({ page: currentPage, per_page: 12, ...(search ? { search } : {}) }).toString();
    const res = await fetchJSON(`${window.HOARDINGS_API}?${q}`);
    hoardingsCache = res.data || [];
    totalPages = res.last_page || 1;
    document.getElementById('available-count').innerText = res.count ?? hoardingsCache.length;
    renderHoardingsGrid();
    renderPagination();
}

function renderHoardingsGrid() {
    const grid = document.getElementById('hoardings-grid');
    if (!hoardingsCache.length) {
        grid.innerHTML = `<div class="col-span-full text-center text-xs text-gray-400 py-10">No hoardings found</div>`;
        return;
    }
    grid.innerHTML = hoardingsCache.map(h => {
        const isSel = selected.has(h.id);
        const isDooh = (h.type || '').toUpperCase() === 'DOOH';
        return `<div class="hoarding-card border rounded-lg overflow-hidden bg-white ${isSel?'is-selected':''}" onclick="toggleHoarding(${h.id})">
            <div class="relative">
                <img src="${h.image_url||'/placeholder.png'}" class="w-full h-[72px] object-cover" onerror="this.src='/placeholder.png'">
                ${isSel?'<span class="absolute top-1 left-1 bg-green-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded">✓</span>':''}
                ${isDooh?'<span class="absolute top-1 right-1 bg-purple-600 text-white text-[8px] font-bold px-1.5 py-0.5 rounded">DOOH</span>':''}
            </div>
            <div class="p-2">
                <p class="text-[10px] font-bold text-gray-800 truncate">${h.title}</p>
                <p class="text-[9px] text-gray-400 truncate">${h.location_city||''}</p>
                <p class="text-[10px] font-bold text-gray-700 mt-0.5">${fmt(h.price_per_month)}<span class="font-normal text-gray-400">/mo</span></p>
            </div>
        </div>`;
    }).join('');
}

function renderPagination() {
    const el = document.getElementById('hoardings-pagination');
    if (totalPages <= 1) { el.innerHTML=''; return; }
    let html = '';
    for (let i=1;i<=totalPages;i++) {
        html += `<button onclick="changePage(${i})" class="px-2.5 py-1 border rounded text-xs font-medium ${i===currentPage?'bg-green-600 text-white border-green-600':'bg-white hover:bg-gray-50'}">${i}</button>`;
    }
    el.innerHTML = html;
}
function changePage(p) { currentPage = p; loadHoardings(); }

function toggleHoarding(id) {
    if (selected.has(id)) {
        selected.delete(id);
        renderSummary();
        renderHoardingsGrid();
        return;
    }
    const h = hoardingsCache.find(x => x.id === id);
    if (!h) return;
    selected.set(id, {
        hoarding_id: h.id, title: h.title, city: h.location_city, location: h.location_address,
        hoarding_type: (h.type || 'ooh').toLowerCase(), price_per_month: h.price_per_month,
        image_url: h.image_url, total_slots_per_day: h.total_slots_per_day,
        startDate: null, endDate: null, source: 'added', enquiry_item_id: null,
    });
    renderSummary();
    renderHoardingsGrid();
    setTimeout(() => openDatePickerFor(id), 100);
}

/* ---------- date helpers ---------- */
function getDurationMonths(startISO, endISO) {
    return monthsBetween(startISO, endISO);
}
function snapToMonths(startISO, rawEndISO) {
    const monthsN = getDurationMonths(startISO, rawEndISO);
    const snapped = new Date(startISO);
    snapped.setMonth(snapped.getMonth() + monthsN);
    return { endISO: toYMD(snapped), months: monthsN };
}
function endForMonths(startISO, n) {
    const d = new Date(startISO);
    d.setMonth(d.getMonth() + n);
    return toYMD(d);
}
function friendlyRange(startISO, endISO) {
    const opts = { day:'2-digit', month:'short', year:'numeric' };
    const s = new Date(startISO).toLocaleDateString('en-IN', opts);
    const e = new Date(endISO).toLocaleDateString('en-IN', opts);
    const m = getDurationMonths(startISO, endISO);
    const lbl = m === 1 ? '1 Month' : `${m} Months`;
    return { s, e, m, lbl, full: `${s} – ${e}`, badge: lbl };
}
function enumerateDates(startISO, endISO) {
    const dates = []; const cur = new Date(startISO); const last = new Date(endISO);
    while (cur <= last) { dates.push(toYMD(cur)); cur.setDate(cur.getDate() + 1); }
    return dates;
}

function _updateDpBar(startISO, endISO, ppm) {
    const rangeEl = document.getElementById('dp-range-label');
    const monthsEl = document.getElementById('dp-months-label');
    const costEl = document.getElementById('dp-cost-label');
    if (!startISO) {
        rangeEl.innerText = '— Pick a date'; monthsEl.innerText = '—'; costEl.innerText = '—';
        _setActiveChip(null); return;
    }
    const r = friendlyRange(startISO, endISO || startISO);
    rangeEl.innerHTML = `${r.s}&nbsp;–&nbsp;${endISO ? r.e : '…'}`;
    monthsEl.innerText = endISO ? r.badge : '—';
    costEl.innerText = (endISO && ppm) ? fmt(ppm * getDurationMonths(startISO, endISO)) : '—';
    _setActiveChip(endISO ? r.m : null);
}
function _setActiveChip(monthsN) {
    document.querySelectorAll('.dp-quick-chip').forEach(btn => {
        btn.classList.toggle('chip-active', monthsN !== null && parseInt(btn.dataset.months) === monthsN);
    });
}
function quickSelectMonths(n) {
    if (!dpFp) return;
    const start = dpCurrentStart || toYMD(new Date());
    const end = endForMonths(start, n);
    dpFp.setDate([start, end], false);
    dpCurrentStart = start;
    const item = selected.get(currentEditId);
    _updateDpBar(start, end, item?.price_per_month);
}

/* ---------- date picker ---------- */
async function openDatePickerFor(id) {
    if (typeof flatpickr === 'undefined') { alert('Calendar library not loaded.'); return; }
    currentEditId = id;
    const item = selected.get(id);
    if (!item) return;

    dpCurrentStart = item.startDate || null;
    const titleEl = document.getElementById('datePickerTitle');
    const fullTitle = item.title || 'Select Booking Dates';
    titleEl.innerText = fullTitle.length > 40 ? fullTitle.slice(0, 40).trimEnd() + '...' : fullTitle;
    titleEl.title = fullTitle;

    document.getElementById('datePickerModal').classList.remove('hidden');
    document.getElementById('date-picker-inline').innerHTML = '<div class="text-center py-8 text-sm text-gray-400 animate-pulse">Loading calendar…</div>';
    _updateDpBar(item.startDate || null, item.endDate || null, item.price_per_month);

    const defaultDate = item.startDate ? (item.endDate ? [item.startDate, item.endDate] : [item.startDate]) : [];
    const today = toYMD(new Date());
    const far = new Date(); far.setDate(far.getDate() + 730);

    try {
        const res = await fetch(`/api/v1/hoardings/${id}/availability/heatmap?start_date=${today}&end_date=${toYMD(far)}`, { headers: { Accept: 'application/json' } }).then(r => r.json());
        heatmapMap = {};
        const disabled = [];
        (res.data?.heatmap || []).forEach(d => {
            heatmapMap[d.date] = d.status;
            if (d.status && !['available', 'blocked'].includes(d.status)) disabled.push(d.date);
        });

        document.getElementById('date-picker-inline').innerHTML = '';
        if (dpFp) { dpFp.destroy(); dpFp = null; }

        dpFp = flatpickr('#date-picker-input', {
            mode: 'range', inline: true, appendTo: document.getElementById('date-picker-inline'),
            minDate: today, disable: disabled, defaultDate,
            showMonths: window.innerWidth < 768 ? 1 : 2,

            onReady(selectedDates, dateStr, fp) {
                fp.calendarContainer.addEventListener('mousedown', (event) => {
                    const target = event.target;
                    if (!(target instanceof Element)) return;
                    const dayElem = target.closest('.flatpickr-day');
                    if (!dayElem || !dayElem.dateObj) return;
                    if (dayElem.classList.contains('flatpickr-disabled')) return;

                    const selectedCount = fp.selectedDates.length;
                    if (selectedCount === 0) return;

                    const clickedISO = toYMD(dayElem.dateObj);
                    const currentStartISO = fp.selectedDates[0] ? toYMD(fp.selectedDates[0]) : null;
                    const currentEndISO = fp.selectedDates[1] ? toYMD(fp.selectedDates[1]) : null;
                    if (!currentStartISO || clickedISO === currentStartISO || clickedISO === currentEndISO) return;

                    let shouldResetToFreshStart = selectedCount >= 2 || dayElem.classList.contains('notAllowed');
                    if (!shouldResetToFreshStart && selectedCount === 1) {
                        const fromISO = clickedISO < currentStartISO ? clickedISO : currentStartISO;
                        const toISO = clickedISO < currentStartISO ? currentStartISO : clickedISO;
                        shouldResetToFreshStart = enumerateDates(fromISO, toISO).some((dateISO) => {
                            if (dateISO === clickedISO) return false;
                            const status = heatmapMap[dateISO];
                            return status && status !== 'available';
                        });
                    }
                    if (!shouldResetToFreshStart) return;

                    event.preventDefault();
                    if (typeof event.stopImmediatePropagation === 'function') event.stopImmediatePropagation();
                    event.stopPropagation();
                    fp.clear(false);
                    fp.setDate([clickedISO], true);
                }, true);
            },

            onDayCreate(dObj, dStr, fp, dayElem) {
                const date = toYMD(dayElem.dateObj);
                const status = heatmapMap[date];
                if (!status || status === 'available') { dayElem.classList.add('avail-day'); dayElem.title = 'Available'; }
                else if (status === 'booked')  { dayElem.classList.add('day-booked');  dayElem.title = 'Booked';  }
                else if (status === 'blocked') { dayElem.classList.add('day-blocked'); dayElem.title = 'Blocked'; }
                else if (status === 'hold')    { dayElem.classList.add('day-hold');    dayElem.title = 'On Hold'; }
                else if (status === 'partial') { dayElem.classList.add('day-partial'); dayElem.title = 'Partial'; }
            },

            onChange(selectedDates) {
                if (!selectedDates.length) return;
                const start = toYMD(selectedDates[0]);
                dpCurrentStart = start;

                if (selectedDates.length === 1) { _updateDpBar(start, null, item.price_per_month); return; }

                const rawEnd = toYMD(selectedDates[1]);
                const { endISO } = snapToMonths(start, rawEnd === start ? endForMonths(start, 1) : rawEnd);
                _updateDpBar(start, endISO, item.price_per_month);

                if (endISO !== rawEnd) setTimeout(() => dpFp?.setDate([start, endISO], false), 0);
            },
        });
    } catch (e) {
        console.error(e);
        alert('Could not load availability data.');
        closeDatePickerModal();
    }
}

function closeDatePickerModal() {
    document.getElementById('datePickerModal').classList.add('hidden');
    if (dpFp) { dpFp.destroy(); dpFp = null; }
    document.getElementById('date-picker-inline').innerHTML = '';
    currentEditId = null;
    dpCurrentStart = null;
}

function confirmDateSelection() {
    if (!dpFp || !currentEditId) { closeDatePickerModal(); return; }
    const dates = dpFp.selectedDates;
    if (!dates?.length) { alert('Please select a start date first.'); return; }

    const startISO = toYMD(dates[0]);
    const rawEnd = dates.length >= 2 ? toYMD(dates[1]) : startISO;
    const { endISO } = snapToMonths(startISO, rawEnd === startISO ? endForMonths(startISO, 1) : rawEnd);

    const bad = enumerateDates(startISO, endISO).some(d => {
        const st = heatmapMap[d];
        return st === 'booked' || st === 'hold' || st === 'partial';
    });
    if (bad) { alert('Selected range includes unavailable dates. Please choose a different period.'); return; }

    const item = selected.get(currentEditId);
    item.startDate = startISO; item.endDate = endISO;
    selected.set(currentEditId, item);
    closeDatePickerModal();
    renderSummary();
    renderHoardingsGrid();
}

/* ---------- Left-panel summary tables ---------- */
function removeItem(id) { selected.delete(id); renderSummary(); renderHoardingsGrid(); }

function renderSummary() {
    const oohItems = [...selected.values()].filter(i => i.hoarding_type === 'ooh');
    const doohItems = [...selected.values()].filter(i => i.hoarding_type === 'dooh');

    document.getElementById('ooh-count').innerText = oohItems.length;
    document.getElementById('dooh-count').innerText = doohItems.length;
    document.getElementById('offer-selected-count').innerText = selected.size;

    document.getElementById('ooh-selected-list').innerHTML = oohItems.length
        ? oohItems.map(rowOOH).join('')
        : `<tr><td colspan="5" class="px-4 py-6 text-center text-gray-400 italic text-xs">No OOH hoardings selected</td></tr>`;

    document.getElementById('dooh-selected-list').innerHTML = doohItems.length
        ? doohItems.map(rowDOOH).join('')
        : `<tr><td colspan="6" class="px-4 py-6 text-center text-gray-400 italic text-xs">No DOOH screens selected</td></tr>`;
}

function sourceBadge(i) {
    return i.source === 'enquiry'
        ? '<span class="text-[9px] bg-blue-50 text-blue-600 px-1 rounded ml-1">Enquiry</span>'
        : '<span class="text-[9px] bg-emerald-50 text-emerald-600 px-1 rounded ml-1">Added</span>';
}
function rowOOH(i) {
    const total = i.startDate ? i.price_per_month * monthsBetween(i.startDate, i.endDate) : null;
    return `<tr><td class="px-3 py-3"><p class="font-bold text-gray-800">${i.title}${sourceBadge(i)}</p><p class="text-[10px] text-gray-400">${i.city||''}</p></td>
        <td class="px-3 py-3">${fmt(i.price_per_month)}</td>
        <td class="px-3 py-3">${i.startDate?`${i.startDate} – ${i.endDate}`:`<button class="text-orange-600 font-semibold" onclick="openDatePickerFor(${i.hoarding_id})">Select dates</button>`}</td>
        <td class="px-3 py-3 font-bold text-green-700">${total?fmt(total):'—'}</td>
        <td class="px-3 py-3 text-right"><button class="text-red-500 font-bold" onclick="removeItem(${i.hoarding_id})">Remove</button></td></tr>`;
}
function rowDOOH(i) {
    const total = i.startDate ? i.price_per_month * monthsBetween(i.startDate, i.endDate) : null;
    return `<tr><td class="px-3 py-3"><p class="font-bold text-gray-800">${i.title}${sourceBadge(i)}</p><p class="text-[10px] text-gray-400">${i.city||''}</p></td>
        <td class="px-3 py-3">${fmt(i.price_per_month)}</td>
        <td class="px-3 py-3">${i.total_slots_per_day||300}</td>
        <td class="px-3 py-3">${i.startDate?`${i.startDate} – ${i.endDate}`:`<button class="text-orange-600 font-semibold" onclick="openDatePickerFor(${i.hoarding_id})">Select dates</button>`}</td>
        <td class="px-3 py-3 font-bold text-green-700">${total?fmt(total):'—'}</td>
        <td class="px-3 py-3 text-right"><button class="text-red-500 font-bold" onclick="removeItem(${i.hoarding_id})">Remove</button></td></tr>`;
}

/* ---------- Preview screen toggle ---------- */
function openPreview() {
    if (!selected.size) { alert('Add at least one hoarding.'); return; }
    const missing = [...selected.values()].find(i => !i.startDate || !i.endDate);
    if (missing) { alert(`Select dates for: ${missing.title}`); openDatePickerFor(missing.hoarding_id); return; }

    const items = [...selected.values()];
    const ooh = items.filter(i => i.hoarding_type === 'ooh');
    const dooh = items.filter(i => i.hoarding_type === 'dooh');
    const cust = window.OFFER_CUSTOMER || {};

    document.getElementById('op-cust-name').innerText = cust.name || '—';
    document.getElementById('op-cust-business').innerText = cust.business || '—';
    document.getElementById('op-cust-gstin').innerText = cust.gstin || '—';
    document.getElementById('op-cust-mobile').innerText = cust.mobile || '—';
    document.getElementById('op-cust-email').innerText = cust.email || '—';
    document.getElementById('op-cust-address').innerText = cust.address || '—';

    document.getElementById('op-total').innerText = document.getElementById('op-total-2').innerText = items.length;
    document.getElementById('op-ooh-count').innerText = document.getElementById('op-ooh-count-2').innerText = ooh.length;
    document.getElementById('op-dooh-count').innerText = document.getElementById('op-dooh-count-2').innerText = dooh.length;
    document.getElementById('op-cities').innerText = [...new Set(items.map(i => i.city).filter(Boolean))].join(', ');
    document.getElementById('op-valid-till').innerText = document.getElementById('offer-valid-till').value || '—';

    document.getElementById('op-ooh-rows').innerHTML = ooh.map((i, idx) => `
        <tr class="border-t"><td class="px-3 py-2">${idx+1}</td>
        <td class="px-3 py-2">${i.title}<br><span class="text-gray-400">${i.city||''}</span></td>
        <td class="px-3 py-2">${fmt(i.price_per_month)}/M</td>
        <td class="px-3 py-2">${i.startDate} – ${i.endDate}<br>${monthsBetween(i.startDate,i.endDate)} Month(s)</td>
        <td class="px-3 py-2 font-bold">${fmt(i.price_per_month*monthsBetween(i.startDate,i.endDate))}</td></tr>`).join('');

    document.getElementById('op-dooh-rows').innerHTML = dooh.map((i, idx) => `
        <tr class="border-t"><td class="px-3 py-2">${idx+1}</td>
        <td class="px-3 py-2">${i.title}<br><span class="text-gray-400">${i.city||''}</span></td>
        <td class="px-3 py-2">${fmt(i.price_per_month)}</td>
        <td class="px-3 py-2">${i.total_slots_per_day||300}</td>
        <td class="px-3 py-2">${i.startDate} – ${i.endDate}</td>
        <td class="px-3 py-2 font-bold">${fmt(i.price_per_month*monthsBetween(i.startDate,i.endDate))}</td></tr>`).join('');

    document.getElementById('selection-screen').classList.add('hidden');
    document.getElementById('preview-screen').classList.remove('hidden');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function backToSelection() {
    document.getElementById('preview-screen').classList.add('hidden');
    document.getElementById('selection-screen').classList.remove('hidden');
}

/* ---------- Confirm & Send — the ONLY place that calls the store endpoint ---------- */
async function confirmAndSend() {
    const sendEmail = document.getElementById('op-send-email').checked;
    const sendWhatsapp = document.getElementById('op-send-whatsapp').checked;

    const items = [...selected.values()].map(i => {
        const finalPrice = i.price_per_month * monthsBetween(i.startDate, i.endDate);
        return {
            hoarding_id: i.hoarding_id,
            enquiry_item_id: i.enquiry_item_id || null,
            hoarding_type: i.hoarding_type,
            start_date: i.startDate,
            end_date: i.endDate,
            unit_price: finalPrice,
            discount_amount: 0,
            final_price: finalPrice,
        };
    });

    const btn = document.getElementById('op-confirm-btn');
    btn.disabled = true; btn.innerText = 'Sending…';

    try {
        const res = await fetchJSON(window.OFFER_STORE_URL, {
            method: 'POST',
            body: JSON.stringify({
                enquiry_id: window.ENQUIRY_ID,
                offer_id: window.EDITING_OFFER_ID,
                valid_until: document.getElementById('offer-valid-till').value,
                send_email: sendEmail,
                send_whatsapp: sendWhatsapp,
                items,
            }),
        });

        if (!res.success) {
            if (res.unavailable_hoardings) {
                backToSelection();
                document.getElementById('availability-alert').classList.remove('hidden');
                document.getElementById('availability-alert-body').innerHTML =
                    res.unavailable_hoardings.map(u => `${u.hoarding_name}: ${u.reasons.join(', ')}`).join('<br>');
            }
            alert(res.message || 'Failed to send offer.');
            return;
        }

        document.getElementById('success-offer-id').innerText = res.data.id;
        document.getElementById('success-manage-offers-btn').href = res.data.manage_offers_url;
        document.getElementById('offerSuccessModal').classList.remove('hidden');
    } catch (e) {
        console.error(e);
        alert('Something went wrong. Please try again.');
    } finally {
        btn.disabled = false; btn.innerText = 'Confirm & Send offer';
    }
}
