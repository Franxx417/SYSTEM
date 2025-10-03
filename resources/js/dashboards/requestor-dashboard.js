// Requestor Dashboard: real-time status updates via polling
// - Reads data-summary-url and po-show-template from #req-dashboard
(function(){
    function onReady(fn){
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn); else fn();
    }

    onReady(function(){
        var root = document.getElementById('req-dashboard');
        if (!root) return;

        var summaryUrl = root.getAttribute('data-summary-url');
        var poShowTemplate = root.getAttribute('data-po-show-template');
        if (!summaryUrl) return;

        async function refresh(){
            try {
                var res = await fetch(summaryUrl);
                if (!res.ok) return;
                var data = await res.json();
                if (data.role !== 'requestor') return;
                var m = data.metrics || {};
                var set = function(id, val){ var el = document.getElementById(id); if (el){ el.textContent = (val ?? 0); } };
                set('metric-my-total', m.my_total);
                set('metric-my-verified', m.my_verified);
                set('metric-my-approved', m.my_approved);
            } catch (e) {
                // swallow errors to avoid UI noise
            }
        }

        refresh();
        setInterval(refresh, 10000);
    });
})();


