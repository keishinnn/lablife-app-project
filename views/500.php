<?php require base_path('Views/shared/header.php'); ?>

<style>
    .status-page {
        position: relative;
        flex: 1 0 auto;
        display: grid;
        place-items: center;
        padding: 3.5rem 1.5rem 5rem;
        overflow: hidden;
    }

    .status-page::before,
    .status-page::after {
        content: "";
        position: absolute;
        border-radius: 9999px;
        filter: blur(16px);
        pointer-events: none;
    }

    .status-page::before {
        width: 26rem;
        height: 26rem;
        top: -7rem;
        left: -7rem;
        background: radial-gradient(circle, rgba(248, 113, 113, 0.22) 0%, rgba(248, 113, 113, 0) 68%);
    }

    .status-page::after {
        width: 22rem;
        height: 22rem;
        right: -5rem;
        bottom: -2rem;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.18) 0%, rgba(59, 130, 246, 0) 70%);
    }

    .status-card {
        position: relative;
        z-index: 1;
        width: min(100%, 68rem);
        display: grid;
        grid-template-columns: 1.05fr 0.95fr;
        gap: 2rem;
        padding: 2rem;
        border-radius: 2rem;
        border: 1px solid rgba(148, 163, 184, 0.2);
        background: rgba(255, 255, 255, 0.84);
        box-shadow: 0 26px 90px rgba(15, 23, 42, 0.13);
        backdrop-filter: blur(14px);
    }

    .status-copy {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 1rem;
    }

    .status-eyebrow {
        width: fit-content;
        padding: 0.45rem 0.8rem;
        border-radius: 9999px;
        background: rgba(239, 68, 68, 0.12);
        color: #991b1b;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .status-code {
        font-family: "Geist Mono", monospace;
        font-size: clamp(3.25rem, 10vw, 6.5rem);
        line-height: 0.95;
        color: #0f172a;
    }

    .status-title {
        font-size: clamp(2rem, 4vw, 3.1rem);
        line-height: 1.05;
        letter-spacing: -0.04em;
        color: #111827;
    }

    .status-description {
        max-width: 35rem;
        color: #475569;
        font-size: 1.02rem;
        line-height: 1.75;
    }

    .status-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.9rem;
        margin-top: 0.5rem;
    }

    .status-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 3rem;
        padding: 0.9rem 1.3rem;
        border-radius: 9999px;
        border: 1px solid transparent;
        font-weight: 700;
        text-decoration: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, color 0.2s ease;
    }

    .status-button:hover {
        transform: translateY(-1px);
    }

    .status-button-primary {
        background: linear-gradient(135deg, #ef4444, #f97316);
        color: #fff;
        box-shadow: 0 14px 30px rgba(239, 68, 68, 0.28);
    }

    .status-button-secondary {
        background: rgba(255, 255, 255, 0.72);
        border-color: rgba(148, 163, 184, 0.32);
        color: #0f172a;
    }

    .status-panel-card {
        display: flex;
        flex-direction: column;
        gap: 1.2rem;
        height: 100%;
        padding: 1.5rem;
        border-radius: 1.5rem;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.88), rgba(255, 255, 255, 0.7)),
            linear-gradient(135deg, rgba(239, 68, 68, 0.08), rgba(59, 130, 246, 0.06));
        border: 1px solid rgba(255, 255, 255, 0.55);
    }

    .status-monitor {
        position: relative;
        min-height: 14.5rem;
        padding: 1rem;
        border-radius: 1.25rem;
        background:
            linear-gradient(180deg, rgba(15, 23, 42, 0.94), rgba(2, 6, 23, 0.98));
        overflow: hidden;
    }

    .status-monitor::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.06), transparent 26%);
        pointer-events: none;
    }

    .status-monitor-grid {
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(148, 163, 184, 0.08) 1px, transparent 1px),
            linear-gradient(90deg, rgba(148, 163, 184, 0.08) 1px, transparent 1px);
        background-size: 2rem 2rem;
    }

    .status-monitor-wave {
        position: absolute;
        left: 0;
        right: 0;
        top: 50%;
        height: 7rem;
        transform: translateY(-50%);
    }

    .status-monitor-wave svg {
        width: 100%;
        height: 100%;
    }

    .status-monitor-wave path {
        fill: none;
        stroke: #38bdf8;
        stroke-width: 3;
        stroke-linecap: round;
        stroke-linejoin: round;
        stroke-dasharray: 420;
        stroke-dashoffset: 420;
        animation: status-line 4.6s ease-in-out infinite;
    }

    .status-monitor-chip {
        position: absolute;
        top: 1rem;
        right: 1rem;
        padding: 0.35rem 0.6rem;
        border-radius: 9999px;
        background: rgba(239, 68, 68, 0.14);
        color: #fca5a5;
        font-family: "Geist Mono", monospace;
        font-size: 0.74rem;
        letter-spacing: 0.04em;
    }

    .status-panel-card h2 {
        font-size: 1.05rem;
        color: #111827;
    }

    .status-panel-card p {
        color: #64748b;
        line-height: 1.72;
    }

    @keyframes status-line {
        0%,
        100% {
            stroke-dashoffset: 420;
            opacity: 0.5;
        }

        35%,
        65% {
            stroke-dashoffset: 0;
            opacity: 1;
        }
    }

    @media (prefers-color-scheme: dark) {
        .status-card {
            background: rgba(15, 23, 42, 0.82);
            border-color: rgba(148, 163, 184, 0.16);
            box-shadow: 0 26px 90px rgba(2, 6, 23, 0.5);
        }

        .status-code,
        .status-title,
        .status-panel-card h2 {
            color: #f8fafc;
        }

        .status-description,
        .status-panel-card p {
            color: #cbd5e1;
        }

        .status-eyebrow {
            background: rgba(239, 68, 68, 0.16);
            color: #fca5a5;
        }

        .status-button-secondary {
            background: rgba(15, 23, 42, 0.52);
            border-color: rgba(148, 163, 184, 0.22);
            color: #f8fafc;
        }

        .status-panel-card {
            background:
                linear-gradient(180deg, rgba(15, 23, 42, 0.84), rgba(15, 23, 42, 0.74)),
                linear-gradient(135deg, rgba(239, 68, 68, 0.08), rgba(59, 130, 246, 0.06));
            border-color: rgba(148, 163, 184, 0.14);
        }
    }

    @media (max-width: 840px) {
        .status-card {
            grid-template-columns: 1fr;
        }

        .status-monitor {
            min-height: 12rem;
        }
    }
</style>

<main class="status-page">
    <section class="status-card" aria-labelledby="status-500-title">
        <div class="status-copy">
            <p class="status-eyebrow">Temporary System Issue</p>
            <p class="status-code">500</p>
            <h1 class="status-title" id="status-500-title">Something went wrong on our side.</h1>
            <p class="status-description">
                The request reached LabLife, but the server hit an unexpected problem while trying to finish it. Refreshing in a moment usually fixes temporary issues.
            </p>

            <div class="status-actions">
                <a class="status-button status-button-primary" href="javascript:window.location.reload()">
                    Try Again
                </a>
                <a class="status-button status-button-secondary" href="/bug-report">
                    Report The Issue
                </a>
            </div>
        </div>

        <aside class="status-panel">
            <div class="status-panel-card">
                <div class="status-monitor" aria-hidden="true">
                    <div class="status-monitor-grid"></div>
                    <div class="status-monitor-chip">SERVER</div>
                    <div class="status-monitor-wave">
                        <svg viewBox="0 0 600 120" preserveAspectRatio="none">
                            <path d="M0 68 L48 68 L74 40 L110 92 L146 56 L184 56 L218 68 L254 68 L288 30 L328 96 L368 48 L406 62 L442 62 L480 80 L516 44 L552 68 L600 68" />
                        </svg>
                    </div>
                </div>

                <div>
                    <h2>What you can do now</h2>
                    <p>
                        Retry the page, head back to a stable section like Messages or Discover, or send a bug report if this keeps happening so the issue can be investigated.
                    </p>
                </div>
            </div>
        </aside>
    </section>
</main>

<?php require base_path('Views/shared/footer.php'); ?>
