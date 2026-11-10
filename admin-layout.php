<?php
/**
 * Left sidebar shared by the admin pages.
 *
 * $active      'dashboard' or 'forms' (which nav item is highlighted)
 * $adminName   shown at the bottom of the sidebar
 * $tabs        open student forms (only used on the Student Forms page). Each tab:
 *              ['name', 'status_label', 'status_class', 'url', 'close_url', 'active']
 * $closeAllUrl link for "Close all"
 */
function adm_h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function render_admin_sidebar($active, $adminName, array $tabs = [], $closeAllUrl = '') {
    ?>
    <aside class="sidebar no-print">
        <div class="sidebar-top">
            <a href="/admin/" class="sidebar-brand">CCST <span>Portal</span></a>
            <p class="sidebar-role">Admin</p>
        </div>

        <nav class="side-nav" aria-label="Admin navigation">
            <a href="/admin/" class="<?php echo $active === 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
            <a href="/admin/forms.php" class="<?php echo $active === 'forms' ? 'active' : ''; ?>">Student Forms</a>
        </nav>

        <?php if ($active === 'forms'): ?>
            <div class="side-section">
                <div class="side-heading">
                    <span>Open forms (<?php echo count($tabs); ?>)</span>
                    <?php if (count($tabs) > 0 && $closeAllUrl !== ''): ?>
                        <a href="<?php echo adm_h($closeAllUrl); ?>" class="side-link">Close all</a>
                    <?php endif; ?>
                </div>

                <?php if (count($tabs) === 0): ?>
                    <p class="tab-empty">No forms open yet. Search for a student and click a result to open their form here.</p>
                <?php else: ?>
                    <ul class="tab-list">
                        <?php foreach ($tabs as $tab): ?>
                            <li class="tab-item <?php echo !empty($tab['active']) ? 'active' : ''; ?>">
                                <a href="<?php echo adm_h($tab['url']); ?>" class="tab-link">
                                    <span class="tab-name"><?php echo adm_h($tab['name']); ?></span>
                                    <span class="badge <?php echo adm_h($tab['status_class']); ?>"><?php echo adm_h($tab['status_label']); ?></span>
                                </a>
                                <a href="<?php echo adm_h($tab['close_url']); ?>" class="tab-close" title="Close this form" aria-label="Close <?php echo adm_h($tab['name']); ?>">&times;</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="sidebar-footer">
            <span class="sidebar-user"><?php echo adm_h($adminName); ?></span>
            <a href="/logout.php" class="btn-logout">Logout</a>
        </div>
    </aside>
    <?php
}