<script>
    (function () {
        if (window.__jazzMediaCardPlayerBound) {
            return;
        }

        window.__jazzMediaCardPlayerBound = true;

        window.pauseJazzMediaCards = function (exceptAudio) {
            document.querySelectorAll('.jazz-media-card__audio').forEach(function (audio) {
                if (audio === exceptAudio) {
                    return;
                }

                audio.pause();
                audio.currentTime = 0;

                var card = audio.closest('.jazz-media-card');
                if (!card) {
                    return;
                }

                var btn = card.querySelector('.jazz-media-card__play');
                var icon = card.querySelector('.jazz-media-card__play-icon');

                if (btn) {
                    btn.classList.remove('is-playing');
                }

                if (icon) {
                    icon.textContent = '▶';
                }
            });
        };

        document.addEventListener('click', function (event) {
            var btn = event.target.closest('.jazz-media-card__play');

            if (!btn) {
                return;
            }

            var card = btn.closest('.jazz-media-card');

            if (!card) {
                return;
            }

            var audio = card.querySelector('.jazz-media-card__audio');

            if (!audio) {
                return;
            }

            var icon = btn.querySelector('.jazz-media-card__play-icon');

            if (audio.paused) {
                window.pauseJazzMediaCards(audio);

                audio.play().catch(function () {
                });
                btn.classList.add('is-playing');

                if (icon) {
                    icon.textContent = '⏸';
                }
            } else {
                audio.pause();
                btn.classList.remove('is-playing');

                if (icon) {
                    icon.textContent = '▶';
                }
            }
        });

        document.addEventListener('ended', function (event) {
            var audio = event.target;

            if (!audio.classList || !audio.classList.contains('jazz-media-card__audio')) {
                return;
            }

            var card = audio.closest('.jazz-media-card');

            if (!card) {
                return;
            }

            var btn = card.querySelector('.jazz-media-card__play');
            var icon = card.querySelector('.jazz-media-card__play-icon');

            if (btn) {
                btn.classList.remove('is-playing');
            }

            if (icon) {
                icon.textContent = '▶';
            }
        }, true);
    })();
</script>