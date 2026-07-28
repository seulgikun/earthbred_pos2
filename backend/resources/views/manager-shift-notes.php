<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earthbred - Shift Notes Console</title>
    <meta name="description"
        content="Earthbred Coffee Studio Manager Shift Notes Console. Monitor, filter, and resolve shift logs submitted by staff.">
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/manager.css') ?>?v=<?= time() ?>">
</head>

<body>
    <div class="mgr-app">

        <!-- =========================================
         SIDEBAR
    ========================================= -->
        <aside class="mgr-sidebar">
            <div class="mgr-logo-section">
                <h1 class="mgr-logo-main">earthbred</h1>
                <p class="mgr-logo-sub">Coffee Studio</p>
            </div>

            <div class="mgr-user-profile">
                <i class="fa-solid fa-circle-user mgr-profile-icon"></i>
                <div class="mgr-user-info">
                    <p class="mgr-user-name">Juan Reyes</p>
                    <p class="mgr-user-id">MGR-001</p>
                </div>
            </div>

            <nav class="mgr-nav">
                <h3 class="mgr-nav-heading">NAVIGATION</h3>
                <ul class="mgr-nav-list">
                    <li class="mgr-nav-item" onclick="window.location.href='<?= url('') ?>/manager'">
                        <i class="fa-solid fa-chart-line mgr-nav-icon"></i> Dashboard
                    </li>
                </ul>

                <h3 class="mgr-nav-heading">OPERATIONS</h3>
                <ul class="mgr-nav-list">
                    <li class="mgr-nav-item" onclick="window.location.href='<?= url('') ?>/manager/products'">
                        <i class="fa-solid fa-tags mgr-nav-icon"></i> Product Management
                    </li>
                    <li class="mgr-nav-item active">
                        <i class="fa-solid fa-note-sticky mgr-nav-icon"></i> Shift Notes
                    </li>
                    <li class="mgr-nav-item"
                        onclick="window.location.href='<?= url('') ?>/manager/sales-report'">
                        <i class="fa-solid fa-file-invoice-dollar mgr-nav-icon"></i> Sales Reports
                    </li>
                    <li class="mgr-nav-item"
                        onclick="window.location.href='<?= url('') ?>/manager/inventory'">
                        <i class="fa-solid fa-boxes-stacked mgr-nav-icon"></i> Inventory
                    </li>
                </ul>

                <h3 class="mgr-nav-heading">TOOLS</h3>
                <ul class="mgr-nav-list">
                    <li class="mgr-nav-item" onclick="window.location.href='<?= url('') ?>/manager/ai'">
                        <i class="fa-solid fa-robot mgr-nav-icon"></i> AI Gemini Assistant
                    </li>
                    <li class="mgr-nav-item owner-only-link" onclick="window.location.href='<?= url('') ?>/manager/accounts'">
                        <i class="fa-solid fa-users mgr-nav-icon"></i> Account Management
                    </li>
                </ul>
            </nav>

            <div class="mgr-sidebar-footer">
                <div class="mgr-clock-out" onclick="window.location.href='<?= url('') ?>/login'">
                    <i class="fa-solid fa-power-off"></i> Clock Out
                </div>
            </div>
        </aside>
        <script>
            if (localStorage.getItem('userRole') !== 'owner') {
                document.querySelectorAll('.owner-only-link').forEach(el => el.style.display = 'none');
            }
            if (localStorage.getItem('userName')) {
                document.querySelector('.mgr-user-name').textContent = localStorage.getItem('userName');
            }
        </script>

        <!-- =========================================
         MAIN CONTENT AREA
    ========================================= -->
        <main class="mgr-main">
            <!-- Top header -->
            <header class="mgr-header">
                <h2 class="mgr-page-title">Manager Dashboard</h2>
            </header>

            <!-- Inner Content Scroll Area -->
            <div class="mgr-content">
                <!-- Greeting -->
                <div class="mgr-greeting-wrap">
                    <h3 class="mgr-greeting-title">Shift Notes</h3>
                    <p class="mgr-greeting-sub">All notes submitted by staff across shifts.</p>
                </div>

                <!-- Filter Buttons -->
                <div class="filter-buttons-container">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="general">General</button>
                    <button class="filter-btn" data-filter="equipment">Equipment</button>
                    <button class="filter-btn" data-filter="complaint">Complaint</button>
                    <button class="filter-btn" data-filter="task">Task</button>
                    <button class="filter-btn" data-filter="resolved">Resolved</button>
                </div>

                <!-- Notes List -->
                <div class="mgr-notes-list" id="notesListContainer">
                    <?php if (isset($notes) && $notes->count() > 0): ?>
                        <?php foreach ($notes as $note): ?>
                            <div class="mgr-note-card"
                                data-category="<?= strtolower(htmlspecialchars($note->category ?? 'general')) ?>"
                                data-status="<?= $note->is_done ? 'resolved' : 'unresolved' ?>"
                                style="background: #ffffff; border: 1.5px solid #eadeca; border-radius: 14px; padding: 1.5rem; display: flex; flex-direction: column; gap: 10px; box-shadow: 0 4px 12px rgba(44, 26, 20, 0.03); margin-bottom: 1rem;">

                                <div class="mgr-note-header"
                                    style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <!-- Category Tag -->
                                        <span
                                            class="category-tag tag-<?= strtolower(htmlspecialchars($note->category ?? 'general')) ?>"
                                            style="font-size: 0.72rem; font-weight: 700; padding: 4px 10px; border-radius: 8px; text-transform: uppercase;">
                                            <?= htmlspecialchars($note->category ?? 'General') ?>
                                        </span>
                                        <span class="mgr-note-author"
                                            style="font-weight: 600; color: #2c1a14; font-size: 0.9rem;">
                                            <?= htmlspecialchars($note->cashier_name ?? 'Staff') ?>
                                        </span>
                                    </div>
                                    <span class="mgr-note-time" style="font-size: 0.8rem; color: #8d786c; font-weight: 500;">
                                        <?= $note->created_at->format('M d, h:i A') ?>
                                    </span>
                                </div>

                                <div class="mgr-note-body"
                                    style="font-size: 0.92rem; line-height: 1.6; color: #5c4a40; margin-top: 5px; white-space: pre-wrap;">
                                    <?= htmlspecialchars($note->note) ?>
                                </div>

                                <div class="mgr-note-footer"
                                    style="display: flex; justify-content: flex-end; align-items: center; margin-top: 10px; border-top: 1px solid #f5edd6; padding-top: 10px; min-height: 35px;">
                                    <?php if ($note->is_done): ?>
                                        <span class="status-resolved"
                                            style="color: #2e7d32; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 6px;">
                                            <i class="fa-solid fa-circle-check"></i> Resolved
                                        </span>
                                    <?php else: ?>
                                        <form action="<?= url('') ?>/shift-notes/<?= $note->id ?>/done" method="POST"
                                            style="margin: 0;">
                                            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="_method" value="PATCH">
                                            <button type="submit" class="resolve-btn">
                                                <i class="fa-solid fa-check"></i> Resolve
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state-card"
                            style="display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 4rem 2rem; color: #8d786c; background: #ffffff; border: 1.5px solid #eadeca; border-radius: 14px;">
                            <i class="fa-solid fa-note-sticky"
                                style="font-size: 3rem; margin-bottom: 1rem; color: #eadeca;"></i>
                            <p style="font-weight: 700; font-size: 1.1rem; color: #2c1a14; margin-bottom: 5px;">No shift
                                notes yet</p>
                            <p style="font-size: 0.88rem;">Notes logged by staff from the POS will show up here.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Dynamic JS Empty State (hidden by default) -->
                <div id="filterEmptyState" class="empty-state-card"
                    style="display: none; flex-direction: column; justify-content: center; align-items: center; padding: 4rem 2rem; color: #8d786c; background: #ffffff; border: 1.5px solid #eadeca; border-radius: 14px; margin-top: 1rem;">
                    <i class="fa-solid fa-filter" style="font-size: 3rem; margin-bottom: 1rem; color: #eadeca;"></i>
                    <p style="font-weight: 700; font-size: 1.1rem; color: #2c1a14; margin-bottom: 5px;">No matching
                        notes found</p>
                    <p style="font-size: 0.88rem;">There are no shift notes for the selected filter category.</p>
                </div>

            </div>
        </main>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const noteCards = document.querySelectorAll('.mgr-note-card');
            const filterEmptyState = document.getElementById('filterEmptyState');

            filterButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    // Remove active class from all buttons
                    filterButtons.forEach(b => b.classList.remove('active'));
                    // Add active class to clicked button
                    this.classList.add('active');

                    const filterValue = this.getAttribute('data-filter');
                    let visibleCount = 0;

                    noteCards.forEach(card => {
                        const category = card.getAttribute('data-category');
                        const status = card.getAttribute('data-status');

                        let shouldShow = false;

                        if (filterValue === 'all') {
                            shouldShow = true;
                        } else if (filterValue === 'resolved') {
                            shouldShow = (status === 'resolved');
                        } else {
                            shouldShow = (category === filterValue);
                        }

                        if (shouldShow) {
                            card.style.display = 'block';
                            visibleCount++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    // Handle empty state display
                    if (visibleCount === 0 && noteCards.length > 0) {
                        filterEmptyState.style.display = 'flex';
                    } else {
                        filterEmptyState.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>

</html>
