document.addEventListener('DOMContentLoaded', function() {
    function renderHotelDropdown(input, dropdown, hotels, query) {
        dropdown.innerHTML = '';
        dropdown.classList.remove('active');
        let hasResults = false;
        if (hotels && hotels.length) {
            hotels.forEach(hotel => {
                let opt = document.createElement('div');
                opt.className = 'option';
                let label = hotel.city ? `${hotel.name} (${hotel.country}${hotel.city ? ', ' + hotel.city : ''})` : hotel.name;
                opt.innerHTML = `<span>${label}</span>`;
                opt.onclick = () => {
                    input.value = hotel.name;
                    dropdown.classList.remove('active');
                };
                dropdown.appendChild(opt);
                hasResults = true;
            });
        }
        if (!hasResults) {
            let noRes = document.createElement('div');
            noRes.className = 'option';
            noRes.style.color = '#b00';
            noRes.textContent = 'No hotels or countries found.';
            dropdown.appendChild(noRes);
        }
        dropdown.classList.add('active');
    }
    function setupHotelAutocomplete(inputId, dropdownId) {
        const input = document.getElementById(inputId);
        const dropdown = document.getElementById(dropdownId);
        input.addEventListener('input', function() {
            const query = input.value.trim();
            if (query.length < 2) {
                dropdown.classList.remove('active');
                return;
            }
            fetch((window.EBOOKY_BASE_PATH || '/') + 'hotels/autocomplete?keyword=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => {
                    let hotels = (data && data.data) ? data.data.map(h => ({ name: h.name })) : [];
                    renderHotelDropdown(input, dropdown, hotels, query);
                });
        });
        input.addEventListener('focus', function() {
            if (!input.value.trim()) {
                dropdown.classList.remove('active');
            }
        });
        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target) && e.target !== input) {
                dropdown.classList.remove('active');
            }
        });
    }
    setupHotelAutocomplete('city', 'city-dropdown');
});

// Add style for autocomplete dropdown for visibility
(function() {
    const style = document.createElement('style');
    style.innerHTML = `
    .autocomplete-dropdown .option {
        background: #fff;
        color: #222;
        padding: 0.75rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
    }
    .autocomplete-dropdown .option:hover {
        background: #f5f5f5;
    }
    `;
    document.head.appendChild(style);
})(); 