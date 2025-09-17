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
                set('metric-my-drafts', m.my_drafts);
                set('metric-my-verified', m.my_verified);
                set('metric-my-approved', m.my_approved);

                if (data.drafts && Array.isArray(data.drafts)){
                    var tbody = document.querySelector('#table-drafts tbody');
                    if (tbody){
                        tbody.innerHTML = '';
                        if (!data.drafts.length){
                            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No drafts</td></tr>';
                        } else {
                            data.drafts.forEach(function(r){
                                var tr = document.createElement('tr');
                                var total = Number(r.total || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                var viewHref = poShowTemplate ? poShowTemplate.replace('__po__', r.purchase_order_no) : '#';
                                tr.innerHTML = '<td>' + (r.purchase_order_no ?? '') + '</td>' +
                                               '<td>' + (r.purpose ?? '') + '</td>' +
                                               '<td><span class="badge bg-secondary">' + (r.status_name ?? '') + '</span></td>' +
                                               '<td class="text-end">' + total + '</td>' +
                                               '<td class="text-end"><a class="btn btn-sm btn-outline-primary" href="' + viewHref + '">View</a></td>';
                                tbody.appendChild(tr);
                            });
                        }
                    }
                }
            } catch (e) {
                // swallow errors to avoid UI noise
            }
        }

        refresh();
        setInterval(refresh, 10000);
    });
})();


