/**
 * Thin wrapper over SweetAlert2 so every dialog and toast in the app shares one
 * look, and the brand colours live in a single place instead of being repeated
 * at each call site.
 *
 * Loaded after sweetalert2.min.js. If that file ever fails to load, everything
 * here falls back to the native alert()/confirm() rather than throwing — a
 * missing cosmetic library must not stop an admin deleting a lab.
 */
(function () {
    var BRAND = '#4a5f62';
    var DANGER = '#c0392b';

    function hasSwal() {
        return typeof window.Swal !== 'undefined';
    }

    /**
     * Corner toast for "it worked" / "it didn't" messages — replaces alert()
     * and the flash-message banners. Non-blocking and self-dismissing.
     */
    window.notify = function (message, type) {
        if (!message) return;

        if (!hasSwal()) {
            window.alert(message);
            return;
        }

        window.Swal.fire({
            toast: true,
            position: 'top-end',
            icon: type || 'success',
            title: message,
            showConfirmButton: false,
            timer: type === 'error' ? 6000 : 3500,
            timerProgressBar: true,
            didOpen: function (el) {
                el.addEventListener('mouseenter', window.Swal.stopTimer);
                el.addEventListener('mouseleave', window.Swal.resumeTimer);
            },
        });
    };

    window.notifySuccess = function (message) { window.notify(message, 'success'); };
    window.notifyError = function (message) { window.notify(message, 'error'); };

    /**
     * Promise<boolean> confirmation, replacing confirm(). Always resolves —
     * cancelling resolves false rather than rejecting, so callers can just
     * `if (!await confirmAction(...)) return;`.
     */
    window.confirmAction = function (options) {
        var opts = options || {};

        if (!hasSwal()) {
            return Promise.resolve(window.confirm(opts.text || opts.title || 'Are you sure?'));
        }

        return window.Swal.fire({
            title: opts.title || 'Are you sure?',
            text: opts.text || '',
            icon: opts.danger ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonText: opts.confirmText || 'Confirm',
            cancelButtonText: opts.cancelText || 'Cancel',
            confirmButtonColor: opts.danger ? DANGER : BRAND,
            cancelButtonColor: '#8a8078',
            reverseButtons: true,
            focusCancel: !!opts.danger,
        }).then(function (result) {
            return !!result.isConfirmed;
        });
    };
})();
