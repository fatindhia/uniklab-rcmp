{{--
    Terms & Conditions + How to book, shown two ways:
      • on demand from the "Info" nav item (closable straight away), and
      • automatically on a visitor's first page view of the session, where the
        close button stays locked for 10s so the terms actually get read.
    "Once per session" is tracked in sessionStorage, so it comes back the next
    time the browser (or tab) is opened, exactly as intended.
--}}
<style>
    .info-overlay {
        position: fixed;
        inset: 0;
        z-index: 200;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(34, 29, 30, .55);
        backdrop-filter: blur(3px);
        opacity: 0;
        transition: opacity .28s ease;
    }

    .info-overlay.is-open {
        display: flex;
    }

    .info-overlay.is-visible {
        opacity: 1;
    }

    .info-dialog {
        width: 100%;
        max-width: 720px;
        max-height: min(86vh, 780px);
        display: flex;
        flex-direction: column;
        background: var(--panel-strong, #fff);
        border: 1px solid var(--line);
        border-radius: 20px;
        box-shadow: 0 30px 60px -24px rgba(34, 29, 30, .45);
        overflow: hidden;
        transform: translateY(18px) scale(.97);
        opacity: 0;
        transition: transform .34s cubic-bezier(.2, .8, .25, 1), opacity .28s ease;
    }

    .info-overlay.is-visible .info-dialog {
        transform: none;
        opacity: 1;
    }

    @media (prefers-reduced-motion: reduce) {

        .info-overlay,
        .info-dialog {
            transition: none;
        }
    }

    .info-head {
        position: relative;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 24px 26px 20px;
        border-bottom: 1px solid var(--line);
        background: linear-gradient(180deg, rgba(125, 145, 148, .09), rgba(125, 145, 148, 0));
    }

    .info-head .eyebrow {
        margin-bottom: 6px;
    }

    .info-head h2 {
        margin: 0;
        font-size: 1.32rem;
    }

    .info-head p {
        margin: 6px 0 0;
        font-size: .86rem;
        color: var(--muted);
        max-width: 46ch;
    }

    .info-close {
        flex-shrink: 0;
        width: 38px;
        height: 38px;
        min-height: 38px;
        padding: 0;
        border-radius: 999px;
        border: 1px solid var(--line);
        background: #fff;
        color: var(--ink);
        font-size: 1rem;
        font-weight: 800;
        cursor: pointer;
        display: grid;
        place-items: center;
        transition: border-color .15s ease, color .15s ease, background .15s ease, width .2s ease;
    }

    .info-close:hover:not(:disabled) {
        border-color: var(--brand);
        color: var(--brand-2);
    }

    /* Locked state during the first-visit countdown. */
    .info-close:disabled {
        width: auto;
        padding: 0 14px;
        border-radius: 999px;
        cursor: not-allowed;
        color: var(--muted);
        background: rgba(49, 43, 44, .04);
        font-family: var(--mono);
        font-size: .78rem;
        font-weight: 700;
    }

    .info-body {
        padding: 4px 26px 26px;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    .info-body::-webkit-scrollbar {
        width: 10px;
    }

    .info-body::-webkit-scrollbar-track {
        background: rgba(49, 43, 44, .05);
    }

    .info-body::-webkit-scrollbar-thumb {
        background: rgba(125, 145, 148, .55);
        border: 2px solid transparent;
        background-clip: padding-box;
        border-radius: 999px;
    }

    .info-section {
        padding-top: 24px;
    }

    .info-section h3 {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 0 0 14px;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        font-weight: 800;
        color: var(--brand-2);
    }

    .info-section h3 .ic {
        font-size: 1rem;
    }

    .info-terms {
        margin: 0;
        padding: 0;
        list-style: none;
        display: grid;
        gap: 11px;
    }

    .info-terms li {
        display: flex;
        gap: 11px;
        font-size: .9rem;
        line-height: 1.6;
        color: var(--ink);
    }

    .info-terms li::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--brand);
        margin-top: 9px;
        flex-shrink: 0;
    }

    .info-steps {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .info-step {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 14px 15px;
        border: 1px solid var(--line);
        border-radius: 14px;
        background: #fff;
    }

    .info-step-num {
        flex-shrink: 0;
        width: 28px;
        height: 28px;
        border-radius: 9px;
        display: grid;
        place-items: center;
        background: rgba(125, 145, 148, .14);
        color: var(--brand-2);
        font-family: 'Sora', sans-serif;
        font-weight: 800;
        font-size: .82rem;
    }

    .info-step strong {
        display: block;
        font-size: .92rem;
        margin-bottom: 3px;
    }

    .info-step span {
        font-size: .8rem;
        color: var(--muted);
        line-height: 1.5;
    }

    .info-foot {
        padding: 16px 26px;
        border-top: 1px solid var(--line);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        background: rgba(49, 43, 44, .02);
    }

    .info-foot .muted {
        font-size: .78rem;
    }

    @media (max-width: 620px) {
        .info-overlay {
            padding: 0;
            align-items: flex-end;
        }

        .info-dialog {
            max-width: none;
            max-height: 92vh;
            border-radius: 20px 20px 0 0;
        }

        .info-steps {
            grid-template-columns: 1fr;
        }

        .info-head,
        .info-body,
        .info-foot {
            padding-left: 20px;
            padding-right: 20px;
        }
    }
</style>

<div class="info-overlay" id="infoOverlay" role="dialog" aria-modal="true" aria-labelledby="infoTitle"
    onclick="infoBackdropClose(event)">
    <div class="info-dialog" onclick="event.stopPropagation()">
        <div class="info-head">
            <div>
                <div class="eyebrow">Before you book</div>
                <h2 id="infoTitle">Terms &amp; How to Book</h2>
                <p id="infoIntro">Please review the booking policy and the steps below.</p>
            </div>
            <button type="button" class="info-close" id="infoClose" onclick="closeInfoModal()"
                aria-label="Close">&times;</button>
        </div>

        <div class="info-body">
            <div class="info-section">
                <h3><span class="ic">📋</span> Terms &amp; Conditions</h3>
                <ul class="info-terms">
                    <li><span>The minimum booking duration is <strong>60 minutes</strong>.</span></li>
                    <li><span>All bookings are <strong>subject to administrator approval</strong> and are <strong>not
                                confirmed until approved</strong>.</span></li>
                    <li><span>You will receive an <strong>email notification</strong> once your booking status has been
                            updated.</span></li>
                    <li><span>Any cancellation or modification must be made <strong>at least 24 hours</strong> before
                            the scheduled booking time.</span></li>
                    <li><span>Repeated <strong>no-shows</strong> or <strong>late cancellations</strong> may result in
                            the suspension of booking privileges.</span></li>
                    <li><span>Users are responsible for the proper use of all labs and equipment and will be held
                            accountable for any damage caused during their booked session.</span></li>
                </ul>
            </div>

            <div class="info-section">
                <h3><span class="ic">🧭</span> How to Book</h3>
                <div class="info-steps">
                    <div class="info-step">
                        <span class="info-step-num">1</span>
                        <div>
                            <strong>Select lab type</strong>
                            <span>Research &amp; Development, CSL, or Pharma</span>
                        </div>
                    </div>
                    <div class="info-step">
                        <span class="info-step-num">2</span>
                        <div>
                            <strong>Choose room / equipment</strong>
                            <span>Availability is checked in real time</span>
                        </div>
                    </div>
                    <div class="info-step">
                        <span class="info-step-num">3</span>
                        <div>
                            <strong>Fill in your details</strong>
                            <span>Date, time, purpose and applicant info</span>
                        </div>
                    </div>
                    <div class="info-step">
                        <span class="info-step-num">4</span>
                        <div>
                            <strong>Submit &amp; get confirmation</strong>
                            <span>Track it any time under Check Booking</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="info-foot">
            <span class="muted">Questions? Contact the lab administrator.</span>
            <a class="button button-primary" style="min-height:38px; padding:0 18px; font-size:.84rem;"
                href="{{ route('booking.create') }}">Book a Lab →</a>
        </div>
    </div>
</div>

<script>
    (function () {
        const SEEN_KEY = 'rcmp_info_seen';
        const LOCK_SECONDS = 10;
        const overlay = document.getElementById('infoOverlay');
        const closeBtn = document.getElementById('infoClose');
        const intro = document.getElementById('infoIntro');
        if (!overlay) return;

        let locked = false;
        let tick = null;

        function unlock() {
            locked = false;
            clearInterval(tick);
            closeBtn.disabled = false;
            closeBtn.innerHTML = '&times;';
            closeBtn.setAttribute('aria-label', 'Close');
        }

        // lockSeconds > 0 keeps the close button disabled (counting down) so the
        // first-visit popup can't be dismissed before it's been read.
        window.openInfoModal = function (lockSeconds) {
            const seconds = Number(lockSeconds) || 0;
            overlay.classList.add('is-open');
            document.body.style.overflow = 'hidden';
            // next frame, so the transition runs from the closed state
            requestAnimationFrame(() => overlay.classList.add('is-visible'));

            if (seconds > 0) {
                locked = true;
                let left = seconds;
                closeBtn.disabled = true;
                closeBtn.textContent = 'Close in ' + left + 's';
                closeBtn.setAttribute('aria-label', 'Closing available in ' + left + ' seconds');
                intro.textContent = 'Please take a moment to read the booking policy — you can close this in '
                    + seconds + ' seconds.';

                clearInterval(tick);
                tick = setInterval(() => {
                    left -= 1;
                    if (left > 0) {
                        closeBtn.textContent = 'Close in ' + left + 's';
                        closeBtn.setAttribute('aria-label', 'Closing available in ' + left + ' seconds');
                        return;
                    }
                    unlock();
                    intro.textContent = 'You can close this now — it won\'t show again until your next visit.';
                }, 1000);
            } else {
                unlock();
                intro.textContent = 'Please review the booking policy and the steps below.';
            }
        };

        window.closeInfoModal = function () {
            if (locked) return;
            overlay.classList.remove('is-visible');
            document.body.style.overflow = '';
            try { sessionStorage.setItem(SEEN_KEY, '1'); } catch (e) { /* private mode — just don't remember */ }
            // wait out the exit transition before pulling it from the flow
            setTimeout(() => overlay.classList.remove('is-open'), 300);
        };

        window.infoBackdropClose = function (event) {
            if (event.target === overlay) closeInfoModal();
        };

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && overlay.classList.contains('is-open')) closeInfoModal();
        });

        // First page view of this browser session only. sessionStorage is per
        // tab and cleared when it closes, so a fresh visit sees it again.
        let seen = false;
        try { seen = sessionStorage.getItem(SEEN_KEY) === '1'; } catch (e) { seen = false; }

        if (!seen) {
            setTimeout(() => window.openInfoModal(LOCK_SECONDS), 450);
        }
    })();
</script>
