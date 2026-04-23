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
        filter: blur(14px);
        pointer-events: none;
    }

    .status-page::before {
        width: 24rem;
        height: 24rem;
        top: -6rem;
        left: -6rem;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.2) 0%, rgba(59, 130, 246, 0) 68%);
    }

    .status-page::after {
        width: 20rem;
        height: 20rem;
        right: -4rem;
        bottom: -1rem;
        background: radial-gradient(circle, rgba(14, 165, 233, 0.2) 0%, rgba(14, 165, 233, 0) 70%);
    }

    .status-card {
        position: relative;
        z-index: 1;
        width: min(100%, 60rem);
        display: grid;
        grid-template-columns: 1.05fr 0.95fr;
        gap: 2rem;
        padding: 2rem;
        border-radius: 2rem;
        border: 1px solid rgba(148, 163, 184, 0.2);
        background: rgba(255, 255, 255, 0.84);
        box-shadow: 0 26px 90px rgba(15, 23, 42, 0.12);
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
        background: rgba(59, 130, 246, 0.12);
        color: #1d4ed8;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .status-code {
        font-family: "Geist Mono", monospace;
        font-size: clamp(3.25rem, 10vw, 6rem);
        line-height: 0.95;
        color: #0f172a;
    }

    .status-title {
        font-size: clamp(2rem, 4vw, 3rem);
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
    }

    .status-button-primary {
        background: linear-gradient(135deg, #2563eb, #0ea5e9);
        color: #fff;
        box-shadow: 0 14px 30px rgba(37, 99, 235, 0.24);
    }

    .status-button-secondary {
        background: rgba(255, 255, 255, 0.72);
        border-color: rgba(148, 163, 184, 0.32);
        color: #0f172a;
    }

    .status-panel-card {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 1.2rem;
        height: 100%;
        padding: 1.5rem;
        border-radius: 1.5rem;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.88), rgba(255, 255, 255, 0.72)),
            linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(14, 165, 233, 0.06));
        border: 1px solid rgba(255, 255, 255, 0.55);
    }

    .status-meter {
        position: relative;
        min-height: 13rem;
        border-radius: 1.25rem;
        background:
            radial-gradient(circle at top, rgba(59, 130, 246, 0.16), transparent 58%),
            linear-gradient(145deg, #eff6ff, #ffffff);
        overflow: hidden;
        display: grid;
        place-items: center;
    }

    .status-ring {
        position: relative;
        width: 10.5rem;
        height: 10.5rem;
        border-radius: 50%;
        background:
            conic-gradient(#0ea5e9 0deg, #2563eb 250deg, rgba(226, 232, 240, 0.8) 250deg 360deg);
        display: grid;
        place-items: center;
    }

    .status-ring::before {
        content: "";
        width: 7.2rem;
        height: 7.2rem;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.94);
        box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.18);
    }

    .status-ring span {
        position: absolute;
        font-family: "Geist Mono", monospace;
        font-size: 1.1rem;
        color: #0f172a;
        letter-spacing: 0.08em;
    }

    .status-panel-card h2 {
        font-size: 1.05rem;
        color: #111827;
    }

    .status-panel-card p {
        color: #64748b;
        line-height: 1.72;
    }

    @media (prefers-color-scheme: dark) {
        .status-card {
            background: rgba(15, 23, 42, 0.82);
            border-color: rgba(148, 163, 184, 0.16);
            box-shadow: 0 26px 90px rgba(2, 6, 23, 0.5);
        }

        .status-code,
        .status-title,
        .status-panel-card h2,
        .status-ring span {
            color: #f8fafc;
        }

        .status-description,
        .status-panel-card p {
            color: #cbd5e1;
        }

        .status-eyebrow {
            background: rgba(59, 130, 246, 0.16);
            color: #93c5fd;
        }

        .status-button-secondary {
            background: rgba(15, 23, 42, 0.52);
            border-color: rgba(148, 163, 184, 0.22);
            color: #f8fafc;
        }

        .status-panel-card {
            background:
                linear-gradient(180deg, rgba(15, 23, 42, 0.84), rgba(15, 23, 42, 0.74)),
                linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(14, 165, 233, 0.06));
            border-color: rgba(148, 163, 184, 0.14);
        }

        .status-meter {
            background:
                radial-gradient(circle at top, rgba(59, 130, 246, 0.16), transparent 58%),
                linear-gradient(145deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.96));
        }

        .status-ring::before {
            background: rgba(15, 23, 42, 0.94);
            box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.18);
        }
    }

    @media (max-width: 840px) {
        .status-card {
            grid-template-columns: 1fr;
        }
    }
</style>

<main class="status-page">
    <section class="status-card" aria-labelledby="status-429-title">
        <div class="status-copy">
            <p class="status-eyebrow">Slow Down A Bit</p>
            <p class="status-code">429</p>
            <h1 class="status-title" id="status-429-title">Too many requests were sent too quickly.</h1>
            <p class="status-description">
                <?= htmlspecialchars($message ?? 'Please wait a moment before trying again.', ENT_QUOTES, 'UTF-8') ?>
                <?php if (!empty($retryAfter)): ?>
                    Try again in about <?= (int) $retryAfter ?> seconds.
                <?php endif; ?>
            </p>

            <div class="status-actions">
                <a class="status-button status-button-primary" href="<?= \Core\Auth::check() ? '/u' : '/' ?>">
                    Go To Home
                </a>
                <a class="status-button status-button-secondary" href="javascript:window.history.back()">
                    Go Back
                </a>
            </div>
        </div>

        <aside class="status-panel">
            <div class="status-panel-card">
                <div class="status-meter" aria-hidden="true">
                    <div class="status-ring">
                        <span>LIMIT</span>
                    </div>
                </div>

                <div>
                    <h2>Why this happened</h2>
                    <p>
                        Rate limiting protects authenticated actions from abuse and accidental rapid repeats. Waiting briefly before retrying should resolve it.
                    </p>
                </div>
            </div>
        </aside>
    </section>
</main>

<?php require base_path('Views/shared/footer.php'); ?>
