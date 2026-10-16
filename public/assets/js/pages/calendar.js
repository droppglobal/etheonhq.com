(function () {
    var types = window.calendarTypes || {};
    var hidden = {};
    try { hidden = JSON.parse(localStorage.getItem('calendarHiddenTypes') || '{}') || {}; } catch (e) { hidden = {}; }

    var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        themeSystem: 'bootstrap',
        initialDate: new Date(),
        navLinks: true,
        height: 'auto',
        dayMaxEvents: true,
        handleWindowResize: true,
        selectable: false, // read-only calendar
        editable: false,

        eventDidMount: function (info) {
            var type = types[info.event.extendedProps.type];
            info.el.setAttribute('title', (type ? type.label + ': ' : '') + info.event.title);
        },

        eventClick: function (info) {
            info.jsEvent.preventDefault();
            var redirectUrl = info.event.extendedProps.redirectUrl;
            if (redirectUrl) {
                window.location.href = redirectUrl;
            }
        },

        events: (window.eventData || []).map(function (e) {
            e.display = hidden[e.type] ? 'none' : 'auto';
            return e;
        })
    });
    calendar.render();

    // legend chips switch each kind of event on and off (remembered in this browser)
    document.querySelectorAll('.cal-filter').forEach(function (chip) {
        var type = chip.getAttribute('data-type');
        chip.classList.toggle('off', !!hidden[type]);
        chip.addEventListener('click', function () {
            hidden[type] = !hidden[type];
            chip.classList.toggle('off', hidden[type]);
            calendar.getEvents().forEach(function (event) {
                if (event.extendedProps.type === type) {
                    event.setProp('display', hidden[type] ? 'none' : 'auto');
                }
            });
            try { localStorage.setItem('calendarHiddenTypes', JSON.stringify(hidden)); } catch (e) {}
        });
    });

    // FullCalendar adds Bootstrap button-group classes the theme does not style well
    document.querySelectorAll('.fc-toolbar-chunk').forEach(function (chunk) {
        if (chunk.children[0]) {
            chunk.children[0].classList.remove('btn-group');
            chunk.children[0].classList.add('d-inline-flex');
        }
    });
})();
