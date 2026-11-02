const nearbyIcon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 11.2 20 4l-7.2 16-1.6-7.2L4 11.2Z" fill="#dceeff" stroke="#4b9be8" stroke-width="1.5" stroke-linejoin="round"/><path d="m11.2 12.8 4.1-4.1" stroke="#4b9be8" stroke-width="1.5" stroke-linecap="round"/></svg>';
const destinationIcons = [
    '<svg viewBox="0 0 48 48" fill="none" aria-hidden="true"><path d="M8 38h32M12 38V20h10v18m4 0V12h10v26" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M15 24h4m-4 5h4m10-12h4m-4 5h4m-4 5h4m-4 5h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M6 42h36" stroke="var(--destination-accent)" stroke-width="2.5" stroke-linecap="round"/><path d="M8 17h8m12-8h8" stroke="var(--destination-accent)" stroke-width="2" stroke-linecap="round"/></svg>',
    '<svg viewBox="0 0 48 48" fill="none" aria-hidden="true"><path d="M8 39h32M11 39V23h9v16m4 0V16h12v23" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M14 27h3m-3 5h3m10-12h4m-4 5h4m-4 5h4m-4 5h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M39 13v13m-4-5 4-5 4 5m-8 8h8m-7 3h6" stroke="var(--destination-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    '<svg viewBox="0 0 48 48" fill="none" aria-hidden="true"><path d="M7 39h34M11 39V20h11v19m4 0V11h11v28" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M14 24h4m-4 5h4m-4 5h4m11-19h4m-4 5h4m-4 5h4m-4 5h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M8 15h7m14-8h7M6 43h36" stroke="var(--destination-accent)" stroke-width="2.5" stroke-linecap="round"/></svg>',
];
const destinationPalettes = ['destination-green', 'destination-coral', 'destination-pink', 'destination-ochre', 'destination-blue'];

function initDestinationSearch(input) {
    const form = input.form;
    const dropdown = document.getElementById(input.getAttribute('aria-controls'));
    const nearbyInput = form?.querySelector('[data-near-me]');
    const latitudeInput = form?.querySelector('[data-near-latitude]');
    const longitudeInput = form?.querySelector('[data-near-longitude]');
    if (!form || !dropdown || !nearbyInput || !latitudeInput || !longitudeInput) return;
    document.body.append(dropdown);

    let timer;
    let activeRequest;
    let requestId = 0;
    let positionFrame;

    const close = () => {
        dropdown.hidden = true;
        input.setAttribute('aria-expanded', 'false');
    };

    const showMessage = (message) => {
        let results = dropdown.querySelector('[data-suggestion-results]');
        if (!results) {
            results = document.createElement('div');
            results.setAttribute('data-suggestion-results', '');
            dropdown.append(results);
        }
        results.replaceChildren();
        const status = document.createElement('div');
        status.className = 'destination-suggestion-status';
        status.setAttribute('role', 'status');
        status.textContent = message;
        results.append(status);
        show();
    };

    const createOption = ({ icon, name, subtitle, action, iconClass = '' }) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'destination-suggestion-option';
        button.setAttribute('role', 'option');

        const iconContainer = document.createElement('span');
        iconContainer.className = `destination-suggestion-icon ${iconClass}`;
        iconContainer.innerHTML = icon;

        const copy = document.createElement('span');
        copy.className = 'destination-suggestion-copy';
        const label = document.createElement('span');
        label.className = 'destination-suggestion-name';
        label.textContent = name;
        const description = document.createElement('span');
        description.className = 'destination-suggestion-subtitle';
        description.textContent = subtitle;
        copy.append(label, description);
        button.append(iconContainer, copy);
        button.addEventListener('click', action);
        return button;
    };

    const positionDropdown = () => {
        const bounds = input.getBoundingClientRect();
        const formBounds = form.getBoundingClientRect();
        const dropdownWidth = Math.min(420, window.innerWidth - 24);
        const left = Math.max(12, Math.min(bounds.left, window.innerWidth - dropdownWidth - 12));
        const top = Math.min(formBounds.bottom + 12, window.innerHeight - dropdown.offsetHeight - 12);
        dropdown.style.left = `${left}px`;
        dropdown.style.top = `${Math.max(12, top)}px`;
    };

    const show = () => {
        dropdown.hidden = false;
        positionDropdown();
        input.setAttribute('aria-expanded', 'true');
    };

    const nearbyOption = () => createOption({
        icon: nearbyIcon,
        iconClass: 'destination-nearby-icon',
        name: 'Nearby',
        subtitle: "Find what's around you",
        action: () => {
            if (!navigator.geolocation) {
                showMessage('Location is not supported by this browser. You can search by city or area.');
                return;
            }

            showMessage('Finding nearby hoardings…');
            navigator.geolocation.getCurrentPosition(
                ({ coords }) => {
                    input.value = 'Nearby';
                    nearbyInput.value = '1';
                    latitudeInput.value = coords.latitude;
                    longitudeInput.value = coords.longitude;
                    close();
                    form.requestSubmit();
                },
                (error) => {
                    const message = error.code === error.PERMISSION_DENIED
                        ? 'Location permission was denied. You can search by city or area instead.'
                        : 'Unable to get your location. You can search by city or area instead.';
                    showMessage(message);
                },
                { enableHighAccuracy: false, timeout: 10000, maximumAge: 60000 }
            );
        },
    });

    const loadSuggestions = async () => {
        const currentRequest = ++requestId;
        if (activeRequest) activeRequest.abort();
        activeRequest = new AbortController();
        const url = new URL(input.dataset.suggestionsUrl, window.location.origin);
        const query = input.value.trim();
        if (query) url.searchParams.set('q', query);

        dropdown.replaceChildren();
        const heading = document.createElement('div');
        heading.className = 'destination-suggestions-heading';
        heading.textContent = query ? 'Matching destinations' : 'Suggested destinations';
        dropdown.append(heading, nearbyOption());
        const results = document.createElement('div');
        results.setAttribute('data-suggestion-results', '');
        dropdown.append(results);
        const loading = document.createElement('div');
        loading.className = 'destination-suggestion-status';
        loading.setAttribute('role', 'status');
        loading.textContent = query ? 'Searching destinations…' : 'Loading destinations…';
        results.append(loading);
        show();

        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
                signal: activeRequest.signal,
            });
            if (!response.ok) throw new Error(`Suggestion request failed (${response.status}).`);
            const data = await response.json();
            if (currentRequest !== requestId) return;

            results.replaceChildren();
            if (!Array.isArray(data.suggestions) || data.suggestions.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'destination-suggestion-status';
                empty.textContent = 'No destinations found. Try another city or area.';
                results.append(empty);
                return;
            }

            data.suggestions.forEach((suggestion, index) => {
                results.append(createOption({
                    icon: destinationIcons[index % destinationIcons.length],
                    iconClass: destinationPalettes[index % destinationPalettes.length],
                    name: suggestion.value,
                    subtitle: suggestion.subtitle || (suggestion.type === 'area' ? 'Area' : 'City'),
                    action: () => {
                        input.value = suggestion.value;
                        nearbyInput.value = '';
                        latitudeInput.value = '';
                        longitudeInput.value = '';
                        close();
                    },
                }));
            });
        } catch (error) {
            if (error.name === 'AbortError' || currentRequest !== requestId) return;
            console.error('Unable to load destination suggestions.', error);
            results.replaceChildren();
            const message = document.createElement('div');
            message.className = 'destination-suggestion-status';
            message.textContent = 'Suggestions are temporarily unavailable. You can still search manually.';
            results.append(message);
        }
    };

    input.addEventListener('focus', loadSuggestions);
    input.addEventListener('click', () => {
        if (dropdown.hidden) loadSuggestions();
    });
    input.addEventListener('input', () => {
        nearbyInput.value = '';
        latitudeInput.value = '';
        longitudeInput.value = '';
        requestId++;
        if (activeRequest) activeRequest.abort();
        window.clearTimeout(timer);
        timer = window.setTimeout(loadSuggestions, 250);
    });
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') close();
    });
    form.addEventListener('submit', () => {
        if (input.value.trim().toLowerCase() !== 'nearby') {
            nearbyInput.value = '';
            latitudeInput.value = '';
            longitudeInput.value = '';
        }

        form.querySelectorAll('[data-preserved-search-param]').forEach((field) => field.remove());
        const formNames = new Set(Array.from(form.elements).map((field) => field.name).filter(Boolean));
        new URLSearchParams(window.location.search).forEach((value, name) => {
            if (name === 'page' || formNames.has(name)) return;
            const field = document.createElement('input');
            field.type = 'hidden';
            field.name = name;
            field.value = value;
            field.dataset.preservedSearchParam = '';
            form.append(field);
        });
    });

    document.addEventListener('click', (event) => {
        if (!dropdown.contains(event.target) && !input.contains(event.target)) close();
    });
    form.querySelector('#hoardingWhenTrigger')?.addEventListener('click', close);
    document.addEventListener('scroll', (event) => {
        if (dropdown.contains(event.target) || dropdown.hidden || positionFrame) return;
        positionFrame = window.requestAnimationFrame(() => {
            positionFrame = null;
            positionDropdown();
        });
    }, true);
    const searchBlock = input.closest('#searchBlock');
    searchBlock?.addEventListener('transitionend', (event) => {
        if (event.target === searchBlock && event.propertyName === 'transform' && !dropdown.hidden) {
            positionDropdown();
        }
    });
    window.addEventListener('resize', () => {
        if (!dropdown.hidden) positionDropdown();
    });
}

document.querySelectorAll('[data-destination-search]').forEach(initDestinationSearch);
