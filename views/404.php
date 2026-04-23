<?php
require(base_path("views/shared/header.php"));
?>

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
        filter: blur(12px);
        opacity: 0.9;
        pointer-events: none;
    }

    .status-page::before {
        width: 24rem;
        height: 24rem;
        top: -7rem;
        left: -6rem;
        background: radial-gradient(circle, rgba(251, 191, 36, 0.28) 0%, rgba(251, 191, 36, 0) 68%);
    }

    .status-page::after {
        width: 20rem;
        height: 20rem;
        right: -4rem;
        bottom: 0;
        background: radial-gradient(circle, rgba(236, 72, 153, 0.24) 0%, rgba(236, 72, 153, 0) 72%);
    }

    .status-card {
        position: relative;
        z-index: 1;
        width: min(100%, 64rem);
        display: grid;
        grid-template-columns: 1.1fr 0.9fr;
        gap: 2rem;
        padding: 2rem;
        border: 1px solid rgba(148, 163, 184, 0.2);
        border-radius: 2rem;
        background: rgba(255, 255, 255, 0.82);
        box-shadow: 0 24px 80px rgba(15, 23, 42, 0.12);
        backdrop-filter: blur(12px);
    }

    .status-copy {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        justify-content: center;
    }

    .status-eyebrow {
        width: fit-content;
        padding: 0.45rem 0.8rem;
        border-radius: 9999px;
        background: rgba(251, 191, 36, 0.16);
        color: #92400e;
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
        text-shadow: 0 10px 30px rgba(15, 23, 42, 0.1);
    }

    .status-title {
        font-size: clamp(2rem, 4vw, 3.1rem);
        line-height: 1.05;
        letter-spacing: -0.04em;
        color: #111827;
    }

    .status-description {
        max-width: 34rem;
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
        background: linear-gradient(to right, #ec4899, #ef4444);
        color: #fff;
        box-shadow: 0 14px 30px rgba(239, 68, 68, 0.26);
    }

    .status-button-secondary {
        border-color: rgba(148, 163, 184, 0.32);
        background: rgba(255, 255, 255, 0.72);
        color: #0f172a;
    }

    .status-panel {
        position: relative;
        display: flex;
        align-items: stretch;
    }

    .status-panel-card {
        width: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 1.25rem;
        padding: 1.5rem;
        border-radius: 1.5rem;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.86), rgba(255, 255, 255, 0.66)),
            linear-gradient(135deg, rgba(251, 191, 36, 0.14), rgba(236, 72, 153, 0.12));
        border: 1px solid rgba(255, 255, 255, 0.5);
    }

    .status-panel-art {
        display: grid;
        place-items: center;
        min-height: 14rem;
        border-radius: 1.25rem;
        background:
            radial-gradient(circle at top, rgba(251, 191, 36, 0.22), transparent 58%),
            linear-gradient(145deg, #fff7ed, #ffffff);
        overflow: hidden;
    }

    .status-radar {
        position: relative;
        width: 11rem;
        height: 11rem;
        border-radius: 50%;
        background:
            radial-gradient(circle, rgba(251, 191, 36, 0.14) 0%, rgba(251, 191, 36, 0.05) 42%, rgba(255, 255, 255, 0) 43%),
            linear-gradient(135deg, rgba(245, 158, 11, 0.16), rgba(236, 72, 153, 0.18));
        box-shadow: inset 0 0 0 1px rgba(251, 191, 36, 0.18);
    }

    .status-radar::before,
    .status-radar::after {
        content: "";
        position: absolute;
        inset: 1.25rem;
        border-radius: 50%;
        border: 1px solid rgba(245, 158, 11, 0.24);
    }

    .status-radar::after {
        inset: 2.5rem;
    }

    .status-radar-beam {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        background: conic-gradient(from 30deg, rgba(249, 115, 22, 0.52), rgba(249, 115, 22, 0) 24%);
        animation: status-sweep 5s linear infinite;
    }

    .status-radar-dot {
        position: absolute;
        top: 2.3rem;
        right: 2rem;
        width: 0.9rem;
        height: 0.9rem;
        border-radius: 50%;
        background: #f97316;
        box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.45);
        animation: status-pulse 2.2s ease-out infinite;
    }

    .status-panel-card h2 {
        font-size: 1.05rem;
        color: #111827;
    }

    .status-panel-card p {
        color: #64748b;
        line-height: 1.7;
    }

    @keyframes status-sweep {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    @keyframes status-pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.5);
        }

        70% {
            box-shadow: 0 0 0 18px rgba(249, 115, 22, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(249, 115, 22, 0);
        }
    }

    @media (prefers-color-scheme: dark) {
        .status-card {
            background: rgba(15, 23, 42, 0.8);
            border-color: rgba(148, 163, 184, 0.16);
            box-shadow: 0 24px 80px rgba(2, 6, 23, 0.45);
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
            background: rgba(251, 191, 36, 0.18);
            color: #fcd34d;
        }

        .status-button-secondary {
            background: rgba(15, 23, 42, 0.52);
            border-color: rgba(148, 163, 184, 0.22);
            color: #f8fafc;
        }

        .status-panel-card {
            background:
                linear-gradient(180deg, rgba(15, 23, 42, 0.82), rgba(15, 23, 42, 0.72)),
                linear-gradient(135deg, rgba(251, 191, 36, 0.08), rgba(236, 72, 153, 0.08));
            border-color: rgba(148, 163, 184, 0.14);
        }

        .status-panel-art {
            background:
                radial-gradient(circle at top, rgba(251, 191, 36, 0.16), transparent 55%),
                linear-gradient(145deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.96));
        }
    }

    @media (max-width: 840px) {
        .status-card {
            grid-template-columns: 1fr;
        }

        .status-panel {
            order: -1;
        }

        .status-panel-art {
            min-height: 11rem;
        }
    }
</style>

<main class="status-page">
    <section class="status-card" aria-labelledby="status-404-title">
        <div class="status-copy">
            <p class="status-eyebrow">Lost In Transit</p>
            <p class="status-code">404</p>
            <h1 class="status-title" id="status-404-title">We couldn’t find the page you were looking for.</h1>
            <p class="status-description">
                The link may be outdated, the address may be mistyped, or the page may have been moved somewhere else in LabLife.
            </p>

            <div class="status-actions">
                <a class="status-button status-button-primary" href="<?= \Core\Auth::check() ? '/u' : '/' ?>">
                    Return Home
                </a>
                <a class="status-button status-button-secondary" href="<?= \Core\Auth::check() ? '/u/discover' : '/login' ?>">
                    <?= \Core\Auth::check() ? 'Open Discover' : 'Go To Login' ?>
                </a>
            </div>
        </div>

        <aside class="status-panel" aria-label="Helpful recovery panel">
            <div class="status-panel-card">
                <div class="status-panel-art">
                    <div class="status-radar" aria-hidden="true">
                        <div class="status-radar-beam"></div>
                        <div class="status-radar-dot"></div>
                    </div>
                </div>

                <div>
                    <h2>Try one of these next</h2>
                    <p>
                        Head back home, start discovering profiles again, or revisit the last page from your browser history if you were following a saved link.
                    </p>
                </div>
            </div>
        </aside>
    </section>
</main>

<?php require(base_path("views/shared/footer.php")); ?>