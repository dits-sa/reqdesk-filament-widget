@if ($config !== null)
    <script src="{{ $config['scriptUrl'] }}" defer></script>
    <script data-reqdesk-init>
        (function () {
            var cfg = @json($config['init']);
            @if (! empty($config['identifyEndpoint']))
                cfg.customer = cfg.customer || {};
                cfg.customer.refreshIdentity = async function () {
                    var response = await fetch(@json($config['identifyEndpoint']), {
                        credentials: 'include',
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!response.ok) {
                        throw new Error('reqdesk identity fetch failed with status ' + response.status);
                    }
                    return response.json();
                };
            @endif
            var boot = function () {
                if (typeof window.ReqdeskWidget === 'undefined') {
                    return window.setTimeout(boot, 50);
                }
                try {
                    window.ReqdeskWidget.init(cfg);
                } catch (err) {
                    if (window.console && window.console.error) {
                        window.console.error('[reqdesk] init failed', err);
                    }
                }
            };
            boot();
        })();
    </script>
@endif
