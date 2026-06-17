document.addEventListener('DOMContentLoaded', function () {
    var grid = document.getElementById('storiesGrid');
    var loading = document.getElementById('cardsLoading');

    if (!grid || !loading) {
        return;
    }

    // NOTE: not in course slides (DOMPurify), but required for safe HTML from API data.
    if (typeof DOMPurify === 'undefined') {
        loading.innerHTML = '<p style="color:#cfcfcf;">Could not load stories. Please refresh the page.</p>';
        return;
    }

    var currentDay = typeof window.STORIES_DAY === 'string' ? window.STORIES_DAY : 'all';
    var apiUrl = '/api/stories';

    if (currentDay !== 'all') {
        apiUrl += '?day=' + encodeURIComponent(currentDay);
    }

    function clean(value) {
        return DOMPurify.sanitize(String(value || ''));
    }

    function buildCard(story) {
        var img = clean(story.image_path || '/images/Stories/cards/default.jpg');
        var name = clean(story.name || 'Story');
        var day = clean(story.event_day ? story.event_day.charAt(0).toUpperCase() + story.event_day.slice(1) : '');
        var time = clean(story.start_time || '');
        var lang = clean(story.language || '');
        var age = clean(story.age || '');
        var desc = clean(story.description || '');
        var storyId = parseInt(story.story_id, 10) || 0;
        var ticketDetailsId = parseInt(story.ticket_details_id, 10) || 0;
        var langRow = '';
        var ageRow = '';
        var ticketAction = '';

        if (lang !== '') {
            langRow = `<div class="meta-row"><span class="meta-ico">Lang</span><span>Lang: ${lang}</span></div>`;
        }

        if (age !== '') {
            ageRow = `<div class="meta-row"><span class="meta-ico">Age</span><span>Age ${age}</span></div>`;
        }

        if (ticketDetailsId > 0) {
            ticketAction = `<button type="button" class="card-btn primary add-to-cart-button" data-ticket-details-id="${ticketDetailsId}">BUY TICKETS</button>`;
        } else {
            ticketAction = '<a class="card-btn primary" href="/tickets">BUY TICKETS</a>';
        }

        return `
            <article class="stories-card">
                <div class="stories-card-img"
                     style="background-image:url('${img}')"
                     role="img"
                     aria-label="${name}"></div>
                <div class="stories-card-body">
                    <h3 class="stories-card-title">${name}</h3>
                    <div class="stories-card-meta">
                        <div class="meta-row">
                            <span class="meta-ico">Date</span>
                            <span>${day} ${time}</span>
                        </div>
                        ${langRow}
                        ${ageRow}
                    </div>
                    <p class="stories-card-desc">${desc}</p>
                    <div class="stories-card-actions">
                        ${ticketAction}
                        <a class="card-btn outline" href="/stories/detail?id=${storyId}">MORE INFO</a>
                    </div>
                </div>
            </article>`;
    }

    fetch(apiUrl)
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            var html = '';
            var stories = data.stories || [];

            loading.remove();

            if (stories.length === 0) {
                grid.innerHTML = '<p style="color:#cfcfcf;padding:20px;">No stories found.</p>';
                return;
            }

            for (var i = 0; i < stories.length; i++) {
                html += buildCard(stories[i]);
            }

            grid.innerHTML = html;

            if (window.attachCartButtonHandlers) {
                window.attachCartButtonHandlers();
            }
        })
        .catch(function () {
            loading.innerHTML = '<p style="color:#cfcfcf;">Could not load stories. Please refresh the page.</p>';
        });
});
