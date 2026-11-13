{{-- Shows the karma reward dialog after the page has loaded (all layouts). --}}
@if (session('karma_earned'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof showKarmaBootbox === 'function') {
                showKarmaBootbox(@json(session('karma_earned')));
            }
        });
    </script>
@endif
