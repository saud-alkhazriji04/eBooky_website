document.addEventListener('DOMContentLoaded', function() {
    function getRecentAirports() {
        return JSON.parse(localStorage.getItem('recentAirports') || '[]');
    }
    function setRecentAirports(list) {
        localStorage.setItem('recentAirports', JSON.stringify(list.slice(0, 5)));
    }
    function getFavoriteAirports() {
        return JSON.parse(localStorage.getItem('favoriteAirports') || '[]');
    }
    function setFavoriteAirports(list) {
        localStorage.setItem('favoriteAirports', JSON.stringify(list));
    }
    function addRecentAirport(airport) {
        let recents = getRecentAirports();
        recents = recents.filter(a => a.code !== airport.code);
        recents.unshift(airport);
        setRecentAirports(recents);
    }
    function toggleFavoriteAirport(airport) {
        let favs = getFavoriteAirports();
        const idx = favs.findIndex(a => a.code === airport.code);
        if (idx >= 0) {
            favs.splice(idx, 1);
        } else {
            favs.push(airport);
        }
        setFavoriteAirports(favs);
    }
    function isFavoriteAirport(code) {
        return getFavoriteAirports().some(a => a.code === code);
    }
    function renderDropdown(input, dropdown, groupedData, query) {
        dropdown.innerHTML = '';
        dropdown.classList.remove('active');
        let hasResults = false;
        // Recents and favorites
        const favs = getFavoriteAirports();
        const recents = getRecentAirports();
        if (favs.length) {
            let favLabel = document.createElement('div');
            favLabel.className = 'group-label';
            favLabel.textContent = '★ Favorites';
            dropdown.appendChild(favLabel);
            favs.forEach(item => {
                let opt = document.createElement('div');
                opt.className = 'airport-option';
                opt.innerHTML = `<span>${item.city} (${item.code})</span><span class="star">★</span>`;
                opt.onclick = () => {
                    input.value = item.code;
                    addRecentAirport(item);
                    dropdown.classList.remove('active');
                };
                opt.querySelector('.star').onclick = (e) => {
                    e.stopPropagation();
                    toggleFavoriteAirport(item);
                    renderDropdown(input, dropdown, groupedData, query);
                };
                dropdown.appendChild(opt);
                hasResults = true;
            });
        }
        if (recents.length) {
            let recLabel = document.createElement('div');
            recLabel.className = 'group-label';
            recLabel.textContent = 'Recent';
            dropdown.appendChild(recLabel);
            recents.forEach(item => {
                let opt = document.createElement('div');
                opt.className = 'airport-option';
                opt.innerHTML = `<span>${item.city} (${item.code})</span><span class="star" style="opacity:${isFavoriteAirport(item.code)?1:0.3}">★</span>`;
                opt.onclick = () => {
                    input.value = item.code;
                    addRecentAirport(item);
                    dropdown.classList.remove('active');
                };
                opt.querySelector('.star').onclick = (e) => {
                    e.stopPropagation();
                    toggleFavoriteAirport(item);
                    renderDropdown(input, dropdown, groupedData, query);
                };
                dropdown.appendChild(opt);
                hasResults = true;
            });
        }
        // Grouped by city, prioritize those starting with query
        const q = (query || '').toLowerCase();
        let prioritized = [], others = [];
        Object.keys(groupedData).forEach(city => {
            let group = groupedData[city];
            // Prioritize if city or any airport name starts with query
            if (city.toLowerCase().startsWith(q) || group.some(item => item.name.toLowerCase().startsWith(q))) {
                prioritized.push([city, group]);
            } else {
                others.push([city, group]);
            }
        });
        const allGroups = prioritized.concat(others);
        allGroups.forEach(([city, group]) => {
            let label = document.createElement('div');
            label.className = 'group-label';
            label.textContent = city;
            dropdown.appendChild(label);
            group.forEach(item => {
                let opt = document.createElement('div');
                opt.className = 'airport-option';
                opt.innerHTML = `<span>${item.name} (${item.code})</span><span class="star" style="opacity:${isFavoriteAirport(item.code)?1:0.3}">★</span>`;
                opt.onclick = () => {
                    input.value = item.code;
                    addRecentAirport(item);
                    dropdown.classList.remove('active');
                };
                opt.querySelector('.star').onclick = (e) => {
                    e.stopPropagation();
                    toggleFavoriteAirport(item);
                    renderDropdown(input, dropdown, groupedData, query);
                };
                dropdown.appendChild(opt);
                hasResults = true;
            });
        });
        if (!hasResults) {
            let noRes = document.createElement('div');
            noRes.className = 'airport-option';
            noRes.style.color = '#b00';
            noRes.textContent = 'No airports found.';
            dropdown.appendChild(noRes);
        }
        dropdown.classList.add('active');
    }
    function setupCustomAutocomplete(inputId, dropdownId) {
        const input = document.getElementById(inputId);
        const dropdown = document.getElementById(dropdownId);
        let lastData = {};
        input.addEventListener('input', function() {
            const query = input.value.trim();
            if (query.length < 2) {
                // Show recents/favorites
                renderDropdown(input, dropdown, {}, query);
                return;
            }
            fetch((window.EBOOKY_BASE_PATH || '') + '/iata-lookup.php?q=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => {
                    lastData = data;
                    renderDropdown(input, dropdown, data, query);
                });
        });
        input.addEventListener('focus', function() {
            if (!input.value.trim()) {
                renderDropdown(input, dropdown, {}, '');
            }
        });
        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target) && e.target !== input) {
                dropdown.classList.remove('active');
            }
        });
    }
    setupCustomAutocomplete('from-airport', 'from-airport-dropdown');
    setupCustomAutocomplete('to-airport', 'to-airport-dropdown');
}); 