document.addEventListener('DOMContentLoaded', function () {
    const details = document.getElementById('externalserver-details');
    if (!details) return;

    const prefKey = details.dataset.prefkey;

    details.addEventListener('toggle', function () {
        const open = details.open ? 1 : 0;
        fetch(`${M.cfg.wwwroot}/lib/ajax/service.php?sesskey=${M.cfg.sesskey}`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify([{
                methodname: 'assignsubmission_externalserver_set_toggle_state',
                args: {
                    state: open,
                    key: prefKey
                }
            }])
        });
    });
});
