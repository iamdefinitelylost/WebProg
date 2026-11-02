/* React-powered searchable team collection. React 18 is loaded from the UMD CDN in index.php. */
(function () {
    'use strict';

    const rootElement = document.getElementById('teamGrid');
    const searchInput = document.getElementById('reactTeamSearch');
    const countLabel = document.getElementById('reactTeamCount');

    if (!rootElement || !window.React || !window.ReactDOM) {
        if (rootElement) {
            rootElement.innerHTML = '<p class="react-team-error">The team collection could not load. Please refresh the page.</p>';
        }
        return;
    }

    const h = React.createElement;
    const teams = [
        'Ferrari', 'McLaren', 'Red Bull Racing', 'Mercedes', 'Aston Martin',
        'Alpine', 'Williams', 'Haas', 'Racing Bulls', 'Audi', 'Cadillac'
    ];

    function TeamFinder(props) {
        const query = props.query.trim().toLowerCase();
        const filteredTeams = teams.filter(function (team) {
            return team.toLowerCase().includes(query);
        });

        React.useEffect(function () {
            if (countLabel) {
                countLabel.textContent = filteredTeams.length +
                    (filteredTeams.length === 1 ? ' team' : ' teams');
            }
        }, [filteredTeams.length]);

        if (!filteredTeams.length) {
            return h('p', { className: 'react-team-empty' }, 'No teams match your search.');
        }

        return h(React.Fragment, null, filteredTeams.map(function (team) {
            return h('a', {
                className: 'team-tile group rounded-xl border border-white/10 bg-[#14161C] px-5 py-6 transition duration-200 hover:-translate-y-1 hover:border-red-500/60 hover:shadow-lg',
                href: 'drops.php?team=' + encodeURIComponent(team),
                key: team
            },
                h('span', { className: 'font-semibold tracking-wide text-white' }, team),
                h('small', { className: 'mt-2 block text-xs font-medium tracking-widest text-gray-400 transition group-hover:text-red-400' }, 'SHOP COLLECTION →')
            );
        }));
    }

    const reactRoot = ReactDOM.createRoot(rootElement);
    function renderTeams(query) {
        reactRoot.render(h(TeamFinder, { query: query || '' }));
    }

    renderTeams('');

    if (searchInput) {
        searchInput.addEventListener('input', function (event) {
            renderTeams(event.target.value);
        });
    }
})();
