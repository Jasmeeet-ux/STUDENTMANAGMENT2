<?php ob_start(); ?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Student Management</h1>
        <div class="text-sm text-slate-500 mt-1 flex items-center">
            <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <span class="text-slate-700 font-medium">Students</span>
        </div>
    </div>
</div>

<!-- Filters and Actions -->
<div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor mb-6">
    <form method="GET" action="index.php" class="flex flex-wrap gap-4 items-end justify-between">
        <input type="hidden" name="module" value="students">
        <div class="flex flex-wrap items-end gap-4 flex-1">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Search</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Name, Reg No, Email..." class="pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary w-64">
                </div>
            </div>
            <button type="submit" class="bg-primary hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                Search
            </button>
            <?php if (!empty($_GET['search'])): ?>
                <a href="?module=students" class="px-4 py-2 text-danger hover:underline text-sm font-medium">Clear</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Data Table -->
<div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead class="sticky top-0 bg-white">
                <tr class="border-b border-bordercolor text-slate-600 text-xs uppercase tracking-wider font-semibold">
                    <th class="p-4 cursor-pointer hover:text-primary">Student <i class="fas fa-sort ml-1 text-slate-400"></i></th>
                    <th class="p-4 cursor-pointer hover:text-primary">Contact <i class="fas fa-sort ml-1 text-slate-400"></i></th>
                    <th class="p-4 cursor-pointer hover:text-primary">Class/Section <i class="fas fa-sort ml-1 text-slate-400"></i></th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-bordercolor">
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="4" class="p-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <div class="h-16 w-16 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                                    <i class="fas fa-user-slash text-2xl text-slate-400"></i>
                                </div>
                                <p class="text-base font-semibold text-slate-700">No students found</p>
                                <p class="text-sm text-slate-500 mt-1">Try adjusting your search query.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $student): ?>
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="p-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-full bg-blue-100 text-primary flex items-center justify-center font-bold text-sm mr-3">
                                        <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <a href="?module=students&action=show&id=<?php echo $student['id']; ?>" class="font-semibold text-slate-800 text-sm hover:text-primary transition-colors"><?php echo htmlspecialchars($student['name']); ?></a>
                                        <p class="text-xs text-slate-500 font-medium">REG: <?php echo htmlspecialchars($student['reg_no'] ?? 'N/A'); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="text-sm text-slate-600 mb-1"><i class="fas fa-envelope text-slate-400 w-4"></i> <?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></div>
                            </td>
                            <td class="p-4">
                                <div class="text-sm font-medium text-slate-800"><?php echo htmlspecialchars($student['class_name'] ?? 'Unassigned'); ?></div>
                                <div class="text-xs text-slate-500"><?php echo htmlspecialchars($student['section_name'] ?? '-'); ?></div>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end space-x-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="?module=students&action=show&id=<?php echo $student['id']; ?>" class="text-slate-400 hover:text-primary transition-colors" title="View Profile">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="p-4 border-t border-bordercolor flex items-center justify-between bg-slate-50">
        <span class="text-sm text-slate-600">Showing page <span class="font-semibold text-slate-800"><?php echo $page; ?></span> of <?php echo $totalPages; ?></span>
        <div class="flex space-x-1">
            <?php if ($page > 1): ?>
                <a href="?module=students&page=<?php echo $page - 1; ?><?php echo !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="px-3 py-1 border border-slate-200 rounded bg-white text-slate-600 hover:bg-slate-50 text-sm"><i class="fas fa-chevron-left text-xs"></i></a>
            <?php else: ?>
                <button class="px-3 py-1 border border-slate-200 rounded bg-white text-slate-400 cursor-not-allowed text-sm"><i class="fas fa-chevron-left text-xs"></i></button>
            <?php endif; ?>

            <?php
            // Simple pagination logic
            $start_page = max(1, $page - 2);
            $end_page = min($totalPages, $page + 2);
            
            for ($i = $start_page; $i <= $end_page; $i++): 
            ?>
                <?php if ($i == $page): ?>
                    <button class="px-3 py-1 border border-primary rounded bg-primary text-white text-sm font-medium shadow-sm"><?php echo $i; ?></button>
                <?php else: ?>
                    <a href="?module=students&page=<?php echo $i; ?><?php echo !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="px-3 py-1 border border-slate-200 rounded bg-white text-slate-600 hover:bg-slate-50 text-sm font-medium transition-colors"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?module=students&page=<?php echo $page + 1; ?><?php echo !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="px-3 py-1 border border-slate-200 rounded bg-white text-slate-600 hover:bg-slate-50 text-sm"><i class="fas fa-chevron-right text-xs"></i></a>
            <?php else: ?>
                <button class="px-3 py-1 border border-slate-200 rounded bg-white text-slate-400 cursor-not-allowed text-sm"><i class="fas fa-chevron-right text-xs"></i></button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
